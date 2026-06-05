FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY . /app
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

# JSON exec form for proper signal handling + reliable $PORT expansion
CMD ["/usr/local/bin/start.sh"]
