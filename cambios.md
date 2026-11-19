# SUPAY — Cambios, requisitos y estado del proyecto

**Fecha de actualización:** 10 de noviembre de 2026  
**Proyecto:** Sistema web para la gestión de un taller de instrumentos musicales  
**Estado:** Funcional en entorno local con XAMPP, PHP y MariaDB/MySQL.  
**Ruta del proyecto:** `D:\6-SEM\INS-600\sistema-web`

## 1. Resumen del sistema

SUPAY es una aplicación web para administrar un taller de instrumentos musicales. Centraliza clientes, instrumentos, reparaciones, repuestos, materiales, proveedores, movimientos de inventario, ventas, cobros, reportes, códigos QR, usuarios y respaldos.

El sistema trabaja con dos perfiles principales:

- **Administrador:** acceso completo a administración, inventario, usuarios, ventas, cobros, reportes y respaldos.
- **Luthier:** acceso a la operación técnica: clientes, instrumentos, reparaciones, repuestos, materiales necesarios para el trabajo técnico, QR e historial. No debe acceder a las funciones financieras y administrativas restringidas.

La aplicación está preparada para ejecutarse de forma local en XAMPP, con Apache como servidor web y MariaDB/MySQL como base de datos.

## 2. Objetivo y alcance

### Objetivo general

Controlar el ciclo completo de atención de un instrumento: registro del cliente, registro del instrumento, reparación, diagnóstico, uso de materiales, seguimiento mediante QR, cobro y consulta histórica.

### Alcance funcional

El sistema cubre:

1. Autenticación segura de usuarios.
2. Control de roles y permisos.
3. Registro y edición de clientes.
4. Registro de instrumentos asociados a clientes.
5. Generación de código QR único por instrumento.
6. Consulta del historial técnico mediante QR o código manual.
7. Registro y cambio de estado de reparaciones.
8. Registro de repuestos utilizados y descuento automático del stock.
9. Administración de materiales y categorías.
10. Entradas y salidas de inventario.
11. Alertas por stock mínimo.
12. Gestión de proveedores.
13. Registro de ventas y cobros.
14. Reportes financieros, de ventas, reparaciones y stock.
15. Exportación CSV y PDF.
16. Respaldos y restauración de la base de datos.
17. Bitácora de acciones relevantes.

## 3. Requisitos funcionales

| Código | Requisito | Estado |
|---|---|---|
| RF-01 | Permitir iniciar sesión con usuario y contraseña. | Implementado |
| RF-02 | Aplicar bloqueo temporal después de varios intentos fallidos. | Implementado |
| RF-03 | Administrar usuarios, roles y estado activo/inactivo. | Implementado |
| RF-04 | Registrar, buscar, editar y eliminar clientes. | Implementado |
| RF-05 | Asociar cada instrumento con un cliente. | Implementado |
| RF-06 | Generar un código QR único para cada instrumento. | Implementado |
| RF-07 | Imprimir o visualizar el QR del instrumento. | Implementado |
| RF-08 | Consultar el historial técnico por código QR o código de instrumento. | Implementado |
| RF-09 | Crear reparaciones con diagnóstico, costo y estado. | Implementado |
| RF-10 | Cambiar una reparación entre revisión, reparación y finalizada. | Implementado |
| RF-11 | Registrar materiales utilizados en una reparación. | Implementado |
| RF-12 | Descontar automáticamente del inventario los repuestos utilizados. | Implementado |
| RF-13 | Evitar salidas mayores al stock disponible. | Implementado |
| RF-14 | Registrar entradas y salidas manuales de inventario. | Implementado |
| RF-15 | Administrar materiales, categorías, stock mínimo y proveedor. | Implementado |
| RF-16 | Mostrar alerta cuando el stock sea igual o menor al mínimo. | Implementado |
| RF-17 | Administrar proveedores. | Implementado |
| RF-18 | Registrar ventas relacionadas con clientes e instrumentos. | Implementado |
| RF-19 | Registrar cobros relacionados con reparaciones. | Implementado |
| RF-20 | Consultar ingresos por periodo. | Implementado |
| RF-21 | Mostrar información resumida mediante gráficos. | Implementado |
| RF-22 | Exportar reportes en CSV y PDF. | Implementado |
| RF-23 | Generar respaldos manuales de la base de datos. | Implementado |
| RF-24 | Restaurar respaldos desde el módulo administrativo. | Implementado |
| RF-25 | Registrar acciones importantes en la bitácora. | Implementado |

## 4. Requisitos no funcionales

