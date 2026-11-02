FROM php:8.4-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    nodejs \
    npm \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files and install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy package files and install Node dependencies, then build assets
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

COPY . .

# Sentry: Railway inyecta las variables de servicio como build args (docs.railway.com/builds/dockerfiles)
# Sin estos ARG, Vite no ve las variables y el bundle sale sin DSN ni source maps.
ARG VITE_SENTRY_DSN
ARG VITE_SENTRY_TRACES_SAMPLE_RATE
ARG SENTRY_AUTH_TOKEN

# Build frontend assets
RUN npm run build

# Run composer post-install scripts (package:discover, etc.)
RUN composer run-script post-autoload-dump || true

# Set correct permissions
RUN chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Make startup scripts executable
RUN chmod +x /app/start.sh /app/start-scheduler.sh /app/start-worker.sh

# Default script to run (can be overridden via startCommand in railway.toml)
ARG START_SCRIPT=/app/start.sh

EXPOSE 8000

CMD ["/app/start.sh"]
