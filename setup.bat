@echo off
setlocal enabledelayedexpansion

REM BGRemover & Image Upscaler - Windows Setup Script
REM This script automates the setup process for the Laravel application on Windows

echo 🚀 BGRemover & Image Upscaler - Windows Setup Script
echo =====================================================

REM Check if required commands exist
echo [INFO] Checking prerequisites...

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP is not installed or not in PATH
    echo Please install PHP 8.2 or higher
    pause
    exit /b 1
)

where composer >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Composer is not installed or not in PATH
    echo Please install Composer
    pause
    exit /b 1
)

where node >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Node.js is not installed or not in PATH
    echo Please install Node.js 18 or higher
    pause
    exit /b 1
)

where npm >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] npm is not installed or not in PATH
    echo Please install npm
    pause
    exit /b 1
)

echo [SUCCESS] All prerequisites are satisfied!

REM Step 1: Install PHP dependencies
echo [INFO] Installing PHP dependencies...
composer install --no-interaction
if %errorlevel% neq 0 (
    echo [ERROR] Failed to install PHP dependencies
    pause
    exit /b 1
)
echo [SUCCESS] PHP dependencies installed

REM Step 2: Environment setup
echo [INFO] Setting up environment...

if not exist .env (
    if exist .env.example (
        copy .env.example .env
        echo [SUCCESS] Created .env file from .env.example
    ) else (
        echo [WARNING] No .env.example found. You'll need to create .env manually.
    )
) else (
    echo [WARNING] .env file already exists
)

REM Generate application key
echo [INFO] Generating application key...
php artisan key:generate --no-interaction
if %errorlevel% neq 0 (
    echo [ERROR] Failed to generate application key
    pause
    exit /b 1
)
echo [SUCCESS] Application key generated

REM Step 3: Database setup
echo [INFO] Setting up database...

REM Create SQLite database if it doesn't exist
if not exist database\database.sqlite (
    type nul > database\database.sqlite
    echo [SUCCESS] Created SQLite database
)

REM Run migrations
echo [INFO] Running database migrations...
php artisan migrate --no-interaction
if %errorlevel% neq 0 (
    echo [ERROR] Failed to run migrations
    pause
    exit /b 1
)
echo [SUCCESS] Database migrations completed

REM Step 4: Install Node.js dependencies
echo [INFO] Installing Node.js dependencies...
npm install
if %errorlevel% neq 0 (
    echo [ERROR] Failed to install Node.js dependencies
    pause
    exit /b 1
)
echo [SUCCESS] Node.js dependencies installed

REM Step 5: Build frontend assets
echo [INFO] Building frontend assets...
npm run build
if %errorlevel% neq 0 (
    echo [ERROR] Failed to build frontend assets
    pause
    exit /b 1
)
echo [SUCCESS] Frontend assets built

REM Step 6: Storage setup
echo [INFO] Setting up storage...
php artisan storage:link
if %errorlevel% neq 0 (
    echo [WARNING] Failed to create storage link
) else (
    echo [SUCCESS] Storage link created
)

REM Step 7: Python environment setup
echo [INFO] Setting up Python environment...

REM Check if Python 3.10 is available
python --version >nul 2>nul
if %errorlevel% neq 0 (
    echo [WARNING] Python not found. You'll need to install Python 3.10 manually.
    echo Please follow the Python setup instructions in the README.md
) else (
    for /f "tokens=2" %%i in ('python --version 2^>^&1') do set PYTHON_VERSION=%%i
    echo [INFO] Found Python version: !PYTHON_VERSION!
    
    REM Check if it's Python 3.10
    echo !PYTHON_VERSION! | findstr "3.10" >nul
    if %errorlevel% equ 0 (
        echo [SUCCESS] Python 3.10 found
        
        REM Create virtual environment
        if not exist venv (
            echo [INFO] Creating Python virtual environment...
            python -m venv venv
            if %errorlevel% neq 0 (
                echo [ERROR] Failed to create virtual environment
            ) else (
                echo [SUCCESS] Virtual environment created
            )
        ) else (
            echo [WARNING] Virtual environment already exists
        )
        
        REM Install packages
        if exist venv (
            echo [INFO] Installing Python packages...
            call venv\Scripts\activate.bat
            python -m pip install --upgrade pip setuptools wheel
            python -m pip install rembg realesrgan
            if %errorlevel% neq 0 (
                echo [ERROR] Failed to install Python packages
            ) else (
                echo [SUCCESS] Python packages installed
            )
            call venv\Scripts\deactivate.bat
        )
    ) else (
        echo [WARNING] Python 3.10 not found. You'll need to install it manually.
        echo Please follow the Python setup instructions in the README.md
    )
)

REM Step 8: Final setup
echo [INFO] Performing final setup...

REM Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo [SUCCESS] Caches cleared

REM Create a simple start script for Windows
echo @echo off > start-dev.bat
echo echo 🚀 Starting BGRemover development environment... >> start-dev.bat
echo echo Starting Laravel server on http://localhost:8000 >> start-dev.bat
echo echo Starting queue worker... >> start-dev.bat
echo echo Starting Vite development server... >> start-dev.bat
echo echo. >> start-dev.bat
echo echo Press Ctrl+C to stop all services >> start-dev.bat
echo echo. >> start-dev.bat
echo. >> start-dev.bat
echo REM Start all services using composer script >> start-dev.bat
echo composer run dev >> start-dev.bat

echo [SUCCESS] Created start-dev.bat script

REM Final instructions
echo.
echo 🎉 Setup completed successfully!
echo.
echo Next steps:
echo 1. Edit .env file if needed
echo 2. Run: start-dev.bat
echo 3. Visit: http://localhost:8000
echo.
echo For manual start:
echo   php artisan serve
echo   php artisan queue:work
echo   npm run dev
echo.
echo For more information, see README.md
echo.
pause 