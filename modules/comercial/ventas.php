<?php
require_once __DIR__.'/../../includes/comercial.php';

exigir_comercial_admin();
validar_post_csrf();

$db = obtener_conexion();
$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $cliente = (int)($_POST['id_cliente'] ?? 0);
        $instrumento = (int)($_POST['id_instrumento'] ?? 0);
        $total = $_POST['total'] ?? null;
        $fecha = trim((string)($_POST['fecha'] ?? ''));

        if (!$cliente || !$instrumento || !$fecha) {
            throw new InvalidArgumentException(
                'Complete todos los campos obligatorios.'
            );
        }

        if (!fecha_valida($fecha) || !dinero_positivo($total)) {
            throw new InvalidArgumentException(
                'Ingrese una fecha válida y un monto mayor que cero.'
            );
        }

        $s = $db->prepare(
            'SELECT id_cliente FROM cliente WHERE id_cliente = ?'
        );
        $s->execute([$cliente]);

        if (!$s->fetch()) {
            throw new InvalidArgumentException(
                'El cliente seleccionado no está registrado.'
            );
        }

        $s = $db->prepare(
            'SELECT id_instrumento FROM instrumento
             WHERE id_instrumento = ?'
        );
        $s->execute([$instrumento]);

        if (!$s->fetch()) {
            throw new InvalidArgumentException(
                'El instrumento seleccionado no está disponible.'
            );
        }

        $s = $db->prepare(
            'INSERT INTO venta
            (id_cliente, id_instrumento, fecha, total)
            VALUES (?, ?, ?, ?)'
        );

        $s->execute([
            $cliente,
            $instrumento,
            $fecha . ' 00:00:00',
            (float)$total
        ]);

        $idVenta = (int)$db->lastInsertId();

        registrar_bitacora(
            'venta_creada',
            'Registro de venta número ' . $idVenta
        );

        $ok = 'Venta N.º ' . $idVenta .
              ' registrada correctamente.';

    } catch (Throwable $e) {
        $error = $e instanceof InvalidArgumentException
            ? $e->getMessage()
            : 'No fue posible completar el registro de la venta.';
    }
}

// Parámetros de búsqueda
$buscar = trim((string)($_GET['buscar'] ?? ''));
$desde = (string)($_GET['desde'] ?? '');
$hasta = (string)($_GET['hasta'] ?? '');

$pagina = max(1, (int)($_GET['pagina'] ?? 1));
$limite = 8;
$offset = ($pagina - 1) * $limite;

$condiciones = [];
$params = [];

if ($buscar !== '') {
    $condiciones[] = '(
        c.nombre LIKE ?
        OR i.codigo_qr LIKE ?
        OR i.tipo LIKE ?
    )';

    $texto = '%' . $buscar . '%';
    $params = array_merge($params, [$texto, $texto, $texto]);
}

if (fecha_valida($desde)) {
    $condiciones[] = 'v.fecha >= ?';
    $params[] = $desde . ' 00:00:00';
}

if (fecha_valida($hasta)) {
    $condiciones[] = 'v.fecha <= ?';
    $params[] = $hasta . ' 23:59:59';
}

$where = $condiciones
    ? ' WHERE ' . implode(' AND ', $condiciones)
    : '';

// Cantidad de ventas
$sql = '
    SELECT COUNT(*)
    FROM venta v
    INNER JOIN cliente c ON c.id_cliente = v.id_cliente
    LEFT JOIN instrumento i ON i.id_instrumento = v.id_instrumento
    ' . $where;

$s = $db->prepare($sql);
$s->execute($params);

$total_registros = (int)$s->fetchColumn();

// Obtener registros
$sql = '
    SELECT
        v.id_venta,
        v.fecha,
        v.total,
        c.nombre AS cliente_nombre,
        i.codigo_qr,
        i.tipo
    FROM venta v
    INNER JOIN cliente c ON c.id_cliente = v.id_cliente
    LEFT JOIN instrumento i ON i.id_instrumento = v.id_instrumento
    ' . $where . '
    ORDER BY v.fecha DESC, v.id_venta DESC
    LIMIT ' . $limite . ' OFFSET ' . $offset;

$s = $db->prepare($sql);
$s->execute($params);

$ventas = $s->fetchAll();

// Datos de los selectores
$clientes = $db->query(
    'SELECT id_cliente, nombre FROM cliente ORDER BY nombre ASC'
)->fetchAll();

$instrumentos = $db->query(
    'SELECT id_instrumento, codigo_qr, tipo
     FROM instrumento ORDER BY codigo_qr ASC'
)->fetchAll();

$titulo = 'Ventas';

require __DIR__.'/../../includes/header.php';
?>

<div class="page-heading">
    <div>
        <h2><i class="bi bi-cart3"></i> Administración de ventas</h2>
        <p>Control y registro de ventas de instrumentos terminados.</p>
    </div>

    <a class="btn btn-outline-primary" href="reportes.php">
        <i class="bi bi-bar-chart-line"></i> Reportes
    </a>
</div>

