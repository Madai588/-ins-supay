<?php
require_once __DIR__ . '/../../includes/inventario.php';
exigir_inventario_consulta();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    exigir_inventario_admin();
    validar_post_csrf();
}

$db = obtener_conexion();
$error = '';
$ok = $_GET['ok'] ?? '';
$acciones_validas = ['crear', 'editar', 'eliminar'];

if ($ok === 'material_eliminado') {
    $ok = 'Material eliminado correctamente.';
} else {
    $ok = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = (string) ($_POST['accion'] ?? '');

    try {
        if (!in_array($accion, $acciones_validas, true)) {
            throw new InvalidArgumentException('La acción solicitada no es válida.');
        }

        // Eliminar solo necesita el ID; no debe validar los campos del formulario de edición.
        if ($accion === 'eliminar') {
            $id_material = (int) ($_POST['id_material'] ?? 0);
            if ($id_material <= 0) {
                throw new InvalidArgumentException('No se identificó el material que deseas eliminar.');
            }

            $sentencia = $db->prepare('DELETE FROM material WHERE id_material = ?');
            $sentencia->execute([$id_material]);

            if ($sentencia->rowCount() !== 1) {
                throw new InvalidArgumentException('El material ya no existe o no pudo eliminarse.');
            }

            registrar_bitacora('material_eliminado', 'ID ' . $id_material);
            header('Location: materiales.php?ok=material_eliminado');
            exit;
        }

        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $categoria = (string) ($_POST['categoria'] ?? '');
        $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_FLOAT);
        $minimo = filter_var($_POST['stock_minimo'] ?? null, FILTER_VALIDATE_FLOAT);
        $categorias = ['maderas', 'cueros', 'pernos', 'herrajes'];

        if ($nombre === '' || !in_array($categoria, $categorias, true) || $stock === false || $minimo === false || $stock < 0 || $minimo < 0) {
            throw new InvalidArgumentException('Nombre, categoría y cantidades válidas son obligatorios.');
        }

        $id_proveedor = (int) ($_POST['id_proveedor'] ?? 0);
        $id_proveedor = $id_proveedor > 0 ? $id_proveedor : null;

        if ($accion === 'crear') {
            $sentencia = $db->prepare('INSERT INTO material(nombre, categoria, stock, stock_minimo, id_proveedor) VALUES (?, ?, ?, ?, ?)');
            $sentencia->execute([$nombre, $categoria, $stock, $minimo, $id_proveedor]);
            registrar_bitacora('material_creado', 'ID ' . $db->lastInsertId());
            $ok = 'Material creado correctamente.';
        } else {
            $id_material = (int) ($_POST['id_material'] ?? 0);
            if ($id_material <= 0) {
                throw new InvalidArgumentException('No se identificó el material que deseas editar.');
            }

            $sentencia = $db->prepare('UPDATE material SET nombre = ?, categoria = ?, stock = ?, stock_minimo = ?, id_proveedor = ? WHERE id_material = ?');
            $sentencia->execute([$nombre, $categoria, $stock, $minimo, $id_proveedor, $id_material]);
            registrar_bitacora('material_actualizado', 'ID ' . $id_material);
            $ok = 'Material actualizado correctamente.';
        }
    } catch (Throwable $e) {
        if ($e instanceof InvalidArgumentException) {
            $error = $e->getMessage();
        } elseif ($e instanceof PDOException && $e->getCode() === '23000') {
            $error = 'No se puede eliminar el material porque tiene movimientos o repuestos asociados.';
        } else {
            error_log('SUPAY materiales: ' . $e->getMessage());
            $error = 'No se pudo completar la operación. Revisa la conexión y los datos.';
        }
    }
}

$buscar = trim((string) ($_GET['buscar'] ?? ''));
$categoria_filtro = (string) ($_GET['categoria'] ?? '');
$pagina = max(1, (int) ($_GET['pagina'] ?? 1));
$limite = 8;
$offset = ($pagina - 1) * $limite;
$condiciones = [];
$parametros = [];

if ($buscar !== '') {
    $condiciones[] = '(m.nombre LIKE ? OR p.nombre LIKE ?)';
    $parametros[] = '%' . $buscar . '%';
    $parametros[] = '%' . $buscar . '%';
}
if (in_array($categoria_filtro, ['maderas', 'cueros', 'pernos', 'herrajes'], true)) {
    $condiciones[] = 'm.categoria = ?';
    $parametros[] = $categoria_filtro;
}

