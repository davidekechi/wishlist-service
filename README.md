# Wishlist Service API

A Laravel-based RESTful API service for managing user wishlists in an e-commerce environment. This service provides authentication, product management, and wishlist functionality.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Local Development Setup](#local-development-setup)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Project Structure](#project-structure)
- [Technologies Used](#technologies-used)

## Features

- **User Authentication**: Token-based authentication using Laravel Sanctum
  - User registration
  - User login with remember me option
  - User logout

- **Product Management**: 
  - Retrieve available products with pagination
  - Products are publicly accessible

- **Wishlist Management**:
  - Add products to user's wishlist
  - Retrieve user's wishlist with pagination
  - Remove products from wishlist
  - All wishlist operations require authentication

## Requirements

- Docker and Docker Compose
- Git

## Local Development Setup

### 1. Clone the Repository

```bash
git clone https://github.com/davidekechi/wishlist-service
cd wishlist-service
```

### 2. Environment Configuration

The project includes `.env.example` and `.env.testing` files. Create an `.env` file:

```bash
cp .env.example .env
```

Update the database configuration in `.env` if needed (defaults should work with Docker Compose):

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=wishlist_service
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

### 3. Build Docker Containers

Build the Docker containers using Docker Compose:

```bash
docker-compose build
```

This will build the following services:
- **app**: PHP 8.2-FPM application container
- **nginx**: Web server
- **postgres**: PostgreSQL 15 database

### 4. Start Docker Containers

Start all services:

```bash
docker-compose up -d
```

This will start:
- Application server on `http://localhost:8000`
- PostgreSQL on port `5432`

### 5. Access the Application Container

Bash into the application container:

```bash
docker exec -it wishlist-service-app bash
```

### 6. Install Dependencies

Inside the container, install Composer dependencies:

```bash
composer install
```

If you encounter an issue with the vendor directory or anything like an autoload problem, delete the vendor directory from outside the container, bash into the container and install composer again:

```bash
exit

rm -rf vendor

docker exec -it wishlist-service-app bash

composer install
```

### 7. Generate Application Key

Generate the application encryption key:

```bash
php artisan key:generate
```

### 8. Run Database Migrations

Run the migrations to set up the database schema:

```bash
php artisan migrate
```

### 9. Seed the Database

Run the seeders to populate the database with initial data:

```bash
php artisan db:seed
```

This will seed:
- Sample users
- Sample products

### 10. Verify Installation

Check the health endpoint:

```bash
http://localhost:8000/api/v1/health
```

You should receive a JSON response indicating the API is healthy.

## API Documentation

Complete API documentation is available in the Postman collection file:

**`Wishlist Service API.postman_collection.json`**

### Importing the Postman Collection

1. Open Postman
2. Click **Import** button
3. Select the `Wishlist Service API.postman_collection.json` file
4. The collection will be imported with all endpoints, examples, and test scripts

### API Endpoints Overview

#### Health Check
- `GET /api/v1/health` - Check API health status

#### Authentication
- `POST /api/v1/auth/register` - Register a new user
- `POST /api/v1/auth/login` - Login user
- `POST /api/v1/auth/logout` - Logout user (requires authentication)

#### Products
- `GET /api/v1/products` - Get all products (public, paginated)

#### Wishlist (All require authentication)
- `POST /api/v1/wishlist` - Add product to wishlist
- `GET /api/v1/wishlist` - Get user's wishlist (paginated)
- `DELETE /api/v1/wishlist/{productPublicId}` - Remove product from wishlist

### Authentication

The API uses Bearer token authentication via Laravel Sanctum. After registering or logging in, you'll receive a token that should be included in the `Authorization` header:

```
Authorization: Bearer {your-token-here}
```

### Response Format

All API responses follow a consistent format:

```json
{
  "statusCode": 200,
  "success": true,
  "message": "Success message",
  "data": { ... }
}
```

Error responses:

```json
{
  "statusCode": 422,
  "success": false,
  "message": "Validation failed",
  "errors": { ... }
}
```

## Testing

### Prerequisites

The project uses Pest (built on PHPUnit) for testing. The test database configuration is in `.env.testing`.

### Setup Test Database

1. Create the test database inside the PostgreSQL container:

```bash
docker exec -it wishlist-service-postgres psql -U postgres -c "CREATE DATABASE wishlist_service_test;"
```

2. Run migrations and seed test data from inside of the application container:

```bash
docker exec -it wishlist-service-app bash
```

```bash
php artisan migrate:fresh --env=testing
php artisan db:seed --env=testing
```

### Running Tests

Run all tests:

```bash
php artisan test
```

Or use Pest directly:

```bash
./vendor/bin/pest
```

Run specific test files:

```bash
php artisan test tests/Feature/Wishlist/WishlistTest.php
```

### Test Structure

- **Feature Tests**: Located in `tests/Feature/`
  - Authentication tests
  - Product endpoint tests
  - Wishlist endpoint tests

- **Unit Tests**: Located in `tests/Unit/`
  - Repository tests
  - Service tests
  - Request validation tests
  - Resource transformation tests

### Test Database

The test database (`wishlist_service_test`) is automatically used when running tests via the `.env.testing` configuration. The `RefreshDatabase` trait ensures a clean database state for each test.

## Project Structure

```
wishlist-service/
├── app/
│   ├── Contracts/          # Repository interfaces
│   ├── DTOs/               # Data Transfer Objects
│   ├── Http/
│   │   ├── Controllers/    # API controllers
│   │   ├── Requests/       # Form request validation
│   │   └── Resources/      # API resource transformers
│   ├── Models/             # Eloquent models
│   ├── Repositories/       # Data access layer
│   ├── Services/           # Business logic layer
│   └── Traits/             # Reusable traits
├── database/
│   ├── factories/          # Model factories
│   ├── migrations/         # Database migrations
│   └── seeders/           # Database seeders
├── routes/
│   └── api.php            # API routes
├── tests/
│   ├── Feature/           # Feature tests
│   └── Unit/              # Unit tests
├── docker-compose.yml     # Docker services configuration
├── Dockerfile            # Application container definition
└── nginx.conf            # Nginx configuration
```

## Technologies Used

- **Laravel 12**: PHP framework
- **Laravel Sanctum**: API authentication
- **PostgreSQL 15**: Database
- **Pest**: Testing framework
- **PHPStan**: Static analysis
- **Docker & Docker Compose**: Containerization
- **Nginx**: Web server

## Development Commands

### Inside Docker Container

```bash
# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Run tests
php artisan test

# Static analysis
composer analyse

# Code formatting
composer fix
```

### Docker Commands

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f postgres

# Rebuild containers
docker-compose build --no-cache

# Access database
docker exec -it wishlist-service-postgres psql -U postgres -d wishlist_service
```

## Troubleshooting

### Port Already in Use

If port 8000 is already in use, modify the port mapping in `docker-compose.yml`:

```yaml
ports:
  - "8001:80"  # Change 8000 to 8001
```

### Database Connection Issues

Ensure the database container is running:

```bash
docker-compose ps
```

Check database logs:

```bash
docker-compose logs postgres
```

### Permission Issues

If you encounter permission issues, ensure the application container has proper permissions:

```bash
docker exec -it wishlist-service-app chown -R sail:sail /var/www
```

### Vendor/Autoload Issues

If you encounter an issue with the vendor directory or anything like an autoload problem, delete the vendor directory from outside the container and then run:

```bash
docker exec -it wishlist-service-app rm -rf vendor composer.lock
docker exec -it wishlist-service-app composer install --no-scripts
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