<?php if ($error): ?>
<div class="alert alert-danger" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <?= e($error) ?>
</div>
<?php endif; ?>

<?php if ($ok): ?>
<div class="alert alert-success" role="alert">
    <i class="bi bi-check-circle-fill"></i>
    <?= e($ok) ?>
</div>
<?php endif; ?>

<section class="card mb-4">
    <div class="card-header">
        <h3><i class="bi bi-cart-plus"></i> Nueva venta</h3>
    </div>

    <div class="card-body">
        <form method="post" onsubmit="return validarVenta()">

            <input type="hidden"
                   name="csrf"
                   value="<?= e(csrf_token()) ?>">

            <div class="row g-3">

                <div class="col-12 col-md-6">
                    <label class="form-label">Cliente *</label>

                    <select class="form-select"
                            name="id_cliente"
                            required>
                        <option value="">Seleccione un cliente</option>

                        <?php foreach ($clientes as $c): ?>
                        <option value="<?= (int)$c['id_cliente'] ?>">
                            <?= e($c['nombre']) ?>
                        </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Instrumento *</label>

                    <select class="form-select"
                            name="id_instrumento"
                            required>
                        <option value="">Seleccione un instrumento</option>

                        <?php foreach ($instrumentos as $i): ?>
                        <option value="<?= (int)$i['id_instrumento'] ?>">
                            <?= e($i['codigo_qr'].' - '.$i['tipo']) ?>
                        </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Fecha de venta *</label>

                    <input class="form-control"
                           type="date"
                           name="fecha"
                           value="<?= e(date('Y-m-d')) ?>"
                           required>
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label">Importe total *</label>

                    <div class="input-group">
                        <span class="input-group-text">Bs</span>

                        <input class="form-control"
                               id="total-venta"
                               type="number"
                               name="total"
                               min="0.01"
                               step="0.01"
                               placeholder="0.00"
                               required>
                    </div>
                </div>

            </div>

            <div class="form-actions">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-check-circle"></i>
                    Guardar venta
                </button>

                <button class="btn btn-outline-secondary" type="reset">
                    <i class="bi bi-eraser"></i>
                    Limpiar campos
                </button>
            </div>

        </form>
    </div>
</section>

<section class="card mb-4">
    <div class="card-body">

        <form class="row g-2 align-items-end" method="get">

            <div class="col-12 col-md-6">
                <label class="form-label">Buscar registros</label>

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>

                    <input class="form-control"
                           name="buscar"
                           value="<?= e($buscar) ?>"
                           placeholder="Cliente, código QR o instrumento">
                </div>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Desde</label>
                <input class="form-control"
                       type="date"
                       name="desde"
                       value="<?= e($desde) ?>">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Hasta</label>
                <input class="form-control"
                       type="date"
                       name="hasta"
                       value="<?= e($hasta) ?>">
            </div>

            <div class="col-12 col-md-2 d-grid">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
            </div>

        </form>

    </div>
</section>

<section class="card table-card">

    <div class="card-header">
        <h3><i class="bi bi-receipt"></i> Historial de ventas</h3>
        <span class="text-muted">
            Registros encontrados: <?= $total_registros ?>
        </span>
    </div>

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>
                <tr>
                    <th>N.º Venta</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Instrumento</th>
                    <th class="monto">Total</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($ventas as $v): ?>
                <tr>

                    <td>
                        <strong>#<?= (int)$v['id_venta'] ?></strong>
                    </td>

                    <td><?= e($v['fecha']) ?></td>

                    <td><?= e($v['cliente_nombre']) ?></td>

                    <td>
                        <?= e(($v['codigo_qr'] ?? 'Sin código')
                            . ' - ' . ($v['tipo'] ?? 'Sin instrumento')) ?>
                    </td>

                    <td class="monto">
                        Bs <?= e(number_format((float)$v['total'], 2)) ?>
                    </td>

                </tr>
                <?php endforeach; ?>

                <?php if (!$ventas): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state">
                            <i class="bi bi-inbox"></i>
                            <p>No existen ventas que coincidan con la búsqueda.</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <?php
    $paginas = max(1, (int)ceil($total_registros / $limite));

    if ($paginas > 1):
    ?>

    <nav aria-label="Paginación">
        <ul class="pagination">

            <?php for ($i = 1; $i <= $paginas; $i++): ?>

            <li class="page-item <?= $i === $pagina ? 'active' : '' ?>">
                <a class="page-link"
                   href="?buscar=<?= urlencode($buscar) ?>&desde=<?= urlencode($desde) ?>&hasta=<?= urlencode($hasta) ?>&pagina=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>

            <?php endfor; ?>

        </ul>
    </nav>

    <?php endif; ?>

</section>

<script>
function validarVenta() {
    const total = document.getElementById('total-venta');

    if (!total || total.value.trim() === '' ||
        !Number.isFinite(Number(total.value)) ||
        Number(total.value) <= 0) {

        alert('El importe debe ser numérico y mayor que cero.');
        total?.focus();

        return false;
    }

    return true;
}
</script>

<?php require __DIR__.'/../../includes/footer.php'; ?>