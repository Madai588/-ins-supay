<?php
require_once __DIR__.'/../../includes/inventario.php';
require_once __DIR__.'/../../includes/comercial.php';
exigir_inventario_admin();
$db = obtener_conexion();
$minimos = materiales_en_minimo($db);
$resumen = ingresos_periodo($db, date('Y-m-01 00:00:00'), date('Y-m-t 23:59:59'));
$ventas_mes = (int) $db->query("SELECT COUNT(*) FROM venta WHERE fecha >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')")->fetchColumn();
$reparaciones_activas = (int) $db->query("SELECT COUNT(*) FROM reparacion WHERE estado <> 'finalizado'")->fetchColumn();
$instrumentos_total = (int) $db->query('SELECT COUNT(*) FROM instrumento')->fetchColumn();
$titulo = 'Panel administrador';
$es_panel = true;
require_once __DIR__.'/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Panel principal</h2><p class="text-muted mb-0">Resumen general del taller y accesos rápidos.</p></div></div>
<?php if ($minimos): ?>
    <div class="alert alert-warning d-flex align-items-start mb-4"><i class="bi bi-exclamation-triangle-fill"></i><div><strong>Alerta de stock mínimo (<?= count($minimos) ?>)</strong><ul class="mb-2 mt-1"><?php foreach ($minimos as $m): ?><li><?= e($m['nombre']) ?>: <?= e((string) $m['stock']) ?> disponibles de <?= e((string) $m['stock_minimo']) ?> mínimos.</li><?php endforeach; ?></ul><a class="btn btn-sm btn-warning" href="/supay/modules/tecnico/movimientos.php"><i class="bi bi-box-arrow-in-down"></i> Registrar reposición</a></div></div>
<?php else: ?>
    <div class="alert alert-success d-flex align-items-center mb-4"><i class="bi bi-check-circle-fill"></i><strong>Inventario sin alertas.</strong><span class="ms-1">Todos los materiales superan su stock mínimo.</span></div>
<?php endif; ?>
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3"><div class="stat-card stat-primary"><i class="bi bi-currency-dollar"></i><div class="stat-value">Bs <?= number_format((float) $resumen['total'], 2, '.', '') ?></div><div class="stat-label">Ingresos del mes</div><small><?= $ventas_mes ?> ventas registradas</small></div></div>
    <div class="col-12 col-sm-6 col-xl-3"><div class="stat-card stat-warning"><i class="bi bi-tools"></i><div class="stat-value"><?= $reparaciones_activas ?></div><div class="stat-label">Reparaciones activas</div></div></div>
    <div class="col-12 col-sm-6 col-xl-3"><div class="stat-card stat-danger"><i class="bi bi-exclamation-diamond"></i><div class="stat-value"><?= count($minimos) ?></div><div class="stat-label">Materiales en mínimo</div></div></div>
    <div class="col-12 col-sm-6 col-xl-3"><div class="stat-card stat-success"><i class="bi bi-music-note-list"></i><div class="stat-value"><?= $instrumentos_total ?></div><div class="stat-label">Instrumentos registrados</div></div></div>
</div>
<div class="page-heading"><div><h2>Accesos rápidos</h2><p class="text-muted mb-0">Selecciona un módulo para continuar.</p></div></div>
<div class="row g-3">
<?php $accesos = [
    ['/supay/modules/tecnico/clientes.php', 'Clientes', 'bi-people', 'Gestionar clientes'],
    ['/supay/modules/tecnico/instrumentos.php', 'Instrumentos', 'bi-music-note-beamed', 'Registrar instrumentos'],
    ['/supay/modules/tecnico/reparaciones.php', 'Reparaciones', 'bi-tools', 'Control técnico'],
    ['/supay/modules/tecnico/escaner.php', 'Escáner QR', 'bi-qr-code-scan', 'Consultar historial'],
    ['/supay/modules/tecnico/materiales.php', 'Materiales', 'bi-box-seam', 'Consultar inventario'],
    ['/supay/modules/tecnico/movimientos.php', 'Movimientos', 'bi-arrow-left-right', 'Entradas y salidas'],
    ['/supay/modules/tecnico/proveedores.php', 'Proveedores', 'bi-truck', 'Gestionar proveedores'],
    ['/supay/modules/comercial/ventas.php', 'Ventas', 'bi-cart3', 'Registrar ventas'],
    ['/supay/modules/comercial/cobros.php', 'Cobros', 'bi-cash-coin', 'Registrar cobros'],
    ['/supay/modules/comercial/reportes.php', 'Reportes', 'bi-bar-chart-line', 'Consultar indicadores'],
    ['/supay/modules/admin/respaldos.php', 'Respaldos', 'bi-database-check', 'Proteger la información'],
    ['/supay/modules/admin/usuarios.php', 'Usuarios', 'bi-person-gear', 'Administración de accesos'],
]; foreach ($accesos as $a): ?>
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3"><a class="card quick-card" href="<?= e($a[0]) ?>"><i class="bi <?= e($a[2]) ?>"></i><span><strong><?= e($a[1]) ?></strong><small class="d-block text-muted"><?= e($a[3]) ?></small></span></a></div>
<?php endforeach; ?>
</div>
<?php require_once __DIR__.'/../../includes/footer.php';
