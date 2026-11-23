<?php
require_once __DIR__ . '/../../includes/comercial.php';
exigir_comercial_admin();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$db = obtener_conexion();
$error = '';
$ok = '';

/* ---------------------------------------------------------
 * Mensaje flash tras redirección
 * --------------------------------------------------------- */
if (!empty($_SESSION['flash_cobros'])) {
    $ok = (string) $_SESSION['flash_cobros'];
    unset($_SESSION['flash_cobros']);
}

/* ---------------------------------------------------------
 * Registrar cobro (POST)
 * --------------------------------------------------------- */
$form = [
    'id_reparacion' => '',
    'monto'         => '',
    'fecha'         => date('Y-m-d'),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_post_csrf();

    $form['id_reparacion'] = (string) ($_POST['id_reparacion'] ?? '');
    $form['monto']         = trim((string) ($_POST['monto'] ?? ''));
    $form['fecha']         = trim((string) ($_POST['fecha'] ?? ''));

    try {
        $reparacion = (int) $form['id_reparacion'];
        $monto      = $form['monto'];
        $fecha      = $form['fecha'];

        // Campos obligatorios
        $faltantes = [];
        if (!$reparacion)  $faltantes[] = 'reparación';
        if ($monto === '') $faltantes[] = 'monto';
        if ($fecha === '') $faltantes[] = 'fecha';
        if ($faltantes) {
            throw new InvalidArgumentException('Faltan campos obligatorios: ' . implode(', ', $faltantes) . '.');
        }

        // Fecha y monto
        if (!fecha_valida($fecha)) {
            throw new InvalidArgumentException('La fecha no es válida.');
        }
        if ($fecha > date('Y-m-d')) {
            throw new InvalidArgumentException('La fecha del cobro no puede ser futura.');
        }
        if (!dinero_positivo($monto)) {
            throw new InvalidArgumentException('El monto debe ser numérico y mayor que 0.');
        }
        if ((float) $monto > 9999999.99) {
            throw new InvalidArgumentException('El monto excede el máximo permitido.');
        }

        // La reparación existe
        $s = $db->prepare('SELECT 1 FROM reparacion WHERE id_reparacion = ?');
        $s->execute([$reparacion]);
        if (!$s->fetchColumn()) {
            throw new InvalidArgumentException('La reparación seleccionada no existe.');
        }

        // Guardar
        $s = $db->prepare('INSERT INTO cobro (id_reparacion, monto, fecha) VALUES (?, ?, ?)');
        $s->execute([$reparacion, round((float) $monto, 2), $fecha . ' 00:00:00']);

        registrar_bitacora('cobro_creado', 'ID ' . $db->lastInsertId());

        // Post/Redirect/Get: evita duplicados al refrescar
        $_SESSION['flash_cobros'] = 'Cobro registrado correctamente.';
        header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    } catch (Throwable $e) {
        if ($e instanceof InvalidArgumentException) {
            $error = $e->getMessage();
        } else {
            error_log('[cobros.php] ' . $e->getMessage());
            $error = 'No se pudo registrar el cobro.';
        }
    }
}

/* ---------------------------------------------------------
 * Filtros y paginación (GET)
 * --------------------------------------------------------- */
$desde = (string) ($_GET['desde'] ?? '');
$hasta = (string) ($_GET['hasta'] ?? '');

if (!fecha_valida($desde)) $desde = '';
if (!fecha_valida($hasta)) $hasta = '';
if ($desde !== '' && $hasta !== '' && $desde > $hasta) {
    [$desde, $hasta] = [$hasta, $desde];
}

$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$limite = 8;

$cond   = [];
$params = [];

if ($desde !== '') {
    $cond[]   = 'c.fecha >= ?';
    $params[] = $desde . ' 00:00:00';
}
if ($hasta !== '') {
    $cond[]   = 'c.fecha <= ?';
    $params[] = $hasta . ' 23:59:59';
}

$where = $cond ? ' WHERE ' . implode(' AND ', $cond) : '';

// Conteo y suma del filtro actual
$s = $db->prepare('SELECT COUNT(*), COALESCE(SUM(c.monto), 0) FROM cobro c' . $where);
$s->execute($params);
[$total_registros, $suma_filtro] = array_map('floatval', $s->fetch(PDO::FETCH_NUM));
$total_registros = (int) $total_registros;

$paginas = max(1, (int) ceil($total_registros / $limite));
$pagina  = min($pagina, $paginas);
$offset  = ($pagina - 1) * $limite;

$s = $db->prepare(
    'SELECT c.id_cobro, c.id_reparacion, c.monto, c.fecha, r.diagnostico, i.codigo_qr, i.tipo
     FROM cobro c
     INNER JOIN reparacion r ON r.id_reparacion = c.id_reparacion
     INNER JOIN instrumento i ON i.id_instrumento = r.id_instrumento'
    . $where
    . ' ORDER BY c.fecha DESC, c.id_cobro DESC LIMIT ' . (int) $limite . ' OFFSET ' . (int) $offset
);
$s->execute($params);
$cobros = $s->fetchAll();

// Lista de reparaciones para el formulario
$reparaciones = $db->query(
    'SELECT r.id_reparacion, r.fecha_ingreso, i.codigo_qr, i.tipo
     FROM reparacion r
     INNER JOIN instrumento i ON i.id_instrumento = r.id_instrumento
     ORDER BY r.fecha_ingreso DESC, r.id_reparacion DESC'
)->fetchAll();

// Helper para enlaces de paginación conservando filtros
$url_pagina = static function (int $p) use ($desde, $hasta): string {
    return '?' . http_build_query([
        'desde'  => $desde,
        'hasta'  => $hasta,
        'pagina' => $p,
    ]);
};

