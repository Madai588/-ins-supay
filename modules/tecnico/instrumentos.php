<?php
require_once __DIR__.'/../../includes/tecnico.php';
exigir_tecnico();
validar_post_csrf();
$db=obtener_conexion();
$error='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $a=$_POST['accion']??'';

        if($a==='crear'){
            $tipo=trim((string)($_POST['tipo']??''));
            $cliente=(int)($_POST['id_cliente']??0);
            $descripcion=trim((string)($_POST['descripcion']??''));

            if($tipo===''||$cliente<1){
                throw new InvalidArgumentException('Tipo y cliente son obligatorios.');
            }

            $s=$db->prepare('SELECT id_cliente FROM cliente WHERE id_cliente=?');
            $s->execute([$cliente]);
            if(!$s->fetchColumn()){
                throw new InvalidArgumentException('El cliente seleccionado no existe.');
            }

            $db->beginTransaction();
            $s=$db->prepare('INSERT INTO instrumento(codigo_qr,tipo,descripcion,id_cliente) VALUES(?,?,?,?)');
            $s->execute(['PENDIENTE-'.uniqid('',true),$tipo,$descripcion,$cliente]);
            $id=(int)$db->lastInsertId();
            $s=$db->prepare('UPDATE instrumento SET codigo_qr=? WHERE id_instrumento=?');
            $s->execute([generar_codigo_instrumento($db,$id),$id]);
            $db->commit();
            registrar_bitacora('instrumento_creado','ID '.$id);
            header('Location: instrumento_qr.php?id='.$id);
            exit;
        }

        if($a==='editar'){
            $s=$db->prepare('UPDATE instrumento SET tipo=?,descripcion=?,id_cliente=? WHERE id_instrumento=?');
            $s->execute([trim((string)$_POST['tipo']),trim((string)$_POST['descripcion']),(int)$_POST['id_cliente'],(int)$_POST['id_instrumento']]);
            registrar_bitacora('instrumento_actualizado','ID '.$_POST['id_instrumento']);
        }

        if($a==='eliminar'){
            $s=$db->prepare('DELETE FROM instrumento WHERE id_instrumento=?');
            $s->execute([(int)$_POST['id_instrumento']]);
            registrar_bitacora('instrumento_eliminado','ID '.$_POST['id_instrumento']);
        }
    }catch(Throwable $e){
        if($db->inTransaction())$db->rollBack();
        if($e instanceof InvalidArgumentException){
            $error=$e->getMessage();
        }elseif($e instanceof PDOException && str_contains($e->getMessage(),'1451')){
            $error='No se puede eliminar o modificar el instrumento porque tiene reparaciones o ventas asociadas.';
        }else{
            error_log('SUPAY instrumentos.php: '.$e->getMessage());
            $error='No se pudo guardar el instrumento. Verifica la conexión y la estructura de la base de datos.';
        }
    }
}
$buscar=trim((string)($_GET['buscar']??''));$cliente=(int)($_GET['cliente']??0);$pagina=max(1,(int)($_GET['pagina']??1));$limite=8;$offset=($pagina-1)*$limite;$cond=[];$params=[];if($buscar!==''){$cond[]='(i.codigo_qr LIKE ? OR i.tipo LIKE ? OR c.nombre LIKE ?)';$params=array_fill(0,3,'%'.$buscar.'%');}if($cliente){$cond[]='i.id_cliente=?';$params[]=$cliente;}$where=$cond?' WHERE '.implode(' AND ',$cond):'';$s=$db->prepare('SELECT COUNT(*) FROM instrumento i INNER JOIN cliente c ON c.id_cliente=i.id_cliente'.$where);$s->execute($params);$total=(int)$s->fetchColumn();$s=$db->prepare('SELECT i.*,c.nombre AS cliente_nombre FROM instrumento i INNER JOIN cliente c ON c.id_cliente=i.id_cliente'.$where.' ORDER BY i.id_instrumento DESC LIMIT '.$limite.' OFFSET '.$offset);$s->execute($params);$instrumentos=$s->fetchAll();$clientes=$db->query('SELECT id_cliente,nombre FROM cliente ORDER BY nombre')->fetchAll();$titulo='Instrumentos';require __DIR__.'/../../includes/header.php';
?>
<div class="page-heading"><div><h2><i class="bi bi-music-note-beamed"></i> Instrumentos</h2><p>Registra instrumentos y administra sus códigos QR únicos.</p></div><div class="btn-toolbar"><a class="btn btn-outline-primary" href="clientes.php"><i class="bi bi-people"></i> Clientes</a><a class="btn btn-primary" href="escaner.php"><i class="bi bi-qr-code-scan"></i> Escanear QR</a></div></div>
<?php if($error): ?><div class="alert alert-danger d-flex align-items-center" data-auto-dismiss="4000"><i class="bi bi-exclamation-triangle-fill"></i><?=e($error)?></div><?php endif; ?>
<section class="card mb-4"><div class="card-header"><h3><i class="bi bi-plus-circle"></i> Registrar instrumento</h3></div><div class="card-body"><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="accion" value="crear"><div class="row g-3"><div class="col-12 col-md-4"><label class="form-label" for="tipo">Tipo <span class="required">*</span></label><input class="form-control" id="tipo" name="tipo" required></div><div class="col-12 col-md-4"><label class="form-label" for="id_cliente">Cliente <span class="required">*</span></label><select class="form-select" id="id_cliente" name="id_cliente" required><option value="">Selecciona un cliente</option><?php foreach($clientes as $c): ?><option value="<?=$c['id_cliente']?>"><?=e($c['nombre'])?></option><?php endforeach; ?></select></div><div class="col-12"><label class="form-label" for="descripcion">Descripción</label><textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea></div></div><div class="form-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-qr-code"></i> Guardar y generar QR</button></div></form></div></section>
<section class="card mb-4"><div class="card-body"><form class="row g-2 align-items-end" method="get"><div class="col-12 col-md-9"><label class="form-label" for="buscar">Buscar instrumento</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input class="form-control" id="buscar" name="buscar" value="<?=e($buscar)?>" placeholder="Código QR, tipo o cliente"></div></div><div class="col-12 col-md-3 d-grid"><button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button></div></form></div></section>
<section class="card table-card"><div class="card-header d-flex justify-content-between align-items-center"><h3><i class="bi bi-list-ul"></i> Instrumentos registrados</h3><span class="badge bg-light text-primary"><?=$total?> registros</span></div><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Código QR</th><th>Tipo</th><th>Cliente</th><th>Acciones</th></tr></thead><tbody><?php foreach($instrumentos as $i): ?><tr><td><span class="badge bg-primary"><?=e($i['codigo_qr'])?></span></td><td class="fw-semibold"><?=e($i['tipo'])?></td><td><?=e($i['cliente_nombre'])?></td><td><div class="table-actions"><a class="btn btn-edit btn-sm" title="Ver QR" data-bs-toggle="tooltip" href="instrumento_qr.php?id=<?=$i['id_instrumento']?>"><i class="bi bi-qr-code"></i><span>QR</span></a><a class="btn btn-primary btn-sm" title="Ver historial" data-bs-toggle="tooltip" href="historial.php?codigo=<?=urlencode($i['codigo_qr'])?>"><i class="bi bi-clock-history"></i><span>Historial</span></a><button class="btn btn-danger btn-sm" type="button" title="Eliminar instrumento" data-bs-toggle="modal" data-bs-target="#modalEliminarInstrumento" data-delete-id="<?=$i['id_instrumento']?>" data-delete-target="#idInstrumentoEliminar"><i class="bi bi-trash"></i><span>Eliminar</span></button></div></td></tr><?php endforeach; ?><?php if(!$instrumentos): ?><tr><td colspan="4"><div class="empty-state"><i class="bi bi-music-note-list"></i><p class="mb-3">Aún no hay instrumentos registrados.</p><a class="btn btn-primary btn-sm" href="#tipo"><i class="bi bi-plus-circle"></i> Registrar instrumento</a></div></td></tr><?php endif; ?></tbody></table></div><?php $paginas=max(1,(int)ceil($total/$limite)); if($paginas>1): ?><nav aria-label="Paginación"><ul class="pagination"><li class="page-item <?=$pagina<=1?'disabled':''?>"><a class="page-link" href="?buscar=<?=urlencode($buscar)?>&cliente=<?=$cliente?>&pagina=<?=max(1,$pagina-1)?>">Anterior</a></li><?php for($n=1;$n<=$paginas;$n++): ?><li class="page-item <?=$n===$pagina?'active':''?>"><a class="page-link" href="?buscar=<?=urlencode($buscar)?>&cliente=<?=$cliente?>&pagina=<?=$n?>"><?=$n?></a></li><?php endfor; ?><li class="page-item <?=$pagina>=$paginas?'disabled':''?>"><a class="page-link" href="?buscar=<?=urlencode($buscar)?>&cliente=<?=$cliente?>&pagina=<?=min($paginas,$pagina+1)?>">Siguiente</a></li></ul></nav><?php endif; ?></section>
<div class="modal fade" id="modalEliminarInstrumento" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="bi bi-trash text-danger"></i> Confirmar eliminación</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body">¿Seguro que deseas eliminar este instrumento?</div><div class="modal-footer"><button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancelar</button><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id_instrumento" id="idInstrumentoEliminar"><button class="btn btn-danger" type="submit"><i class="bi bi-trash"></i> Eliminar</button></form></div></div></div></div>
<?php require __DIR__.'/../../includes/footer.php';
