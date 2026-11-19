# Manual de instalación SUPAY

## Instalación local

1. Instalar XAMPP con Apache, PHP 8.x y MariaDB.
2. Copiar el proyecto en `D:\xamppP\htdocs\supay`.
3. Iniciar Apache y MySQL desde XAMPP.
4. Abrir phpMyAdmin o la consola de MariaDB.
5. Importar `database/supay.sql`.
6. Ejecutar `database/usuario_app.sql` con el usuario `root`.
7. Confirmar que `config/constantes.php` usa `supay_app`, no `root`.
8. Para producción, definir `SUPAY_DB_USER` y `SUPAY_DB_PASS` en el entorno de Apache y de la tarea automática.
9. Crear la carpeta `backups/` y verificar que contiene `.htaccess`.
10. Abrir `http://localhost/supay/`.

## Red local

Asignar una IP fija al equipo servidor, por ejemplo `192.168.1.50`, y permitir Apache en el firewall de Windows. Los demás equipos accederán a `http://192.168.1.50/supay/`. MariaDB debe permanecer escuchando solo localmente; no abrir el puerto 3306 a internet.

## Cámara del celular

La cámara requiere un contexto seguro. Activar SSL de Apache, configurar un certificado local para el nombre/IP del servidor y acceder mediante `https://192.168.1.50/supay/`. En el celular aceptar el certificado local únicamente dentro de la red del taller. Si no se habilita HTTPS, usar la entrada manual del código QR.

## Respaldo automático

Probar manualmente:

```powershell
D:\xamppP\php\php.exe D:\xamppP\htdocs\supay\scripts\respaldo_auto.php
```

En el Programador de tareas de Windows crear una tarea diaria, seleccionar “Ejecutar tanto si el usuario inició sesión como si no”, y usar:

- Programa: `D:\xamppP\php\php.exe`
- Argumentos: `D:\xamppP\htdocs\supay\scripts\respaldo_auto.php`
- Iniciar en: `D:\xamppP\htdocs\supay`

Conservar varias copias en otro disco o almacenamiento externo. Se recomienda un UPS para el equipo servidor y el router.
