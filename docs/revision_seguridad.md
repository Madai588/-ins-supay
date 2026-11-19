# Revisión final de seguridad

| Control | Estado | Evidencia o recomendación |
|---|---|---|
| Hash bcrypt | Cumple | Usuarios almacenan `password_hash`; login usa `password_verify`. |
| Política de contraseña | Cumple parcialmente | El login rechaza menos de 8 caracteres, sin mayúscula, número o especial. Para cambios de contraseña futuros debe reutilizarse `password_cumple_politica()`. |
| Bloqueo 15 minutos | Cumple | Tres intentos fallidos activan `bloqueado_hasta`. |
| Sesión 15 minutos | Cumple | `SESION_MAX_INACTIVIDAD=900`, cookies HttpOnly y regeneración de sesión. |
| RBAC | Cumple | Cada endpoint administrativo/comercial/inventario valida rol en servidor. |
| PDO parametrizado | Cumple | Datos del usuario usan consultas preparadas; consultas fijas no reciben entrada externa. |
| CSRF | Cumple | Formularios POST validan token CSRF. |
| XSS | Cumple | Salidas HTML pasan por `e()`/`htmlspecialchars`. |
| Bitácora | Cumple | Accesos, cambios, movimientos, reportes y respaldos se registran. |
| Privilegios mínimos | Cumple | `supay_app` solo tiene SELECT, INSERT, UPDATE y DELETE sobre `supay`. |
| Backups protegidos | Cumple | `.htaccess` bloquea acceso web directo y el nombre se valida con lista segura. |
| Errores técnicos | Cumple en producción | `config/produccion.php` desactiva `display_errors` y activa `log_errors`. |
| HTTPS/cámara | Pendiente de despliegue | Configurar certificado local y HTTPS en Apache para uso desde celular. |
| Rotación de credenciales | Pendiente operativo | Cambiar las credenciales semilla y `SUPAY_DB_PASS` antes de exponer el sistema. |

## Configuración recomendada

Definir `SUPAY_DB_USER` y `SUPAY_DB_PASS` como variables de entorno de Apache y de las tareas CLI. No publicar contraseñas en repositorios ni compartirlas por chat. Mantener MariaDB en localhost, restringir Apache a la red del taller, usar HTTPS para celulares y revisar periódicamente los respaldos.
