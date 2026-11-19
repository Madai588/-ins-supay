@echo off
setlocal
PowerShell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0instalar_supay.ps1" -XamppRoot "C:\xampp"
if errorlevel 1 (
  echo.
  echo La instalacion no se completo. Revisa el mensaje anterior.
  pause
  exit /b 1
)
echo.
echo SUPAY quedo instalado correctamente.
pause
