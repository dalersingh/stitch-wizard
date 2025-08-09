#!/bin/bash
#
# E2E Test Runner for Stitch Wizard
# This script runs Playwright E2E tests in a Docker environment

# Exit on error
set -e

# Color codes for better readability
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Directory where this script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$SCRIPT_DIR"
DOCKER_DIR="$PROJECT_ROOT/docker/e2e-tests"
TEST_RESULTS_DIR="$PROJECT_ROOT/test-results"

# Print section header
section() {
  echo -e "\n${BLUE}=== $1 ===${NC}\n"
}

# Print success message
success() {
  echo -e "${GREEN}✓ $1${NC}"
}

# Print info message
info() {
  echo -e "${YELLOW}ℹ $1${NC}"
}

# Print error message and exit
error() {
  echo -e "${RED}✗ ERROR: $1${NC}" >&2
  exit 1
}

# Check if command exists
command_exists() {
  command -v "$1" >/dev/null 2>&1
}

# Check prerequisites
check_prerequisites() {
  section "Checking Prerequisites"
  
  if ! command_exists docker; then
    error "Docker is not installed. Please install Docker first."
  fi
  success "Docker is installed"
  
  if ! command_exists docker-compose; then
    error "Docker Compose is not installed. Please install Docker Compose first."
  fi
  success "Docker Compose is installed"
  
  # Check if Docker is running
  if ! docker info >/dev/null 2>&1; then
    error "Docker is not running. Please start Docker first."
  fi
  success "Docker is running"
}

# Create necessary directories
prepare_environment() {
  section "Preparing Environment"
  
  # Create test results directory if it doesn't exist
  if [ ! -d "$TEST_RESULTS_DIR" ]; then
    mkdir -p "$TEST_RESULTS_DIR"
    success "Created test results directory: $TEST_RESULTS_DIR"
  else
    info "Test results directory already exists: $TEST_RESULTS_DIR"
  fi
  
  # Ensure Docker directory exists
  if [ ! -d "$DOCKER_DIR" ]; then
    error "Docker directory not found: $DOCKER_DIR"
  fi
  success "Docker directory found: $DOCKER_DIR"
}

# Build and start Docker containers
start_containers() {
  section "Building and Starting Docker Containers"
  
  cd "$PROJECT_ROOT"
  
  info "Building Docker containers..."
  docker-compose -f "$DOCKER_DIR/docker-compose.yml" build || error "Failed to build Docker containers"
  success "Docker containers built successfully"
  
  info "Starting Docker containers..."
  docker-compose -f "$DOCKER_DIR/docker-compose.yml" up -d php || error "Failed to start Docker containers"
  success "Docker containers started successfully"
  
  info "Waiting for PHP service to be ready..."
  timeout=60
  elapsed=0
  while ! docker-compose -f "$DOCKER_DIR/docker-compose.yml" exec php curl -s http://localhost:8000 > /dev/null; do
    sleep 1
    elapsed=$((elapsed + 1))
    if [ "$elapsed" -ge "$timeout" ]; then
      docker-compose -f "$DOCKER_DIR/docker-compose.yml" logs php
      error "Timed out waiting for PHP service to be ready"
    fi
    echo -n "."
  done
  echo ""
  success "PHP service is ready"
}

# Run the tests
run_tests() {
  section "Running E2E Tests"
  
  cd "$PROJECT_ROOT"
  
  info "Starting Playwright tests..."
  docker-compose -f "$DOCKER_DIR/docker-compose.yml" run --rm playwright || {
    test_exit_code=$?
    info "Tests completed with exit code: $test_exit_code"
    return $test_exit_code
  }
  
  success "Tests completed successfully"
}

# Process test results
process_results() {
  section "Processing Test Results"
  
  if [ -d "$TEST_RESULTS_DIR" ] && [ "$(ls -A "$TEST_RESULTS_DIR")" ]; then
    info "Test results available in: $TEST_RESULTS_DIR"
    
    # Count test files
    test_files=$(find "$TEST_RESULTS_DIR" -name "*.png" | wc -l)
    info "Generated $test_files screenshot files"
    
    # Check for HTML report
    if [ -f "$TEST_RESULTS_DIR/index.html" ]; then
      success "HTML report generated: $TEST_RESULTS_DIR/index.html"
    fi
  else
    info "No test results found in $TEST_RESULTS_DIR"
  fi
}

# Clean up Docker containers
cleanup() {
  section "Cleaning Up"
  
  cd "$PROJECT_ROOT"
  
  info "Stopping Docker containers..."
  docker-compose -f "$DOCKER_DIR/docker-compose.yml" down || info "Warning: Failed to stop some containers"
  success "Docker containers stopped"
  
  # Optional: remove volumes
  if [ "$1" = "--clean" ]; then
    info "Removing Docker volumes..."
    docker-compose -f "$DOCKER_DIR/docker-compose.yml" down -v || info "Warning: Failed to remove some volumes"
    success "Docker volumes removed"
  fi
}

# Main execution
main() {
  echo -e "${BLUE}========================================${NC}"
  echo -e "${BLUE}   Stitch Wizard E2E Test Runner       ${NC}"
  echo -e "${BLUE}========================================${NC}"
  
  # Parse command line arguments
  CLEAN_FLAG=""
  for arg in "$@"; do
    case $arg in
      --clean)
        CLEAN_FLAG="--clean"
        shift
        ;;
      --help)
        echo "Usage: $0 [options]"
        echo ""
        echo "Options:"
        echo "  --clean    Remove all Docker volumes after tests"
        echo "  --help     Show this help message"
        exit 0
        ;;
    esac
  done
  
  # Execute test workflow
  check_prerequisites
  prepare_environment
  start_containers
  
  # Run tests and capture exit code
  run_tests
  test_exit_code=$?
  
  process_results
  cleanup "$CLEAN_FLAG"
  
  # Final message
  if [ $test_exit_code -eq 0 ]; then
    echo -e "\n${GREEN}✓ All E2E tests passed successfully!${NC}"
  else
    echo -e "\n${RED}✗ Some E2E tests failed. Check the test results for details.${NC}"
    exit $test_exit_code
  fi
}

# Run the main function
main "$@"
