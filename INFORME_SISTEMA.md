# INFORME TÉCNICO DEL SISTEMA SUPAY

## Alcance y criterio de evidencia

Informe elaborado a partir del código y documentación presentes en el proyecto. Las referencias usan ruta y línea. Cuando el dato procede de un documento de proyecto, se identifica expresamente; cuando solo se infiere del código, se marca `[DEDUCIDO]`. Los datos que no aparecen en los archivos revisados se marcan `[NO ENCONTRADO]`.

## 1. DATOS DEL PROYECTO

| Dato | Resultado | Evidencia |
|---|---|---|
| Título | Sistema Integral de Gestión Comercial y Técnica con Integración QR para la Optimización de Procesos en el Taller de Instrumentos de Percusión SUPAY | [perfil_de_proyecto_TMG-600A_completo.docx](D:/6-SEM/INS-600/sistema-web/perfil_de_proyecto_TMG-600A_completo.docx), portada y texto extraído del documento |
| Nombre corto | SUPAY | [sis_history.md](D:/6-SEM/INS-600/sistema-web/sis_history.md:7) y [config/constantes.php](D:/6-SEM/INS-600/sistema-web/config/constantes.php:10) |
| Autor | Madai Nayeli Surco Quisbert | [perfil_de_proyecto_TMG-600A_completo.docx](D:/6-SEM/INS-600/sistema-web/perfil_de_proyecto_TMG-600A_completo.docx), portada |
| Tutor | Ing. Adrian Quisbert | [perfil_de_proyecto_TMG-600A_completo.docx](D:/6-SEM/INS-600/sistema-web/perfil_de_proyecto_TMG-600A_completo.docx), portada |
| Institución | Escuela Industrial Superior Pedro Domingo Murillo; Instituto Tecnológico; Carrera Informática Industrial | [perfil_de_proyecto_TMG-600A_completo.docx](D:/6-SEM/INS-600/sistema-web/perfil_de_proyecto_TMG-600A_completo.docx), portada |
| Lugar y fecha del perfil | La Paz, Bolivia; octubre de 2026 | [perfil_de_proyecto_TMG-600A_completo.docx](D:/6-SEM/INS-600/sistema-web/perfil_de_proyecto_TMG-600A_completo.docx), portada |
| Problemática | Gestión analógica y no centralizada: pérdida de trazabilidad técnica, desfases de inventario, desorden en ventas/cobros, vulnerabilidad de registros físicos y falta de datos consolidados | [perfil_de_proyecto_TMG-600A_completo.docx](D:/6-SEM/INS-600/sistema-web/perfil_de_proyecto_TMG-600A_completo.docx), “Planteamiento del problema” y “Problema principal” |
| Objetivo general | Desarrollar un sistema web con base relacional e integración QR para optimizar inventarios, agilizar ventas y permitir trazabilidad de servicios de luthería | [perfil_de_proyecto_TMG-600A_completo.docx](D:/6-SEM/INS-600/sistema-web/perfil_de_proyecto_TMG-600A_completo.docx), “Objetivo principal” |
| Objetivos específicos | Levantar requisitos; diseñar arquitectura, BD e interfaces; integrar QR, stock y registro comercial; implementar respaldos/reportes; validar con pruebas | [perfil_de_proyecto_TMG-600A_completo.docx](D:/6-SEM/INS-600/sistema-web/perfil_de_proyecto_TMG-600A_completo.docx), “Objetivos específicos” |

El código confirma el alcance operativo de clientes, instrumentos, reparaciones, inventario, ventas, cobros, reportes, respaldos y usuarios en [sis_history.md](D:/6-SEM/INS-600/sistema-web/sis_history.md:47), pero no contiene una especificación formal separada de requisitos.

## 2. TECNOLOGÍA

### 2.1 Tecnologías y versiones

| Elemento | Evidencia real |
|---|---|
| Lenguaje servidor | PHP con `declare(strict_types=1)` en configuración y módulos; versión exacta `[NO ENCONTRADO]`. La documentación solo exige PHP 8.x ([docs/manual_instalacion.md](D:/6-SEM/INS-600/sistema-web/docs/manual_instalacion.md:5)). |
| Base de datos | MariaDB/MySQL; el dump indica MariaDB 10.4.32 ([database/supay_actual.sql](D:/6-SEM/INS-600/sistema-web/database/supay_actual.sql:1)). Motor esperado: InnoDB. |
| Acceso a datos | PDO, `pdo_mysql`, consultas preparadas y excepciones ([config/conexion.php](D:/6-SEM/INS-600/sistema-web/config/conexion.php:5)). |
| HTML/CSS/JavaScript | Vistas PHP generan HTML; CSS propio en [assets/css/supay.css](D:/6-SEM/INS-600/sistema-web/assets/css/supay.css) y [assets/css/estilos.css](D:/6-SEM/INS-600/sistema-web/assets/css/estilos.css); JS en [assets/js/ui.js](D:/6-SEM/INS-600/sistema-web/assets/js/ui.js) y [assets/js/tecnico.js](D:/6-SEM/INS-600/sistema-web/assets/js/tecnico.js). |
| Bootstrap | Bootstrap CSS 5.3.3 en el encabezado de [assets/vendor/bootstrap.min.css](D:/6-SEM/INS-600/sistema-web/assets/vendor/bootstrap.min.css:2); no hay `package.json`. |
| Bootstrap Icons | 1.11.3 en [assets/vendor/bootstrap-icons/bootstrap-icons.css](D:/6-SEM/INS-600/sistema-web/assets/vendor/bootstrap-icons/bootstrap-icons.css:2). |
| Chart.js | Archivo local [assets/vendor/chart.min.js](D:/6-SEM/INS-600/sistema-web/assets/vendor/chart.min.js), versión exacta `[NO ENCONTRADO]`; se usa en [modules/comercial/reportes.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/reportes.php:9). |
| QR | `qrcode.min.js` para generar etiquetas y `html5-qrcode.min.js` para cámara; versiones exactas `[NO ENCONTRADO]` ([modules/tecnico/instrumento_qr.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/instrumento_qr.php:2), [modules/tecnico/escaner.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/escaner.php:4)). |
| Composer/npm | `[NO ENCONTRADO]`: no existen `composer.json`, `package.json`, `requirements.txt` ni `.csproj`. |

### 2.2 Arquitectura

