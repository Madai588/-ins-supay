# Instalación rápida de SUPAY

## Paso 1: copiar el instalador

Copia la carpeta completa llamada `SUPAY-INSTALADOR` en cualquier ubicación de la unidad `C:`.

## Paso 2: ejecutar el instalador

Abre PowerShell como administrador y ejecuta:

```powershell
& "C:\SUPAY-INSTALADOR\scripts\instalar_supay.bat"
```

Si colocaste `SUPAY-INSTALADOR` en otra carpeta, cambia la ruta del comando.

El instalador hará todo automáticamente:

- Copiar SUPAY a `C:\xampp\htdocs\supay`.
- Crear e importar la base de datos.
- Crear el usuario de conexión.
- Iniciar Apache y MySQL.
- Abrir el navegador.

Si pregunta si deseas reemplazar una instalación existente, escribe `SI` y presiona `Enter`.

## Paso 3: ingresar al sistema

Abre este enlace:

```text
http://localhost/supay/modules/auth/login.php
```

Listo.

## Requisito

La computadora debe tener XAMPP instalado en:

```text
C:\xampp
```