| Código | Requisito | Estado/criterio |
|---|---|---|
| RNF-01 | Usabilidad | Interfaz web con navegación por módulos y mensajes de éxito/error. |
| RNF-02 | Diseño responsive | La interfaz se adapta a pantallas de escritorio y móviles. |
| RNF-03 | Seguridad de contraseñas | Las contraseñas se almacenan con `password_hash` y se validan con `password_verify`. |
| RNF-04 | Control de acceso | Los permisos se validan en el servidor, no únicamente en el menú visual. |
| RNF-05 | Protección CSRF | Los formularios POST utilizan token CSRF. |
| RNF-06 | Protección XSS | Las salidas HTML utilizan escape mediante `htmlspecialchars`/`e()`. |
| RNF-07 | Consultas seguras | La conexión usa PDO y consultas preparadas. |
| RNF-08 | Integridad de datos | La base usa claves foráneas, restricciones y transacciones. |
| RNF-09 | Disponibilidad local | Puede operar en una computadora con XAMPP sin depender de internet. |
| RNF-10 | Mantenibilidad | El código está separado por configuración, módulos, includes, CSS y JavaScript. |
| RNF-11 | Trazabilidad | Las operaciones importantes se registran en `bitacora`. |
| RNF-12 | Recuperación | Se incluyen respaldos SQL y funciones de restauración. |
| RNF-13 | Compatibilidad | Se orienta a navegadores modernos basados en Chromium y Firefox. |
| RNF-14 | Escáner QR | El escaneo depende de permisos de cámara del navegador; existe búsqueda manual como alternativa. |

## 5. Casos de uso principales

### CU-01 — Iniciar sesión

1. El usuario abre la pantalla de acceso.
2. Introduce usuario y contraseña.
3. El sistema valida que la cuenta esté activa.
4. Se verifica la contraseña y la política de seguridad.
5. Se crea la sesión y se redirige al panel correspondiente.
6. Después de tres intentos fallidos se aplica bloqueo temporal.

### CU-02 — Registrar cliente

1. El usuario abre **Clientes**.
2. Introduce nombre, teléfono y dirección.
3. El sistema valida el nombre obligatorio.
4. Guarda el cliente y actualiza la tabla.

### CU-03 — Registrar instrumento y generar QR

1. El usuario abre **Instrumentos**.
2. Introduce tipo y descripción.
3. Selecciona un cliente existente.
4. El sistema crea el registro dentro de una transacción.
5. Genera un código único con formato `SUPAY-INS-XXXXXX`.
6. Redirige a la pantalla del QR generado.

### CU-04 — Consultar historial por QR

1. El usuario escanea el código o escribe el código manualmente.
2. El sistema identifica el instrumento.
3. Muestra cliente, datos del instrumento, reparaciones, diagnósticos, estados y repuestos.

### CU-05 — Registrar reparación

1. El usuario selecciona un instrumento.
2. Introduce diagnóstico, costo y estado inicial.
3. El sistema guarda la reparación asociada al usuario actual.
4. El historial se actualiza.

### CU-06 — Registrar repuesto utilizado

1. El usuario selecciona reparación, material y cantidad.
2. El sistema valida que la cantidad sea positiva.
3. Bloquea el material durante la operación.
4. Comprueba que exista stock suficiente.
5. Inserta el detalle del repuesto.
6. Descuenta el stock.
7. Registra una salida en `movimiento_inventario`.
8. Confirma todo o revierte toda la operación si ocurre un error.

### CU-07 — Administrar inventario

1. El administrador registra o edita material, categoría, proveedor y stock mínimo.
2. Puede registrar entradas o salidas.
3. El sistema actualiza el stock y guarda el movimiento.
4. Si el stock queda en el mínimo o debajo, se muestra una alerta.

### CU-08 — Registrar venta o cobro

1. El administrador selecciona cliente o reparación.
2. Introduce el total o monto.
3. El sistema valida que el importe sea válido.
4. Guarda la operación y la incluye en los reportes del periodo.

### CU-09 — Generar reporte

1. El usuario autorizado selecciona el tipo de reporte y el periodo.
2. El sistema consulta los datos.
3. Presenta resumen, detalle y totales.
4. Permite exportar a CSV o PDF.

### CU-10 — Respaldar y restaurar

1. El administrador abre **Respaldos**.
2. Genera un archivo SQL o selecciona uno permitido.
3. El sistema valida el nombre y la ubicación.
4. La restauración se ejecuta únicamente con permiso administrativo.

## 6. Roles y permisos

