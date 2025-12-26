FROM php:8.3-fpm-alpine

# Устанавливаем системные зависимости
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libwebp-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    postgresql-client \
    supervisor \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install pdo pdo_pgsql pgsql gd mbstring exif pcntl bcmath zip intl xml soap \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del -f .build-deps

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем Node.js и npm (для Laravel Mix/Vite)
RUN apk add --no-cache nodejs npm

# Устанавливаем дополнительные инструменты
RUN apk add --no-cache bash vim

# Устанавливаем рабочую директорию
WORKDIR /var/www/html

# Настройка пользователя
ARG USER_ID=1000
ARG GROUP_ID=1000

RUN addgroup -g ${GROUP_ID} laravel && \
    adduser -u ${USER_ID} -G laravel -g laravel -s /bin/sh -D laravel

# Создаем необходимые директории
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache && \
    chown -R laravel:laravel /var/www/html && \
    chmod -R 755 /var/www/html/storage && \
    chmod -R 755 /var/www/html/bootstrap/cache

# Копируем конфигурации
COPY php.ini /usr/local/etc/php/conf.d/custom.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Меняем владельца рабочей директории
RUN chown -R laravel:laravel /var/www/html
RUN mkdir -p /var/log/supervisor /var/ru
USER root
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]