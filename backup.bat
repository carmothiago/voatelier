@echo off
REM =====================================================================
REM Vitoria Oliver Atelier - Backup automatico
REM Gera um dump do banco de dados e copia a pasta uploads/
REM Ajuste os caminhos abaixo conforme sua instalacao do XAMPP
REM =====================================================================

setlocal

REM --- Ajuste estes caminhos conforme seu ambiente ---
set XAMPP_PATH=C:\xampp
set MYSQL_BIN=%XAMPP_PATH%\mysql\bin
set PROJECT_PATH=%XAMPP_PATH%\htdocs\voatelier
set BACKUP_ROOT=C:\backups\voatelier
set DB_NAME=voatelier
set DB_USER=root
set DB_PASS=

REM --- Nao alterar a partir daqui ---
for /f "tokens=1-4 delims=/ " %%a in ('date /t') do set DATA=%%c-%%b-%%a
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set HORA=%%a%%b

set PASTA_BACKUP=%BACKUP_ROOT%\%DATA%_%HORA%

mkdir "%PASTA_BACKUP%" 2>nul

echo Gerando backup do banco de dados...
if "%DB_PASS%"=="" (
    "%MYSQL_BIN%\mysqldump.exe" -u %DB_USER% %DB_NAME% > "%PASTA_BACKUP%\voatelier.sql"
) else (
    "%MYSQL_BIN%\mysqldump.exe" -u %DB_USER% -p%DB_PASS% %DB_NAME% > "%PASTA_BACKUP%\voatelier.sql"
)

echo Copiando pasta de uploads...
xcopy "%PROJECT_PATH%\uploads" "%PASTA_BACKUP%\uploads\" /E /I /Y

echo.
echo Backup concluido em: %PASTA_BACKUP%
echo.

endlocal
pause
