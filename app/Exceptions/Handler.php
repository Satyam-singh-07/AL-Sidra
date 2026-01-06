<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\AuthenticationException; // Import this!
use Illuminate\Validation\ValidationException; // Keep this if you have it
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; // Keep this if you have it
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException; // Keep this if you have it
use Symfony\Component\HttpKernel\Exception\HttpException; // Keep this if you have it

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // ONLY apply custom rendering for API requests
        if ($request->is('api/*')) {
            // Handle AuthenticationException (e.g., invalid token, unauthenticated)
            // This is the CRITICAL part for your issue!
            if ($exception instanceof AuthenticationException) {
                return $this->errorResponse(
                    'AUTHENTICATION_REQUIRED',
                    'Authentication is required to access this resource.',
                    ['reason' => 'You are not authenticated or your token is invalid/missing.'],
                    401 // HTTP 401 Unauthorized
                );
            }

            // Handle ValidationException (e.g., form request validation failures)
            if ($exception instanceof ValidationException) {
                return $this->errorResponse(
                    'VALIDATION_FAILED',
                    'The provided data failed validation.',
                    [
                        'reason' => 'One or more fields are invalid.',
                        'field_errors' => $exception->errors() // Laravel's validation errors
                    ],
                    422
                );
            }

            // Handle 404 Not Found exceptions (e.g., ModelNotFoundException, route not found)
            if ($exception instanceof NotFoundHttpException) {
                return $this->errorResponse(
                    'RESOURCE_NOT_FOUND',
                    'The requested resource was not found.',
                    ['reason' => 'The specified URL or resource does not exist.'],
                    404
                );
            }

            // Handle 405 Method Not Allowed exceptions
            if ($exception instanceof MethodNotAllowedHttpException) {
                return $this->errorResponse(
                    'METHOD_NOT_ALLOWED',
                    'The HTTP method used is not supported for this route.',
                    ['reason' => 'Please check the allowed methods for this endpoint.'],
                    405
                );
            }

            // Handle other generic HTTP exceptions (e.g., abort(403), abort(400))
            if ($exception instanceof HttpException) {
                $statusCode = $exception->getStatusCode();
                $errorCode = 'HTTP_ERROR';
                $message = 'An HTTP error occurred.';
                $reason = 'Generic HTTP error.';

                if ($statusCode === 403) {
                    $errorCode = 'PERMISSION_DENIED';
                    $message = 'You do not have permission to perform this action.';
                    $reason = 'Access to this resource is forbidden.';
                } elseif ($statusCode === 400) {
                    $errorCode = 'BAD_REQUEST';
                    $message = 'The request was malformed or invalid.';
                    $reason = 'Check your request parameters and body.';
                }

                return $this->errorResponse(
                    $errorCode,
                    $message,
                    ['reason' => $reason],
                    $statusCode
                );
            }

            // Catch all other unhandled exceptions as a generic server error
            // In production, keep this generic for security
            $statusCode = $this->isHttpException($exception) ? $exception->getStatusCode() : 500;

            if (config('app.debug')) {
                $message = $exception->getMessage();
                $details = [
                    'reason' => 'An unhandled server error occurred.',
                    'exception' => get_class($exception),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    // 'trace' => $exception->getTraceAsString(), // Uncomment for full trace in debug
                ];
            } else {
                $message = 'An internal server error occurred.';
                $details = ['reason' => 'Please try again later.'];
            }

            return $this->errorResponse(
                'SERVER_ERROR',
                $message,
                $details,
                $statusCode
            );
        }

        // For non-API requests, let Laravel's default handler take over
        return parent::render($request, $exception);
    }

    /**
     * Helper method to format error responses.
     *
     * @param string $errorCode
     * @param string $message
     * @param array $details
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(string $errorCode, string $message, array $details, int $statusCode): JsonResponse
    {
        return response()->json([
            'error_code' => $errorCode,
            'message' => $message,
            'details' => $details
        ], $statusCode);
    }
}