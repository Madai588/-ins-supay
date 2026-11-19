param(
    [string]$XamppRoot = 'C:\xampp',
    [string]$Destino = ''
)

$ErrorActionPreference = 'Stop'
$Proyecto = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
if ([string]::IsNullOrWhiteSpace($Destino)) { $Destino = Join-Path $XamppRoot 'htdocs\supay' }

Write-Host '=== Instalador SUPAY ===' -ForegroundColor Cyan
Write-Host "Origen:  $Proyecto"
Write-Host "Destino: $Destino"

if (-not (Test-Path (Join-Path $XamppRoot 'xampp_start.exe'))) {
    throw "No se encontró XAMPP en '$XamppRoot'. Usa: .\instalar_supay.ps1 -XamppRoot 'C:\xampp'"
}

$php = Join-Path $XamppRoot 'php\php.exe'
$mysql = Join-Path $XamppRoot 'mysql\bin\mysql.exe'
if (-not (Test-Path $php)) { throw "No se encontró PHP en $php" }
if (-not (Test-Path $mysql)) { throw "No se encontró MariaDB/MySQL en $mysql" }

$modulosPhp = & $php -m
if (-not ($modulosPhp -contains 'pdo_mysql')) { throw 'PHP no tiene habilitada la extensión pdo_mysql.' }

Write-Host 'Iniciando Apache y MySQL...' -ForegroundColor Yellow
Start-Process -FilePath (Join-Path $XamppRoot 'xampp_start.exe') -WindowStyle Minimized

$listo = $false
for ($i = 0; $i -lt 30; $i++) {
    try {
        $conexion = New-Object Net.Sockets.TcpClient('127.0.0.1', 3306)
        $conexion.Close()
        $listo = $true
        break
    } catch { Start-Sleep -Seconds 1 }
}
if (-not $listo) { throw 'MySQL/MariaDB no respondió en el puerto 3306.' }

if (Test-Path $Destino) {
    $respuesta = Read-Host "La carpeta '$Destino' ya existe. Escribe SI para reemplazar sus archivos"
    if ($respuesta -ne 'SI') { throw 'Instalación cancelada por el usuario.' }
}

Write-Host 'Copiando archivos del sistema...' -ForegroundColor Yellow
New-Item -ItemType Directory -Force -Path $Destino | Out-Null
Get-ChildItem -LiteralPath $Proyecto -Force |
    Where-Object { $_.Name -notin @('.git', 'perfil_de_proyecto_TMG-600A_completo.docx') } |
    Copy-Item -Destination $Destino -Recurse -Force

$sqlBase = Join-Path $Destino 'database\supay.sql'
$sqlUsuario = Join-Path $Destino 'database\usuario_app.sql'
if (-not (Test-Path $sqlBase)) { throw "No se encontró $sqlBase" }

Write-Host 'Creando base de datos e importando tablas...' -ForegroundColor Yellow
& $mysql -u root -e "CREATE DATABASE IF NOT EXISTS supay CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if ($LASTEXITCODE -ne 0) { throw 'No se pudo crear la base de datos. Verifica la contraseña de root de XAMPP.' }
Get-Content -Raw -LiteralPath $sqlBase | & $mysql -u root supay
if ($LASTEXITCODE -ne 0) { throw 'Falló la importación de database\supay.sql.' }
Get-Content -Raw -LiteralPath $sqlUsuario | & $mysql -u root
if ($LASTEXITCODE -ne 0) { throw 'Falló la creación del usuario supay_app.' }

Write-Host 'Configurando la ruta de XAMPP para respaldos...' -ForegroundColor Yellow
$constantes = Join-Path $Destino 'config\constantes.php'
$contenido = [IO.File]::ReadAllText($constantes)
$contenido = $contenido -replace "define\('XAMPP_ROOT'.*?define\('MYSQLDUMP_PATH'.*?;", "define('XAMPP_ROOT', getenv('XAMPP_ROOT') ?: (is_dir('C:\\xampp') ? 'C:\\xampp' : 'D:\\xamppP'));`r`ndefine('MYSQLDUMP_PATH', getenv('MYSQLDUMP_PATH') ?: XAMPP_ROOT.'\\mysql\\bin\\mysqldump.exe');"
[IO.File]::WriteAllText($constantes, $contenido, (New-Object Text.UTF8Encoding($false)))

Write-Host 'Instalación terminada.' -ForegroundColor Green
Write-Host 'Abriendo http://localhost/supay/modules/auth/login.php ...'
Start-Process 'http://localhost/supay/modules/auth/login.php'
