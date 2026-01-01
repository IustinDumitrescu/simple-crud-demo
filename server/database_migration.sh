#!/bin/sh
set -e

DB_HOST=${DB_HOST:-postgres}
DB_PORT=${DB_PORT:-5432}

echo "Waiting for database at $DB_HOST:$DB_PORT..."
while ! nc -z $DB_HOST $DB_PORT; do
  sleep 1
done

echo "Database is up!"

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Start PHP-FPM
exec php-fpm