| Área | Administrador | Luthier |
|---|---:|---:|
| Panel principal | Sí | Sí |
| Clientes | Sí | Sí |
| Instrumentos y QR | Sí | Sí |
| Reparaciones | Sí | Sí |
| Historial técnico | Sí | Sí |
| Repuestos de reparación | Sí | Sí |
| Consulta de materiales | Sí | Sí |
| Cambios administrativos de inventario | Sí | Según operación técnica |
| Proveedores | Sí | No |
| Ventas | Sí | No |
| Cobros | Sí | No |
| Reportes financieros | Sí | No |
| Usuarios | Sí | No |
| Respaldos/restauración | Sí | No |
| Bitácora administrativa | Sí | No |

La autorización se aplica mediante funciones como `exigir_login()`, `exigir_rol()` y controles específicos de los módulos. Ocultar un enlace no reemplaza la validación del servidor.

## 7. Módulos implementados

### Autenticación

- [login.php](D:/6-SEM/INS-600/sistema-web/modules/auth/login.php): inicio de sesión y controles de seguridad.
- [logout.php](D:/6-SEM/INS-600/sistema-web/modules/auth/logout.php): cierre de sesión.

### Administración

- [index.php](D:/6-SEM/INS-600/sistema-web/modules/admin/index.php): panel administrativo.
- [usuarios.php](D:/6-SEM/INS-600/sistema-web/modules/admin/usuarios.php): usuarios y roles.
- [respaldos.php](D:/6-SEM/INS-600/sistema-web/modules/admin/respaldos.php): respaldos y restauración.

### Gestión técnica

- [clientes.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/clientes.php): clientes.
- [cliente_editar.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/cliente_editar.php): edición de clientes.
- [instrumentos.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/instrumentos.php): instrumentos y generación de QR.
- [instrumento_editar.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/instrumento_editar.php): edición de instrumentos.
- [instrumento_qr.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/instrumento_qr.php): visualización del QR.
- [escaner.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/escaner.php): escáner QR.
- [historial.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/historial.php): historial técnico.
- [reparaciones.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/reparaciones.php): reparaciones y estados.
- [repuestos.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/repuestos.php): materiales utilizados por reparación.
- [materiales.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/materiales.php): inventario.
- [movimientos.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/movimientos.php): entradas y salidas.
- [proveedores.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/proveedores.php): proveedores.

### Gestión comercial

- [ventas.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/ventas.php): ventas.
- [cobros.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/cobros.php): cobros.
- [reportes.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/reportes.php): reportes y gráficos.
- [exportar.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/exportar.php): exportación CSV.
- [exportar_pdf.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/exportar_pdf.php): exportación PDF según tipo de reporte.

## 8. Base de datos

La base de datos se llama `supay` y se crea con [supay.sql](D:/6-SEM/INS-600/sistema-web/database/supay.sql). Utiliza InnoDB, `utf8mb4`, claves foráneas, índices y restricciones de integridad.

### Tablas

| Tabla | Propósito |
|---|---|
| `usuario` | Usuarios, roles, estado, intentos fallidos y bloqueo. |
| `cliente` | Personas propietarias o responsables de instrumentos. |
| `instrumento` | Tipo, descripción, cliente y código QR único. |
| `reparacion` | Diagnóstico, estado, costo, usuario e instrumento. |
| `proveedor` | Proveedores y datos de contacto. |
| `material` | Materiales, categoría, stock, mínimo y proveedor. |
| `repuesto_utilizado` | Material y cantidad utilizados en cada reparación. |
| `movimiento_inventario` | Entradas y salidas de materiales. |
| `venta` | Ventas relacionadas con clientes e instrumentos. |
| `cobro` | Cobros asociados a reparaciones. |
| `bitacora` | Acciones, usuario, IP, detalle y fecha. |

### Relaciones principales

- Un `cliente` puede tener muchos `instrumento`.
- Un `instrumento` puede tener muchas `reparacion`.
- Una `reparacion` puede tener muchos `repuesto_utilizado`.
- Un `material` puede utilizarse en muchos detalles de reparación y movimientos.
- Un `proveedor` puede tener muchos materiales; si se elimina, el proveedor del material queda en `NULL`.
- Una `venta` pertenece a un cliente y puede asociarse a un instrumento.
- Un `cobro` pertenece a una reparación.
- Un `usuario` registra reparaciones, movimientos y bitácora.

### Restricciones relevantes

- El código QR del instrumento es único.
- Stock y stock mínimo no pueden ser negativos.
- Cantidades de repuestos y movimientos deben ser mayores que cero.
- Costos, ventas y cobros no aceptan valores inválidos o negativos según la operación.
- Las claves foráneas impiden dejar registros huérfanos.
- Las operaciones críticas de inventario se ejecutan dentro de transacciones.

