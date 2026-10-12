FROM --platform=$BUILDPLATFORM node:22-alpine AS frontend
WORKDIR /frontend-build
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY frontend .
RUN npm run build

FROM php:8.2-fpm AS base
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        gettext-base \
        procps \
        curl \
        git \
        unzip \
    && install-php-extensions \
        bcmath \
        bz2 \
        gd \
        intl \
        memcached \
        mysqli \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && rm -f /etc/nginx/sites-enabled/default \
    && rm -rf /var/lib/apt/lists/*

# Installing composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# forward request and error logs to docker log collector
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
 && ln -sf /dev/stderr /var/log/nginx/error.log

# Setting workdir for PHP
WORKDIR /var/www/oms
COPY docker/nginx /etc/nginx/conf.d
COPY docker/supervisor /etc/supervisor/conf.d

EXPOSE 8000
# Uncomment if you want to expose backend API port
# EXPOSE 8100

CMD ["/var/www/oms/docker/startup.sh"]

FROM base AS development
RUN install-php-extensions xdebug
COPY docker/php/custom.ini $PHP_INI_DIR/conf.d/custom.ini
COPY --from=frontend /frontend-build/public /var/www/oms/frontend/public

FROM base AS production
LABEL org.opencontainers.image.title="OhMyShares" \
      org.opencontainers.image.description="Personal portfolio management" \
      org.opencontainers.image.licenses="AGPL-3.0-or-later"
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
ENV LOG_STACK=stderr

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader

COPY . .
COPY --from=frontend /frontend-build/public /var/www/oms/frontend/public

RUN composer dump-autoload --no-dev --optimize --no-interaction \
    && php artisan l5-swagger:generate \
    && chown -R www-data:www-data storage bootstrap/cache

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
    CMD curl -fsS http://localhost:8000/ > /dev/null || exit 1
