FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY . /app

EXPOSE 8080

# Copy entry script and make it executable.
# Using a script file prevents RAILPACK from inlining/parsing the shell expansion.
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Exec form ENTRYPOINT: RAILPACK will run exactly [/start.sh] with no shell parsing.
# /start.sh uses /bin/sh internally to expand $PORT.
ENTRYPOINT ["/start.sh"]
