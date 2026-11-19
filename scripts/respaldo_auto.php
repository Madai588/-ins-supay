<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli'){fwrite(STDERR,"Este archivo solo puede ejecutarse desde CLI.\n");exit(1);}
require_once __DIR__.'/../config/produccion.php';require_once __DIR__.'/../includes/respaldo.php';
try{$nombre=crear_respaldo(obtener_conexion());registrar_bitacora('respaldo_automatico',$nombre);echo "Respaldo creado: {$nombre}\n";exit(0);}catch(Throwable $e){fwrite(STDERR,"Error al crear respaldo: ".$e->getMessage()."\n");exit(1);}