### Usuario de aplicación

[usuario_app.sql](D:/6-SEM/INS-600/sistema-web/database/usuario_app.sql) crea el usuario técnico `supay_app` y le concede únicamente `SELECT`, `INSERT`, `UPDATE` y `DELETE` sobre la base `supay`. Para producción se recomienda cambiar la contraseña semilla mediante las variables `SUPAY_DB_USER` y `SUPAY_DB_PASS`.

## 9. Correcciones y cambios realizados

### 9.1 Campos de texto y autocompletado

- Se corrigió el color del texto mientras se escribe.
- Se corrigió el texto que quedaba blanco o invisible al perder el foco.
- Se agregaron reglas para autofill de navegadores Chromium y Firefox.
- Se definieron color de fondo, color de texto, cursor y placeholder para los controles del tema oscuro.
- Se agregó repintado inmediato mediante eventos `input` en [ui.js](D:/6-SEM/INS-600/sistema-web/assets/js/ui.js).
- Se dejó el autocompletado disponible para login y se evita que el navegador altere el resto de formularios.

### 9.2 Hover de la tabla de inventario

- Se eliminó el fondo crema/blanco que generaba Bootstrap al pasar el mouse.
- Las celdas mantienen texto claro durante el hover.
- Las filas normales usan un tono oscuro azul.
- Las filas con stock mínimo usan tonos café/rojizos oscuros.
- Se sobrescribieron las variables de Bootstrap y los estados de fondo para evitar que `table-hover` vuelva a cambiar el color.
- Se agregó transición de `background-color` de `0.2s`.
- Los botones y la etiqueta **Stock mínimo** conservan sus colores propios.

Las reglas están en [supay.css](D:/6-SEM/INS-600/sistema-web/assets/css/supay.css) y existe una sobrescritura final en [header.php](D:/6-SEM/INS-600/sistema-web/includes/header.php) para asegurar prioridad sobre Bootstrap.

### 9.3 Generación de instrumentos y QR

- Se validan tipo y cliente antes de guardar.
- Se crea el instrumento y luego se genera el código definitivo `SUPAY-INS-XXXXXX`.
- La creación se realiza con transacción y rollback ante errores.
- Se distingue un error de integridad por reparaciones/ventas de un error general de base de datos.
- El QR se genera al finalizar el guardado y queda asociado al instrumento recién creado.

### 9.4 Registro de repuestos

- Se corrigió la llamada faltante a `decimal_positivo()` incluyendo [inventario.php](D:/6-SEM/INS-600/sistema-web/includes/inventario.php).
- Se validan reparación, material y cantidad.
- Se bloquea la fila del material durante la operación.
- Se comprueba el stock antes de descontar.
- Se insertan el detalle y el movimiento dentro de la misma transacción.
- Se informa el error real en el log y se muestra un mensaje funcional al usuario.
- Se conserva el formulario cuando la operación falla.

### 9.5 Alertas de stock

- Las alertas de stock mínimo se calculan con `stock <= stock_minimo`.
- El aviso se muestra en el inventario y en las pantallas relacionadas.
- Al corregir el stock por encima del mínimo, la alerta debe desaparecer después de guardar y recargar la vista.
- El color de las filas de alerta se mantiene oscuro y legible incluso con el puntero encima.

### 9.6 Reportes PDF

- Se creó una plantilla PDF profesional con encabezado SUPAY y color institucional rojo.
- Se quitó la esfera/letra `S` del encabezado porque no se solicitó conservarla.
- El nombre del reporte aparece según el tipo: ingresos, stock, ventas o reparaciones.
- Se agregan subtítulo, periodo, resumen, detalle, tablas y pie de página.
- El diseño usa hoja clara con contenido legible y estructura de reporte.
- La generación se centraliza en [pdf_reporte.php](D:/6-SEM/INS-600/sistema-web/includes/pdf_reporte.php).

### 9.7 Avisos, alertas y ventanas de confirmación

- Se corrigió el contraste de las ventanas de confirmación que aparecían blancas con texto claro.
- Los modales ahora utilizan fondo oscuro, encabezado diferenciado, texto blanco y bordes institucionales.
- Se mejoraron los botones de cancelar, confirmar, eliminar, restaurar y acciones peligrosas.
- Se hizo visible el botón de cierre del modal sobre el fondo oscuro.
- Se mejoraron los avisos de éxito, información, advertencia y error con separación correcta entre icono y mensaje.
- La corrección se aplica globalmente desde [supay.css](D:/6-SEM/INS-600/sistema-web/assets/css/supay.css), por lo que cubre Clientes, Instrumentos, Materiales, Proveedores, Usuarios, Respaldos y demás módulos que usan modales Bootstrap.
- Se actualizó la versión del recurso CSS a `20261110-8` para evitar que el navegador conserve los estilos anteriores en caché.

