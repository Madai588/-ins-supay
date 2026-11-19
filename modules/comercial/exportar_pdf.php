<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/comercial.php';
require_once __DIR__ . '/../../includes/pdf_reporte.php';

exigir_comercial_admin();
$db = obtener_conexion();
$tipo = (string)($_GET['tipo'] ?? '');
$periodo = (string)($_GET['periodo'] ?? 'mensual');
$hoy = new DateTimeImmutable('today');
if ($periodo === 'semanal') {
    $inicio = $hoy->modify('-6 days');
    $fin = $hoy;
} elseif ($periodo === 'anual') {
    $inicio = $hoy->setDate((int)$hoy->format('Y'), 1, 1);
    $fin = $hoy;
} elseif ($periodo === 'personalizado' && fecha_valida($_GET['desde'] ?? '') && fecha_valida($_GET['hasta'] ?? '')) {
    $inicio = new DateTimeImmutable((string)$_GET['desde']);
    $fin = new DateTimeImmutable((string)$_GET['hasta']);
} else {
    $inicio = $hoy->modify('first day of this month');
    $fin = $hoy;
    $periodo = 'mensual';
}
$desde = $inicio->format('Y-m-d');
$hasta = $fin->format('Y-m-d');
$rango = $desde . ' a ' . $hasta;

if (!in_array($tipo, ['ingresos', 'stock', 'ventas', 'reparaciones'], true)) {
    http_response_code(400);
    exit('Tipo de reporte PDF invalido.');
}

$datosReporte = [
    'ingresos' => ['Reporte de ingresos', 'Resumen financiero de ventas y cobros del taller.'],
    'stock' => ['Reporte de inventario', 'Existencias, niveles mínimos y proveedores de materiales.'],
    'ventas' => ['Reporte de ventas', 'Detalle de las ventas registradas en el sistema.'],
    'reparaciones' => ['Reporte de reparaciones', 'Seguimiento de servicios técnicos y costos del taller.'],
];
[$tituloReporte, $subtituloReporte] = $datosReporte[$tipo];
$pdf = new SupayPdf($tituloReporte, $rango, $subtituloReporte);
$nombre = 'supay_' . $tipo . '_' . date('Ymd_His') . '.pdf';

if ($tipo === 'ingresos') {
    $ingresos = ingresos_periodo($db, $desde . ' 00:00:00', $hasta . ' 23:59:59');
    $ventas = $db->prepare('SELECT COUNT(*) FROM venta WHERE fecha>=? AND fecha<=?');
    $ventas->execute([$desde . ' 00:00:00', $hasta . ' 23:59:59']);
    $cobros = $db->prepare('SELECT COUNT(*) FROM cobro WHERE fecha>=? AND fecha<=?');
    $cobros->execute([$desde . ' 00:00:00', $hasta . ' 23:59:59']);
    $pdf->heading('Resumen de ingresos');
    $pdf->summary('INGRESOS TOTALES DEL PERIODO', 'Bs ' . number_format((float)$ingresos['total'], 2, '.', ''));
    $pdf->table(['CONCEPTO', 'CANTIDAD'], [['Ventas', (string)$ventas->fetchColumn()], ['Cobros de servicios', (string)$cobros->fetchColumn()]], [1]);
    $s = $db->prepare("SELECT fecha, 'Venta' AS concepto, total AS monto FROM venta WHERE fecha>=? AND fecha<=? UNION ALL SELECT fecha, 'Cobro' AS concepto, monto AS monto FROM cobro WHERE fecha>=? AND fecha<=? ORDER BY fecha DESC");
    $s->execute([$desde . ' 00:00:00', $hasta . ' 23:59:59', $desde . ' 00:00:00', $hasta . ' 23:59:59']);
    $rows = [];
    foreach ($s->fetchAll() as $r) $rows[] = [date('d/m/Y', strtotime((string)$r['fecha'])), (string)$r['concepto'], 'Bs ' . number_format((float)$r['monto'], 2, '.', '')];
    $pdf->heading('Detalle de movimientos de ingreso');
    $rows ? $pdf->table(['FECHA', 'CONCEPTO', 'MONTO'], $rows, [2]) : $pdf->note('No hay ingresos registrados en el periodo seleccionado.');
} elseif ($tipo === 'stock') {
    $stock = $db->query('SELECT m.nombre,m.categoria,m.stock,m.stock_minimo,p.nombre AS proveedor_nombre FROM material m LEFT JOIN proveedor p ON p.id_proveedor=m.id_proveedor ORDER BY m.stock<=m.stock_minimo DESC,m.nombre')->fetchAll();
    $pdf->heading('Inventario y niveles minimos');
    $rows = [];
    foreach ($stock as $r) $rows[] = [(string)$r['nombre'], (string)$r['categoria'], number_format((float)$r['stock'], 2, '.', ''), number_format((float)$r['stock_minimo'], 2, '.', ''), (string)($r['proveedor_nombre'] ?? '-')];
    $rows ? $pdf->table(['MATERIAL', 'CATEGORIA', 'STOCK', 'MINIMO', 'PROVEEDOR'], $rows, [2, 3]) : $pdf->note('No hay materiales registrados.');
} elseif ($tipo === 'ventas') {
    $s = $db->query('SELECT v.fecha,c.nombre AS cliente,i.codigo_qr,v.total FROM venta v INNER JOIN cliente c ON c.id_cliente=v.id_cliente LEFT JOIN instrumento i ON i.id_instrumento=v.id_instrumento ORDER BY v.fecha DESC');
    $rows = [];
    foreach ($s->fetchAll() as $r) $rows[] = [date('d/m/Y', strtotime((string)$r['fecha'])), (string)$r['nombre'], (string)($r['codigo_qr'] ?? '-'), 'Bs ' . number_format((float)$r['total'], 2, '.', '')];
    $pdf->heading('Ventas registradas');
    $rows ? $pdf->table(['FECHA', 'CLIENTE', 'INSTRUMENTO', 'TOTAL'], $rows, [3]) : $pdf->note('No hay ventas registradas.');
} else {
    $s = $db->query('SELECT r.fecha_ingreso,r.estado,i.codigo_qr,r.costo,u.nombre AS tecnico FROM reparacion r INNER JOIN instrumento i ON i.id_instrumento=r.id_instrumento INNER JOIN usuario u ON u.id_usuario=r.id_usuario ORDER BY r.fecha_ingreso DESC');
    $rows = [];
    foreach ($s->fetchAll() as $r) $rows[] = [date('d/m/Y', strtotime((string)$r['fecha_ingreso'])), (string)$r['estado'], (string)$r['codigo_qr'], 'Bs ' . number_format((float)$r['costo'], 2, '.', ''), (string)$r['tecnico']];
    $pdf->heading('Reparaciones por estado');
    $rows ? $pdf->table(['FECHA', 'ESTADO', 'INSTRUMENTO', 'COSTO', 'TECNICO'], $rows, [3]) : $pdf->note('No hay reparaciones registradas.');
}

registrar_bitacora('reporte_exportado_pdf', $tipo);
$pdf->output($nombre);
