#!/bin/bash
set -e

# Start Xvfb with specific dimensions and color depth
Xvfb :99 -screen 0 1280x1024x24 -ac &
XVFB_PID=$!
export DISPLAY=:99

echo "Starting Xvfb with PID: $XVFB_PID"

# Wait for Xvfb to be ready
echo "Waiting for Xvfb to start..."
for i in $(seq 1 10); do
  if xdpyinfo -display :99 >/dev/null 2>&1; then
    echo "Xvfb is running and ready."
    break
  fi
  echo "Waiting for Xvfb to start... ($i/10)"
  sleep 1
done

# Check if Xvfb is running
if ! xdpyinfo -display :99 >/dev/null 2>&1; then
  echo "ERROR: Xvfb failed to start" >&2
  exit 1
fi

# Trap to ensure Xvfb is killed when the script exits
cleanup() {
  echo "Shutting down Xvfb (PID: $XVFB_PID)..."
  if kill -0 $XVFB_PID 2>/dev/null; then
    kill $XVFB_PID
    echo "Xvfb stopped."
  else
    echo "Xvfb was already stopped."
  fi
  
  # Kill any other processes that might have been started
  jobs -p | xargs -r kill
  
  echo "Cleanup complete."
}

trap cleanup EXIT INT TERM

# Print environment information
echo "Environment information:"
echo "DISPLAY=$DISPLAY"
echo "NODE_VERSION=$(node --version)"
echo "NPM_VERSION=$(npm --version)"
echo "PLAYWRIGHT_VERSION=$(npx playwright --version)"

# Execute the command passed to the script
echo "Running command: $@"
exec "$@"
