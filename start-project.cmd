@echo off
setlocal
cd /d "%~dp0"
set "PHP=C:\OSPanel\modules\PHP-8.3\PHP\php.exe"
set "MYSQL=C:\OSPanel\modules\MySQL-8.4\bin"
if not exist "%PHP%" (
  echo PHP 8.3 was not found in Open Server.
  pause
  exit /b 1
)
if not exist "vendor\autoload.php" (
  echo Installing Composer dependencies...
  set "PATH=C:\OSPanel\modules\PHP-8.3\PHP;%PATH%"
  "%PHP%" "C:\OSPanel\data\PHP-8.3\default\composer\composer.phar" install --no-interaction --prefer-dist
  if errorlevel 1 goto fail
)
if not exist ".env" (
  echo Configuration .env is missing. See START.txt.
  goto fail
)
"%MYSQL%\mysqladmin.exe" --no-defaults --host=127.127.126.32 --port=3306 --user=root ping >nul 2>&1
if errorlevel 1 (
  echo Starting MySQL 8.4...
  start "" /b "%MYSQL%\mysqld.exe" --defaults-file=C:/OSPanel/modules/MySQL-8.4/my.ini
  timeout /t 5 /nobreak >nul
)
"%MYSQL%\mysqladmin.exe" --no-defaults --host=127.127.126.32 --port=3306 --user=root ping >nul 2>&1
if errorlevel 1 (
  echo MySQL could not be started. Start MySQL 8.4 in Open Server.
  goto fail
)
"%PHP%" -r "exit(@file_get_contents('http://127.0.0.1:8000/up') === false ? 1 : 0);" >nul 2>&1
if not errorlevel 1 (
  start "" "http://127.0.0.1:8000"
  exit /b 0
)
echo Open http://127.0.0.1:8000 in your browser.
echo Keep this window open. Press Ctrl+C to stop Laravel.
start "" /b powershell.exe -NoProfile -WindowStyle Hidden -Command "Start-Sleep -Seconds 3; Start-Process 'http://127.0.0.1:8000'"
"%PHP%" artisan serve --host=127.0.0.1 --port=8000 --no-reload
exit /b
:fail
pause
exit /b 1
