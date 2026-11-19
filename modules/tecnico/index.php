<?php
require_once __DIR__.'/../../includes/auth.php';
exigir_rol('administrador', 'luthier');
$db = obtener_conexion();
$estados = ['en_revision'=>0, 'en_reparacion'=>0, 'finalizado'=>0];
$consulta = $db->query("SELECT estado, COUNT(*) total FROM reparacion GROUP BY estado");
foreach ($consulta as $fila) { if (array_key_exists($fila['estado'], $estados)) { $estados[$fila['estado']] = (int) $fila['total']; } }
$titulo = 'Panel luthier';
$es_panel = true;
require_once __DIR__.'/../../includes/header.php';
?>
<div class="page-heading"><div><h2>Panel principal</h2><p class="text-muted mb-0">Herramientas técnicas para el trabajo diario del taller.</p></div></div>
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4"><div class="stat-card stat-warning"><i class="bi bi-search"></i><div class="stat-value"><?= $estados['en_revision'] ?></div><div class="stat-label">En revisión</div></div></div>
    <div class="col-12 col-sm-4"><div class="stat-card stat-primary"><i class="bi bi-tools"></i><div class="stat-value"><?= $estados['en_reparacion'] ?></div><div class="stat-label">En reparación</div></div></div>
    <div class="col-12 col-sm-4"><div class="stat-card stat-success"><i class="bi bi-check2-circle"></i><div class="stat-value"><?= $estados['finalizado'] ?></div><div class="stat-label">Finalizadas</div></div></div>
</div>
<div class="card scanner-card mb-4"><div class="card-body text-center p-4"><i class="bi bi-qr-code-scan display-3 text-primary"></i><h2 class="h4 mt-3">Consultar historial por QR</h2><p class="text-muted">Escanea la etiqueta de un instrumento o escribe su código.</p><a class="btn btn-primary btn-lg" href="/supay/modules/tecnico/escaner.php"><i class="bi bi-camera-fill"></i> Abrir escáner QR</a></div></div>
<div class="page-heading"><div><h2>Accesos técnicos</h2><p class="text-muted mb-0">Registros y consultas frecuentes.</p></div></div>
<div class="row g-3">
<?php $accesos = [
    ['/supay/modules/tecnico/clientes.php', 'Clientes', 'bi-people'],
    ['/supay/modules/tecnico/instrumentos.php', 'Instrumentos', 'bi-music-note-beamed'],
    ['/supay/modules/tecnico/reparaciones.php', 'Reparaciones', 'bi-tools'],
    ['/supay/modules/tecnico/escaner.php', 'Escáner QR', 'bi-qr-code-scan'],
    ['/supay/modules/tecnico/materiales.php', 'Materiales', 'bi-box-seam'],
]; foreach ($accesos as $a): ?>
    <div class="col-12 col-sm-6 col-lg-4"><a class="card quick-card" href="<?= e($a[0]) ?>"><i class="bi <?= e($a[2]) ?>"></i><strong><?= e($a[1]) ?></strong><i class="bi bi-chevron-right ms-auto fs-6"></i></a></div>
<?php endforeach; ?>
</div>
<?php require_once __DIR__.'/../../includes/footer.php';
