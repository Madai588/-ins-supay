<?php
declare(strict_types=1);
require_once __DIR__.'/auth.php';
function exigir_comercial_admin(): void { exigir_rol('administrador'); }
if(!function_exists('validar_post_csrf')){function validar_post_csrf(): void { if($_SERVER['REQUEST_METHOD']==='POST'&&!validar_csrf($_POST['csrf']??null)){http_response_code(419);exit('Token CSRF invalido.');} }}
function fecha_valida(?string $fecha): bool { if(!$fecha)return false; $d=DateTime::createFromFormat('Y-m-d',$fecha);return $d&&$d->format('Y-m-d')===$fecha; }
function dinero_positivo(mixed $valor): bool { return is_numeric($valor)&&(float)$valor>0; }
function ingresos_periodo(PDO $db,string $desde,string $hasta): array { $s=$db->prepare('SELECT COALESCE((SELECT SUM(total) FROM venta WHERE fecha>=? AND fecha<=?),0)+COALESCE((SELECT SUM(monto) FROM cobro WHERE fecha>=? AND fecha<=?),0) AS total');$s->execute([$desde,$hasta,$desde,$hasta]);return $s->fetch()?:['total'=>0]; }