Aplicación web PHP modular server-rendered, cliente-servidor y monolítica `[DEDUCIDO]`: Apache sirve PHP; los módulos controlan petición, validación, SQL y vista en el mismo archivo; `includes/` concentra autenticación, helpers, inventario, comercial, respaldos y plantilla; MariaDB persiste. No hay API REST separada ni SPA: las operaciones son formularios GET/POST y redirecciones HTML ([index.php](D:/6-SEM/INS-600/sistema-web/index.php:1), [includes/header.php](D:/6-SEM/INS-600/sistema-web/includes/header.php:1)).

### 2.3 Ejecución

1. Instalar XAMPP con Apache, PHP 8.x y MariaDB ([docs/manual_instalacion.md](D:/6-SEM/INS-600/sistema-web/docs/manual_instalacion.md:3)).
2. Copiar a `D:\xamppP\htdocs\supay` según el manual, o a `C:\xampp\htdocs\supay` usando el instalador ([docs/manual_instalacion.md](D:/6-SEM/INS-600/sistema-web/docs/manual_instalacion.md:5), [scripts/instalar_supay.ps1](D:/6-SEM/INS-600/sistema-web/scripts/instalar_supay.ps1:1)).
3. Importar [database/supay.sql](D:/6-SEM/INS-600/sistema-web/database/supay.sql:1) y ejecutar [database/usuario_app.sql](D:/6-SEM/INS-600/sistema-web/database/usuario_app.sql:1), o ejecutar el instalador PowerShell que crea la BD, importa ambos archivos e inicia XAMPP ([scripts/instalar_supay.ps1](D:/6-SEM/INS-600/sistema-web/scripts/instalar_supay.ps1:51)).
4. URL local: `http://localhost/supay/` o login directo `http://localhost/supay/modules/auth/login.php` ([INSTALADOR.md](D:/6-SEM/INS-600/sistema-web/INSTALADOR.md:27)).
5. Conexión por defecto: host `127.0.0.1`, puerto `3306`, BD `supay`, usuario `supay_app` y contraseña por defecto definida en configuración ([config/constantes.php](D:/6-SEM/INS-600/sistema-web/config/constantes.php:3)). Se pueden definir `SUPAY_DB_USER`, `SUPAY_DB_PASS`, `XAMPP_ROOT` y `MYSQLDUMP_PATH` ([config/constantes.php](D:/6-SEM/INS-600/sistema-web/config/constantes.php:6)).
6. No hay puerto propio de la aplicación; usa el puerto de Apache de XAMPP `[NO ENCONTRADO]`.

## 3. ESTRUCTURA DEL PROYECTO

```text
/
├─ assets/                 CSS, JavaScript, imágenes y librerías locales
├─ backups/                respaldos SQL y .htaccess de denegación web
├─ config/                 conexión, constantes, producción y sesión
├─ database/               esquema/seed, dump actual y usuario de BD
├─ docs/                   manuales, seguridad, trazabilidad y checklist
├─ includes/               autenticación, helpers, reglas de negocio, PDF y backups
├─ modules/
│  ├─ auth/                login y logout
│  ├─ admin/               panel, usuarios y respaldos
│  ├─ comercial/           ventas, cobros, reportes y exportaciones
│  └─ tecnico/             clientes, instrumentos, QR, reparaciones e inventario
├─ scripts/                instalador y respaldo automático
├─ index.php               redirección según sesión/rol
├─ INSTALADOR.md           instalación rápida
├─ Acceso_Contraseñas.txt  credenciales locales documentadas
├─ cambios.md              historial técnico de cambios
├─ sis_history.md          resumen funcional
└─ perfil_de_proyecto...docx  perfil académico
```

Evidencia: [index.php](D:/6-SEM/INS-600/sistema-web/index.php:1), inventario de archivos del proyecto y referencias de [sis_history.md](D:/6-SEM/INS-600/sistema-web/sis_history.md:225).

## 4. BASE DE DATOS

### 4.1 Motor, nombre e inicialización

Motor MariaDB 10.4.32 en el dump; nombre `supay`; charset `utf8mb4`, collation `utf8mb4_unicode_ci`, tablas InnoDB ([database/supay_actual.sql](D:/6-SEM/INS-600/sistema-web/database/supay_actual.sql:1), [database/supay.sql](D:/6-SEM/INS-600/sistema-web/database/supay.sql:1)). No hay sistema de migraciones versionadas: el SQL elimina y recrea tablas ([database/supay.sql](D:/6-SEM/INS-600/sistema-web/database/supay.sql:3)).

### 4.2 Tablas, columnas, claves y restricciones

En la tabla siguiente, `NN` significa `NOT NULL`, `PK` clave primaria, `FK` clave foránea y `AI` autoincremento. Todos los tipos y restricciones provienen de [database/supay.sql](D:/6-SEM/INS-600/sistema-web/database/supay.sql:6); el dump actual confirma además los índices explícitos en [database/supay_actual.sql](D:/6-SEM/INS-600/sistema-web/database/supay_actual.sql:25).

