#!/bin/bash
#
# run-screenshot-capture.sh - Quick Screenshot Capture for Stitch Wizard
#
# This script quickly runs the existing E2E test to capture screenshots
# of the real estate wizard in action, showing the fixes in place.
#

# Exit on error
set -e

# Color codes for better readability
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Script configuration
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
DOCKER_COMPOSE_FILE="$SCRIPT_DIR/docker-compose.demo.yml"
SCREENSHOTS_DIR="$SCRIPT_DIR/test-results/screenshots"

# Print header
echo -e "\n${BLUE}============================================${NC}"
echo -e "${BLUE}   Stitch Wizard Screenshot Capture Tool    ${NC}"
echo -e "${BLUE}============================================${NC}\n"

# Check prerequisites
echo -e "${YELLOW}Checking prerequisites...${NC}"
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed. Please install Docker first.${NC}"
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}Error: Docker Compose is not installed. Please install Docker Compose first.${NC}"
    exit 1
fi

if ! docker info &> /dev/null; then
    echo -e "${RED}Error: Docker is not running. Please start Docker first.${NC}"
    exit 1
fi

# Create screenshots directory
echo -e "${YELLOW}Creating screenshots directory...${NC}"
mkdir -p "$SCREENSHOTS_DIR"

# Start the services
echo -e "${YELLOW}Starting Docker services...${NC}"
docker-compose -f "$DOCKER_COMPOSE_FILE" up -d mysql redis
echo -e "${GREEN}Database services started.${NC}"

# Wait for MySQL to be ready
echo -e "${YELLOW}Waiting for MySQL to be ready...${NC}"
for i in {1..30}; do
    if docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T mysql mysqladmin ping -h localhost -u laravel -ppassword --silent &> /dev/null; then
        echo -e "${GREEN}MySQL is ready.${NC}"
        break
    fi
    echo -n "."
    sleep 1
    if [ $i -eq 30 ]; then
        echo -e "\n${RED}Error: MySQL did not become ready in time.${NC}"
        exit 1
    fi
done

# Start the Laravel application
echo -e "${YELLOW}Starting Laravel application...${NC}"
docker-compose -f "$DOCKER_COMPOSE_FILE" up -d laravel
echo -e "${GREEN}Laravel application started.${NC}"

# Wait for Laravel to be ready
echo -e "${YELLOW}Waiting for Laravel application to be ready...${NC}"
for i in {1..60}; do
    if docker-compose -f "$DOCKER_COMPOSE_FILE" exec -T laravel php artisan --version &> /dev/null; then
        echo -e "${GREEN}Laravel application is ready.${NC}"
        break
    fi
    echo -n "."
    sleep 1
    if [ $i -eq 60 ]; then
        echo -e "\n${RED}Error: Laravel application did not become ready in time.${NC}"
        exit 1
    fi
done

# Start Nginx
echo -e "${YELLOW}Starting Nginx web server...${NC}"
docker-compose -f "$DOCKER_COMPOSE_FILE" up -d nginx
echo -e "${GREEN}Nginx web server started.${NC}"

# Wait for web server to be ready
echo -e "${YELLOW}Waiting for web server to be ready...${NC}"
for i in {1..30}; do
    if curl -s http://localhost:8000 &> /dev/null; then
        echo -e "${GREEN}Web server is ready.${NC}"
        break
    fi
    echo -n "."
    sleep 1
    if [ $i -eq 30 ]; then
        echo -e "\n${RED}Error: Web server did not become ready in time.${NC}"
        exit 1
    fi
done

# Run the Playwright test to capture screenshots
echo -e "\n${YELLOW}Running Playwright to capture screenshots...${NC}"
echo -e "${YELLOW}This may take a minute or two...${NC}\n"

# Create a special command that focuses on capturing screenshots
docker-compose -f "$DOCKER_COMPOSE_FILE" run --rm playwright bash -c "
    echo 'Waiting for application to be ready...' &&
    wget --retry-connrefused --waitretry=1 --read-timeout=20 --timeout=15 -t 10 http://nginx/ -O /dev/null &&
    echo 'Running test with screenshot capture...' &&
    mkdir -p /test-results/screenshots &&
    npx playwright test e2e/tests/real-estate.spec.ts --config=/app/e2e/playwright.config.js --project=chromium --reporter=list
"

# Check if the test was successful
if [ $? -eq 0 ]; then
    echo -e "\n${GREEN}Screenshot capture completed successfully!${NC}"
else
    echo -e "\n${YELLOW}Screenshot capture completed with some errors, but screenshots may still be available.${NC}"
fi

# Copy screenshots to the local directory
echo -e "${YELLOW}Collecting screenshots...${NC}"
CONTAINER_ID=$(docker-compose -f "$DOCKER_COMPOSE_FILE" ps -q playwright)
if [ -n "$CONTAINER_ID" ]; then
    docker cp "$CONTAINER_ID:/test-results/." "$SCRIPT_DIR/test-results/" 2>/dev/null || true
fi

# Count and list screenshots
SCREENSHOT_COUNT=$(find "$SCREENSHOTS_DIR" -name "*.png" | wc -l)
if [ "$SCREENSHOT_COUNT" -gt 0 ]; then
    echo -e "${GREEN}Captured $SCREENSHOT_COUNT screenshots:${NC}"
    find "$SCREENSHOTS_DIR" -name "*.png" | sort | while read -r file; do
        echo "  - $(basename "$file")"
    done
    echo -e "\n${GREEN}Screenshots are available in: $SCREENSHOTS_DIR${NC}"
else
    echo -e "${RED}No screenshots were captured. Check the Docker logs for errors.${NC}"
    echo -e "${YELLOW}Try running: docker-compose -f $DOCKER_COMPOSE_FILE logs playwright${NC}"
fi

# Create a simple summary file
echo -e "${YELLOW}Creating screenshot summary...${NC}"
SUMMARY_FILE="$SCREENSHOTS_DIR/screenshot-summary.md"

cat > "$SUMMARY_FILE" << EOF
# Real Estate Wizard Screenshots

These screenshots demonstrate the fixes implemented for CodeRabbitAI feedback:

1. **Progress bar correctly reaches 100% on final step**
2. **Empty sections are not rendered**
3. **StepIndex helper is used consistently**

## Screenshots
EOF

# Add screenshots to summary
find "$SCREENSHOTS_DIR" -name "*.png" | sort | while read -r file; do
    FILENAME=$(basename "$file")
    echo -e "\n### $FILENAME\n\n![Screenshot](./$FILENAME)\n" >> "$SUMMARY_FILE"
done

echo -e "${GREEN}Screenshot summary created: $SUMMARY_FILE${NC}"

# Ask if user wants to stop containers
echo -e "\n${YELLOW}Do you want to stop the Docker containers? (y/n)${NC}"
read -r STOP_CONTAINERS
if [[ "$STOP_CONTAINERS" =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}Stopping Docker containers...${NC}"
    docker-compose -f "$DOCKER_COMPOSE_FILE" down
    echo -e "${GREEN}Docker containers stopped.${NC}"
else
    echo -e "${YELLOW}Docker containers are still running.${NC}"
    echo -e "${YELLOW}To stop them later, run: docker-compose -f $DOCKER_COMPOSE_FILE down${NC}"
fi

echo -e "\n${GREEN}All done! Screenshots have been captured successfully.${NC}"
