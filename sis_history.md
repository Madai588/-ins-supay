# SUPAY — Historia y resumen integral del sistema

> Sistema web local para la administración de un taller de luthería, reparación, inventario y operaciones comerciales relacionadas con instrumentos musicales.

## 1. Identidad del proyecto

- **Nombre:** SUPAY.
- **Tipo:** aplicación web administrativa.
- **Propósito:** centralizar clientes, instrumentos, reparaciones, materiales, proveedores, ventas, cobros, reportes y respaldos.
- **Entorno principal:** XAMPP con Apache, PHP y MariaDB/MySQL.
- **URL local:** `http://localhost/supay/`.
- **Ruta habitual de despliegue:** `D:\xamppP\htdocs\supay`.
- **Idioma de la interfaz:** español.
- **Arquitectura:** aplicación PHP modular con HTML, CSS, JavaScript y base de datos relacional.

## 2. Qué problema resuelve

SUPAY reemplaza el control disperso del taller por un flujo único y trazable. Permite registrar un instrumento asociado a un cliente, identificarlo mediante un código QR, abrir reparaciones, controlar repuestos y materiales, registrar ventas o cobros y consultar reportes del negocio.

El sistema está pensado para operar en una computadora servidor dentro del taller y, si se configura HTTPS y red local, también puede ser utilizado desde otros dispositivos autorizados.

## 3. Roles y permisos

### Administrador

Tiene acceso completo al sistema:

- Panel general del taller.
- Clientes e instrumentos.
- Reparaciones y repuestos.
- Materiales, categorías, proveedores y movimientos.
- Ventas y cobros.
- Reportes operativos y financieros.
- Respaldos manuales y restauración.
- Administración de usuarios.

### Maestro Luthier

Tiene acceso a las funciones técnicas:

- Consultar clientes.
- Registrar y consultar instrumentos.
- Registrar reparaciones, diagnósticos y estados.
- Agregar repuestos utilizados.
- Consultar materiales.
- Escanear o escribir códigos QR.
- Consultar el historial del instrumento.

No tiene acceso a ventas, cobros, reportes financieros, respaldos ni administración de usuarios.

## 4. Módulos funcionales

### Autenticación

Ubicación: `modules/auth/`

- Inicio de sesión con usuario y contraseña.
- Verificación con `password_verify()`.
- Política de contraseña: mínimo de 8 caracteres, mayúscula, número y carácter especial.
- Bloqueo temporal después de tres intentos fallidos.
- Regeneración del identificador de sesión después del acceso exitoso.
- Cierre de sesión.

### Panel principal

Ubicaciones: `modules/admin/index.php` y `modules/tecnico/index.php`

- Resumen de reparaciones según estado.
- Resumen de ingresos, ventas e instrumentos.
- Alertas de materiales en stock mínimo.
- Accesos rápidos a los módulos más utilizados.

### Clientes

Ubicación: `modules/tecnico/clientes.php`

- Registro y edición de clientes.
- Datos de nombre, teléfono y dirección.
- Búsqueda y consulta de clientes.
- Asociación con instrumentos.

### Instrumentos y QR

Ubicaciones: `modules/tecnico/instrumentos.php`, `instrumento_editar.php`, `instrumento_qr.php`, `escaner.php` e `historial.php`

- Registro del tipo y descripción del instrumento.
- Asociación con un cliente.
- Generación de código QR único.
- Impresión de etiquetas QR.
- Lectura mediante cámara usando `html5-qrcode`.
- Consulta manual alternativa por código.
- Historial de reparaciones y movimientos relacionados.

### Reparaciones

Ubicaciones: `modules/tecnico/reparaciones.php` y `repuestos.php`

- Registro de ingreso del instrumento.
- Fecha, diagnóstico y costo en bolivianos.
- Estados: `en_revision`, `en_reparacion` y `finalizado`.
- Registro de repuestos utilizados.
- Descuento automático del stock al usar un material.
- Historial QR de la reparación.

### Inventario

Ubicaciones: `modules/tecnico/materiales.php`, `movimientos.php` y `proveedores.php`

- CRUD de materiales.
- Categorías: maderas, cueros, pernos y herrajes.
- Control de stock y stock mínimo.
- Entradas y salidas de inventario.
- Asociación con proveedores.
- Historial de movimientos.
- Alertas visuales para materiales en mínimo.

