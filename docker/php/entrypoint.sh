#!/bin/sh
set -e

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
