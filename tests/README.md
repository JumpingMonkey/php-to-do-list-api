# Laravel Todo List API Testing Documentation

This document provides an overview of the testing approach for the Laravel Todo List API.

## Testing Structure

The test suite is organized into the following categories:

### Feature Tests

These tests verify the functionality of the API endpoints by making HTTP requests and asserting the expected responses.

#### Authentication Tests

- **RegisterTest**: Tests user registration functionality
  - Successful registration with valid data
  - Validation errors for invalid data
  - Prevention of duplicate email registrations

- **LoginTest**: Tests user login functionality
  - Successful login with valid credentials
  - Failed login with invalid credentials
  - Validation errors for login requests

- **LogoutTest**: Tests user logout functionality
  - Successful logout for authenticated users
  - Prevention of logout for unauthenticated users

- **ProfileTest**: Tests user profile retrieval
  - Successful profile retrieval for authenticated users
  - Prevention of profile access for unauthenticated users

- **ForgotPasswordTest**: Tests password reset request functionality
  - Successful password reset request with valid email
  - Handling of non-existent email addresses
  - Validation errors for password reset requests

- **ResetPasswordTest**: Tests password reset functionality
  - Successful password reset with valid token
  - Failed password reset with invalid token
  - Validation errors for password reset

#### Todo Tests

- **IndexTest**: Tests retrieval of todo lists
  - Successful retrieval of todos for authenticated users
  - Empty array response when user has no todos
  - Prevention of access for unauthenticated users

- **StoreTest**: Tests todo creation
  - Successful creation with valid data
  - Creation with minimal data (only required fields)
  - Validation errors for invalid data
  - Prevention of creation for unauthenticated users

- **ShowTest**: Tests retrieval of specific todos
  - Successful retrieval of a specific todo
  - Prevention of access to another user's todos
  - 404 response for non-existent todos
  - Prevention of access for unauthenticated users

- **UpdateTest**: Tests todo updates
  - Successful update with valid data
  - Partial update with only some fields
  - Prevention of updating another user's todos
  - Validation errors for invalid data
  - 404 response for non-existent todos
  - Prevention of updates for unauthenticated users

- **DestroyTest**: Tests todo deletion
  - Successful deletion of a todo
  - Prevention of deleting another user's todos
  - 404 response for non-existent todos
  - Prevention of deletion for unauthenticated users

- **PaginationAndFilteringTest**: Tests pagination and filtering functionality
  - Pagination of todo lists with customizable page size
  - Filtering by completion status
  - Searching by title and description
  - Filtering by due date range
  - Sorting by different fields and directions
  - Validation of sort parameters
  - Combining multiple filters

### Unit Tests

- **ApiResourceTest**: Tests the API resource classes
  - UserResource serialization
  - TodoResource serialization
  - TodoCollection serialization
  - Pagination metadata in responses

## Test Helpers

The test suite includes several helper methods in the `TestCase` class:

- `authenticateUser()`: Creates and authenticates a user for testing protected routes
- `createUser()`: Creates a user without authentication

## Running Tests

To run the tests, use the following command:

```bash
docker-compose exec app php artisan test
```

## Test Database

Tests use the `RefreshDatabase` trait to ensure a clean database state for each test.

## Mocking

Several tests use Laravel's mocking capabilities:

- `Notification::fake()` to mock notification sending
- `Event::fake()` to mock event dispatching

This allows testing without actually sending emails or triggering real events.
