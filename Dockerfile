FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY . /app

EXPOSE 8080

# Use shell form so $PORT is expanded; Railway sets PORT automatically
CMD php -S 0.0.0.0:${PORT:-8080} -t .