### 9.8 Eliminación de materiales

- Se corrigió el orden de validación en [materiales.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/materiales.php).
- Antes de la corrección, al eliminar se validaban también nombre, categoría, stock y stock mínimo; por eso aparecía el aviso de campos obligatorios y no se borraba.
- Ahora la acción `eliminar` valida únicamente el ID del material.
- Crear y editar continúan validando nombre, categoría y cantidades.
- Después de eliminar correctamente se redirige al inventario y se muestra confirmación.
- Si el material tiene movimientos o repuestos asociados, se conserva y se muestra un aviso específico de integridad.

### 9.9 Alineación de botones de acciones

- Se alinearon las columnas **Acciones** al extremo derecho de las tablas.
- Los botones **Editar** y **Eliminar** quedan juntos, ordenados y alineados al fondo de cada fila.
- El ajuste se aplica globalmente mediante `.table-actions` y a la última celda de las tablas.
- En pantallas pequeñas se conserva la alineación y se muestran los botones en formato compacto.
- Se definió un ancho uniforme de 88 px, contenido centrado y separación compacta para los botones.
- En pantallas pequeñas se mantiene el ancho compacto de 35 px mostrando solo el icono.
- Se actualizó la versión del CSS a `20261110-10`.
- Se fijó además el ancho de la columna de acciones al contenido necesario y se agregó una regla final de máxima prioridad en el encabezado común para evitar que Bootstrap vuelva a separarlos.
- La versión final del recurso CSS quedó en `20261110-11`.
- Se corrigió el botón **Instrumentos**, que necesita más espacio por su texto: ahora los botones tienen un ancho mínimo compacto, pero se expanden automáticamente sin recortar la etiqueta.
- La versión final del recurso CSS quedó en `20261110-12`.

## 10. Tecnologías utilizadas

| Tecnología/herramienta | Uso |
|---|---|
| PHP 8.x | Lógica del servidor, sesiones, validaciones y vistas. |
| MariaDB/MySQL | Persistencia de datos. |
| Apache | Servidor web local mediante XAMPP. |
| PDO | Conexión y consultas parametrizadas. |
| HTML5 | Estructura de pantallas y formularios. |
| CSS3 | Tema oscuro, responsive, estados y componentes visuales. |
| JavaScript | Interactividad, repintado de formularios y comportamiento de UI. |
| Bootstrap 5 | Grid, componentes base y utilidades. |
| Bootstrap Icons | Iconos de navegación y acciones. |
| Chart.js | Gráficos de reportes. |
| html5-qrcode | Lectura de códigos QR con cámara. |
| qrcode.js | Generación visual de QR en el navegador. |
| PowerShell | Mantenimiento y copia de actualización en Windows. |
| Robocopy | Copia segura de archivos sin tocar la base de datos excluida. |
| Git | Control de versiones, excluido del paquete de actualización. |

## 11. Estructura técnica

```text
sistema-web/
├── assets/              CSS, JavaScript, imágenes y librerías locales
├── config/              conexión, constantes, sesión y producción
├── database/            esquema SQL y usuario de aplicación
├── docs/                manuales, seguridad, checklist y trazabilidad
├── includes/            autenticación, inventario, comercial, PDF y respaldos
├── modules/
│   ├── admin/            administración
│   ├── auth/             login/logout
│   ├── comercial/       ventas, cobros y reportes
│   └── tecnico/         clientes, instrumentos, reparaciones e inventario
├── scripts/              instalación y respaldo automático
├── backups/              copias SQL locales
└── index.php             entrada del sistema
```

La configuración se encuentra en [constantes.php](D:/6-SEM/INS-600/sistema-web/config/constantes.php):

- Base de datos: `supay`.
- Host por defecto: `127.0.0.1`.
- Puerto por defecto: `3306`.
- Usuario y contraseña configurables con `SUPAY_DB_USER` y `SUPAY_DB_PASS`.
- URL base local: `/supay`.
- Sesión máxima por inactividad: 900 segundos.
- Bloqueo por intentos fallidos: 15 minutos.
- Ruta de XAMPP configurable con `XAMPP_ROOT`.

## 12. Instalación inicial

La instalación inicial está documentada en [INSTALADOR.md](D:/6-SEM/INS-600/sistema-web/INSTALADOR.md) y [manual_instalacion.md](D:/6-SEM/INS-600/sistema-web/docs/manual_instalacion.md).

