## Laravel Translation Service

This is an API-driven translation service built with Laravel 12, designed to manage translations for multiple locales with tagging and export capabilities. The service supports creating, updating, viewing, and searching translations.

## Features

Store translations for multiple locales (e.g., en, fr, es) with support for adding new languages.
Tag translations for context (e.g., mobile, desktop, web).
API endpoints to create, update, view, and search translations by tags, keys, or content.
JSON export endpoint to provide translations for frontend applications.
Built with Laravel 12, using Eloquent ORM and API resources for structured responses.

## Requirements

- PHP >= 8.2
- Composer
- Laravel 12
- Laravel Passport
- L5-Swagger
- MySQL database
- Postman or cURL for testing API endpoints


## Setup and Commands to run 

1. clone the project from github
2. copy .env.example to create .env
3. composer install
4. php artisan migrate
5. php artisan db:seed
6. php artisan passport:install
7. php artisan serve