| Tabla | Columnas (tipo; nulabilidad; defecto) | PK/FK, UNIQUE, CHECK e índices |
|---|---|---|
| `usuario` | `id_usuario INT UNSIGNED NN AI`; `nombre VARCHAR(120) NN`; `usuario VARCHAR(50) NN`; `password_hash VARCHAR(255) NN`; `rol ENUM('administrador','luthier') NN`; `activo TINYINT(1) NN DEFAULT 1`; `intentos_fallidos TINYINT UNSIGNED NN DEFAULT 0`; `bloqueado_hasta DATETIME NULL`; `creado_en TIMESTAMP NN DEFAULT CURRENT_TIMESTAMP` | PK `id_usuario`; UNIQUE `usuario`; índice `idx_usuario_activo(usuario,activo)` |
| `cliente` | `id_cliente INT UNSIGNED NN AI`; `nombre VARCHAR(120) NN`; `telefono VARCHAR(30) NULL`; `direccion VARCHAR(180) NULL` | PK `id_cliente`; sin FK/UNIQUE/CHECK adicionales |
| `instrumento` | `id_instrumento INT UNSIGNED NN AI`; `codigo_qr VARCHAR(80) NN`; `tipo VARCHAR(80) NN`; `descripcion TEXT NULL`; `id_cliente INT UNSIGNED NN` | PK; UNIQUE `codigo_qr`; FK `id_cliente → cliente.id_cliente`; índice `idx_instrumento_cliente` |
| `reparacion` | `id_reparacion INT UNSIGNED NN AI`; `id_instrumento INT UNSIGNED NN`; `id_usuario INT UNSIGNED NN`; `diagnostico TEXT NN`; `estado ENUM('en_revision','en_reparacion','finalizado') NN DEFAULT 'en_revision'`; `fecha_ingreso DATETIME NN DEFAULT CURRENT_TIMESTAMP`; `costo DECIMAL(10,2) NN DEFAULT 0.00` | PK; FK a instrumento y usuario; CHECK `costo >= 0`; índice `idx_reparacion_instrumento_fecha` |
| `proveedor` | `id_proveedor INT UNSIGNED NN AI`; `nombre VARCHAR(120) NN`; `contacto VARCHAR(120) NULL` | PK; sin UNIQUE/CHECK |
| `material` | `id_material INT UNSIGNED NN AI`; `nombre VARCHAR(120) NN`; `categoria ENUM('maderas','cueros','pernos','herrajes') NN`; `stock DECIMAL(10,2) NN DEFAULT 0.00`; `stock_minimo DECIMAL(10,2) NN DEFAULT 0.00`; `id_proveedor INT UNSIGNED NULL` | PK; FK a proveedor `ON DELETE SET NULL`; CHECK `stock >= 0 AND stock_minimo >= 0`; índice `idx_material_categoria_stock` |
| `repuesto_utilizado` | `id_detalle INT UNSIGNED NN AI`; `id_reparacion INT UNSIGNED NN`; `id_material INT UNSIGNED NN`; `cantidad DECIMAL(10,2) NN` | PK; FK a reparación y material; CHECK `cantidad > 0`; índices de ambas FK |
| `movimiento_inventario` | `id_movimiento INT UNSIGNED NN AI`; `id_material INT UNSIGNED NN`; `id_usuario INT UNSIGNED NN`; `tipo ENUM('entrada','salida') NN`; `cantidad DECIMAL(10,2) NN`; `fecha DATETIME NN DEFAULT CURRENT_TIMESTAMP` | PK; FK a material y usuario; CHECK `cantidad > 0`; índice `idx_movimiento_material_fecha` |
| `venta` | `id_venta INT UNSIGNED NN AI`; `id_cliente INT UNSIGNED NN`; `id_instrumento INT UNSIGNED NULL`; `fecha DATETIME NN DEFAULT CURRENT_TIMESTAMP`; `total DECIMAL(10,2) NN` | PK; FK a cliente e instrumento; CHECK SQL `total >= 0`; índices de FK y `idx_venta_fecha` |
| `cobro` | `id_cobro INT UNSIGNED NN AI`; `id_reparacion INT UNSIGNED NN`; `monto DECIMAL(10,2) NN`; `fecha DATETIME NN DEFAULT CURRENT_TIMESTAMP` | PK; FK a reparación; CHECK `monto > 0`; índices de FK y `idx_cobro_fecha` |
| `bitacora` | `id_log BIGINT UNSIGNED NN AI`; `id_usuario INT UNSIGNED NULL`; `accion VARCHAR(80) NN`; `detalle VARCHAR(500) NULL`; `ip VARCHAR(45) NULL`; `fecha DATETIME NN DEFAULT CURRENT_TIMESTAMP` | PK; FK a usuario `ON DELETE SET NULL`; índices `idx_bitacora_fecha`, `idx_bitacora_usuario` |

No se encontraron vistas, procedimientos, funciones ni triggers en los SQL revisados `[NO ENCONTRADO]` ([database/supay_actual.sql](D:/6-SEM/INS-600/sistema-web/database/supay_actual.sql:356)).

### 4.3 Seeds y datos disponibles

El seed reproducible [database/supay.sql](D:/6-SEM/INS-600/sistema-web/database/supay.sql:17) crea dos usuarios, dos proveedores, dos clientes, dos instrumentos y cuatro materiales. No inserta reparaciones, ventas, cobros, movimientos ni bitácora. [database/supay_actual.sql](D:/6-SEM/INS-600/sistema-web/database/supay_actual.sql:43) es un dump posterior con registros operativos y valores distintos; por tanto, no debe asumirse que ambos archivos representan el mismo estado.

## 5. LOGIN, ROLES Y SEGURIDAD

| Aspecto | Implementación | Evidencia |
|---|---|---|
| Autenticación | Usuario y contraseña contra `usuario`, cuenta activa; `password_verify()` | [modules/auth/login.php](D:/6-SEM/INS-600/sistema-web/modules/auth/login.php:4) |
| Almacenamiento | Hash generado con `password_hash(..., PASSWORD_DEFAULT)`; seed contiene hashes bcrypt `$2y$10$` | [modules/admin/usuarios.php](D:/6-SEM/INS-600/sistema-web/modules/admin/usuarios.php:23), [database/supay.sql](D:/6-SEM/INS-600/sistema-web/database/supay.sql:17) |
| Política | Mínimo 8 caracteres, mayúscula, dígito y carácter especial; se aplica en login y alta/cambio de usuario | [includes/helpers.php](D:/6-SEM/INS-600/sistema-web/includes/helpers.php:7), [modules/auth/login.php](D:/6-SEM/INS-600/sistema-web/modules/auth/login.php:7) |
| Bloqueo | Tres fallos activan `bloqueado_hasta` durante 15 minutos; el contador se reinicia al alcanzar tres y al login correcto | [modules/auth/login.php](D:/6-SEM/INS-600/sistema-web/modules/auth/login.php:15), [config/constantes.php](D:/6-SEM/INS-600/sistema-web/config/constantes.php:12) |
| Sesión | Cookie HttpOnly, SameSite=Lax, Secure si HTTPS; expiración por 900 segundos de inactividad; regeneración de ID tras login | [config/sesion.php](D:/6-SEM/INS-600/sistema-web/config/sesion.php:5), [modules/auth/login.php](D:/6-SEM/INS-600/sistema-web/modules/auth/login.php:25) |
| CSRF | Token de sesión de 32 bytes y `hash_equals`; POST protegido en módulos que llaman `validar_post_csrf()` | [includes/helpers.php](D:/6-SEM/INS-600/sistema-web/includes/helpers.php:5), [includes/tecnico.php](D:/6-SEM/INS-600/sistema-web/includes/tecnico.php:6) |
| Entrada/salida | PDO preparado y escape HTML con `htmlspecialchars` | [config/conexion.php](D:/6-SEM/INS-600/sistema-web/config/conexion.php:8), [includes/helpers.php](D:/6-SEM/INS-600/sistema-web/includes/helpers.php:3) |
| Auditoría | Bitácora con usuario, acción, detalle, IP y fecha; los errores de bitácora se registran en log | [includes/auth.php](D:/6-SEM/INS-600/sistema-web/includes/auth.php:9) |
| Privilegios BD | `supay_app` solo recibe SELECT, INSERT, UPDATE y DELETE | [database/usuario_app.sql](D:/6-SEM/INS-600/sistema-web/database/usuario_app.sql:1) |
| Cabeceras / rate limit | No se encontró configuración explícita de Content-Security-Policy, HSTS, X-Frame-Options, limitación de peticiones HTTP ni rate limiter `[NO ENCONTRADO]`. | Código de configuración revisado |
| Archivos backup | `.htaccess` deniega acceso web y el nombre se valida con regex segura | [backups/.htaccess](D:/6-SEM/INS-600/sistema-web/backups/.htaccess:1), [includes/respaldo.php](D:/6-SEM/INS-600/sistema-web/includes/respaldo.php:6) |

