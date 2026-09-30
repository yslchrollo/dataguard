@echo off
title DataGuard DLP System Server
echo ========================================================
echo   DataGuard: Web-Based Data Loss Prevention System
echo ========================================================
echo Starting MySQL and Laravel Application Server...
cd /d "%~dp0"
set PATH=C:\xampp\php;C:\ProgramData\ComposerSetup\bin;%PATH%
start "" "http://127.0.0.1:8000/login"
php artisan serve --host=127.0.0.1 --port=8000
pause