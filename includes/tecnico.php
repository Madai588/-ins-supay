<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';

function exigir_tecnico(): void { exigir_rol('administrador', 'luthier'); }
function validar_post_csrf(): void { if ($_SERVER['REQUEST_METHOD'] === 'POST' && !validar_csrf($_POST['csrf'] ?? null)) { http_response_code(419); exit('Token CSRF inválido.'); } }
function generar_codigo_instrumento(PDO $db, int $id): string { return 'SUPAY-INS-' . str_pad((string)$id, 6, '0', STR_PAD_LEFT); }
function obtener_instrumento(PDO $db, int $id): ?array { $s=$db->prepare('SELECT i.*, c.nombre AS cliente_nombre FROM instrumento i INNER JOIN cliente c ON c.id_cliente=i.id_cliente WHERE i.id_instrumento=?'); $s->execute([$id]); return $s->fetch() ?: null; }