Requisitos:

- Windows.
- XAMPP con Apache, PHP y MariaDB/MySQL.
- PHP 8.x recomendado.
- Permisos de administrador para crear base de datos, usuario y servicios.

Flujo general:

1. Copiar el proyecto a una ruta como `C:\SUPAY-INSTALADOR`.
2. Iniciar XAMPP o ejecutar el instalador.
3. Crear/importar la base `supay` con [supay.sql](D:/6-SEM/INS-600/sistema-web/database/supay.sql).
4. Ejecutar [usuario_app.sql](D:/6-SEM/INS-600/sistema-web/database/usuario_app.sql) con un usuario administrativo de MariaDB.
5. Copiar la aplicación a `C:\xampp\htdocs\supay` o a la ruta de XAMPP configurada.
6. Iniciar Apache y MySQL/MariaDB.
7. Abrir `http://localhost/supay/`.

Los scripts disponibles son [instalar_supay.bat](D:/6-SEM/INS-600/sistema-web/scripts/instalar_supay.bat) y [instalar_supay.ps1](D:/6-SEM/INS-600/sistema-web/scripts/instalar_supay.ps1).

## 13. Actualización sobre una instalación existente

Como las correcciones realizadas son principalmente de código, estilos, validaciones y reportes, **no se debe reemplazar ni importar nuevamente la base de datos** si la instalación existente ya funciona y conserva los datos.

El paquete puede conservar el nombre `SUPAY-INSTALADOR` o enviarse como `SUPAY-INSTALADOR2`. Lo importante es copiar el contenido interno correcto, no una carpeta anidada accidentalmente.

Ejemplo para la persona que recibe la actualización cuando el contenido nuevo está directamente en `C:\SUPAY-INSTALADOR`:

```powershell
$origen = "C:\SUPAY-INSTALADOR"
$destino = "C:\xampp\htdocs\supay"
robocopy $origen $destino /E /XD database backups .git /R:2 /W:2
```

Si al extraer queda una carpeta interna `SUPAY-INSTALADOR2`, usar:

```powershell
$origen = "C:\SUPAY-INSTALADOR\SUPAY-INSTALADOR2"
$destino = "C:\xampp\htdocs\supay"
robocopy $origen $destino /E /XD database backups .git /R:2 /W:2
```

Antes de copiar:

1. Respaldar la carpeta actual y la base de datos.
2. Cerrar temporalmente el navegador de la aplicación.
3. No copiar `database`, `backups` ni `.git` durante una actualización de código.
4. Confirmar que `index.php`, `config`, `includes`, `modules` y `assets` queden directamente dentro de `htdocs\supay`.
5. Reiniciar Apache si PHP o CSS parece estar usando una versión anterior.
6. Hacer recarga forzada del navegador con `Ctrl + F5`.
7. Verificar login, clientes, instrumentos, inventario, repuestos y reportes.

El código `robocopy` devuelve códigos distintos de cero aunque la copia sea correcta; se debe revisar que no aparezca `ERROR` y que el resumen indique los archivos copiados.

### 13.1 Actualización completa con la base actual

Se generó una copia de la base de datos actual en [supay_actual.sql](D:/6-SEM/INS-600/sistema-web/database/supay_actual.sql). Este archivo contiene el esquema y los datos actuales de la instalación de desarrollo.

Para una instalación nueva de otra persona con XAMPP en `C:\xampp`, ejecutar PowerShell como administrador desde el equipo receptor. El siguiente procedimiento:

1. Detecta el paquete en `C:\SUPAY-INSTALADOR`.
2. Guarda la base existente en `C:\SUPAY-RESPALDOS`.
3. Mueve la carpeta anterior de la aplicación a una copia de seguridad.
4. Copia el sistema actualizado a `C:\xampp\htdocs\supay`.
5. Reemplaza la base `supay` con `database\supay_actual.sql`.

La importación es destructiva para la base destino, pero la copia anterior queda guardada antes de comenzar. No ejecutar el procedimiento si se desea conservar datos creados por la otra persona.

