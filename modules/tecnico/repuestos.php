<?php
require_once __DIR__.'/../../includes/tecnico.php';
require_once __DIR__.'/../../includes/inventario.php';
exigir_tecnico();
validar_post_csrf();
$db=obtener_conexion();
$id=(int)($_GET['id_reparacion']??$_POST['id_reparacion']??0);
$error=''; $ok=''; $alerta='';

if($_SERVER['REQUEST_METHOD']==='POST'){
    try{
        $material=(int)($_POST['id_material']??0);
        $cantidad=$_POST['cantidad']??null;
        if($id<1||$material<1||!decimal_positivo($cantidad)) throw new InvalidArgumentException('Selecciona un material e indica una cantidad mayor que cero.');
        $cantidad=(float)$cantidad;
        $s=$db->prepare('SELECT id_reparacion FROM reparacion WHERE id_reparacion=?');
        $s->execute([$id]);
        if(!$s->fetchColumn()) throw new InvalidArgumentException('La reparacion seleccionada no existe.');
        $db->beginTransaction();
        $s=$db->prepare('SELECT stock,stock_minimo,nombre FROM material WHERE id_material=? FOR UPDATE');
        $s->execute([$material]); $m=$s->fetch();
        if(!$m) throw new InvalidArgumentException('El material seleccionado no existe.');
        if($cantidad>(float)$m['stock']) throw new InvalidArgumentException('Stock insuficiente; se impide stock negativo.');
        $db->prepare('INSERT INTO repuesto_utilizado(id_reparacion,id_material,cantidad) VALUES(?,?,?)')->execute([$id,$material,$cantidad]);
        $db->prepare('UPDATE material SET stock=stock-? WHERE id_material=?')->execute([$cantidad,$material]);
        $db->prepare("INSERT INTO movimiento_inventario(id_material,id_usuario,tipo,cantidad) VALUES(?,?,'salida',?)")->execute([$material,$_SESSION['usuario']['id_usuario'],$cantidad]);
        $nuevo=(float)$m['stock']-$cantidad; $db->commit();
        registrar_bitacora('repuesto_registrado','Reparacion '.$id.' material '.$material);
        $ok='Repuesto registrado y stock descontado.';
        if($nuevo<=(float)$m['stock_minimo']) $alerta='Alerta: '.$m['nombre'].' quedo en el nivel minimo o por debajo.';
    }catch(Throwable $e){
        if($db->inTransaction()) $db->rollBack();
        if($e instanceof InvalidArgumentException) $error=$e->getMessage();
        else { error_log('SUPAY repuestos.php: '.$e->getMessage()); $error='No se pudo registrar el repuesto. Revisa las tablas de inventario.'; }
    }
}
$s=$db->prepare('SELECT r.*,i.codigo_qr,i.tipo FROM reparacion r INNER JOIN instrumento i ON i.id_instrumento=r.id_instrumento WHERE r.id_reparacion=?');
$s->execute([$id]); $reparacion=$s->fetch();
if(!$reparacion){http_response_code(404);exit('Reparacion no encontrada.');}
$s=$db->prepare('SELECT ru.*,m.nombre FROM repuesto_utilizado ru INNER JOIN material m ON m.id_material=ru.id_material WHERE ru.id_reparacion=?');
$s->execute([$id]); $usados=$s->fetchAll();
$materiales=$db->query('SELECT id_material,nombre,stock FROM material ORDER BY nombre')->fetchAll();
$titulo='Repuestos'; require __DIR__.'/../../includes/header.php';
?>
<div class="page-heading"><div><h2><i class="bi bi-box-seam"></i> Repuestos de reparacion #<?=$id?></h2><p><?=e($reparacion['codigo_qr'].' · '.$reparacion['tipo'])?></p></div><a class="btn btn-outline-primary" href="reparaciones.php"><i class="bi bi-arrow-left"></i> Reparaciones</a></div>
<section class="card mb-4"><div class="card-header"><h3><i class="bi bi-box-arrow-in-down"></i> Registrar material utilizado</h3></div><div class="card-body">
<?php if($error): ?><div class="alert alert-danger d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill"></i><?=e($error)?></div><?php endif; ?>
<?php if($ok): ?><div class="alert alert-success d-flex align-items-center"><i class="bi bi-check-circle-fill"></i><?=e($ok)?></div><?php endif; ?>
<?php if($alerta): ?><div class="alert alert-warning d-flex align-items-center"><i class="bi bi-exclamation-triangle-fill"></i><?=e($alerta)?></div><?php endif; ?>
<form method="post" onsubmit="return validarCantidadRepuesto()"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="id_reparacion" value="<?=$id?>"><div class="row g-3"><div class="col-12 col-md-8"><label class="form-label" for="id-material">Material <span class="required">*</span></label><select class="form-select" id="id-material" name="id_material" required><option value="">Selecciona</option><?php foreach($materiales as $m): ?><option value="<?=$m['id_material']?>" <?=((int)($_POST['id_material']??0)===(int)$m['id_material'])?'selected':''?>><?=e($m['nombre'])?> (stock: <?=e((string)$m['stock'])?>)</option><?php endforeach; ?></select></div><div class="col-12 col-md-4"><label class="form-label" for="cantidad-repuesto">Cantidad <span class="required">*</span></label><input class="form-control" id="cantidad-repuesto" type="number" name="cantidad" min="0.01" step="0.01" value="<?=e((string)($_POST['cantidad']??''))?>" required></div></div><div class="form-actions"><button class="btn btn-primary" type="submit"><i class="bi bi-box-arrow-in-down"></i> Registrar salida</button></div></form></div></section>
<section class="card table-card"><div class="card-header"><h3><i class="bi bi-list-ul"></i> Materiales utilizados</h3></div><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Material</th><th class="stock">Cantidad</th></tr></thead><tbody><?php foreach($usados as $u): ?><tr><td><?=e($u['nombre'])?></td><td class="stock"><?=e((string)$u['cantidad'])?></td></tr><?php endforeach; ?><?php if(!$usados): ?><tr><td colspan="2"><div class="empty-state"><i class="bi bi-box-seam"></i><p>Aun no hay repuestos registrados.</p></div></td></tr><?php endif; ?></tbody></table></div></section>
<script>function validarCantidadRepuesto(){const c=document.getElementById('cantidad-repuesto');if(!c||!Number.isFinite(Number(c.value))||Number(c.value)<=0){alert('La cantidad debe ser numerica y mayor que 0.');return false}return true}</script>
<?php require __DIR__.'/../../includes/footer.php';
