<?php require_once __DIR__.'/../../includes/auth.php'; registrar_bitacora('logout'); $_SESSION=[]; session_destroy(); redirigir('/supay/modules/auth/login.php');
