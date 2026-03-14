@echo off
REM =========================================
REM Warehouse Inventory System - Quick Start
REM =========================================
REM This script sets up and runs the Laravel application

cd /d d:\Knnys_Websites\godhelpme\Laravel3\warehouse_app

echo.
echo ====================================
echo 🚀 Warehouse Inventory System Setup
echo ====================================
echo.

REM Check if database is ready (requires MySQL running in XAMPP)
echo Checking database connection...
echo (Make sure XAMPP MySQL is running!)
echo.

REM Run migrations
echo Running migrations...
call php artisan migrate --force 2>nul
if errorlevel 1 (
    echo ⚠️  Migration may have failed. Ensure MySQL (warehouse_db) exists.
) else (
    echo ✅ Migrations complete
)

echo.

REM Optional: Seed sample data
echo Seeding sample data...
call php artisan db:seed --class=StockItemSeeder 2>nul
if errorlevel 1 (
    echo ⚠️  Seeding may have failed
) else (
    echo ✅ Sample data seeded
)

echo.
echo ====================================
echo ✅ Setup Complete!
echo ====================================
echo.
echo Starting Laravel development server...
echo Access at: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.

call php artisan serve

pause