$where = $condiciones ? ' WHERE ' . implode(' AND ', $condiciones) : '';
$sentencia = $db->prepare('SELECT COUNT(*) FROM material m LEFT JOIN proveedor p ON p.id_proveedor = m.id_proveedor' . $where);
$sentencia->execute($parametros);
$total = (int) $sentencia->fetchColumn();
$sentencia = $db->prepare('SELECT m.*, p.nombre AS proveedor_nombre FROM material m LEFT JOIN proveedor p ON p.id_proveedor = m.id_proveedor' . $where . ' ORDER BY m.stock <= m.stock_minimo DESC, m.nombre LIMIT ' . $limite . ' OFFSET ' . $offset);
$sentencia->execute($parametros);
$materiales = $sentencia->fetchAll();
$proveedores = $db->query('SELECT id_proveedor, nombre FROM proveedor ORDER BY nombre')->fetchAll();

$editar = null;
if (isset($_GET['editar']) && usuario_actual()['rol'] === 'administrador') {
    $sentencia = $db->prepare('SELECT * FROM material WHERE id_material = ?');
    $sentencia->execute([(int) $_GET['editar']]);
    $editar = $sentencia->fetch() ?: null;
}

$minimos = materiales_en_minimo($db);
$formatear_cantidad = static function ($valor): string {
    $texto = number_format((float) $valor, 2, '.', '');
    return rtrim(rtrim($texto, '0'), '.');
};
$titulo = 'Materiales';
require __DIR__ . '/../../includes/header.php';
?>
<div class="page-heading"><div><h2><i class="bi bi-box-seam"></i> Materiales</h2><p>Control de existencias, categorías y niveles mínimos.</p></div><div class="btn-toolbar"><a class="btn btn-primary" href="movimientos.php"><i class="bi bi-arrow-left-right"></i> Movimientos</a><?php if (usuario_actual()['rol'] === 'administrador'): ?><a class="btn btn-outline-primary" href="proveedores.php"><i class="bi bi-truck"></i> Proveedores</a><?php endif; ?></div></div>
<?php if ($error): ?><div class="alert alert-danger d-flex align-items-center" data-auto-dismiss="5000" role="alert"><i class="bi bi-exclamation-triangle-fill"></i><span><?= e($error) ?></span></div><?php endif; ?>
<?php if ($ok): ?><div class="alert alert-success d-flex align-items-center" data-auto-dismiss="4000" role="alert"><i class="bi bi-check-circle-fill"></i><span><?= e($ok) ?></span></div><?php endif; ?>
<?php if ($minimos): ?><div class="alert alert-warning d-flex align-items-center" role="alert"><i class="bi bi-exclamation-triangle-fill"></i><strong>Alerta de stock mínimo:</strong><span class="ms-1"><?= count($minimos) ?> material(es) requieren atención.</span></div><?php endif; ?>
<?php if (usuario_actual()['rol'] === 'administrador'): ?><section class="card mb-4"><div class="card-header"><h3><i class="bi bi-box-seam"></i> <?= $editar ? 'Editar material' : 'Nuevo material' ?></h3></div><div class="card-body"><form method="post" autocomplete="off"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="accion" value="<?= $editar ? 'editar' : 'crear' ?>"><input type="hidden" name="id_material" value="<?= $editar['id_material'] ?? '' ?>"><div class="row g-3"><div class="col-12 col-md-6"><label class="form-label">Nombre <span class="required">*</span></label><input class="form-control" name="nombre" value="<?= e($editar['nombre'] ?? '') ?>" required></div><div class="col-12 col-md-6"><label class="form-label">Categoría <span class="required">*</span></label><select class="form-select" name="categoria" required><?php foreach (['maderas', 'cueros', 'pernos', 'herrajes'] as $categoria): ?><option value="<?= $categoria ?>" <?= $editar && $editar['categoria'] === $categoria ? 'selected' : '' ?>><?= ucfirst($categoria) ?></option><?php endforeach; ?></select></div><div class="col-12 col-md-4"><label class="form-label">Stock</label><input class="form-control" type="number" name="stock" min="0" step="0.01" value="<?= e($formatear_cantidad($editar['stock'] ?? 0)) ?>" required></div><div class="col-12 col-md-4"><label class="form-label">Stock mínimo</label><input class="form-control" type="number" name="stock_minimo" min="0" step="0.01" value="<?= e($formatear_cantidad($editar['stock_minimo'] ?? 0)) ?>" required></div><div class="col-12 col-md-4"><label class="form-label">Proveedor</label><select class="form-select" name="id_proveedor"><option value="">Sin proveedor</option><?php foreach ($proveedores as $proveedor): ?><option value="<?= $proveedor['id_proveedor'] ?>" <?= $editar && $editar['id_proveedor'] == $proveedor['id_proveedor'] ? 'selected' : '' ?>><?= e($proveedor['nombre']) ?></option><?php endforeach; ?></select></div></div><div class="form-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Guardar material</button><?php if ($editar): ?><a class="btn btn-cancel" href="materiales.php"><i class="bi bi-x-circle"></i> Cancelar</a><?php endif; ?></div></form></div></section><?php endif; ?>
<section class="card mb-4"><div class="card-body"><form class="row g-2 align-items-end" method="get"><div class="col-12 col-md-6"><label class="form-label">Buscar material</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input class="form-control" name="buscar" value="<?= e($buscar) ?>" placeholder="Material o proveedor"></div></div><div class="col-12 col-md-4"><label class="form-label">Categoría</label><select class="form-select" name="categoria"><option value="">Todas las categorías</option><?php foreach (['maderas', 'cueros', 'pernos', 'herrajes'] as $categoria): ?><option value="<?= $categoria ?>" <?= $categoria_filtro === $categoria ? 'selected' : '' ?>><?= ucfirst($categoria) ?></option><?php endforeach; ?></select></div><div class="col-12 col-md-2 d-grid"><button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i> Filtrar</button></div></form></div></section>
<section class="card table-card"><div class="card-header d-flex justify-content-between align-items-center"><h3><i class="bi bi-list-ul"></i> Inventario</h3><span class="badge bg-light text-primary"><?= $total ?> registros</span></div><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Material</th><th>Categoría</th><th class="stock">Stock</th><th class="stock">Mínimo</th><th>Proveedor</th><?php if (usuario_actual()['rol'] === 'administrador'): ?><th>Acciones</th><?php endif; ?></tr></thead><tbody><?php foreach ($materiales as $material): ?><tr class="<?= $material['stock'] <= $material['stock_minimo'] ? 'table-warning' : '' ?>"><td class="fw-semibold"><?= e($material['nombre']) ?> <?php if ($material['stock'] <= $material['stock_minimo']): ?><span class="badge stock-min">Stock mínimo</span><?php endif; ?></td><td><?= e(ucfirst($material['categoria'])) ?></td><td class="stock"><?= e($formatear_cantidad($material['stock'])) ?></td><td class="stock"><?= e($formatear_cantidad($material['stock_minimo'])) ?></td><td><?= e($material['proveedor_nombre']) ?></td><?php if (usuario_actual()['rol'] === 'administrador'): ?><td><div class="table-actions"><a class="btn btn-edit btn-sm" title="Editar material" data-bs-toggle="tooltip" href="?editar=<?= $material['id_material'] ?>"><i class="bi bi-pencil-square"></i><span>Editar</span></a><button class="btn btn-danger btn-sm" type="button" title="Eliminar material" data-bs-toggle="modal" data-bs-target="#modalEliminarMaterial" data-delete-id="<?= $material['id_material'] ?>" data-delete-target="#idMaterialEliminar"><i class="bi bi-trash"></i><span>Eliminar</span></button></div></td><?php endif; ?></tr><?php endforeach; ?><?php if (!$materiales): ?><tr><td colspan="6"><div class="empty-state"><i class="bi bi-box-seam"></i><p>No hay materiales registrados.</p></div></td></tr><?php endif; ?></tbody></table></div><?php $paginas = max(1, (int) ceil($total / $limite)); if ($paginas > 1): ?><nav aria-label="Paginación"><ul class="pagination"><?php for ($i = 1; $i <= $paginas; $i++): ?><li class="page-item <?= $i === $pagina ? 'active' : '' ?>"><a class="page-link" href="?buscar=<?= urlencode($buscar) ?>&categoria=<?= urlencode($categoria_filtro) ?>&pagina=<?= $i ?>"><?= $i ?></a></li><?php endfor; ?></ul></nav><?php endif; ?></section>
<?php if (usuario_actual()['rol'] === 'administrador'): ?><div class="modal fade" id="modalEliminarMaterial" tabindex="-1" aria-labelledby="modalEliminarMaterialLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="modalEliminarMaterialLabel"><i class="bi bi-trash text-danger"></i> Confirmar eliminación</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body">¿Seguro que deseas eliminar este material?</div><div class="modal-footer"><button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancelar</button><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id_material" id="idMaterialEliminar"><button class="btn btn-danger" type="submit"><i class="bi bi-trash"></i> Eliminar</button></form></div></div></div></div><?php endif; ?>
<?php require __DIR__ . '/../../includes/footer.php';
