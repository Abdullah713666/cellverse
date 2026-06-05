FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app

COPY . /app
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

# ENTRYPOINT handles expansion of $PORT, then exec's whatever args Railway passes via startCommand
ENTRYPOINT ["/usr/local/bin/start.sh"]
CMD ["php", "-S", "0.0.0.0:${PORT:-8080}", "-t", "."]
