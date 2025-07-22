#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🛑 Stopping BG Remover Server...${NC}"
echo ""

# Function to stop service by PID file
stop_service() {
    local name=$1
    local pid_file=".$name.pid"
    
    if [ -f "$pid_file" ]; then
        local pid=$(cat "$pid_file")
        if ps -p $pid > /dev/null 2>&1; then
            echo -e "${YELLOW}Stopping $name (PID: $pid)...${NC}"
            kill $pid
            sleep 1
            if ! ps -p $pid > /dev/null 2>&1; then
                echo -e "${GREEN}✅ $name stopped${NC}"
                rm -f "$pid_file"
            else
                echo -e "${RED}❌ Failed to stop $name, force killing...${NC}"
                kill -9 $pid
                rm -f "$pid_file"
            fi
        else
            echo -e "${YELLOW}$name is not running${NC}"
            rm -f "$pid_file"
        fi
    else
        echo -e "${YELLOW}No PID file found for $name${NC}"
    fi
}

# Stop all services
stop_service "Laravel Server"
stop_service "Python Backend"
stop_service "Queue Worker"
stop_service "NPM Dev"

# Kill any remaining processes on our ports
echo -e "${YELLOW}Cleaning up ports...${NC}"

# Kill processes on port 8000 (Laravel)
if lsof -ti:8000 > /dev/null 2>&1; then
    echo -e "${YELLOW}Killing processes on port 8000...${NC}"
    lsof -ti:8000 | xargs kill -9
fi

# Kill processes on port 5000 (Python)
if lsof -ti:5000 > /dev/null 2>&1; then
    echo -e "${YELLOW}Killing processes on port 5000...${NC}"
    lsof -ti:5000 | xargs kill -9
fi

echo ""
echo -e "${GREEN}🎉 All services stopped successfully!${NC}" 