```powershell
$origen = 'C:\SUPAY-INSTALADOR'
$xampp = 'C:\xampp'
$destino = Join-Path $xampp 'htdocs\supay'
$mysql = Join-Path $xampp 'mysql\bin\mysql.exe'
$mysqldump = Join-Path $xampp 'mysql\bin\mysqldump.exe'
$fecha = Get-Date -Format 'yyyyMMdd_HHmmss'
$respaldoDir = 'C:\SUPAY-RESPALDOS'
$respaldoBd = Join-Path $respaldoDir "supay_pre_actualizacion_$fecha.sql"
$respaldoApp = "${destino}_respaldo_$fecha"
$bdNueva = Join-Path $origen 'database\supay_actual.sql'

if (-not (Test-Path (Join-Path $origen 'index.php'))) {
    throw "No se encontró index.php directamente en $origen. Extrae el contenido del paquete, no una carpeta anidada."
}
if (-not (Test-Path $mysql) -or -not (Test-Path $mysqldump)) {
    throw 'No se encontraron mysql.exe/mysqldump.exe. Verifica que XAMPP esté instalado en C:\xampp.'
}
if (-not (Test-Path $bdNueva)) {
    throw "No se encontró $bdNueva. El paquete debe incluir database\supay_actual.sql."
}

$confirmacion = Read-Host 'Esto REEMPLAZARÁ la BD supay actual. Escribe REEMPLAZAR para continuar'
if ($confirmacion -cne 'REEMPLAZAR') { throw 'Operación cancelada; no se modificó la instalación.' }

New-Item -ItemType Directory -Path $respaldoDir -Force | Out-Null
& $mysqldump --host=127.0.0.1 --port=3306 --user=root --single-transaction --skip-lock-tables --routines --triggers --no-tablespaces --result-file=$respaldoBd supay
if ($LASTEXITCODE -ne 0 -or -not (Test-Path $respaldoBd)) { throw 'No se pudo crear el respaldo de la base destino.' }

if (Test-Path $destino) { Move-Item -LiteralPath $destino -Destination $respaldoApp }
New-Item -ItemType Directory -Path $destino -Force | Out-Null
robocopy $origen $destino /E /XD database backups .git /R:2 /W:2
if ($LASTEXITCODE -ge 8) { throw 'Falló la copia de los archivos del sistema.' }

Get-Content -Raw -LiteralPath $bdNueva | & $mysql --host=127.0.0.1 --port=3306 --user=root supay
if ($LASTEXITCODE -ne 0) { throw "Falló la importación. La BD anterior está respaldada en $respaldoBd" }

Write-Host "Actualización completa finalizada." -ForegroundColor Green
Write-Host "Aplicación: $destino"
Write-Host "Respaldo BD: $respaldoBd"
Write-Host "Respaldo aplicación: $respaldoApp"
```

El comando supone la configuración XAMPP habitual, donde `root` no tiene contraseña. Si MariaDB solicita contraseña en el equipo receptor, debe ejecutarse el respaldo/importación con las credenciales administrativas de ese equipo o importar el archivo mediante phpMyAdmin.

## 14. Seguridad implementada

La revisión está registrada en [revision_seguridad.md](D:/6-SEM/INS-600/sistema-web/docs/revision_seguridad.md). Los controles principales son:

- Hash bcrypt para contraseñas.
- `password_verify()` en el login.
- Política de contraseña mínima.
- Bloqueo temporal por intentos fallidos.
- Sesiones con expiración por inactividad.
- Cookies de sesión protegidas.
- Regeneración de sesión.
- Control de roles por servidor.
- Tokens CSRF.
- Escape de salidas HTML.
- PDO con consultas preparadas.
- Bitácora de acciones.
- Usuario de base de datos con privilegios mínimos.
- Bloqueo de acceso web directo a respaldos.
- Errores técnicos ocultos en producción y enviados al log.

## 15. Validaciones y pruebas realizadas

Se realizaron o se dejaron preparados los siguientes controles:

- `php -l modules/tecnico/instrumentos.php`: sin errores de sintaxis.
- `php -l modules/tecnico/repuestos.php`: sin errores de sintaxis.
- Verificación de la carga del CSS desde `localhost`.
- Verificación de reglas de autofill y color de formularios.
- Verificación de reglas de hover de tablas con Bootstrap.
- Prueba transaccional de inventario con rollback.
- Identificación y corrección de la función faltante `decimal_positivo()`.
- Revisión de generación y asociación de QR.
- Revisión del flujo de exportación PDF.
- Revisión de copia de actualización con Robocopy excluyendo base de datos, respaldos y Git.

El conjunto formal de casos de prueba está en [checklist_pruebas.md](D:/6-SEM/INS-600/sistema-web/docs/checklist_pruebas.md) y [checklist_pruebas.csv](D:/6-SEM/INS-600/sistema-web/docs/checklist_pruebas.csv). La matriz de trazabilidad está en [trazabilidad.md](D:/6-SEM/INS-600/sistema-web/docs/trazabilidad.md). Los campos de resultado de la checklist deben completarse con una prueba final en el equipo donde se instale.

