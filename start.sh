#!/bin/sh
# CellVerse ENTRYPOINT
# Railway injects a startCommand as args; if those args contain a literal "$PORT",
# substitute the real $PORT value (Railway sets it automatically; default 8080).
# Then exec the resulting command.
#
# Example: start.sh php -S 0.0.0.0:$PORT -t .
#   -> exec php -S 0.0.0.0:<actual-port> -t .

# Build the command line, substituting $PORT in each argument
args=""
for arg in "$@"; do
    # Replace $PORT (and ${PORT}) with the actual port value
    new_arg=$(printf '%s' "$arg" | sed "s|\${PORT:-8080}|${PORT:-8080}|g; s|\$PORT|${PORT:-8080}|g")
    if [ -z "$args" ]; then
        args="$new_arg"
    else
        args="$args $new_arg"
    fi
done

# Use exec via sh -c to handle arguments with spaces correctly
exec sh -c "$args"
