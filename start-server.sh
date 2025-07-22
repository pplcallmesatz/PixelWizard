#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🚀 Starting BG Remover Server...${NC}"
echo ""

# Function to check if a port is in use
check_port() {
    if lsof -Pi :$1 -sTCP:LISTEN -t >/dev/null ; then
        echo -e "${RED}❌ Port $1 is already in use!${NC}"
        return 1
    else
        return 0
    fi
}

# Check if required ports are available
echo -e "${YELLOW}📋 Checking ports...${NC}"
if ! check_port 8000; then
    echo "Please stop the service using port 8000"
    exit 1
fi

if ! check_port 5000; then
    echo "Please stop the service using port 5000"
    exit 1
fi

echo -e "${GREEN}✅ All ports are available${NC}"
echo ""

# Function to start services in background
start_service() {
    local name=$1
    local command=$2
    local color=$3
    
    echo -e "${color}Starting $name...${NC}"
    eval "$command" &
    local pid=$!
    echo -e "${GREEN}✅ $name started (PID: $pid)${NC}"
    echo $pid > ".$name.pid"
    sleep 2
}

# Start Laravel development server
start_service "Laravel Server" "php artisan serve --host=0.0.0.0 --port=8000" $BLUE

# Start Python backend server
start_service "Python Backend" "python3 backend/app.py" $YELLOW

# Start Laravel queue worker
start_service "Queue Worker" "php artisan queue:work --memory=1024 --timeout=300" $GREEN

# Start NPM development server
start_service "NPM Dev" "npm run dev" $RED

echo ""
echo -e "${GREEN}🎉 All services started successfully!${NC}"
echo ""
echo -e "${BLUE}📱 Services running:${NC}"
echo -e "  • Laravel Server: ${GREEN}http://localhost:8000${NC}"
echo -e "  • Python Backend: ${GREEN}http://localhost:5000${NC}"
echo -e "  • Queue Worker: ${GREEN}Running in background${NC}"
echo ""
echo -e "${YELLOW}💡 To stop all services, run: ./stop-server.sh${NC}"
echo -e "${YELLOW}💡 To view logs, run: ./view-logs.sh${NC}" 