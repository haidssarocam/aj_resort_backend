# AJ Resort Backend API

## Overview

AJ Resort Backend is a RESTful API built with Laravel, designed to manage accommodations, bookings, and user authentication for a resort management system. This API supports both customer and administrator functionalities, with role-based access control.

## Features

- **Authentication System**
  - User registration and login
  - Sanctum token-based authentication
  - Role-based permissions (admin/customer)

- **Accommodation Management**
  - Create, read, update, delete accommodations
  - Image upload and management
  - Filter accommodations by type, capacity, and duration
  - Toggle accommodation availability

- **Booking System**
  - Book accommodations with quantity and payment details
  - View, update, and cancel bookings
  - Admin approval workflow
  - Status tracking (pending, confirmed, completed, cancelled)

## Technology Stack

- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **File Storage**: Laravel's filesystem with public disk

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd aj_resort_backend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your database in the .env file**
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=aj_resort
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Create storage link for public file access**
   ```bash
   php artisan storage:link
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## Running on Local Network

To make the API accessible from other devices on your network (like a mobile phone or another computer):

1. **Find your computer's WiFi IP address**
   - On Windows: Open Command Prompt and type `ipconfig`
   - On macOS: Open System Preferences > Network
   - On Linux: Open Terminal and type `ip addr show` or `ifconfig`

2. **Start the server using your IP address**
   ```bash
   php artisan serve --host=192.168.x.x --port=8000
   ```
   Replace `192.168.x.x` with your actual WiFi IP address.

3. **Access the API from other devices**
   Your API will be accessible at `http://192.168.x.x:8000/api/...`

4. **Note**: You may need to:
   - Allow the connection through your firewall
   - Add your IP address to trusted hosts in `.env` if using Laravel's CORS protection:
     ```
     APP_URL=http://192.168.x.x:8000
     SANCTUM_STATEFUL_DOMAINS=192.168.x.x:8000
     ```

## API Documentation

### Authentication

- **POST /api/register** - Register a new user
- **POST /api/login** - Login user
- **POST /api/logout** - Logout user (requires authentication)

### User Management

- **GET /api/users** - List all users (requires authentication)
- **GET /api/users/{user}** - Show a specific user
- **PUT /api/users/{user}** - Update a user
- **DELETE /api/users/{user}** - Delete a user

### Accommodation Endpoints

#### Public

- **GET /api/accommodations/available** - Get available accommodations (requires authentication)

#### Admin Only

- **GET /api/accommodations** - List all accommodations
- **POST /api/accommodations** - Create a new accommodation
- **GET /api/accommodations/{accommodation}** - Show a specific accommodation
- **PUT /api/accommodations/{accommodation}** - Update an accommodation
- **DELETE /api/accommodations/{accommodation}** - Delete an accommodation
- **PATCH /api/accommodations/{accommodation}/toggle-active** - Toggle accommodation active status

### Booking Endpoints

#### All Authenticated Users

- **GET /api/bookings** - List user's bookings (admin sees all)
- **POST /api/bookings** - Create a new booking
- **GET /api/bookings/{booking}** - Show a specific booking
- **PUT /api/bookings/{booking}** - Update a booking
- **DELETE /api/bookings/{booking}** - Delete a booking

#### Admin Only

- **PATCH /api/bookings/{booking}/status** - Update booking status
- **GET /api/admin/dashboard/bookings/pending** - View pending bookings
- **GET /api/admin/dashboard/bookings/confirmed** - View confirmed bookings
- **GET /api/admin/dashboard/bookings/completed** - View completed bookings
- **GET /api/admin/dashboard/bookings/cancelled** - View cancelled bookings
- **GET /api/admin/dashboard/bookings/{status?}** - View bookings by status (optional parameter)

## Authentication Flow

1. Register a user or login to get a token
2. Include the token in subsequent requests:
   ```
   Authorization: Bearer {your_token}
   ```

## File Storage

Images are stored in the `storage/app/public/accommodations` directory and are accessible via:
```
/storage/accommodations/{filename}
```

## Error Handling

The API returns standardized error responses with appropriate HTTP status codes:

- **400 Bad Request** - Invalid input data
- **401 Unauthorized** - Authentication required
- **403 Forbidden** - Insufficient permissions
- **404 Not Found** - Resource not found
- **422 Unprocessable Entity** - Validation errors
- **500 Internal Server Error** - Server-side errors

## Development

### Running Tests

```bash
php artisan test
```

### Code Style

This project follows the Laravel coding standards. Run Laravel Pint to enforce these standards:

```bash
./vendor/bin/pint
```

## License

This project is licensed under the MIT License.