### Roles y protección de rutas

| Rol | Permisos observables |
|---|---|
| `administrador` | Panel administrativo; clientes/instrumentos/reparaciones; CRUD de materiales y proveedores; movimientos; ventas; cobros; reportes; respaldos; usuarios. La protección se hace con `exigir_rol('administrador')`, `exigir_inventario_admin()` o `exigir_comercial_admin()`. |
| `luthier` | Panel técnico; clientes/instrumentos; reparaciones/estados/repuestos; consulta de materiales; escáner e historial QR. `exigir_tecnico()` permite administrador y luthier, mientras inventario de escritura/comercial/admin quedan restringidos. |

La matriz documental coincide en lo principal con [docs/manual_usuario.md](D:/6-SEM/INS-600/sistema-web/docs/manual_usuario.md:3). Login y logout son las únicas pantallas públicas identificables; el resto llama una función de exigencia de sesión/rol `[DEDUCIDO]` ([includes/auth.php](D:/6-SEM/INS-600/sistema-web/includes/auth.php:6)).

## 6. FUNCIONALIDADES Y CASOS DE USO

| ID | Actor | Funcionalidad implementada |
|---|---|---|
| CUF-01 | Visitante | Ver login, enviar credenciales y recibir redirección por rol. |
| CUF-02 | Usuario autenticado | Cerrar sesión y registrar logout. |
| CUF-03 | Administrador/luthier | Crear, buscar, editar y consultar clientes. |
| CUF-04 | Administrador/luthier | Registrar instrumentos, asignarlos a cliente, generar QR, editar y consultar historial. |
| CUF-05 | Administrador/luthier | Registrar reparación, diagnóstico, costo y cambiar estado. |
| CUF-06 | Administrador/luthier | Agregar repuestos usados; descuenta stock y registra salida transaccional. |
| CUF-07 | Administrador | Crear/editar/eliminar materiales y proveedores; asociar proveedor. |
| CUF-08 | Administrador | Registrar entradas y salidas de inventario, controlar stock y alertas mínimas. |
| CUF-09 | Administrador | Registrar y consultar ventas filtradas/paginadas. |
| CUF-10 | Administrador | Registrar y consultar cobros asociados a reparaciones. |
| CUF-11 | Administrador | Consultar ingresos por periodo, stock, ventas y reparaciones; imprimir y exportar CSV/PDF. |
| CUF-12 | Administrador/CLI | Crear, descargar, restaurar y eliminar respaldos; crear backup automático por tarea. |
| CUF-13 | Administrador | Crear, editar, activar/desactivar usuarios y filtrar usuarios. |

### Documentado pero no comprobable o no implementado

El perfil académico menciona diagramas de clases, modelos y planificación Scrum, pero son documentación, no funcionalidades ejecutables. La generación de PDF sí está implementada localmente en [includes/pdf_reporte.php](D:/6-SEM/INS-600/sistema-web/includes/pdf_reporte.php:4). No se encontró implementación de correo, WhatsApp, pagos, API externa, multi-sucursal, descuentos, órdenes, clientes externos o notificaciones `[NO ENCONTRADO]`.

### Requisitos no funcionales verificables

| Categoría | Evidencia |
|---|---|
| Seguridad | PDO preparado, CSRF, RBAC, bcrypt, expiración de sesión, bloqueo y escape HTML ([docs/revision_seguridad.md](D:/6-SEM/INS-600/sistema-web/docs/revision_seguridad.md:3)). |
| Mantenibilidad | Separación por módulos e includes; funciones reutilizables ([includes/](D:/6-SEM/INS-600/sistema-web/includes/)). |
| Rendimiento | Paginación de 8/10 registros en varios listados, índices SQL y límites de 100 en reportes ([modules/tecnico/materiales.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/materiales.php:89), [modules/comercial/reportes.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/reportes.php:2)). |
| Disponibilidad local | Dependencias frontend locales; no requiere CDN para Bootstrap, gráficos o QR ([assets/vendor/](D:/6-SEM/INS-600/sistema-web/assets/vendor/)). |
| Responsive/usabilidad | Bootstrap, formularios `required`, mensajes de error, paginación y soporte `prefers-reduced-motion` documentado en [sis_history.md](D:/6-SEM/INS-600/sistema-web/sis_history.md:233). |
| Meta cuantitativa | `[NO ENCONTRADO]`: no hay mediciones de tiempo, SLA, usuarios concurrentes o throughput. |

## 7. RUTAS Y ENDPOINTS

Todas son archivos PHP servidos por Apache; no existe router central. Las respuestas exitosas son HTML/redirecciones salvo exportaciones. `[NO ENCONTRADO]` significa que no hay un código/mensaje específico explícito en el archivo.

