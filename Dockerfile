FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    linux-headers \
    $PHPIZE_DEPS \
    && docker-php-ext-install -j$(nproc) \
        intl \
        opcache \
        pdo_pgsql \
        pcntl \
        sockets \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN chmod +x scripts/patch-rabbitmq-consumer.sh docker/entrypoint.sh docker/wait-for-services.sh docker/declare-queues.sh

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["php-fpm"]
