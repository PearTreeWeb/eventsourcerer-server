#!/bin/sh
set -e

urlencode() {
    LC_ALL=C
    s="$1"
    while [ -n "$s" ]; do
        rest="${s#?}"
        c="${s%"$rest"}"
        case "$c" in
            [a-zA-Z0-9.~_-]) printf '%s' "$c" ;;
            *) printf '%%%02X' "'$c" ;;
        esac
        s="$rest"
    done
}

# Build DATABASE_URL from individual ECS-injected components (no-op locally where DATABASE_URL is already set)
if [ -z "${DATABASE_URL:-}" ] && [ -n "${DB_HOST:-}" ]; then
    ENC_USER=$(urlencode "${DB_USER}")
    ENC_PASS=$(urlencode "${DB_PASSWORD}")
    PORT="${DB_PORT:-5432}"
    SERVER_VER="${DB_SERVER_VERSION:-16}"
    export DATABASE_URL="postgresql://${ENC_USER}:${ENC_PASS}@${DB_HOST}:${PORT}/${DB_NAME}?serverVersion=${SERVER_VER}&charset=utf8&sslmode=require"
    php bin/console doctrine:database:create --if-not-exists --no-interaction || echo "Database already exists or cannot be created, continuing..."
fi

# Ensure certs directory exists
mkdir -p /app/certs
chown -R www-data:www-data /app/certs 2>/dev/null || true
chmod -R 775 /app/certs 2>/dev/null || true

# Generate certificates if they don't exist
if [ ! -f /app/certs/eventsourcerer.docker.localhost.pem ]; then
    echo "Generating certificates..."
    # Ensure certs directory is writable before generating
    mkdir -p /app/certs
    chown -R www-data:www-data /app/certs 2>/dev/null || true
    chmod -R 775 /app/certs 2>/dev/null || true
    php bin/console app:setup:create_certificates --no-interaction || echo "Certificate generation failed, continuing..."
fi

# Run migrations
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || echo "Migrations failed, continuing..."

# Execute the main command
exec "$@"