| Método/ruta | Rol | Qué hace y recibe | Validaciones / éxito / errores |
|---|---|---|---|
| GET/POST `/modules/auth/login.php` | Público | Login: `usuario`, `password` | Campos; política; usuario activo; hash; bloqueo. Éxito redirige admin o técnico. Error exacto: “Completa usuario y contraseña.”, “La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un carácter especial.”, “Credenciales inválidas o cuenta bloqueada temporalmente.” |
| GET `/modules/auth/logout.php` | Sesión | Cierra sesión | Bitácora y redirección al login ([modules/auth/logout.php](D:/6-SEM/INS-600/sistema-web/modules/auth/logout.php:1)). |
| GET `/` | Público/sesión | Redirige por ausencia de usuario o rol | Login, panel admin o panel técnico ([index.php](D:/6-SEM/INS-600/sistema-web/index.php:1)). |
| GET/POST `/modules/tecnico/clientes.php` | Admin/luthier; escritura según código | CRUD/búsqueda de clientes: nombre, teléfono, dirección, ID | Nombre obligatorio; CSRF en POST; FK impide borrar si tiene instrumentos `[DEDUCIDO]`; éxito HTML/redirección; error de BD genérico. |
| GET/POST `/modules/tecnico/instrumentos.php` | Admin/luthier | CRUD: tipo, cliente, descripción, ID | Tipo/cliente obligatorios, cliente existente, QR generado `SUPAY-INS-` + ID de 6 dígitos; FK protege asociados. |
| GET `/modules/tecnico/instrumento_qr.php?id=` | Admin/luthier | Muestra QR descargable/imprimible | Instrumento existente; 404 “Instrumento no encontrado.” `[DEDUCIDO según archivo]`; usa `qrcode.min.js`. |
| GET `/modules/tecnico/escaner.php` | Admin/luthier | Cámara o código manual | Manual envía `codigo` a historial; HTTPS requerido para cámara según [docs/manual_instalacion.md](D:/6-SEM/INS-600/sistema-web/docs/manual_instalacion.md:20). |
| GET `/modules/tecnico/historial.php?codigo=` | Admin/luthier | Busca instrumento y reparaciones/repuestos | Código exacto; error “No existe un instrumento con ese código.”; éxito HTML. |
| GET/POST `/modules/tecnico/reparaciones.php` | Admin/luthier | Crea reparación y cambia estado: instrumento, diagnóstico, fecha, costo, estado | Instrumento/diagnóstico/costo válido; estado enum; éxito HTML; error “Instrumento, diagnóstico y costo válido son obligatorios.” / “Estado inválido.” |
| GET/POST `/modules/tecnico/repuestos.php?id_reparacion=` | Admin/luthier | Añade material y cantidad | Reparación/material existentes, decimal >0, stock suficiente; transacción inserta detalle, descuenta material y crea salida; 404 “Reparación no encontrada.” |
| GET/POST `/modules/tecnico/materiales.php` | Consulta admin/luthier; escritura admin | CRUD material: nombre, categoría, stock, mínimo, proveedor | Categorías enum; stocks >=0; CSRF; éxito HTML; error de FK para asociados. |
| GET/POST `/modules/tecnico/movimientos.php` | Admin | Entrada/salida: material, tipo, cantidad, fecha | tipo enum, cantidad >0, salida <= stock; transacción actualiza material e inserta movimiento. |
| GET/POST `/modules/tecnico/proveedores.php` | Admin | CRUD proveedor: nombre, contacto | Nombre obligatorio, CSRF, paginación; no eliminar si materiales asociados. |
| GET/POST `/modules/comercial/ventas.php` | Admin | Crea/consulta venta: cliente, instrumento, fecha, total | Campos obligatorios; fecha ISO válida; total >0; instrumento existente; inserta venta y bitácora. |
| GET/POST `/modules/comercial/cobros.php` | Admin | Crea/consulta cobro: reparación, monto, fecha | Reparación existente; fecha válida; monto >0; inserta cobro y bitácora. |
| GET `/modules/comercial/reportes.php` | Admin | Ingresos y listados de stock/ventas/reparaciones | Periodo semanal/mensual/anual/personalizado; ventas/cobros agregados; listados de ventas y reparaciones limitados a 100. |
| GET `/modules/comercial/exportar.php?tipo=ventas\|cobros` | Admin | Descarga CSV | Tipo permitido en el código; `[NO ENCONTRADO]` en el fragmento revisado para otros valores; bitácora documentada en [docs/revision_seguridad.md](D:/6-SEM/INS-600/sistema-web/docs/revision_seguridad.md:13). |
| GET `/modules/comercial/exportar_pdf.php?tipo=` | Admin | Genera PDF local de ingresos/stock/ventas/reparaciones | Tipos y datos SQL en archivo; salida `application/pdf`; errores específicos `[NO ENCONTRADO]`. |
| GET/POST `/modules/admin/respaldos.php` | Admin | Crea, descarga, restaura y elimina SQL | CSRF, nombre regex, archivo <=50 MiB, cabecera `SUPAY_BACKUP_VERSION:1`; éxito HTML/descarga; error “Nombre de respaldo invalido.”, “El archivo no existe o supera el limite permitido.” o genérico. |
| GET/POST `/modules/admin/usuarios.php` | Admin | CRUD/estado/búsqueda usuarios | Rol permitido, política, no desactivar cuenta propia; UNIQUE usuario; errores exactos en [modules/admin/usuarios.php](D:/6-SEM/INS-600/sistema-web/modules/admin/usuarios.php:20). |
| CLI `scripts/respaldo_auto.php` | CLI, no web | Crea backup automático y registra bitácora | Rechaza `PHP_SAPI` distinto de CLI; salida “Respaldo creado: ...” o error stderr; [scripts/respaldo_auto.php](D:/6-SEM/INS-600/sistema-web/scripts/respaldo_auto.php:1). |

### Pantallas y formularios

| Pantalla | Campos principales |
|---|---|
| Login | `usuario`, `password` ([modules/auth/login.php](D:/6-SEM/INS-600/sistema-web/modules/auth/login.php:60)) |
| Clientes | `nombre`, `telefono`, `direccion`; búsqueda `buscar`; editar/eliminar por ID ([modules/tecnico/cliente_editar.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/cliente_editar.php:1)) |
| Instrumentos | `tipo`, `id_cliente`, `descripcion`; filtros `buscar`, `cliente`, `pagina` ([modules/tecnico/instrumentos.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/instrumentos.php:66)) |
| Reparaciones | `id_instrumento`, `fecha_ingreso`, `costo`, `diagnostico`; filtro `estado` ([modules/tecnico/reparaciones.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/reparaciones.php:8)) |
| Repuestos | `id_reparacion`, `id_material`, `cantidad` ([modules/tecnico/repuestos.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/repuestos.php:45)) |
| Materiales | `nombre`, `categoria`, `stock`, `stock_minimo`, `id_proveedor`; filtros `buscar`, `categoria` ([modules/tecnico/materiales.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/materiales.php:135)) |
| Movimientos | `id_material`, `tipo`, `cantidad`, `fecha`; filtros material/desde/hasta ([modules/tecnico/movimientos.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/movimientos.php:7)) |
| Proveedores | `nombre`, `contacto`; búsqueda `buscar` ([modules/tecnico/proveedores.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/proveedores.php:7)) |
| Ventas | `id_cliente`, `id_instrumento`, `fecha`, `total`; filtros buscar/fechas ([modules/comercial/ventas.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/ventas.php:7)) |
| Cobros | `id_reparacion`, `monto`, `fecha`; filtros de fechas ([modules/comercial/cobros.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/cobros.php:7)) |
| Reportes | `periodo`, `desde`, `hasta`; links PDF/CSV ([modules/comercial/reportes.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/reportes.php:3)) |
| Usuarios | `nombre`, `usuario`, `rol`, `password`; búsqueda `buscar`; estado `activo` ([modules/admin/usuarios.php](D:/6-SEM/INS-600/sistema-web/modules/admin/usuarios.php:83)) |

