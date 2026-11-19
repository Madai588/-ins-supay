# Checklist de pruebas SUPAY

| Código | Caso de prueba | Pasos y datos concretos | Resultado esperado | Resultado obtenido | Aprobado/No | Historia |
|---|---|---|---|---|---|---|
| PV01 | Login campos vacíos | Login; dejar usuario y contraseña vacíos; enviar | Mensaje de campos obligatorios y no ingresa | | | HU01 |
| PV02 | Contraseña menor a 8 | En login usar usuario `admin`, contraseña `Ab1!` | Rechazo por política de contraseña | | | HU01 |
| PV03 | Bloqueo tras 3 intentos | Usuario `admin`; usar `Incorrecta1!` tres veces | Bloqueo durante 15 minutos | | | HU01 |
| PV04 | Cantidad inválida | Movimiento: material `Madera mara`; cantidades `-1`, `0`, `abc` | Rechazo y mensaje claro | | | HU06 |
| PV05 | Venta incompleta | Ventas; cliente vacío, instrumento vacío, total vacío | No guarda y señala campos faltantes | | | HU09 |
| PV06 | QR único | Registrar dos instrumentos para clientes de ejemplo | Códigos `SUPAY-INS-000003` y `SUPAY-INS-000004` distintos | | | HU03 |
| PV07 | Luthier sin finanzas | Login `luthier`; abrir `modules/comercial/reportes.php` | HTTP 403 / Acceso denegado | | | HU01 |
| PI01 | Repuesto descuenta stock | Reparación del instrumento 1; material `Perno 8 mm`; cantidad `2` | Stock disminuye 2 y aparece salida | | | HU05 |
| PI02 | Salida al mínimo | Material `Aro metálico`, stock 6, mínimo 1; salida 5 | Alerta visible y stock 1 | | | HU08 |
| PI03 | Historial QR | Escanear `SUPAY-INS-000001` o escribirlo manualmente | Muestra reparaciones, diagnósticos, repuestos y estados | | | HU04 |
| PI04 | Estado en historial | Reparación 1: cambiar `en_revision` a `finalizado`; abrir historial | Historial muestra `finalizado` | | | HU02 |
| PI05 | Cobro en reportes | Reparación 1; cobro Bs 150.00; periodo mensual | Ingreso incluye Bs 150.00 | | | HU10 |
| PI06 | Venta en reportes | Cliente ejemplo; instrumento 1; venta Bs 500.00; periodo mensual | Ingreso incluye Bs 500.00 | | | HU09 |
| PA01 | Flujo comercial admin | Login admin; registrar venta y cobro con datos anteriores | Ambos se guardan sin ayuda | | | HU09/HU10 |
| PA02 | Flujo técnico luthier | Login luthier; diagnóstico en instrumento 1; consultar QR | Diagnóstico e historial visibles | | | HU02/HU04 |
| PA03 | Control inventario | Login admin; registrar entrada/salida y abrir panel | Stock actualizado y alerta visible | | | HU06/HU08 |
| PA04 | Coincidencia reportes | Comparar venta Bs 500 y cobro Bs 150 con Reportes | Totales coinciden con registros manuales | | | HU11 |
| PA05 | Respaldo y restauración | Admin; generar respaldo; crear registro temporal; restaurar copia | Registro temporal desaparece y datos vuelven al respaldo | | | HU12 |