$hay_filtros = ($desde !== '' || $hasta !== '');

$titulo = 'Cobros';
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading">
    <div>
        <h2><i class="bi bi-cash-coin"></i> Cobros de servicios</h2>
        <p>Registra los pagos asociados a reparaciones.</p>
    </div>
    <a class="btn btn-outline-primary" href="reportes.php"><i class="bi bi-bar-chart-line"></i> Reportes</a>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger d-flex align-items-center" role="alert" data-auto-dismiss="4000">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?>
    </div>
<?php endif; ?>
<?php if ($ok): ?>
    <div class="alert alert-success d-flex align-items-center" role="status" data-auto-dismiss="4000">
        <i class="bi bi-check-circle-fill me-2"></i><?= e($ok) ?>
    </div>
<?php endif; ?>

<section class="card mb-4">
    <div class="card-header"><h3><i class="bi bi-wallet2"></i> Registrar cobro</h3></div>
    <div class="card-body">
        <form method="post" id="form-cobro" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label" for="id-reparacion">Reparación <span class="required">*</span></label>
                    <select class="form-select" id="id-reparacion" name="id_reparacion" required>
                        <option value=""><?= $reparaciones ? 'Selecciona' : 'No hay reparaciones registradas' ?></option>
                        <?php foreach ($reparaciones as $r): ?>
                            <option value="<?= (int) $r['id_reparacion'] ?>" <?= (string) $r['id_reparacion'] === $form['id_reparacion'] ? 'selected' : '' ?>>
                                #<?= (int) $r['id_reparacion'] ?> - <?= e($r['codigo_qr'] . ' ' . $r['tipo']) ?>
                                (<?= e(date('d/m/Y', strtotime((string) $r['fecha_ingreso']))) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="monto-cobro">Monto (Bs) <span class="required">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">Bs</span>
                        <input class="form-control" id="monto-cobro" type="number" name="monto"
                               min="0.01" max="9999999.99" step="0.01" inputmode="decimal"
                               value="<?= e($form['monto']) ?>" required>
                    </div>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label" for="fecha-cobro">Fecha <span class="required">*</span></label>
                    <input class="form-control" type="date" id="fecha-cobro" name="fecha"
                           value="<?= e($form['fecha']) ?>" max="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="form-actions">
                <button class="btn btn-success" type="submit"><i class="bi bi-check2-circle"></i> Guardar cobro</button>
            </div>
        </form>
    </div>
</section>

<section class="card mb-4">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get">
            <div class="col-6 col-md-5">
                <label class="form-label" for="desde">Desde</label>
                <input class="form-control" type="date" id="desde" name="desde" value="<?= e($desde) ?>">
            </div>
            <div class="col-6 col-md-5">
                <label class="form-label" for="hasta">Hasta</label>
                <input class="form-control" type="date" id="hasta" name="hasta" value="<?= e($hasta) ?>">
            </div>
            <div class="col-12 col-md-2 d-grid">
                <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
            </div>
            <?php if ($hay_filtros): ?>
                <div class="col-12">
                    <a class="btn btn-link p-0" href="cobros.php"><i class="bi bi-x-circle"></i> Limpiar filtros</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
</section>

<section class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3><i class="bi bi-list-ul"></i> Cobros registrados</h3>
        <small class="text-muted">
            <?= $total_registros ?> <?= $total_registros === 1 ? 'cobro' : 'cobros' ?>
            · Total: <strong>Bs <?= number_format($suma_filtro, 2, '.', ',') ?></strong>
        </small>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Reparación</th>
                    <th>Instrumento</th>
                    <th class="monto">Monto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cobros as $c): ?>
                    <tr>
                        <td><?= e(date('d/m/Y', strtotime((string) $c['fecha']))) ?></td>
                        <td>#<?= (int) $c['id_reparacion'] ?></td>
                        <td><?= e($c['codigo_qr'] . ' - ' . $c['tipo']) ?></td>
                        <td class="monto">Bs <?= number_format((float) $c['monto'], 2, '.', ',') ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$cobros): ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="bi bi-cash-coin"></i>
                                <p><?= $hay_filtros
                                        ? 'No se encontraron cobros en ese rango de fechas.'
                                        : 'Aún no hay cobros registrados.' ?></p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($paginas > 1): ?>
        <nav aria-label="Paginación">
            <ul class="pagination">
                <li class="page-item <?= $pagina <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e($url_pagina(max(1, $pagina - 1))) ?>" aria-label="Anterior">&laquo;</a>
                </li>
                <?php for ($i = 1; $i <= $paginas; $i++): ?>
                    <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                        <a class="page-link" href="<?= e($url_pagina($i)) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $pagina >= $paginas ? 'disabled' : '' ?>">
                    <a class="page-link" href="<?= e($url_pagina(min($paginas, $pagina + 1))) ?>" aria-label="Siguiente">&raquo;</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</section>

<script>
document.getElementById('form-cobro').addEventListener('submit', function (ev) {
    const reparacion = this.id_reparacion.value;
    const fecha      = this.fecha.value;
    const monto      = Number(this.monto.value);
    let mensaje = '';

    if (!reparacion)                                mensaje = 'Selecciona una reparación.';
    else if (!fecha)                                mensaje = 'Indica la fecha del cobro.';
    else if (fecha > this.fecha.max)                mensaje = 'La fecha no puede ser futura.';
    else if (!Number.isFinite(monto) || monto <= 0) mensaje = 'El monto debe ser numérico y mayor que 0.';

    if (mensaje) {
        ev.preventDefault();
        alert(mensaje);
    }
});
</script>
<?php require __DIR__ . '/../../includes/footer.php'; ?>