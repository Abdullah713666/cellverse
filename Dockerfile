FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql

# Railway sets PORT; default to 8080 for local
ENV PORT=8080

WORKDIR /app

COPY . /app

EXPOSE 8080

CMD ["sh", "-c", "php -S 0.0.0.0:$PORT -t ."]
