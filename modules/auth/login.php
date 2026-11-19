<?php
require_once __DIR__.'/../../includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim((string) ($_POST['usuario'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($nombre_usuario === '' || $password === '') {
        $error = 'Completa usuario y contraseña.';
    } elseif (!password_cumple_politica($password)) {
        $error = 'La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.';
    } else {
        $s = obtener_conexion()->prepare('SELECT * FROM usuario WHERE usuario=? AND activo=1 LIMIT 1');
        $s->execute([$nombre_usuario]);
        $u = $s->fetch();
        $bloqueado = $u && $u['bloqueado_hasta'] && strtotime($u['bloqueado_hasta']) > time();
        if (!$u || $bloqueado || !password_verify($password, $u['password_hash'])) {
            if ($u && !$bloqueado) {
                $n = (int) $u['intentos_fallidos'] + 1;
                $hasta = $n >= 3 ? date('Y-m-d H:i:s', strtotime('+'.BLOQUEO_MINUTOS.' minutes')) : null;
                obtener_conexion()->prepare('UPDATE usuario SET intentos_fallidos=?,bloqueado_hasta=? WHERE id_usuario=?')->execute([$n >= 3 ? 0 : $n, $hasta, $u['id_usuario']]);
            }
            registrar_bitacora('login_fallido', 'Usuario: '.$nombre_usuario);
            $error = 'Credenciales inválidas o cuenta bloqueada temporalmente.';
        } else {
            session_regenerate_id(true);
            $_SESSION['usuario'] = ['id_usuario'=>$u['id_usuario'], 'nombre'=>$u['nombre'], 'usuario'=>$u['usuario'], 'rol'=>$u['rol']];
            obtener_conexion()->prepare('UPDATE usuario SET intentos_fallidos=0,bloqueado_hasta=NULL WHERE id_usuario=?')->execute([$u['id_usuario']]);
            registrar_bitacora('login_exitoso');
            redirigir($u['rol'] === 'administrador' ? '/supay/modules/admin/index.php' : '/supay/modules/tecnico/index.php');
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SUPAY | Acceso</title>
    <link rel="stylesheet" href="/supay/assets/vendor/bootstrap.min.css">
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>/assets/vendor/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="/supay/assets/css/supay.css?v=20261110-12">
</head>
<body>
<main class="login-page">
    <div class="login-scene login-scene-static" aria-hidden="true">
        <div class="scene-orbit scene-orbit-one"></div>
        <div class="scene-orbit scene-orbit-two"></div>
        <div class="scene-glow"></div>
    </div>
    <section class="card login-card">
        <div class="card-body p-4 p-sm-5">
            <div class="text-center login-header">
                <img class="login-logo mx-auto mb-3" src="<?= e(BASE_URL) ?>/assets/img/supay-logo.png" alt="Logo SUPAY">
                <div class="login-brand">SUPAY</div>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert"><i class="bi bi-exclamation-triangle-fill"></i><span><?= e($error) ?></span></div>
            <?php endif; ?>
            <form class="login-form" method="post" novalidate>
                <div class="mb-3">
                    <label class="form-label" for="login-user">Usuario</label>
                    <div class="input-icon"><i class="bi bi-person-fill"></i><input class="form-control" id="login-user" name="usuario" autocomplete="username" required autofocus></div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="login-password">Contraseña</label>
                    <div class="input-icon"><i class="bi bi-lock-fill"></i><input class="form-control" id="login-password" type="password" name="password" autocomplete="current-password" required><button class="password-toggle" type="button" data-password-toggle="#login-password" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button></div>
                </div>
                <button class="btn btn-primary w-100 login-submit" type="submit"><i class="bi bi-box-arrow-in-right"></i><span>Ingresar al sistema</span></button>
            </form>
            <div class="login-footer"><i class="bi bi-shield-check"></i><span>Acceso protegido para personal autorizado</span><span class="login-dot"></span><span>SUPAY 2.0</span></div>
        </div>
    </section>
</main>
<script src="/supay/assets/vendor/bootstrap.bundle.min.js"></script>
<script src="/supay/assets/js/ui.js"></script>
</body>
</html>
