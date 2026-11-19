<?php
declare(strict_types=1);
require_once __DIR__.'/tecnico.php';

function exigir_inventario_admin(): void { exigir_rol('administrador'); }
function exigir_inventario_consulta(): void { exigir_rol('administrador','luthier'); }
function materiales_en_minimo(PDO $db): array { return $db->query('SELECT m.*, p.nombre AS proveedor_nombre FROM material m LEFT JOIN proveedor p ON p.id_proveedor=m.id_proveedor WHERE m.stock <= m.stock_minimo ORDER BY m.stock ASC, m.nombre ASC')->fetchAll(); }
function decimal_positivo(mixed $valor): bool { return is_numeric($valor) && (float)$valor > 0; }
