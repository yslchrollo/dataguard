# ─────────────────────────────────────────────
# Stage 1: Node – build frontend assets
# ─────────────────────────────────────────────
FROM node:20-alpine AS node-builder

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY . .
RUN npm run build

# ─────────────────────────────────────────────
# Stage 2: PHP – production image
# ─────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS php-base

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    openssl-dev \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    xml \
    curl \
    zip \
    gd \
    bcmath \
    opcache \
    ctype \
    fileinfo \
    tokenizer

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files and install dependencies (no dev)
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy app source
COPY . .

# Copy compiled frontend assets from node stage
COPY --from=node-builder /app/public/build ./public/build

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run post-install scripts now that full source is available
RUN composer run-script post-autoload-dump

# ─────────────────────────────────────────────
# Nginx config – document root at /public
# ─────────────────────────────────────────────
RUN mkdir -p /etc/nginx/http.d
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# ─────────────────────────────────────────────
# Supervisor – keeps nginx + php-fpm running
# ─────────────────────────────────────────────
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# PHP opcache tuning for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
