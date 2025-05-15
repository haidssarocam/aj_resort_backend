<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Accommodation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Display a listing of the bookings.
     * 
     * For admin users: all bookings, with optional status filter
     * For regular users: only their own bookings
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Add debugging information
            Log::info('BookingController index method called', [
                'is_admin' => auth()->user()->isAdmin(),
                'user_id' => auth()->id(),
                'route_name' => $request->route()->getName(),
                'route_status_param' => $request->route('status'),
                'request_status' => $request->status,
                'request_url' => $request->url(),
                'request_path' => $request->path()
            ]);
            
            $query = Booking::with(['user', 'accommodation']);
            
            // If requesting from admin dashboard, filter by status
            if (auth()->user()->isAdmin()) {
                // First check for status parameter from the route
                $statusFromParameter = $request->route('status');
                
                // Then check for named routes
                $routeName = $request->route()->getName();
                $status = null;
                
                if ($statusFromParameter) {
                    $status = $statusFromParameter;
                } elseif ($routeName) {
                    if ($routeName === 'admin.bookings.pending') {
                        $status = 'pending';
                    } elseif ($routeName === 'admin.bookings.confirmed') {
                        $status = 'confirmed';
                    } elseif ($routeName === 'admin.bookings.completed') {
                        $status = 'completed';
                    } elseif ($routeName === 'admin.bookings.cancelled') {
                        $status = 'cancelled';
                    }
                } elseif ($request->has('status')) {
                    $status = $request->status;
                }
                
                if ($status) {
                    $query->where('status', $status);
                }
            } 
            // If regular user, only show their bookings
            else {
                $query->where('user_id', auth()->id());
                
                // Allow additional filtering by status for regular users too
                if ($request->has('status')) {
                    $query->where('status', $request->status);
                }
            }
            
            $bookings = $query->latest()->get();
            
            return response()->json(['data' => $bookings], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error('Error in BookingController index method', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to fetch bookings',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(BookingRequest $request): JsonResponse
    {
        try {
            // Find the accommodation
            $accommodation = Accommodation::findOrFail($request->accommodation_id);
            
            // Check if accommodation is available
            if (!$accommodation->is_active || $accommodation->available_units <= 0) {
                return response()->json([
                    'message' => 'This accommodation is currently unavailable'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Create the booking
            $booking = Booking::create([
                'user_id' => auth()->id(),
                'accommodation_id' => $accommodation->id,
                'cottage_type' => $accommodation->name, // Store the name at booking time
                'duration' => $accommodation->duration_hours,
                'quantity' => $request->quantity,
                'total_price' => $accommodation->price * $request->quantity,
                'payment_method' => $request->payment_method,
                'status' => 'pending' // All bookings start as pending
            ]);

            // Temporarily decrease available units
            $accommodation->decrement('available_units', $request->quantity);

            return response()->json([
                'message' => 'Booking submitted successfully. Waiting for admin approval.',
                'data' => $booking->load('accommodation')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create booking',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking): JsonResponse
    {
        try {
            // Check if user has permission to view this booking
            if (!auth()->user()->isAdmin() && auth()->id() !== $booking->user_id) {
                return response()->json([
                    'message' => 'You are not authorized to view this booking'
                ], Response::HTTP_FORBIDDEN);
            }

            return response()->json(['data' => $booking->load(['user', 'accommodation'])], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch booking',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified booking in storage.
     */
    public function update(BookingRequest $request, Booking $booking): JsonResponse
    {
        try {
            // Check if user has permission to update this booking
            if (!auth()->user()->isAdmin() && auth()->id() !== $booking->user_id) {
                return response()->json([
                    'message' => 'You are not authorized to update this booking'
                ], Response::HTTP_FORBIDDEN);
            }

            // Customers can only update pending bookings
            if (!auth()->user()->isAdmin() && $booking->status !== 'pending') {
                return response()->json([
                    'message' => 'Only pending bookings can be updated'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $booking->update($request->validated());
            
            return response()->json([
                'message' => 'Booking updated successfully',
                'data' => $booking
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update booking',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy(Booking $booking): JsonResponse
    {
        try {
            // Check if user has permission to delete this booking
            if (!auth()->user()->isAdmin() && auth()->id() !== $booking->user_id) {
                return response()->json([
                    'message' => 'You are not authorized to delete this booking'
                ], Response::HTTP_FORBIDDEN);
            }

            // Customers can only cancel pending bookings
            if (!auth()->user()->isAdmin() && $booking->status !== 'pending') {
                return response()->json([
                    'message' => 'Only pending bookings can be cancelled'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // If the booking is being deleted/cancelled, release the accommodation units
            if ($booking->status === 'pending' || $booking->status === 'confirmed') {
                $booking->accommodation->increment('available_units', $booking->quantity);
            }

            $booking->delete();
            
            return response()->json([
                'message' => 'Booking deleted successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete booking',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update booking status.
     * Only admins can call this method.
     */
    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        try {
            // Check if user is admin
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'message' => 'Only administrators can approve or reject bookings'
                ], Response::HTTP_FORBIDDEN);
            }

            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed,completed,cancelled',
            ]);

            $oldStatus = $booking->status;
            $newStatus = $validated['status'];

            // Handle accommodation availability based on status change
            if ($oldStatus !== $newStatus) {
                // If cancelling a pending or confirmed booking, release the units
                if (($oldStatus === 'pending' || $oldStatus === 'confirmed') && $newStatus === 'cancelled') {
                    $booking->accommodation->increment('available_units', $booking->quantity);
                }
                
                // If re-confirming a cancelled booking, decrease available units again
                if ($oldStatus === 'cancelled' && ($newStatus === 'pending' || $newStatus === 'confirmed')) {
                    $booking->accommodation->decrement('available_units', $booking->quantity);
                }
            }

            $booking->update(['status' => $newStatus]);
            
            return response()->json([
                'message' => 'Booking status updated successfully',
                'data' => $booking
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update booking status',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 