## 8. REGLAS DE VALIDACIÓN Y LÍMITES

| Entidad/formulario | Reglas verificadas |
|---|---|
| Login/usuario | Usuario y contraseña no vacíos; contraseña >=8, mayúscula, dígito y símbolo; rol solo administrador/luthier; usuario UNIQUE. No hay longitud mínima/máxima adicional en PHP para campos de texto. |
| Cliente | Nombre obligatorio; BD limita a 120, teléfono 30 y dirección 180. No hay validación de formato telefónico ni unicidad. |
| Instrumento | Tipo y cliente obligatorios; cliente debe existir; BD limita tipo 80, QR 80 y descripción TEXT; QR UNIQUE y generado por ID. |
| Reparación | Instrumento, diagnóstico, costo; costo >=0; estados exactos `en_revision`, `en_reparacion`, `finalizado`; BD usa DECIMAL(10,2). |
| Repuesto | Material y reparación existentes; cantidad numérica >0 y <= stock; stock no puede quedar negativo; se usan transacciones y `FOR UPDATE` ([modules/tecnico/repuestos.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/repuestos.php:14)). |
| Material | Nombre obligatorio; categoría en cuatro valores; stock y mínimo numéricos >=0; proveedor opcional y existente; DECIMAL(10,2). |
| Movimiento | Tipo `entrada`/`salida`; cantidad numérica >0; salida no supera stock; cantidad DECIMAL(10,2); fecha se transforma de `T` a espacio. |
| Venta | Cliente, instrumento y fecha obligatorios; fecha `Y-m-d`; total numérico >0. BD permite instrumento NULL, pero formulario exige instrumento; no se valida que cliente exista antes del INSERT `[DEDUCIDO: la FK lo controla]`. |
| Cobro | Reparación y fecha obligatorias; fecha válida; monto >0; reparación existente; DECIMAL(10,2). |
| Reportes | Periodo permitido: semanal, mensual, anual o personalizado; fechas personalizadas deben ser `Y-m-d`; no se valida que desde <= hasta `[NO ENCONTRADO]`. |
| Respaldo | Nombre `^supay_[0-9]{8}_[0-9]{6}\.sql$`; restauración máximo 50 MiB; cabecera de versión obligatoria; guardado en `/backups`; no hay subida de archivos web. |

Longitudes máximas adicionales, formatos regex de nombres/teléfonos y unicidad de nombre de cliente/material/proveedor no se implementan `[NO ENCONTRADO]`; las longitudes SQL están en la tabla de BD.

## 9. CANDIDATAS A HISTORIAS DE USUARIO

Las siguientes cinco se seleccionan por integrar pantalla, lógica, persistencia y reglas verificables. Las complejidades son conteos manuales de decisiones explícitas del flujo principal (`if`, `elseif`, validaciones booleanas, ciclos y `catch`) + 1; deben tomarse como estimación para diseño de pruebas, no como resultado de una herramienta de análisis estático.

### HU-C1 — Iniciar sesión con control de seguridad

**Historia:** Como usuario autorizado quiero iniciar sesión con mis credenciales para acceder únicamente al panel correspondiente a mi rol.

**Código principal:** [modules/auth/login.php](D:/6-SEM/INS-600/sistema-web/modules/auth/login.php:4), [includes/helpers.php](D:/6-SEM/INS-600/sistema-web/includes/helpers.php:7), [config/sesion.php](D:/6-SEM/INS-600/sistema-web/config/sesion.php:5).

**Flujo:** login → POST con usuario/contraseña → valida vacíos y política → SELECT de usuario activo → comprueba bloqueo/hash → actualiza intentos o sesión → bitácora → redirige a panel admin/técnico.

**Decisiones y M:** POST; campos vacíos; política; usuario/bloqueo/hash; usuario no bloqueado; umbral de 3; ternario de contador; rol de redirección; `catch` interno de bitácora `[DEDUCIDO del flujo]`: 8 decisiones principales, `M = 9`. Caminos independientes: campos vacíos; política inválida; usuario inexistente; cuenta bloqueada; fallo 1; fallo 2; fallo 3/bloqueo; credencial correcta admin; credencial correcta luthier.

**Persistencia:** actualiza `usuario.intentos_fallidos`, `usuario.bloqueado_hasta`; inserta `bitacora`.

**Dependencias:** usuarios seed y conexión funcional. **Estimación:** 8–12 horas `[DEDUCIDO]`.

### HU-C2 — Registrar instrumento y generar QR trazable

**Historia:** Como administrador o luthier quiero registrar un instrumento asociado a un cliente y obtener un QR único para consultar su historial.

**Código principal:** [modules/tecnico/instrumentos.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/instrumentos.php:8), [includes/tecnico.php](D:/6-SEM/INS-600/sistema-web/includes/tecnico.php:7), [modules/tecnico/instrumento_qr.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/instrumento_qr.php:2).

**Flujo:** formulario → POST y CSRF → valida tipo/cliente → confirma cliente → transacción inserta QR temporal → obtiene ID → actualiza `SUPAY-INS-XXXXXX` → commit → bitácora → pantalla QR genera imagen/descarga.

**Decisiones y M:** POST; acción crear/editar/eliminar; campos obligatorios; cliente existente; `try/catch`; transacción activa en catch; tipo de excepción; además ciclo de listado y paginación: 8 decisiones/categorías de decisión, `M = 9` `[DEDUCIDO]`. Caminos: creación válida; tipo vacío; cliente inexistente; fallo SQL/rollback; edición; eliminación sin dependencias; eliminación con FK; consulta QR.

**Persistencia:** inserta/actualiza `instrumento`; bitácora. **Dependencias:** cliente existente; librería `qrcode.min.js`. **Estimación:** 10–14 horas `[DEDUCIDO]`.

### HU-C3 — Registrar reparación y repuestos sin stock negativo

**Historia:** Como luthier quiero registrar el diagnóstico y los repuestos utilizados para mantener el historial técnico y el inventario consistente.

**Código principal:** [modules/tecnico/reparaciones.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/reparaciones.php:2), [modules/tecnico/repuestos.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/repuestos.php:10), [modules/tecnico/historial.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/historial.php:1).

**Flujo:** reparación → POST diagnóstico/costo/fecha → inserta reparación en revisión → pantalla repuestos → valida reparación/material/cantidad → inicia transacción y bloquea material → verifica stock → inserta detalle → descuenta stock → inserta salida → commit → historial QR consulta reparación y repuestos.

