#!/bin/bash

# Build the Docker images first
docker-compose build

# Start the containers
docker-compose up -d

# Create Laravel project in a temporary directory inside the container
docker-compose exec app bash -c "composer create-project laravel/laravel /tmp/laravel && cp -R /tmp/laravel/. /var/www/html/ && rm -rf /tmp/laravel"

# Set proper permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache

# Wait for the database to be ready
echo "Waiting for database to be ready..."
sleep 10

# Update .env file with database credentials
docker-compose exec app sed -i 's/DB_HOST=127.0.0.1/DB_HOST=db/g' /var/www/html/.env
docker-compose exec app sed -i 's/DB_DATABASE=laravel/DB_DATABASE=laravel/g' /var/www/html/.env
docker-compose exec app sed -i 's/DB_USERNAME=root/DB_USERNAME=laravel/g' /var/www/html/.env
docker-compose exec app sed -i 's/DB_PASSWORD=/DB_PASSWORD=password/g' /var/www/html/.env

# Generate application key
docker-compose exec app php artisan key:generate

# Clear config cache
docker-compose exec app php artisan config:clear

# Install API and Sanctum for API authentication (required in Laravel 11)
docker-compose exec app php artisan install:api

# Run migrations
docker-compose exec app php artisan migrate

# Optimize the application
docker-compose exec app php artisan optimize

# Create storage link
docker-compose exec app php artisan storage:link

echo "Laravel project setup complete!"
echo "Access your Laravel application at: http://localhost:8000"
echo "Access phpMyAdmin at: http://localhost:8080"
echo "Database credentials for phpMyAdmin:"
echo "Server: db"
echo "Username: root"
echo "Password: root"
