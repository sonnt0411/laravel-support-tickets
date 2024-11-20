#!/bin/bash

    cp .env.example .env


# Install composer dependencies
composer install

# Generate application key
php artisan key:generate

# Run database migrations and seed data
php artisan migrate --seed
