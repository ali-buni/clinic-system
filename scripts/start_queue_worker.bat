@echo off
REM Start the Laravel queue worker for database driver
REM Opens a new window so you can keep coding in the current terminal
start "Laravel Queue Worker" cmd /c "php artisan queue:work --tries=3 --delay=5"
echo Queue worker started in a new window.
