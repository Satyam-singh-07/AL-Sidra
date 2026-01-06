<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the request URI starts with '/api/' (covers /api, /api/v1, /api/v2, etc.)
        if ($request->is('api/*')) {
            // Set Accept header to application/json if it's an API request
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        // Ensure JSON response for API routes, especially for errors
        // The 'api/*' pattern here will correctly match /api, /api/v1, /api/v2, etc.
        if ($request->is('api/*') && !($response instanceof JsonResponse) && ($response->isClientError() || $response->isServerError())) {
            $statusCode = $response->getStatusCode();
            $errorMessage = 'An unexpected error occurred.';
            $errorCode = 'UNKNOWN_ERROR';
            $details = ['reason' => 'An internal server error.'];

            // You can customize error messages/codes based on status code or other conditions
            if ($statusCode === 404) {
                $errorMessage = 'The requested resource was not found.';
                $errorCode = 'NOT_FOUND';
                $details = ['reason' => 'The URI does not match any known route.'];
            } elseif ($statusCode === 401) {
                $errorMessage = 'Unauthorized.';
                $errorCode = 'UNAUTHORIZED';
                $details = ['reason' => 'Authentication is required or has failed.'];
            } elseif ($statusCode === 403) {
                $errorMessage = 'Forbidden.';
                $errorCode = 'FORBIDDEN';
                $details = ['reason' => 'You do not have permission to access this resource.'];
            } elseif ($statusCode === 405) {
                $errorMessage = 'Method Not Allowed.';
                $errorCode = 'METHOD_NOT_ALLOWED';
                $details = ['reason' => 'The HTTP method used is not supported for this route.'];
            } elseif ($statusCode >= 500) {
                $errorMessage = 'Internal Server Error.';
                $errorCode = 'SERVER_ERROR';
                $details = ['reason' => 'Something went wrong on our end.'];
            }

            return response()->json([
                'error_code' => $errorCode,
                'message' => $errorMessage,
                'details' => $details
            ], $statusCode);
        }

        return $response;
    }
}