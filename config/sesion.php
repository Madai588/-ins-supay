<?php
declare(strict_types=1);
require_once __DIR__ . '/constantes.php';
require_once __DIR__ . '/produccion.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]); session_start(); }
if (isset($_SESSION['ultima_actividad']) && time()-(int)$_SESSION['ultima_actividad'] > SESION_MAX_INACTIVIDAD) { $_SESSION=[]; session_destroy(); session_start(); }
$_SESSION['ultima_actividad']=time();
