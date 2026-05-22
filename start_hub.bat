@echo off
title DecorArte Media Hub - Launcher
color 0A
cls
echo ====================================================================
echo             DECORARTE MEDIA HUB + ACADEMIA (2027 PROTOTYPE)
echo ====================================================================
echo.
echo [1/3] Verificando dependencias del Servidor en Node.js...
cd realtime-service
call npm.cmd install --no-audit --no-fund
if %errorlevel% neq 0 (
    echo [ERROR] No se pudo ejecutar npm install. Asegurate de tener Node instalado.
    pause
    exit /b %errorlevel%
)

echo.
echo [2/3] Iniciando el Servidor de APIs y WebSockets (Puerto 5000)...
echo El servidor se ejecutara en segundo plano.
start /b cmd.exe /c "node server.js"

echo.
echo [3/3] Abriendo el Navegador en el Dashboard Principal...
timeout /t 3 /nobreak > nul
start http://localhost:5000

echo.
echo ====================================================================
echo   DecorArte Media Hub esta listo!
echo   Para detener el servidor, cierra esta ventana de comandos.
echo ====================================================================
echo.
cmd.exe /k
