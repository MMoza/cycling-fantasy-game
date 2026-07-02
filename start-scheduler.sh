#!/bin/bash
set -e

# Generate .env from environment variables injected by Railway
cat > /app/.env <<EOF
APP_NAME="${APP_NAME:-Laravel}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY:-}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL:-http://localhost}"

APP_LOCALE="${APP_LOCALE:-en}"
APP_FALLBACK_LOCALE="${APP_FALLBACK_LOCALE:-en}"
APP_FAKER_LOCALE="${APP_FAKER_LOCALE:-en_US}"

APP_MAINTENANCE_DRIVER="${APP_MAINTENANCE_DRIVER:-file}"

BCRYPT_ROUNDS="${BCRYPT_ROUNDS:-12}"

LOG_CHANNEL="${LOG_CHANNEL:-stack}"
LOG_STACK="${LOG_STACK:-single}"
LOG_DEPRECATIONS_CHANNEL="${LOG_DEPRECATIONS_CHANNEL:-null}"
LOG_LEVEL="${LOG_LEVEL:-error}"

DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE:-laravel}"
DB_USERNAME="${DB_USERNAME:-${DB_USER:-root}}"
DB_PASSWORD="${DB_PASSWORD:-}"

SESSION_DRIVER="${SESSION_DRIVER:-database}"
SESSION_SECURE_COOKIE="${SESSION_SECURE_COOKIE:-true}"
SESSION_LIFETIME="${SESSION_LIFETIME:-120}"
SESSION_ENCRYPT="${SESSION_ENCRYPT:-false}"
SESSION_PATH="${SESSION_PATH:-/}"
SESSION_DOMAIN="${SESSION_DOMAIN:-}"

BROADCAST_CONNECTION="${BROADCAST_CONNECTION:-log}"
FILESYSTEM_DISK="${FILESYSTEM_DISK:-local}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-database}"

AWS_ACCESS_KEY_ID="${AWS_ACCESS_KEY_ID:-}"
AWS_SECRET_ACCESS_KEY="${AWS_SECRET_ACCESS_KEY:-}"
AWS_DEFAULT_REGION="${AWS_DEFAULT_REGION:-auto}"
AWS_BUCKET="${AWS_BUCKET:-}"
AWS_ENDPOINT="${AWS_ENDPOINT:-}"
AWS_USE_PATH_STYLE_ENDPOINT="${AWS_USE_PATH_STYLE_ENDPOINT:-true}"

CACHE_STORE="${CACHE_STORE:-database}"

MAIL_MAILER="${MAIL_MAILER:-log}"
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS:-hello@example.com}"
MAIL_FROM_NAME="${MAIL_FROM_NAME:-Laravel}"
EOF

# Generate app key if not set
if [ -z "${APP_KEY}" ]; then
    php artisan key:generate --force
fi

# Cache config, routes and views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run the scheduler once and exit cleanly
echo "$(date '+%Y-%m-%d %H:%M:%S') - Starting schedule:run"
START_TIME=$(date +%s)
php artisan schedule:run --verbose
END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))
echo "$(date '+%Y-%m-%d %H:%M:%S') - Finished schedule:run (took ${DURATION}s)"
