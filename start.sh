#!/bin/sh
# CellVerse startup script — expands $PORT reliably even when invoked via Docker exec form
# Railway sets PORT automatically; default to 8080 for local dev
exec php -S 0.0.0.0:${PORT:-8080} -t .
