FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY . /app

EXPOSE 8080

# ENTRYPOINT (not CMD) so Railway doesn't override it
# Explicit sh -c will expand $PORT before running PHP
ENTRYPOINT ["sh", "-c", "exec php -S 0.0.0.0:${PORT:-8080} -t ."]