### Ventas

Ubicación: `modules/comercial/ventas.php`

- Registro de ventas.
- Asociación con cliente e instrumento.
- Fecha y total de venta.
- Consulta y filtrado de ventas.

### Cobros

Ubicación: `modules/comercial/cobros.php`

- Registro de cobros asociados a reparaciones.
- Monto y fecha.
- Consulta de cobros registrados.

### Reportes

Ubicaciones: `modules/comercial/reportes.php`, `exportar.php` y `exportar_pdf.php`

- Ingresos por periodo.
- Filtros mensual, fechas inicial y final.
- Resumen de ventas, cobros y reparaciones.
- Reportes de stock.
- Gráficos mediante Chart.js.
- Exportación a CSV.
- Exportación e impresión en PDF.

### Respaldos

Ubicaciones: `modules/admin/respaldos.php`, `includes/respaldo.php` y `scripts/respaldo_auto.php`

- Generación manual de respaldos SQL.
- Respaldo automático mediante tarea programada de Windows.
- Descarga de copias.
- Restauración controlada.
- Eliminación de respaldos.
- Protección contra acceso web directo a la carpeta de respaldos.

### Usuarios

Ubicación: `modules/admin/usuarios.php`

- Creación de usuarios.
- Roles de administrador y luthier.
- Activación y desactivación de cuentas.
- Control de estado de acceso.

## 5. Base de datos

Base principal: `database/supay.sql`.

Tablas principales:

- `usuario`: cuentas, roles, estado e intentos fallidos.
- `cliente`: información de clientes.
- `instrumento`: instrumentos, código QR y cliente asociado.
- `reparacion`: diagnóstico, estado, fecha y costo.
- `proveedor`: proveedores de materiales.
- `material`: materiales, categorías, stock y mínimos.
- `repuesto_utilizado`: materiales usados en cada reparación.
- `movimiento_inventario`: entradas y salidas.
- `venta`: operaciones comerciales.
- `cobro`: pagos asociados a reparaciones.
- `bitacora`: auditoría de acciones del sistema.

La base usa claves foráneas, índices, restricciones de cantidades y motor InnoDB. El acceso de la aplicación se realiza mediante PDO y consultas preparadas.

## 6. Seguridad implementada

- Contraseñas almacenadas con hash bcrypt.
- Validación de política de contraseñas.
- Bloqueo temporal por intentos fallidos.
- Sesión con tiempo máximo de inactividad de 15 minutos.
- Cookies de sesión HttpOnly.
- Regeneración de sesión al iniciar sesión.
- Control de roles en el servidor, no solamente en la interfaz.
- Protección CSRF para formularios POST.
- Escape HTML mediante `e()`/`htmlspecialchars()`.
- Consultas PDO parametrizadas.
- Registro de accesos, modificaciones, reportes, movimientos y respaldos.
- Usuario de base de datos con privilegios mínimos.
- Validación de nombres de archivos de respaldo.
- Desactivación de errores técnicos visibles en producción.

## 7. Diseño y mejoras visuales realizadas

La interfaz fue modernizada sin cambiar la lógica funcional:

- Paleta oscura profesional basada en azul carbón, cobre, naranja suave y marfil.
- Login con fondo radial, profundidad, círculos decorativos y sombras.
- Tarjeta de acceso con mejor jerarquía visual.
- Navegación lateral fija y responsive.
- Barra superior con usuario, rol y estado del sistema.
- Tarjetas, botones, formularios y tablas con bordes, contraste y estados coherentes.
- Cabeceras de tablas en tono azul carbón sólido.
- Valores monetarios como `Bs 0.00` con contraste blanco.
- Campos numéricos con texto blanco sobre fondo oscuro.
- Barras de desplazamiento ocultas visualmente sin quitar la posibilidad de desplazarse.
- Menú lateral adaptable para pantallas pequeñas.
- Soporte para `prefers-reduced-motion`.
- Recursos locales para evitar dependencia de internet durante la ejecución.

Archivo principal de estilos: `assets/css/supay.css`.

## 8. Recursos y dependencias

El sistema incluye localmente sus dependencias principales dentro de `assets/vendor/`:

