# Laravel To-Do List API

A RESTful API for managing to-do lists built with Laravel and Docker.

## Requirements

- Docker
- Docker Compose

## Setup Instructions

1. Clone this repository
2. Navigate to the project directory
3. Run the setup script:

```bash
./setup.sh
```

This will:
- Create a new Laravel project
- Configure the database connection
- Set proper permissions
- Start all Docker containers

## Accessing the Application

- Laravel Application: http://localhost:8000
- phpMyAdmin: http://localhost:8080
  - Server: db
  - Username: root
  - Password: root

## Docker Services

- **app**: PHP application container
- **nginx**: Web server
- **db**: MySQL database
- **phpmyadmin**: Database management tool

## Manual Setup (if not using setup.sh)

```bash
# Start the containers
docker-compose up -d

# Install Laravel dependencies
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate
```

## Development

To run artisan commands:

```bash
docker-compose exec app php artisan <command>
```

To access the database:

```bash
docker-compose exec db mysql -u laravel -p
```

## Stopping the Application

```bash
docker-compose down
```
