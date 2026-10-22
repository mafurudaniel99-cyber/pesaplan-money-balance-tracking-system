@echo off
REM PesaPlan — Quick install script (Windows / XAMPP)
REM
REM Usage:
REM   1. Edit config\database.php with your MySQL credentials.
REM   2. Double-click install.bat OR run from Command Prompt.

setlocal enabledelayedexpansion

set DB_HOST=127.0.0.1
set DB_NAME=pesaplan
set DB_USER=root
set DB_PASS=

REM Detect XAMPP MySQL path
set MYSQL_BIN=mysql
if exist "C:\xampp\mysql\bin\mysql.exe" set MYSQL_BIN=C:\xampp\mysql\bin\mysql.exe

echo ==========================================
echo   PesaPlan Installer (Windows)
echo ==========================================
echo Host:     %DB_HOST%
echo Database: %DB_NAME%
echo User:     %DB_USER%
echo MySQL:    %MYSQL_BIN%
echo.

set /p confirm=Continue? [y/N]:
if /i not "%confirm%"=="y" (
    echo Aborted.
    pause
    exit /b 1
)

echo.
echo -^> Creating database...
"%MYSQL_BIN%" -h %DB_HOST% -u %DB_USER% %DB_PASS% -e "CREATE DATABASE IF NOT EXISTS %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 (
    echo ERROR: Could not connect to MySQL. Is XAMPP MySQL running?
    pause
    exit /b 1
)

echo -^> Importing schema...
"%MYSQL_BIN%" -h %DB_HOST% -u %DB_USER% %DB_PASS% %DB_NAME% < database\schema.sql

echo -^> Importing seed data...
"%MYSQL_BIN%" -h %DB_HOST% -u %DB_USER% %DB_PASS% %DB_NAME% < database\seed.sql

echo.
echo ==========================================
echo   Installation complete!
echo ==========================================
echo.
echo Next steps:
echo   1. Make sure Apache is running in XAMPP Control Panel.
echo   2. Visit http://localhost/pesa-plan/public/ in your browser.
echo   3. Register your first account and complete the onboarding wizard.
echo.
echo See README.md and docs\INSTALLATION.md for details.
echo.
pause
