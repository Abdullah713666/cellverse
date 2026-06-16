#!/bin/sh
# Cellverse entry point for Railway/RAILPACK.
# RAILPACK parses simple shell commands into exec form, breaking $PORT expansion.
# Using a script file ensures the shell stays in scope to expand $PORT.
set -e
PORT="${PORT:-8080}"
echo "Starting Cellverse on 0.0.0.0:${PORT}"
exec php -S 0.0.0.0:${PORT} -t .
