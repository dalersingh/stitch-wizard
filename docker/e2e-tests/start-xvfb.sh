#!/bin/bash
set -e

# Start Xvfb
Xvfb :99 -screen 0 1280x1024x24 &
export DISPLAY=:99

# Wait for Xvfb to be ready
echo "Waiting for Xvfb to start..."
for i in $(seq 1 10); do
  if xdpyinfo -display :99 >/dev/null 2>&1; then
    echo "Xvfb is running."
    break
  fi
  echo "Waiting for Xvfb to start... ($i/10)"
  sleep 1
done

# Check if Xvfb is running
if ! xdpyinfo -display :99 >/dev/null 2>&1; then
  echo "Xvfb failed to start" >&2
  exit 1
fi

# Trap to ensure Xvfb is killed when the script exits
trap 'kill $(jobs -p)' EXIT

# Execute the command passed to the script
echo "Running command: $@"
exec "$@"
