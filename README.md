# Laravel Todo List API

A RESTful API for a Todo List application built with Laravel 11 and Sanctum authentication.

## Features

- User authentication (register, login, logout)
- Todo management (create, read, update, delete)
- Token-based authentication with Laravel Sanctum
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

### Todo Management

- **Get All Todos**: `GET /api/todos` (Requires authentication)

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

## Postman Collection

A Postman collection file is included in the repository for testing the API endpoints. Import the `Todo-API.postman_collection.json` file into Postman to get started.

## Technologies Used

- Laravel 11
- PHP 8.2
- MySQL 8.0
- Docker
- Laravel Sanctum for API authentication

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
