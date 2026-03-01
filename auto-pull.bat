@echo off
cd /d C:\sihealing

REM Pull latest code from GitHub
git fetch origin
git pull origin claude/add-healing-database-5boVD

REM Optional: Auto restart PHP server
taskkill /F /IM php.exe 2>nul
timeout /t 2
start cmd /k "php artisan serve"

echo [%date% %time%] Auto-pull completed >> C:\sihealing\auto-pull.log
