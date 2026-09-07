@echo off
title AegisLock Examination Suite - Setup and Launch
color 0b

echo =====================================================================
echo    AEGIS SECURE LOCKDOWN EXAMINATION PLATFORM (v2.4)
echo    Desktop Client and Telemetry Backend Orchestrator
echo =====================================================================
echo.

cd /d "%~dp0"

:: 1. Check Node.js and NPM Installation
echo [1/5] Checking environment prerequisites...
where node >nul 2>nul
if errorlevel 1 (
    color 0c
    echo [ERROR] Node.js is not found in your system PATH!
    echo Please install Node.js from https://nodejs.org/ and try again.
    echo.
    pause
    exit /b 1
)

where npm >nul 2>nul
if errorlevel 1 (
    color 0c
    echo [ERROR] npm is not found in your system PATH!
    echo Please verify your Node.js installation.
    echo.
    pause
    exit /b 1
)
echo [OK] Node.js and npm detected successfully.
echo.

:: 2. Check Database Service Status (Port 3306)
echo [2/5] Checking database connection status...
netstat -ano 2>nul | findstr ":3306 " | findstr "LISTENING" >nul 2>nul
if errorlevel 1 (
    echo [NOTICE] MySQL service not detected on port 3306.
    echo          Ensure MySQL or XAMPP is started if required.
) else (
    echo [OK] MySQL Service detected on port 3306.
)
echo.

:: 3. Clear port 5000 if occupied
echo [3/5] Checking Backend Port 5000...
for /f "tokens=5" %%p in ('netstat -ano 2^>nul ^| findstr ":5000 " ^| findstr "LISTENING"') do (
    echo Freeing occupied port 5000 with PID %%p
    taskkill /F /T /PID %%p >nul 2>&1
)
ping 127.0.0.1 -n 2 >nul
echo [OK] Port 5000 is clear.
echo.

:: 4. Verify and Install Dependencies
echo [4/5] Checking application dependencies...
if not exist "server\node_modules\" (
    echo Installing backend dependencies...
    cd /d "%~dp0server"
    call npm install
    cd /d "%~dp0"
) else (
    echo [OK] Backend dependencies present.
)

if not exist "client\node_modules\" (
    echo Installing client dependencies...
    cd /d "%~dp0client"
    call npm install
    cd /d "%~dp0"
) else (
    echo [OK] Client dependencies present.
)
echo.

:: 5. Launch Backend Service
echo [5/5] Launching AegisLock Backend and Security Shell...
echo Starting Backend Telemetry Service on port 5000...
start "AegisLock Backend Server" /D "%~dp0server" cmd /k node server.js

echo Waiting for backend service initialization...
set attempts=0

:WAIT_LOOP
ping 127.0.0.1 -n 2 >nul
set /a attempts+=1
netstat -ano 2>nul | findstr ":5000 " | findstr "LISTENING" >nul 2>nul
if %errorlevel% equ 0 goto :BACKEND_READY
if %attempts% geq 5 goto :BACKEND_TIMEOUT
goto :WAIT_LOOP

:BACKEND_READY
echo [OK] Backend service is actively listening on port 5000.
goto :LAUNCH_CLIENT

:BACKEND_TIMEOUT
echo [WARNING] Backend service took longer to respond. Proceeding with launch...
goto :LAUNCH_CLIENT

:LAUNCH_CLIENT
echo.
echo =====================================================================
echo  Starting AegisLock Desktop Security Client...
echo =====================================================================
cd /d "%~dp0client"
call npx electron .

:: Cleanup when client closes
echo.
echo =====================================================================
echo  Examination Session Closed.
echo =====================================================================
echo Terminating background backend service on port 5000...
for /f "tokens=5" %%p in ('netstat -ano 2^>nul ^| findstr ":5000 " ^| findstr "LISTENING"') do (
    taskkill /F /T /PID %%p >nul 2>&1
)

cd /d "%~dp0"
echo [OK] Session ended cleanly.
echo.
echo Press any key to exit...
pause >nul
