<?php
require_once __DIR__.'/../../includes/tecnico.php'; exigir_tecnico(); validar_post_csrf(); $db=obtener_conexion(); $error='';
if($_SERVER['REQUEST_METHOD']==='POST') { try { $accion=$_POST['accion']??''; if($accion==='crear'){ $n=trim((string)$_POST['nombre']); if($n==='') throw new InvalidArgumentException('El nombre es obligatorio.'); $s=$db->prepare('INSERT INTO cliente(nombre,telefono,direccion) VALUES(?,?,?)'); $s->execute([$n,trim((string)$_POST['telefono']),trim((string)$_POST['direccion'])]); registrar_bitacora('cliente_creado','ID '.$db->lastInsertId()); }
 elseif($accion==='editar'){ $s=$db->prepare('UPDATE cliente SET nombre=?,telefono=?,direccion=? WHERE id_cliente=?'); $s->execute([trim((string)$_POST['nombre']),trim((string)$_POST['telefono']),trim((string)$_POST['direccion']), (int)$_POST['id_cliente']]); registrar_bitacora('cliente_actualizado','ID '.$_POST['id_cliente']); }
 elseif($accion==='eliminar'){ $s=$db->prepare('DELETE FROM cliente WHERE id_cliente=?'); $s->execute([(int)$_POST['id_cliente']]); registrar_bitacora('cliente_eliminado','ID '.$_POST['id_cliente']); } } catch(Throwable $e){ $error=$e instanceof InvalidArgumentException?$e->getMessage():'No se puede modificar el cliente; puede tener instrumentos asociados.'; } }
$buscar=trim((string)($_GET['buscar']??'')); $pagina=max(1,(int)($_GET['pagina']??1)); $limite=8; $offset=($pagina-1)*$limite; $where=$buscar!==''?' WHERE nombre LIKE ? OR telefono LIKE ?':''; $params=$buscar!==''?['%'.$buscar.'%','%'.$buscar.'%']:[]; $s=$db->prepare('SELECT COUNT(*) FROM cliente'.$where);$s->execute($params);$total=(int)$s->fetchColumn();$s=$db->prepare('SELECT * FROM cliente'.$where.' ORDER BY id_cliente DESC LIMIT '.$limite.' OFFSET '.$offset);$s->execute($params);$clientes=$s->fetchAll(); $titulo='Clientes'; require __DIR__.'/../../includes/header.php';
?>
<div class="page-heading">
    <div><h2><i class="bi bi-people-fill"></i> Clientes</h2><p>Registra y consulta la información de los clientes del taller.</p></div>
    <a class="btn btn-outline-primary" href="<?= e(BASE_URL) ?>/modules/tecnico/instrumentos.php"><i class="bi bi-music-note-beamed"></i> Ver instrumentos</a>
</div>
<?php if($error): ?><div class="alert alert-danger d-flex align-items-center" data-auto-dismiss="4000"><i class="bi bi-exclamation-triangle-fill"></i><?=e($error)?></div><?php endif; ?>
<section class="card mb-4">
    <div class="card-header"><h3><i class="bi bi-person-plus-fill"></i> Nuevo cliente</h3></div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="accion" value="crear">
            <div class="row g-3">
                <div class="col-12 col-md-4"><label class="form-label" for="nombre">Nombre <span class="required">*</span></label><input class="form-control" id="nombre" name="nombre" required></div>
                <div class="col-12 col-md-4"><label class="form-label" for="telefono">Teléfono</label><div class="input-group"><span class="input-group-text"><i class="bi bi-telephone"></i></span><input class="form-control" id="telefono" name="telefono"></div></div>
                <div class="col-12 col-md-4"><label class="form-label" for="direccion">Dirección</label><div class="input-group"><span class="input-group-text"><i class="bi bi-geo-alt"></i></span><input class="form-control" id="direccion" name="direccion"></div></div>
            </div>
            <div class="form-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-person-plus"></i> Guardar cliente</button></div>
        </form>
    </div>
</section>
<section class="card mb-4">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="get">
            <div class="col-12 col-md-9"><label class="form-label" for="buscar">Buscar cliente</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input class="form-control" id="buscar" name="buscar" value="<?=e($buscar)?>" placeholder="Nombre o teléfono"></div></div>
            <div class="col-12 col-md-3 d-grid"><button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button></div>
        </form>
    </div>
</section>
<section class="card table-card">
    <div class="card-header d-flex justify-content-between align-items-center"><h3><i class="bi bi-list-ul"></i> Clientes registrados</h3><span class="badge bg-light text-primary"><?= $total ?> registros</span></div>
    <div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Nombre</th><th>Teléfono</th><th>Dirección</th><th>Acciones</th></tr></thead><tbody>
    <?php foreach($clientes as $c): ?><tr><td class="fw-semibold"><?=e($c['nombre'])?></td><td><?=e($c['telefono'])?></td><td><?=e($c['direccion'])?></td><td><div class="table-actions"><a class="btn btn-edit btn-sm" title="Ver instrumentos" data-bs-toggle="tooltip" href="instrumentos.php?cliente=<?=$c['id_cliente']?>"><i class="bi bi-music-note-beamed"></i><span>Instrumentos</span></a><button class="btn btn-danger btn-sm" type="button" title="Eliminar cliente" data-bs-toggle="modal" data-bs-target="#modalEliminarCliente" data-delete-id="<?=$c['id_cliente']?>"><i class="bi bi-trash"></i><span>Eliminar</span></button></div></td></tr><?php endforeach; ?>
    <?php if(!$clientes): ?><tr><td colspan="4"><div class="empty-state"><i class="bi bi-people"></i><p class="mb-3">Aún no hay clientes registrados.</p><a class="btn btn-primary btn-sm" href="#nombre"><i class="bi bi-person-plus"></i> Registrar cliente</a></div></td></tr><?php endif; ?>
    </tbody></table></div>
    <?php $paginas=max(1,(int)ceil($total/$limite)); if($paginas>1): ?><nav aria-label="Paginación de clientes"><ul class="pagination"><li class="page-item <?=$pagina<=1?'disabled':''?>"><a class="page-link" href="?buscar=<?=urlencode($buscar)?>&pagina=<?=max(1,$pagina-1)?>">Anterior</a></li><?php for($i=1;$i<=$paginas;$i++): ?><li class="page-item <?=$i===$pagina?'active':''?>"><a class="page-link" href="?buscar=<?=urlencode($buscar)?>&pagina=<?=$i?>"><?=$i?></a></li><?php endfor; ?><li class="page-item <?=$pagina>=$paginas?'disabled':''?>"><a class="page-link" href="?buscar=<?=urlencode($buscar)?>&pagina=<?=min($paginas,$pagina+1)?>">Siguiente</a></li></ul></nav><?php endif; ?>
</section>
<div class="modal fade" id="modalEliminarCliente" tabindex="-1" aria-labelledby="modalEliminarClienteLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="modalEliminarClienteLabel"><i class="bi bi-trash text-danger"></i> Confirmar eliminación</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body">¿Seguro que deseas eliminar este cliente?</div><div class="modal-footer"><button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancelar</button><form method="post" id="formEliminarCliente"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id_cliente" id="idClienteEliminar"><button class="btn btn-danger" type="submit"><i class="bi bi-trash"></i> Eliminar</button></form></div></div></div></div>
<?php require __DIR__.'/../../includes/footer.php';
