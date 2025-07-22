#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}📋 BG Remover Server Logs${NC}"
echo ""

# Function to show service status
show_status() {
    local name=$1
    local pid_file=".$name.pid"
    
    if [ -f "$pid_file" ]; then
        local pid=$(cat "$pid_file")
        if ps -p $pid > /dev/null 2>&1; then
            echo -e "${GREEN}✅ $name is running (PID: $pid)${NC}"
        else
            echo -e "${RED}❌ $name is not running${NC}"
        fi
    else
        echo -e "${YELLOW}⚠️  $name status unknown${NC}"
    fi
}

# Show status of all services
echo -e "${BLUE}Service Status:${NC}"
show_status "Laravel Server"
show_status "Python Backend"
show_status "Queue Worker"
show_status "NPM Dev"
echo ""

# Function to show recent logs
show_logs() {
    local name=$1
    local log_file=$2
    
    echo -e "${BLUE}📄 Recent $name logs:${NC}"
    if [ -f "$log_file" ]; then
        tail -n 10 "$log_file"
    else
        echo -e "${YELLOW}No log file found for $name${NC}"
    fi
    echo ""
}

# Show Laravel logs
show_logs "Laravel" "storage/logs/laravel.log"

# Show queue logs (if any)
if [ -f "storage/logs/queue.log" ]; then
    show_logs "Queue" "storage/logs/queue.log"
fi

# Show Python backend logs (if any)
if [ -f "backend/app.log" ]; then
    show_logs "Python Backend" "backend/app.log"
fi

echo -e "${YELLOW}💡 To view live logs, use:${NC}"
echo -e "  • Laravel: tail -f storage/logs/laravel.log"
echo -e "  • Queue: tail -f storage/logs/queue.log"
echo -e "  • Python: tail -f backend/app.log" 