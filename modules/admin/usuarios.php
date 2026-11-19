<?php
require_once __DIR__.'/../../includes/auth.php';
exigir_rol('administrador');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !validar_csrf($_POST['csrf'] ?? null)) {
    http_response_code(419);
    exit('Token CSRF inválido.');
}
$db = obtener_conexion();
$error = '';
$ok = '';
$editar = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accion = (string) ($_POST['accion'] ?? '');
        if ($accion === 'crear') {
            $nombre = trim((string) ($_POST['nombre'] ?? ''));
            $usuario = trim((string) ($_POST['usuario'] ?? ''));
            $rol = (string) ($_POST['rol'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            if ($nombre === '' || $usuario === '' || !in_array($rol, ['administrador', 'luthier'], true) || !password_cumple_politica($password)) {
                throw new InvalidArgumentException('Completa nombre, usuario, rol y una contraseña válida de al menos 8 caracteres.');
            }
            $s = $db->prepare('INSERT INTO usuario(nombre,usuario,password_hash,rol,activo) VALUES(?,?,?,?,1)');
            $s->execute([$nombre, $usuario, password_hash($password, PASSWORD_DEFAULT), $rol]);
            registrar_bitacora('usuario_creado', 'Usuario '.$usuario);
            $ok = 'Usuario creado correctamente.';
        } elseif ($accion === 'editar') {
            $id = (int) ($_POST['id_usuario'] ?? 0);
            $nombre = trim((string) ($_POST['nombre'] ?? ''));
            $usuario = trim((string) ($_POST['usuario'] ?? ''));
            $rol = (string) ($_POST['rol'] ?? '');
            $password = (string) ($_POST['password'] ?? '');
            if ($id < 1 || $nombre === '' || $usuario === '' || !in_array($rol, ['administrador', 'luthier'], true)) {
                throw new InvalidArgumentException('Nombre, usuario y rol son obligatorios.');
            }
            if ($password !== '' && !password_cumple_politica($password)) {
                throw new InvalidArgumentException('La nueva contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.');
            }
            if ($password !== '') {
                $s = $db->prepare('UPDATE usuario SET nombre=?,usuario=?,rol=?,password_hash=? WHERE id_usuario=?');
                $s->execute([$nombre, $usuario, $rol, password_hash($password, PASSWORD_DEFAULT), $id]);
            } else {
                $s = $db->prepare('UPDATE usuario SET nombre=?,usuario=?,rol=? WHERE id_usuario=?');
                $s->execute([$nombre, $usuario, $rol, $id]);
            }
            registrar_bitacora('usuario_actualizado', 'ID '.$id);
            $ok = 'Datos del usuario actualizados correctamente.';
        } elseif ($accion === 'estado') {
            $id = (int) ($_POST['id_usuario'] ?? 0);
            $activo = (int) ($_POST['activo'] ?? 0) === 1 ? 1 : 0;
            if ($id === (int) ($_SESSION['usuario']['id_usuario'] ?? 0) && $activo === 0) {
                throw new InvalidArgumentException('No puedes desactivar tu propia cuenta.');
            }
            $s = $db->prepare('UPDATE usuario SET activo=?,intentos_fallidos=0,bloqueado_hasta=NULL WHERE id_usuario=?');
            $s->execute([$activo, $id]);
            registrar_bitacora($activo ? 'usuario_activado' : 'usuario_desactivado', 'ID '.$id);
            $ok = $activo ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.';
        }
    } catch (Throwable $e) {
        $error = $e instanceof InvalidArgumentException ? $e->getMessage() : 'No se pudo completar la operación. El usuario puede estar repetido.';
    }
}
if (isset($_GET['editar'])) {
    $s = $db->prepare('SELECT id_usuario,nombre,usuario,rol,activo FROM usuario WHERE id_usuario=?');
    $s->execute([(int) $_GET['editar']]);
    $editar = $s->fetch() ?: null;
}
$buscar = trim((string) ($_GET['buscar'] ?? ''));
$params = [];
$where = '';
if ($buscar !== '') {
    $where = ' WHERE nombre LIKE ? OR usuario LIKE ? OR rol LIKE ?';
    $params = ['%'.$buscar.'%', '%'.$buscar.'%', '%'.$buscar.'%'];
}
$s = $db->prepare('SELECT id_usuario,nombre,usuario,rol,activo,intentos_fallidos,bloqueado_hasta,creado_en FROM usuario'.$where.' ORDER BY nombre');
$s->execute($params);
$usuarios = $s->fetchAll();
$titulo = 'Usuarios';
require __DIR__.'/../../includes/header.php';
?>
<div class="page-heading"><div><h2><i class="bi bi-person-gear"></i> Administración de accesos</h2><p>Crear, editar y activar o desactivar usuarios del sistema.</p></div><a class="btn btn-outline-primary" href="<?=e(BASE_URL)?>/modules/admin/index.php"><i class="bi bi-grid-1x2"></i> Panel principal</a></div>
<?php if($error): ?><div class="alert alert-danger d-flex align-items-center" data-auto-dismiss="4000"><i class="bi bi-exclamation-triangle-fill"></i><?=e($error)?></div><?php endif; ?><?php if($ok): ?><div class="alert alert-success d-flex align-items-center" data-auto-dismiss="4000"><i class="bi bi-check-circle-fill"></i><?=e($ok)?></div><?php endif; ?>
<section class="card mb-4"><div class="card-header"><h3><i class="bi bi-person-plus-fill"></i> <?=$editar?'Editar usuario':'Crear usuario nuevo'?></h3></div><div class="card-body"><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="accion" value="<?=$editar?'editar':'crear'?>"><input type="hidden" name="id_usuario" value="<?=$editar['id_usuario']??''?>"><div class="row g-3"><div class="col-12 col-md-6"><label class="form-label">Nombre completo <span class="required">*</span></label><input class="form-control" name="nombre" value="<?=e($editar['nombre']??'')?>" required></div><div class="col-12 col-md-6"><label class="form-label">Usuario <span class="required">*</span></label><input class="form-control" name="usuario" value="<?=e($editar['usuario']??'')?>" required></div><div class="col-12 col-md-4"><label class="form-label">Rol <span class="required">*</span></label><select class="form-select" name="rol" required><option value="administrador" <?=$editar&&$editar['rol']==='administrador'?'selected':''?>>Administrador</option><option value="luthier" <?=$editar&&$editar['rol']==='luthier'?'selected':''?>>Luthier</option></select></div><div class="col-12 col-md-8"><label class="form-label">Contraseña <?=$editar?'(dejar vacía para conservarla':'<span class="required">*</span>'?></label><input class="form-control" type="password" name="password" <?=$editar?'':'required'?> placeholder="Mínimo 8 caracteres, mayúscula, número y símbolo"></div></div><div class="form-actions"><a class="btn btn-cancel" href="usuarios.php"><i class="bi bi-x-circle"></i> Cancelar</a><button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> <?=$editar?'Guardar cambios':'Crear usuario'?></button></div></form></div></section>
<section class="card mb-4"><div class="card-body"><form class="row g-2 align-items-end" method="get"><div class="col-12 col-md-9"><label class="form-label">Buscar usuario</label><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input class="form-control" name="buscar" value="<?=e($buscar)?>" placeholder="Nombre, usuario o rol"></div></div><div class="col-12 col-md-3 d-grid"><button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button></div></form></div></section>
<section class="card table-card"><div class="card-header d-flex justify-content-between align-items-center"><h3><i class="bi bi-people"></i> Usuarios registrados</h3><span class="badge bg-light text-primary"><?=count($usuarios)?> usuarios</span></div><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Nombre</th><th>Usuario</th><th>Rol</th><th>Estado</th><th>Seguridad</th><th>Acciones</th></tr></thead><tbody><?php foreach($usuarios as $usuario): ?><tr><td class="fw-semibold"><?=e($usuario['nombre'])?></td><td><?=e($usuario['usuario'])?></td><td><span class="badge badge-role"><?=e(ucfirst($usuario['rol']))?></span></td><td><?php if($usuario['activo']): ?><span class="badge bg-success"><i class="bi bi-check-circle"></i> Activo</span><?php else: ?><span class="badge bg-secondary">Inactivo</span><?php endif; ?></td><td><?php if($usuario['bloqueado_hasta']&&strtotime($usuario['bloqueado_hasta'])>time()): ?><span class="badge bg-danger"><i class="bi bi-lock-fill"></i> Bloqueado</span><?php else: ?><span class="text-muted small">Disponible</span><?php endif; ?></td><td><div class="table-actions"><a class="btn btn-edit btn-sm" title="Editar usuario" data-bs-toggle="tooltip" href="?editar=<?=$usuario['id_usuario']?>"><i class="bi bi-pencil-square"></i><span>Editar</span></a><?php $es_propio=(int)$usuario['id_usuario']===(int)($_SESSION['usuario']['id_usuario']??0); if($usuario['activo']): ?><button class="btn btn-warning btn-sm" type="button" title="Desactivar usuario" data-bs-toggle="modal" data-bs-target="#modalEstadoUsuario" data-user-id="<?=$usuario['id_usuario']?>" data-user-active="0" data-user-name="<?=e($usuario['nombre'])?>" <?=$es_propio?'disabled':''?>><i class="bi bi-person-slash"></i><span>Desactivar</span></button><?php else: ?><button class="btn btn-success btn-sm" type="button" title="Activar usuario" data-bs-toggle="modal" data-bs-target="#modalEstadoUsuario" data-user-id="<?=$usuario['id_usuario']?>" data-user-active="1" data-user-name="<?=e($usuario['nombre'])?>"><i class="bi bi-person-check"></i><span>Activar</span></button><?php endif; ?></div></td></tr><?php endforeach; ?><?php if(!$usuarios): ?><tr><td colspan="6"><div class="empty-state"><i class="bi bi-person-x"></i><p>No se encontraron usuarios.</p></div></td></tr><?php endif; ?></tbody></table></div></section>
<div class="modal fade" id="modalEstadoUsuario" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title"><i class="bi bi-person-gear"></i> Confirmar cambio de acceso</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div><div class="modal-body" id="textoEstadoUsuario">¿Deseas cambiar el estado de este usuario?</div><div class="modal-footer"><button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancelar</button><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="accion" value="estado"><input type="hidden" name="id_usuario" id="estadoUsuarioId"><input type="hidden" name="activo" id="estadoUsuarioActivo"><button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Confirmar</button></form></div></div></div></div>
<?php require __DIR__.'/../../includes/footer.php';
