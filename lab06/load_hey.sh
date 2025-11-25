#!/bin/bash

# Usage: ./load_hey.sh <threads> <duration_seconds>

THREADS=${1:-10}
DURATION=${2:-60}

ALB_DNS="project-alb-857742360.eu-central-1.elb.amazonaws.com"
TARGET="http://$ALB_DNS/"

echo "========================================="
echo "Starting load with hey"
echo "Target: $TARGET"
echo "Threads: $THREADS"
echo "Duration: ${DURATION}s"
echo "========================================="

hey -c "$THREADS" -z "${DURATION}s" "$TARGET"