**Decisiones y M:** creación/estado; validación de reparación; estado permitido; catch; transacción activa; tipo de excepción; validación de repuesto; reparación existente; material existente; stock suficiente; alerta de mínimo; dos ciclos de historial/repuestos: 12 decisiones principales, `M = 13` `[DEDUCIDO]`. Caminos: diagnóstico válido; diagnóstico/costo inválido; estado inválido; reparación inexistente; material inexistente; cantidad <=0; stock insuficiente; éxito sin alerta; éxito con alerta; excepción con rollback.

**Persistencia:** `reparacion`, `repuesto_utilizado`, `material.stock`, `movimiento_inventario`, `bitacora`. **Dependencias:** cliente, instrumento y material. **Estimación:** 16–24 horas `[DEDUCIDO]`.

### HU-C4 — Controlar entradas y salidas de inventario

**Historia:** Como administrador quiero registrar entradas y salidas de materiales para mantener el stock y recibir alertas cuando llegue al mínimo.

**Código principal:** [modules/tecnico/movimientos.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/movimientos.php:2), [includes/inventario.php](D:/6-SEM/INS-600/sistema-web/includes/inventario.php:5), [modules/tecnico/materiales.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/materiales.php:54).

**Flujo:** formulario → CSRF → valida material/tipo/cantidad → transacción y bloqueo del material → rechaza inexistente o salida mayor al stock → calcula nuevo stock → actualiza material → inserta movimiento → commit → bitácora/alerta → historial paginado.

**Decisiones y M:** POST; material/tipo/cantidad; material existente; tipo salida y stock; cálculo entrada/salida; catch/rollback/tipo de error; alerta de salida al mínimo; filtros material/desde/hasta; ciclo paginación: 10 decisiones, `M = 11` `[DEDUCIDO]`. Caminos: entrada válida; salida válida sin alerta; salida que alcanza mínimo; cantidad inválida; material inexistente; salida insuficiente; fecha/filtros; excepción y rollback.

**Persistencia:** actualiza `material.stock`, inserta `movimiento_inventario`, bitácora. **Dependencias:** material existente y rol administrador. **Estimación:** 12–18 horas `[DEDUCIDO]`.

### HU-C5 — Generar y restaurar un respaldo

**Historia:** Como administrador quiero generar y restaurar copias SQL para recuperar la información del taller ante una pérdida o error.

**Código principal:** [modules/admin/respaldos.php](D:/6-SEM/INS-600/sistema-web/modules/admin/respaldos.php:2), [includes/respaldo.php](D:/6-SEM/INS-600/sistema-web/includes/respaldo.php:6), [scripts/respaldo_auto.php](D:/6-SEM/INS-600/sistema-web/scripts/respaldo_auto.php:1).

**Flujo:** pantalla admin → POST crear → nombre seguro → intenta `mysqldump` → si falla genera SQL PHP → guarda en temporal/renombra → lista; para restaurar recibe nombre → valida path, tamaño y cabecera → vacía tablas en orden → ejecuta SQL dentro de transacción → commit o rollback.

**Decisiones y M:** descarga/crear/restaurar/eliminar; existencia archivo; tipo de acción; `mysqldump` disponible; estado/archivo no vacío; fallback PHP; escritura temporal; lista/regex; restauración existencia/tamaño; lectura/cabecera; try/catch y rollback: 13 decisiones, `M = 14` `[DEDUCIDO]`. Caminos: backup mysqldump; fallback PHP; error de escritura; descarga válida/inválida; restauración válida; archivo ausente; >50 MiB; cabecera inválida; error SQL con rollback; eliminación válida/ausente.

**Persistencia:** todas las 11 tablas durante backup/restauración; `bitacora` registra acciones. **Dependencias:** permisos de carpeta, conexión y `mysqldump` opcional. **Estimación:** 14–22 horas `[DEDUCIDO]`.

## 10. DATOS PARA EJECUTAR PRUEBAS

### Usuarios

| Usuario | Rol | Contraseña documentada |
|---|---|---|
| `admin` | administrador | `SupayAdmin2026!` en [Acceso_Contraseñas.txt](D:/6-SEM/INS-600/sistema-web/Acceso_Contraseñas.txt:8) |
| `luthier` | luthier | `Luthier2026!` en [Acceso_Contraseñas.txt](D:/6-SEM/INS-600/sistema-web/Acceso_Contraseñas.txt:9) |

Estas credenciales son sensibles y el propio archivo indica cambiarlas antes de producción ([Acceso_Contraseñas.txt](D:/6-SEM/INS-600/sistema-web/Acceso_Contraseñas.txt:5)).

### Seed reproducible

| Tipo | Datos |
|---|---|
| Proveedores | Maderas La Paz; Insumos Andinos |
| Clientes | Cliente de ejemplo; Banda Los Andes |
| Instrumentos | `SUPAY-INS-000001` Bombo; `SUPAY-INS-000002` Caja |
| Materiales | Madera mara (10/2), Cuero de res (8/2), Perno 8 mm (30/5), Aro metálico (6/1) |

Fuente: [database/supay.sql](D:/6-SEM/INS-600/sistema-web/database/supay.sql:17). El dump actual contiene además reparación 3, venta 2, cobro 2, movimiento 3 y bitácora histórica ([database/supay_actual.sql](D:/6-SEM/INS-600/sistema-web/database/supay_actual.sql:101), [database/supay_actual.sql](D:/6-SEM/INS-600/sistema-web/database/supay_actual.sql:196), [database/supay_actual.sql](D:/6-SEM/INS-600/sistema-web/database/supay_actual.sql:349)).

### Preparación por historia

| Historia | Datos que deben existir/crearse |
|---|---|
| HU-C1 | Usuarios seed; para bloqueo usar `admin` y contraseña que cumpla política pero sea incorrecta tres veces. |
| HU-C2 | Cliente seed; crear dos instrumentos y comprobar códigos distintos; no requiere archivo. |
| HU-C3 | Instrumento, reparación y material con stock suficiente; para alerta usar stock cercano al mínimo. |
| HU-C4 | Material con stock conocido; ejecutar entrada, salida válida, salida exacta al mínimo y salida superior. |
| HU-C5 | Permiso de escritura en `backups/`; crear un registro temporal y restaurar una copia. |

Archivos PDF/imágenes: no se suben archivos mediante formularios. QR se genera en navegador y el PDF se construye en servidor; cámara requiere HTTPS/contexto seguro ([docs/manual_instalacion.md](D:/6-SEM/INS-600/sistema-web/docs/manual_instalacion.md:20)).

## 11. RENDIMIENTO

