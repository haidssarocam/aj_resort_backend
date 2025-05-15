<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccommodationRequest;
use App\Models\Accommodation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class AccommodationController extends Controller
{
    /**
     * Display a listing of the accommodations.
     */
    public function index(): JsonResponse
    {
        try {
            $accommodations = Accommodation::all()->map(function ($accommodation) {
                if ($accommodation->image_path) {
                    $accommodation->image_url = Storage::url($accommodation->image_path);
                }
                return $accommodation;
            });
            return response()->json([
                'data' => $accommodations
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch accommodations',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created accommodation in storage.
     */
    public function store(AccommodationRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            // Handle image upload if present
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('accommodations', 'public');
                $validated['image_path'] = $path;
            }
            
            $accommodation = Accommodation::create($validated);
            
            if ($accommodation->image_path) {
                $accommodation->image_url = Storage::url($accommodation->image_path);
            }
            
            return response()->json([
                'message' => 'Accommodation created successfully',
                'data' => $accommodation
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create accommodation',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified accommodation.
     */
    public function show(Accommodation $accommodation): JsonResponse
    {
        try {
            if ($accommodation->image_path) {
                $accommodation->image_url = Storage::url($accommodation->image_path);
            }
            return response()->json(['data' => $accommodation], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch accommodation',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified accommodation in storage.
     */
    public function update(AccommodationRequest $request, Accommodation $accommodation): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            // Handle image upload if present
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($accommodation->image_path) {
                    Storage::disk('public')->delete($accommodation->image_path);
                }
                
                $path = $request->file('image')->store('accommodations', 'public');
                $validated['image_path'] = $path;
            }
            
            $accommodation->update($validated);
            
            if ($accommodation->image_path) {
                $accommodation->image_url = Storage::url($accommodation->image_path);
            }
            
            return response()->json([
                'message' => 'Accommodation updated successfully',
                'data' => $accommodation
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update accommodation',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified accommodation from storage.
     */
    public function destroy(Accommodation $accommodation): JsonResponse
    {
        try {
            // Check if accommodation has bookings
            if ($accommodation->bookings()->exists()) {
                return response()->json([
                    'message' => 'Cannot delete accommodation with existing bookings'
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            // Delete image if exists
            if ($accommodation->image_path) {
                Storage::disk('public')->delete($accommodation->image_path);
            }
            
            $accommodation->delete();
            
            return response()->json([
                'message' => 'Accommodation deleted successfully'
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete accommodation',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get available accommodations with filtering.
     */
    public function available(Request $request): JsonResponse
    {
        try {
            $query = Accommodation::where('is_active', true)
                ->where('available_units', '>', 0);
                
            // Filter by type if specified
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }
            
            // Filter by duration if specified
            if ($request->has('duration')) {
                $query->where('duration_hours', $request->duration);
            }
            
            // Filter by capacity if specified
            if ($request->has('persons')) {
                $persons = (int) $request->persons;
                $query->where('capacity_min', '<=', $persons)
                      ->where('capacity_max', '>=', $persons);
            }
            
            $accommodations = $query->get()->map(function ($accommodation) {
                if ($accommodation->image_path) {
                    $accommodation->image_url = Storage::url($accommodation->image_path);
                }
                return $accommodation;
            });
            
            return response()->json(['data' => $accommodations], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch available accommodations',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Toggle the active status of an accommodation.
     */
    public function toggleActive(Accommodation $accommodation): JsonResponse
    {
        try {
            $accommodation->update([
                'is_active' => !$accommodation->is_active
            ]);
            
            return response()->json([
                'message' => 'Accommodation status updated successfully',
                'data' => $accommodation
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to toggle accommodation status',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 