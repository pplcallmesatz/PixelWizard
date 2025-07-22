# Quick Start Guide

## 🚀 One-Command Setup

### macOS/Linux
```bash
./setup.sh
```

### Windows
```cmd
setup.bat
```

## 🏃‍♂️ Quick Commands

### Start Development Environment
```bash
# All-in-one (recommended)
./start-dev.sh

# Or manually:
php artisan serve
php artisan queue:work
npm run dev
```

### Stop All Services
```bash
# Press Ctrl+C in the terminal running start-dev.sh
```

## 🔧 Common Commands

### Laravel Commands
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Database
php artisan migrate
php artisan migrate:fresh
php artisan db:seed

# Queue
php artisan queue:work
php artisan queue:restart
```

### Frontend Commands
```bash
# Development
npm run dev

# Production build
npm run build

# Install dependencies
npm install
```

### Python Commands
```bash
# Activate virtual environment
source venv/bin/activate  # macOS/Linux
venv\Scripts\activate.bat # Windows

# Install packages
pip install rembg realesrgan

# Deactivate
deactivate
```

## 🌐 Access Points

- **Main Application**: http://localhost:8000
- **Registration**: http://localhost:8000/register
- **Login**: http://localhost:8000/login
- **Dashboard**: http://localhost:8000/dashboard
- **Image Upload**: http://localhost:8000/image/upload
- **Image Upscale**: http://localhost:8000/upscale

## 🐞 Quick Troubleshooting

### Reset Everything
```bash
# Clear all caches
php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear

# Reset database
rm database/database.sqlite && touch database/database.sqlite && php artisan migrate

# Reinstall dependencies
rm -rf vendor && composer install
rm -rf node_modules && npm install
```

### Check Status
```bash
# PHP version
php --version

# Node version
node --version

# Python version
python --version

# Laravel status
php artisan --version
```

## 📁 Important Files

- `.env` - Environment configuration
- `database/database.sqlite` - SQLite database
- `storage/app/public/` - Uploaded files
- `python/` - Python scripts for image processing
- `venv/` - Python virtual environment

## 🔐 Default User

After setup, you can register a new user at http://localhost:8000/register

## 📝 Environment Variables

Key variables in `.env`:
```env
APP_NAME="BGRemover & Image Upscaler"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=sqlite
QUEUE_CONNECTION=database
```

## 🆘 Need Help?

1. Check the full [README.md](README.md) for detailed instructions
2. Look at the troubleshooting section in README.md
3. Check Laravel logs: `tail -f storage/logs/laravel.log`
4. Ensure Python 3.10 is installed for image processing 