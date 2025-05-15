<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        try {
            // Log authentication status
            Log::info('Admin middleware check', [
                'is_authenticated' => auth()->check(),
                'user' => auth()->user() ? [
                    'id' => auth()->user()->id,
                    'email' => auth()->user()->email,
                    'role' => auth()->user()->role
                ] : null,
                'token' => $request->bearerToken(),
                'path' => $request->path(),
                'method' => $request->method()
            ]);

            if (!auth()->check()) {
                Log::warning('Unauthorized access attempt - not authenticated', [
                    'ip' => $request->ip(),
                    'path' => $request->path()
                ]);
                
                return response()->json([
                    'message' => 'Unauthorized. Authentication required.',
                    'error' => 'No authenticated user found'
                ], Response::HTTP_UNAUTHORIZED);
            }

            if (!auth()->user()->isAdmin()) {
                Log::warning('Unauthorized admin access attempt', [
                    'user_id' => auth()->id(),
                    'user_role' => auth()->user()->role,
                    'path' => $request->path()
                ]);
                
                return response()->json([
                    'message' => 'Unauthorized. Admin access required.',
                    'error' => 'User is not an admin',
                    'user_role' => auth()->user()->role
                ], Response::HTTP_FORBIDDEN);
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Exception in AdminMiddleware', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'path' => $request->path()
            ]);
            
            return response()->json([
                'message' => 'An error occurred while processing your request',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 