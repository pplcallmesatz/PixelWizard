#!/bin/bash

# BGRemover & Image Upscaler - Setup Script
# This script automates the setup process for the Laravel application

set -e  # Exit on any error

echo "🚀 BGRemover & Image Upscaler - Setup Script"
echo "=============================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if required commands exist
check_command() {
    if ! command -v $1 &> /dev/null; then
        print_error "$1 is not installed. Please install it first."
        exit 1
    fi
}

# Check prerequisites
print_status "Checking prerequisites..."

check_command "php"
check_command "composer"
check_command "node"
check_command "npm"
check_command "git"

# Check PHP version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
if [[ ! "$PHP_VERSION" =~ ^8\.[2-9] ]]; then
    print_error "PHP 8.2 or higher is required. Current version: $PHP_VERSION"
    exit 1
fi
print_success "PHP version: $PHP_VERSION"

# Check Node.js version
NODE_VERSION=$(node --version)
print_success "Node.js version: $NODE_VERSION"

print_success "All prerequisites are satisfied!"

# Step 1: Install PHP dependencies
print_status "Installing PHP dependencies..."
composer install --no-interaction
print_success "PHP dependencies installed"

# Step 2: Environment setup
print_status "Setting up environment..."

if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        print_success "Created .env file from .env.example"
    else
        print_warning "No .env.example found. You'll need to create .env manually."
    fi
else
    print_warning ".env file already exists"
fi

# Generate application key
print_status "Generating application key..."
php artisan key:generate --no-interaction
print_success "Application key generated"

# Step 3: Database setup
print_status "Setting up database..."

# Create SQLite database if it doesn't exist
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
    print_success "Created SQLite database"
fi

# Run migrations
print_status "Running database migrations..."
php artisan migrate --no-interaction
print_success "Database migrations completed"

# Step 4: Install Node.js dependencies
print_status "Installing Node.js dependencies..."
npm install
print_success "Node.js dependencies installed"

# Step 5: Build frontend assets
print_status "Building frontend assets..."
npm run build
print_success "Frontend assets built"

# Step 6: Storage setup
print_status "Setting up storage..."
php artisan storage:link
print_success "Storage link created"

# Set permissions
print_status "Setting file permissions..."
chmod -R 775 storage bootstrap/cache
print_success "File permissions set"

# Step 7: Python environment setup
print_status "Setting up Python environment..."

# Check if Python 3.10 is available
PYTHON_VERSION=$(python3.10 --version 2>/dev/null || python --version 2>/dev/null || echo "Python not found")

if [[ "$PYTHON_VERSION" =~ "3.10" ]]; then
    print_success "Python 3.10 found: $PYTHON_VERSION"
    
    # Create virtual environment
    if [ ! -d "venv" ]; then
        print_status "Creating Python virtual environment..."
        python3.10 -m venv venv 2>/dev/null || python -m venv venv
        print_success "Virtual environment created"
    else
        print_warning "Virtual environment already exists"
    fi
    
    # Activate virtual environment and install packages
    print_status "Installing Python packages..."
    source venv/bin/activate
    pip install --upgrade pip setuptools wheel
    pip install rembg realesrgan
    print_success "Python packages installed"
    
    # Deactivate virtual environment
    deactivate
else
    print_warning "Python 3.10 not found. You'll need to install it manually."
    print_warning "Please follow the Python setup instructions in the README.md"
fi

# Step 8: Final setup
print_status "Performing final setup..."

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

print_success "Caches cleared"

# Create a simple start script
cat > start-dev.sh << 'EOF'
#!/bin/bash
echo "🚀 Starting BGRemover development environment..."
echo "Starting Laravel server on http://localhost:8000"
echo "Starting queue worker..."
echo "Starting Vite development server..."
echo ""
echo "Press Ctrl+C to stop all services"
echo ""

# Start all services using composer script
composer run dev
EOF

chmod +x start-dev.sh
print_success "Created start-dev.sh script"

# Final instructions
echo ""
echo "🎉 Setup completed successfully!"
echo ""
echo "Next steps:"
echo "1. Edit .env file if needed"
echo "2. Run: ./start-dev.sh"
echo "3. Visit: http://localhost:8000"
echo ""
echo "For manual start:"
echo "  php artisan serve"
echo "  php artisan queue:work"
echo "  npm run dev"
echo ""
echo "For more information, see README.md"
echo "" 