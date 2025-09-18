<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {

            // Handle validation errors
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $exception->errors()
                ], 422);
            }

            // Blog not found
            if ($exception instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Resource not found.'
                ], 404);
            }

            // Route not found
            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'Route not found.'
                ], 404);
            }

            // Method not allowed
            if ($exception instanceof MethodNotAllowedHttpException) {
                return response()->json([
                    'message' => 'HTTP method not allowed.'
                ], 405);
            }

            // Unauthenticated
            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated.'
                ], 401);
            }

            // Catch-all for other exceptions
            return response()->json([
                'message' => 'Something went wrong.',
                'error' => config('app.debug') ? $exception->getMessage() : null
            ], 500);
        }

        return parent::render($request, $exception);
    }
}