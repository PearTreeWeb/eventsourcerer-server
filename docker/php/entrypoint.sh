#!/bin/sh
set -e

# Build DATABASE_URL from individual ECS-injected components (no-op locally where DATABASE_URL is already set)
if [ -z "${DATABASE_URL:-}" ] && [ -n "${DB_HOST:-}" ]; then
    export DATABASE_URL="postgresql://${DB_USER}:${DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_NAME}?sslmode=require"
fi

# Ensure certs directory exists
mkdir -p /app/certs
chown -R www-data:www-data /app/certs
chmod -R 775 /app/certs

# Generate certificates if they don't exist
if [ ! -f /app/certs/eventsourcerer.docker.localhost.pem ]; then
    echo "Generating certificates..."
    # Ensure certs directory is writable before generating
    mkdir -p /app/certs
    chown -R www-data:www-data /app/certs
    chmod -R 775 /app/certs
    php bin/console app:setup:create_certificates --no-interaction || echo "Certificate generation failed, continuing..."
fi

# Run migrations
echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || echo "Migrations failed, continuing..."

# Execute the main command
exec "$@"
