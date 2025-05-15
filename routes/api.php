<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AccommodationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    
    // User management routes
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Public accommodation route
    Route::get('/accommodations/available', [AccommodationController::class, 'available']);

    // Booking routes for all authenticated users
    Route::apiResource('/bookings', BookingController::class);
    
    // Admin-only routes
    Route::middleware('admin')->group(function () {
        // Admin booking management
        Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);
        Route::get('/admin/dashboard/bookings/pending', [BookingController::class, 'index'])->name('admin.bookings.pending');
        Route::get('/admin/dashboard/bookings/confirmed', [BookingController::class, 'index'])->name('admin.bookings.confirmed');
        Route::get('/admin/dashboard/bookings/completed', [BookingController::class, 'index'])->name('admin.bookings.completed');
        Route::get('/admin/dashboard/bookings/cancelled', [BookingController::class, 'index'])->name('admin.bookings.cancelled');
        
        // Alternative approach using route parameters
        Route::get('/admin/dashboard/bookings/{status?}', [BookingController::class, 'index'])->name('admin.bookings.status');
        
        // Accommodation management routes
        Route::get('/accommodations', [AccommodationController::class, 'index']); // Get all accommodations
        Route::post('/accommodations', [AccommodationController::class, 'store']); // Create accommodation
        Route::get('/accommodations/{accommodation}', [AccommodationController::class, 'show']); // Get single accommodation
        Route::put('/accommodations/{accommodation}', [AccommodationController::class, 'update']); // Update accommodation
        Route::delete('/accommodations/{accommodation}', [AccommodationController::class, 'destroy']); // Delete accommodation
        Route::patch('/accommodations/{accommodation}/toggle-active', [AccommodationController::class, 'toggleActive']); // Toggle active status
    });
});

// API Endpoints Documentation:
// Public Routes:
// POST /api/register - Register a new user
// POST /api/login - Login user

// Protected Routes (Requires Authentication):
// POST /api/logout - Logout user
// GET /api/users - List all users
// GET /api/users/{user} - Show a specific user
// PUT /api/users/{user} - Update a user
// DELETE /api/users/{user} - Delete a user
// GET /api/accommodations/available - Get available accommodations

// Booking Routes (All Protected):
// GET /api/bookings - List user's bookings (admin sees all)
// POST /api/bookings - Create a new booking
// GET /api/bookings/{booking} - Show a specific booking
// PUT /api/bookings/{booking} - Update a booking
// DELETE /api/bookings/{booking} - Delete a booking

// Admin-Only Routes:
// Booking Management:
// PATCH /api/bookings/{booking}/status - Update booking status
// GET /api/admin/dashboard/bookings/pending - View pending bookings
// GET /api/admin/dashboard/bookings/confirmed - View confirmed bookings
// GET /api/admin/dashboard/bookings/completed - View completed bookings
// GET /api/admin/dashboard/bookings/cancelled - View cancelled bookings
// GET /api/admin/dashboard/bookings/{status?} - View bookings by status (optional parameter)

// Accommodation Management:
// GET /api/accommodations - List all accommodations
// POST /api/accommodations - Create a new accommodation
// GET /api/accommodations/{accommodation} - Show a specific accommodation
// PUT /api/accommodations/{accommodation} - Update an accommodation
// DELETE /api/accommodations/{accommodation} - Delete an accommodation
// PATCH /api/accommodations/{accommodation}/toggle-active - Toggle accommodation active status