| Elemento | Hallazgo |
|---|---|
| Índices | PK, FKs, fechas, QR, usuario activo, categoría/stock y material/fecha están definidos en SQL. |
| Paginación | 8 registros en clientes/instrumentos/materiales/proveedores/ventas/cobros/usuarios; 10 en movimientos; hay `LIMIT 100` en reportes de ventas/reparaciones. |
| Caché | `[NO ENCONTRADO]`. No hay caché de aplicación ni consultas cacheadas. |
| Rate limit | `[NO ENCONTRADO]`; el bloqueo solo protege login por cuenta. |
| Consultas potencialmente pesadas | Reportes agregan ventas/cobros por fecha y cargan inventario/listados; historial ejecuta una consulta de repuestos por reparación (patrón N+1) ([modules/tecnico/historial.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/historial.php:1)). Backup recorre las 11 tablas ([includes/respaldo.php](D:/6-SEM/INS-600/sistema-web/includes/respaldo.php:10)). |
| Metas/mediciones | `[NO ENCONTRADO]`. |
| Pruebas de carga recomendadas | Búsqueda/paginación de materiales y ventas; reportes por periodo; concurrencia en dos salidas del mismo material; creación masiva de instrumentos/QR; backup/restauración con tablas grandes. |

## 12. INTEGRACIONES EXTERNAS

| Integración | Implementación / fallo |
|---|---|
| Apache/XAMPP | Servidor local; instalador inicia `xampp_start.exe` y verifica puerto 3306 ([scripts/instalar_supay.ps1](D:/6-SEM/INS-600/sistema-web/scripts/instalar_supay.ps1:26)). |
| MariaDB/MySQL | PDO a `127.0.0.1:3306`; excepción PDO si falla conexión ([config/conexion.php](D:/6-SEM/INS-600/sistema-web/config/conexion.php:5)). |
| Cámara | `html5-qrcode` local; requiere permiso/cámara y HTTPS según manual; existe consulta manual alternativa ([modules/tecnico/escaner.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/escaner.php:1)). |
| Generación QR | `qrcode.min.js` local en navegador ([modules/tecnico/instrumento_qr.php](D:/6-SEM/INS-600/sistema-web/modules/tecnico/instrumento_qr.php:2)). |
| PDF | Generador PHP propio, sin internet ni extensiones externas obligatorias; usa GD solo opcionalmente para logo ([includes/pdf_reporte.php](D:/6-SEM/INS-600/sistema-web/includes/pdf_reporte.php:4), [includes/pdf_reporte.php](D:/6-SEM/INS-600/sistema-web/includes/pdf_reporte.php:152)). |
| CSV | Exportación local desde PHP ([modules/comercial/exportar.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/exportar.php:1)). |
| SMTP, WhatsApp, pagos, nube | `[NO ENCONTRADO]`. |

## 13. LIMITACIONES Y DUDAS

1. No existe `README.md`; el rol documental lo cumplen [sis_history.md](D:/6-SEM/INS-600/sistema-web/sis_history.md), manuales, [cambios.md](D:/6-SEM/INS-600/sistema-web/cambios.md) y el DOCX académico.
2. No hay versiones exactas de PHP, Chart.js, QRCode.js ni html5-qrcode verificables en archivos de configuración.
3. `database/supay.sql` y `database/supay_actual.sql` no son el mismo estado: uno es esquema+seed y el otro dump con datos operativos posteriores. El manual indica importar `supay.sql` ([docs/manual_instalacion.md](D:/6-SEM/INS-600/sistema-web/docs/manual_instalacion.md:8)).
4. El instalador por defecto usa `C:\xampp`, mientras el manual menciona `D:\xamppP`; la configuración intenta ambas rutas ([scripts/instalar_supay.ps1](D:/6-SEM/INS-600/sistema-web/scripts/instalar_supay.ps1:1), [config/constantes.php](D:/6-SEM/INS-600/sistema-web/config/constantes.php:8)).
5. La documentación describe “gráficos mediante Chart.js”, pero no hay archivo de dependencias con su versión; sí hay un archivo local y uso en reportes.
6. La fecha de venta/cobro se guarda como medianoche (`YYYY-MM-DD 00:00:00`), aunque la BD define DATETIME; esto puede afectar filtros de periodos si se esperaba hora real ([modules/comercial/ventas.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/ventas.php:3), [modules/comercial/cobros.php](D:/6-SEM/INS-600/sistema-web/modules/comercial/cobros.php:3).
7. No se encontraron pruebas automatizadas ni configuración de PHPUnit, Cypress, Playwright o similar `[NO ENCONTRADO]`; el checklist contiene casos planificados con resultado vacío ([docs/checklist_pruebas.md](D:/6-SEM/INS-600/sistema-web/docs/checklist_pruebas.md:3)).
8. No se encontraron cabeceras de seguridad HTTP, rate limiting general, registro de auditoría consultable desde una pantalla, recuperación de contraseña ni cambio obligatorio de credenciales iniciales `[NO ENCONTRADO]`.
9. El seed documenta una contraseña de aplicación en SQL y configuración por defecto; esto es adecuado para local, pero la propia revisión de seguridad exige rotación antes de producción ([docs/revision_seguridad.md](D:/6-SEM/INS-600/sistema-web/docs/revision_seguridad.md:17)).
10. La UI de clientes permite eliminación y el código captura errores de integridad, pero las reglas exactas de `clientes.php` no se detallan aquí línea por línea; verificar el flujo antes de usarlo como caso de aceptación.
11. No hay carga de archivos de usuario, por lo que no existe tipo/tamaño de archivo de entrada que probar; el único límite de archivo explícito es restauración de backup de 50 MiB.
12. La complejidad ciclomática de las cinco historias es una estimación manual basada en decisiones del flujo y en archivos con líneas compactadas; para certificación de caja blanca conviene recalcular con una herramienta PHP AST.

## Resumen solicitado en 5 líneas

Tecnología: PHP modular server-rendered, Apache/XAMPP, MariaDB/MySQL, PDO, Bootstrap, Chart.js y librerías QR locales.
Login y roles: sí; autenticación por sesión con bcrypt, bloqueo de 3 intentos/15 minutos y roles administrador/luthier.
Tablas: 11 tablas InnoDB (`usuario`, `cliente`, `instrumento`, `reparacion`, `proveedor`, `material`, `repuesto_utilizado`, `movimiento_inventario`, `venta`, `cobro`, `bitacora`).
Funcionalidades: 13 grupos implementados, incluyendo login, CRUD técnico/inventario, reparaciones, QR, ventas, cobros, reportes, respaldos y usuarios.
Historias recomendadas: HU-C1 login, HU-C3 reparación+repuestos y HU-C4 inventario, por cubrir seguridad, integración y reglas transaccionales.
