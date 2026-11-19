<?php
declare(strict_types=1);
require_once __DIR__.'/../config/sesion.php';
require_once __DIR__.'/../config/conexion.php';
require_once __DIR__.'/helpers.php';
function usuario_actual(): ?array { return $_SESSION['usuario'] ?? null; }
function exigir_login(): void { if (!usuario_actual()) redirigir('/supay/modules/auth/login.php'); }
function exigir_rol(string ...$roles): void { exigir_login(); if (!in_array($_SESSION['usuario']['rol'],$roles,true)) { http_response_code(403); exit('Acceso denegado.'); } }
function registrar_bitacora(string $accion,string $detalle=''): void { try { $s=obtener_conexion()->prepare('INSERT INTO bitacora (id_usuario,accion,detalle,ip) VALUES (?,?,?,?)'); $s->execute([$_SESSION['usuario']['id_usuario']??null,$accion,$detalle,$_SERVER['REMOTE_ADDR']??null]); } catch(Throwable $e) { error_log($e->getMessage()); } }