## 16. Funciones auxiliares relevantes

| Archivo | Responsabilidad |
|---|---|
| [conexion.php](D:/6-SEM/INS-600/sistema-web/config/conexion.php) | Crea la conexión PDO con modo de errores por excepción y UTF-8. |
| [sesion.php](D:/6-SEM/INS-600/sistema-web/config/sesion.php) | Configuración de sesión y expiración. |
| [auth.php](D:/6-SEM/INS-600/sistema-web/includes/auth.php) | Login, permisos, sesión y bitácora. |
| [helpers.php](D:/6-SEM/INS-600/sistema-web/includes/helpers.php) | Escape HTML, redirecciones, CSRF y validaciones comunes. |
| [inventario.php](D:/6-SEM/INS-600/sistema-web/includes/inventario.php) | Roles de inventario, validaciones decimales y stock mínimo. |
| [tecnico.php](D:/6-SEM/INS-600/sistema-web/includes/tecnico.php) | Operaciones técnicas y códigos de instrumentos. |
| [comercial.php](D:/6-SEM/INS-600/sistema-web/includes/comercial.php) | Validaciones comerciales y consultas de ingresos. |
| [pdf_reporte.php](D:/6-SEM/INS-600/sistema-web/includes/pdf_reporte.php) | Construcción de reportes PDF. |
| [respaldo.php](D:/6-SEM/INS-600/sistema-web/includes/respaldo.php) | Generación, validación y restauración de respaldos. |
| [header.php](D:/6-SEM/INS-600/sistema-web/includes/header.php) | Layout común, navegación y reglas finales de estilo. |
| [footer.php](D:/6-SEM/INS-600/sistema-web/includes/footer.php) | Cierre de layout y scripts comunes. |

## 17. Documentación complementaria

- [manual_instalacion.md](D:/6-SEM/INS-600/sistema-web/docs/manual_instalacion.md): instalación y configuración.
- [manual_usuario.md](D:/6-SEM/INS-600/sistema-web/docs/manual_usuario.md): operación por rol.
- [capacitacion.md](D:/6-SEM/INS-600/sistema-web/docs/capacitacion.md): guía de capacitación.
- [revision_seguridad.md](D:/6-SEM/INS-600/sistema-web/docs/revision_seguridad.md): controles de seguridad.
- [checklist_pruebas.md](D:/6-SEM/INS-600/sistema-web/docs/checklist_pruebas.md): pruebas funcionales.
- [trazabilidad.md](D:/6-SEM/INS-600/sistema-web/docs/trazabilidad.md): relación entre requisitos y pruebas.
- [sis_history.md](D:/6-SEM/INS-600/sistema-web/sis_history.md): historial del proyecto.

## 18. Pendientes y recomendaciones

1. Cambiar las credenciales semilla antes de instalar en un equipo de producción.
2. Definir `SUPAY_DB_USER` y `SUPAY_DB_PASS` como variables de entorno, evitando contraseñas en archivos compartidos.
3. Configurar HTTPS si se utilizará la cámara del celular para escanear QR desde otra máquina.
4. Completar la checklist formal en el equipo final.
5. Probar restauración de respaldos en una copia antes de usarla sobre datos reales.
6. Mantener fuera del paquete de actualización los respaldos y la base de datos existente.
7. Confirmar que Apache y PHP estén apuntando al mismo directorio que se actualiza.
8. Limpiar carpetas anidadas antiguas como `SUPAY-INSTALADOR2` si quedaron dentro de `htdocs\supay` después de una copia equivocada, siempre verificando primero su contenido.
9. Programar respaldos automáticos con [respaldo_auto.php](D:/6-SEM/INS-600/sistema-web/scripts/respaldo_auto.php).
10. Mantener una copia de seguridad antes de cada actualización.

## 19. Estado final

El proyecto cuenta con una base funcional para la gestión técnica y comercial del taller. Las correcciones principales aplicadas durante esta etapa fueron:

- Visibilidad del texto en formularios y autocompletado.
- Hover oscuro y legible en inventario.
- Alertas de stock mínimo más claras.
- Generación y asociación de códigos QR.
- Manejo transaccional de repuestos y descuentos de inventario.
- Mensajes de error más precisos.
- Reportes PDF con identidad SUPAY y subtítulos según el tipo de reporte.
- Procedimiento de actualización sin reemplazar la base de datos.

La base de datos existente debe conservarse durante una actualización de código. El siguiente paso recomendado es ejecutar la checklist completa en la instalación final y registrar el resultado de cada caso.