- Bootstrap CSS.
- Bootstrap Bundle JS.
- Bootstrap Icons.
- Chart.js.
- QRCode.js.
- html5-qrcode.

Esto permite trabajar en una red local sin depender de CDN externos.

## 9. Estructura principal

```text
supay/
├── assets/
│   ├── css/              estilos globales
│   ├── img/              logo y recursos visuales
│   ├── js/               comportamiento de interfaz
│   └── vendor/           librerías locales
├── config/               conexión, sesión y constantes
├── database/             scripts SQL de instalación
├── docs/                 manuales, seguridad y trazabilidad
├── includes/             lógica compartida y componentes
├── modules/
│   ├── admin/            usuarios, respaldos y panel administrativo
│   ├── auth/             login y logout
│   ├── comercial/        ventas, cobros y reportes
│   └── tecnico/          clientes, instrumentos, reparaciones e inventario
├── backups/              copias SQL protegidas
├── scripts/              respaldo automático e instalación
├── index.php             entrada principal
└── sis_history.md        este resumen
```

## 10. Instalación en otra computadora

1. Instalar XAMPP con Apache, PHP 8.x y MariaDB.
2. Copiar el proyecto a `D:\xamppP\htdocs\supay` o `C:\xampp\htdocs\supay`.
3. Confirmar que `index.php` esté directamente dentro de `supay`.
4. Importar `database/supay.sql` solamente si se trata de una instalación nueva.
5. Ejecutar `database/usuario_app.sql` con una cuenta administrativa de MariaDB.
6. Revisar `config/constantes.php` y las variables `SUPAY_DB_USER` y `SUPAY_DB_PASS`.
7. Iniciar Apache y MySQL desde XAMPP.
8. Abrir `http://localhost/supay/`.

Si la computadora ya tiene datos, no se debe volver a importar `supay.sql`, porque ese script recrea las tablas. En ese caso se reemplaza el código de la aplicación y se conserva la base de datos existente.

## 11. Respaldo y migración segura

Antes de reemplazar una instalación existente:

1. Generar un respaldo desde el módulo de respaldos.
2. Detener Apache.
3. Renombrar la carpeta anterior como `supay_respaldo`.
4. Copiar la nueva carpeta a `htdocs\supay`.
5. Conservar el archivo de conexión si contiene credenciales específicas de esa computadora.
6. Iniciar Apache y MySQL.
7. Probar login, clientes, instrumentos, reparaciones, inventario, reportes y respaldos.
8. Mantener la carpeta anterior hasta confirmar que todo funciona.

Los cambios visuales no requieren modificar la base de datos.

## 12. Archivos importantes

- `config/constantes.php`: nombre de base, URL, sesión y rutas de XAMPP.
- `config/conexion.php`: conexión PDO a MariaDB.
- `config/sesion.php`: configuración de sesión.
- `includes/auth.php`: autenticación, roles y bitácora.
- `includes/header.php`: navegación y estructura visual interna.
- `includes/footer.php`: cierre de la interfaz interna.
- `assets/css/supay.css`: diseño completo del sistema.
- `assets/js/ui.js`: alertas, menú responsive, contraseñas y acciones comunes.
- `database/supay.sql`: estructura y datos iniciales.
- `database/usuario_app.sql`: usuario de base de datos con privilegios limitados.
- `scripts/respaldo_auto.php`: respaldo automático para el Programador de tareas.

## 13. Estado actual

El sistema cuenta con los módulos funcionales principales, control de acceso por roles, base de datos relacional, reportes, QR, inventario y respaldo. La interfaz fue actualizada visualmente sin cambiar la lógica de negocio.

Pendientes operativos recomendados:

- Cambiar las credenciales iniciales antes de producción.
- Configurar HTTPS si se utilizará la cámara desde celulares.
- Verificar respaldos automáticos en otro disco o almacenamiento externo.
- Mantener MariaDB accesible solamente desde el servidor.
- Probar la migración en una computadora de destino antes de retirar la instalación anterior.

## 14. Credenciales y datos sensibles

Las credenciales de acceso y de base de datos deben mantenerse fuera de este documento y no deben publicarse en repositorios ni enviarse por chat. Para el despliegue, revisar el archivo local de credenciales y las variables de entorno configuradas en Apache y en el Programador de tareas.

