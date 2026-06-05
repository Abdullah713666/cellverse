FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY . /app

EXPOSE 8080

# Shell form CMD: /bin/sh -c "..." expands $PORT before exec'ing php
# This is what Railway/RAILPACK will run when RAILPACK_BUILDER=DOCKER
CMD php -S 0.0.0.0:${PORT:-8080} -t .
