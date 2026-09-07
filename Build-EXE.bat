@echo off
setlocal
title ExamFort Lockdown - Secure Windows Installer Builder
color 0a

echo =====================================================================
echo    EXAMFORT SECURE LOCKDOWN - ENTERPRISE INSTALLER BUILDER
echo =====================================================================
echo.

cd /d "%~dp0client"

echo [1/2] Verifying client build environment...
if not exist "node_modules\" (
    echo Installing client dependencies...
    call npm install
)

echo.
echo [2/2] Generating Secure Windows Installer (NSIS Setup .exe)...
call npm run build

if %errorlevel% equ 0 (
    color 0a
    echo.
    echo =====================================================================
    echo  INSTALLER BUILD COMPLETED SUCCESSFULLY!
    echo =====================================================================
    echo Your official setup installer has been generated in:
    echo "%~dp0client\dist"
    echo.
    echo Opening destination folder...
    explorer "%~dp0client\dist"
) else (
    color 0c
    echo.
    echo [ERROR] Build failed with error code %errorlevel%.
)

echo.
pause
