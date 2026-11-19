<?php
require_once __DIR__.'/auth.php';
$titulo = $titulo ?? 'Panel';
$ruta = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
$es_panel = $es_panel ?? false;
$rol = usuario_actual()['rol'] ?? '';
$base_url = defined('BASE_URL') ? BASE_URL : '/supay';
$menu_tecnico = [
    [$base_url.'/modules/tecnico/clientes.php', 'Clientes', 'bi-people-fill'],
    [$base_url.'/modules/tecnico/instrumentos.php', 'Instrumentos', 'bi-music-note-beamed'],
    [$base_url.'/modules/tecnico/reparaciones.php', 'Reparaciones', 'bi-tools'],
    [$base_url.'/modules/tecnico/escaner.php', 'Escáner QR', 'bi-qr-code-scan'],
    [$base_url.'/modules/tecnico/materiales.php', 'Materiales', 'bi-box-seam'],
];
$menu_admin = [
    [$base_url.'/modules/tecnico/movimientos.php', 'Movimientos', 'bi-arrow-left-right'],
    [$base_url.'/modules/tecnico/proveedores.php', 'Proveedores', 'bi-truck'],
    [$base_url.'/modules/comercial/ventas.php', 'Ventas', 'bi-cart3'],
    [$base_url.'/modules/comercial/cobros.php', 'Cobros', 'bi-cash-coin'],
    [$base_url.'/modules/comercial/reportes.php', 'Reportes', 'bi-bar-chart-line'],
    [$base_url.'/modules/admin/respaldos.php', 'Respaldos', 'bi-database-check'],
    [$base_url.'/modules/admin/usuarios.php', 'Usuarios', 'bi-person-gear'],
];
$es_activo = static fn(string $url): bool => $ruta === parse_url($url, PHP_URL_PATH) || str_starts_with($ruta, (string) parse_url($url, PHP_URL_PATH).'?');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SUPAY | <?= e($titulo) ?></title>
    <link rel="stylesheet" href="<?= e($base_url) ?>/assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="<?= e($base_url) ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= e($base_url) ?>/assets/css/supay.css?v=20261110-12">
    <style>
      .table.table-hover tbody tr:hover > td,
      .table.table-hover tbody tr:hover > th { background: #1e3a4f !important; background-color: #1e3a4f !important; color: #fff !important; transition: background-color .2s ease; }
      .table.table-hover tbody tr.table-warning > td,
      .table.table-hover tbody tr.table-warning > th { background: #542c25 !important; background-color: #542c25 !important; color: #fff !important; }
      .table.table-hover tbody tr.table-warning:hover > td,
      .table.table-hover tbody tr.table-warning:hover > th { background: #6b2a25 !important; background-color: #6b2a25 !important; color: #fff !important; }
      .table.table-hover tbody tr { transition: background-color .2s ease; }
      .table.table-hover th:last-child,
      .table.table-hover td:last-child { width: 1% !important; white-space: nowrap !important; text-align: right !important; }
      .table.table-hover .table-actions { display: flex !important; justify-content: flex-end !important; align-items: center !important; gap: .35rem !important; white-space: nowrap !important; }
      .table.table-hover .table-actions .btn { display: inline-flex !important; width: auto !important; min-width: 88px !important; height: 34px !important; justify-content: center !important; align-items: center !important; margin: 0 !important; padding: .38rem .58rem !important; white-space: nowrap !important; }
      @media (max-width: 575.98px) { .table.table-hover .table-actions .btn { width: 35px !important; min-width: 35px !important; padding-inline: .3rem !important; } }
    </style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar" id="supaySidebar" aria-label="Navegación principal">
        <a class="brand" href="<?= e($base_url) ?>/" aria-label="SUPAY, panel principal">
            <img class="brand-logo" src="<?= e($base_url) ?>/assets/img/supay-logo.png" alt="Logo SUPAY">
            <span><strong>SUPAY</strong></span>
        </a>
        <div class="sidebar-section">Principal</div>
        <a class="nav-link <?= $es_panel ? 'active' : '' ?>" href="<?= e($base_url) ?>/"><i class="bi bi-grid-1x2-fill"></i><span>Panel principal</span></a>
        <div class="sidebar-section">Gestión técnica</div>
        <?php foreach ($menu_tecnico as $item): ?>
            <a class="nav-link <?= $es_activo($item[0]) ? 'active' : '' ?>" href="<?= e($item[0]) ?>"><i class="bi <?= e($item[2]) ?>"></i><span><?= e($item[1]) ?></span></a>
        <?php endforeach; ?>
        <?php if ($rol === 'administrador'): ?>
            <div class="sidebar-section">Administración</div>
            <?php foreach ($menu_admin as $item): ?>
                <a class="nav-link <?= $es_activo($item[0]) ? 'active' : '' ?>" href="<?= e($item[0]) ?>"><i class="bi <?= e($item[2]) ?>"></i><span><?= e($item[1]) ?></span></a>
            <?php endforeach; ?>
        <?php endif; ?>
    </aside>
    <div class="sidebar-backdrop" data-sidebar-close></div>
    <div class="app-main">
        <nav class="topbar">
            <div class="topbar-left">
                <button class="btn btn-light d-lg-none" type="button" data-sidebar-toggle aria-label="Abrir menú"><i class="bi bi-list"></i></button>
                <span class="topbar-title"><?= e($titulo) ?></span>
            </div>
            <div class="topbar-right">
                <span class="system-status d-none d-md-inline-flex"><i class="bi bi-circle-fill"></i> Sistema operativo</span>
                <div class="user-info"><i class="bi bi-person-circle"></i><strong><?= e(usuario_actual()['nombre'] ?? '') ?></strong></div>
                <span class="badge badge-role"><?= e(ucfirst((string) $rol)) ?></span>
                <a class="btn btn-outline-secondary btn-sm" href="<?= e($base_url) ?>/modules/auth/logout.php"><i class="bi bi-box-arrow-right"></i><span class="d-none d-sm-inline">Cerrar sesión</span></a>
            </div>
        </nav>
        <main class="page-content">
            <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= e($base_url) ?>/"><i class="bi bi-house-door-fill"></i> Inicio</a></li><li class="breadcrumb-item active" aria-current="page"><?= e($titulo) ?></li></ol></nav>
