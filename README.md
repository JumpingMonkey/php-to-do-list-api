# Laravel Todo List API

A RESTful API for a Todo List application built with Laravel 11 and Sanctum authentication.

**Project URL:** [https://github.com/JumpingMonkey/php-to-do-list-api](https://github.com/JumpingMonkey/php-to-do-list-api)

## Features

- User authentication (register, login, logout)
- Todo management (create, read, update, delete)
- Token-based authentication with Laravel Sanctum
- Password reset functionality
- Pagination and filtering for todos with OOP design
- Comprehensive test suite
- Docker containerization for easy setup and deployment

## Setup and Installation

1. Clone the repository
2. Run the setup script:
   ```bash
   ./setup.sh
   ```
3. The application will be available at:
   - Laravel App: http://localhost:8000
   - phpMyAdmin: http://localhost:8080 (Server: db, Username: root, Password: root)

## API Endpoints

### Authentication

- **Register**: `POST /api/register`
  ```json
  {
    "name": "User Name",
    "email": "user@example.com",
    "password": "password",
    "password_confirmation": "password"
  }
  ```

- **Login**: `POST /api/login`
  ```json
  {
    "email": "user@example.com",
    "password": "password"
  }
  ```

- **Logout**: `POST /api/logout` (Requires authentication)

- **User Profile**: `GET /api/user` (Requires authentication)

- **Forgot Password**: `POST /api/forgot-password`
  ```json
  {
    "email": "user@example.com"
  }
  ```

- **Reset Password**: `POST /api/reset-password`
  ```json
  {
    "token": "reset-token-from-email",
    "email": "user@example.com",
    "password": "new-password",
    "password_confirmation": "new-password"
  }
  ```

### Todo Management

- **Get All Todos**: `GET /api/todos` (Requires authentication)
  
  Supports the following query parameters:
  - `per_page`: Number of items per page (default: 10)
  - `page`: Page number
  - `completed`: Filter by completion status (true/false)
  - `search`: Search in title and description
  - `due_date_from`: Filter todos with due date from this date
  - `due_date_to`: Filter todos with due date until this date
  - `sort_by`: Field to sort by (id, title, completed, due_date, created_at, updated_at)
  - `sort_direction`: Sort direction (asc/desc)
  
  Example: `GET /api/todos?per_page=5&completed=false&search=meeting&sort_by=due_date&sort_direction=asc`

- **Create Todo**: `POST /api/todos` (Requires authentication)
  ```json
  {
    "title": "Todo Title",
    "description": "Todo Description",
    "completed": false,
    "due_date": "2025-03-10"
  }
  ```

- **Get Todo**: `GET /api/todos/{id}` (Requires authentication)

- **Update Todo**: `PUT /api/todos/{id}` (Requires authentication)
  ```json
  {
    "title": "Updated Title",
    "description": "Updated Description",
    "completed": true,
    "due_date": "2025-03-15"
  }
  ```

- **Delete Todo**: `DELETE /api/todos/{id}` (Requires authentication)

## Architecture

### OOP Filter System

The API uses an object-oriented approach for filtering and pagination:

1. **Base Filter Class**: A reusable abstract class that handles the application of filters
2. **TodoFilter Class**: Specific implementation for Todo filtering with methods for each filter type
3. **Filterable Trait**: Added to models to enable the filter functionality
4. **Controller Integration**: Clean controller code that delegates filtering to the filter classes

Key filtering features include:

- **Flexible Date Handling**: Support for multiple date formats (ISO, US, European)
- **Case-insensitive Search**: Search across title and description fields regardless of case
- **Robust Error Handling**: Gracefully handles invalid inputs without breaking the application
- **Comprehensive Sorting**: Sort by any field in ascending or descending order
- **Customizable Pagination**: Control the number of items per page with metadata

This design follows SOLID principles, making the code more maintainable and extensible. For detailed documentation on the filtering system, see [docs/filters.md](docs/filters.md).

## Testing

The API includes a comprehensive test suite covering all controllers and actions. To run the tests:

```bash
docker-compose exec app php artisan test
```

The test suite includes:
- Feature tests for all API endpoints
- Authentication testing
- Todo CRUD operation testing
- Pagination and filtering testing
- Resource serialization testing

For more details on the testing approach, see the [tests/README.md](tests/README.md) file.

## Postman Collection

A Postman collection file is included in the repository for testing the API endpoints. Import the `Todo-API.postman_collection.json` file into Postman to get started.

## Technologies Used

- Laravel 11
- PHP 8.2
- MySQL 8.0
- Docker
- Laravel Sanctum for API authentication
- PHPUnit for testing

## API Documentation

The API is documented using Swagger (OpenAPI). You can access the documentation in the following ways:

### Using the Laravel Application (Recommended)

1. Make sure your Laravel application is running:
   ```bash
   docker-compose up -d
   ```

2. Visit the API documentation at:
   ```
   http://localhost:8000/api-docs
   ```

### Viewing Raw Specification Files

You can also view the raw API specification in:
- XML format: [docs/swagger.xml](docs/swagger.xml)
- JSON format (after generation): [docs/swagger.json](docs/swagger.json)

To generate the JSON file from the XML definition:
```bash
cd docs
php generate-swagger-json.php
```

The documentation includes:
- All API endpoints with detailed descriptions
- Request and response models
- Authentication requirements
- Filtering and pagination parameters
- Example requests and responses

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
