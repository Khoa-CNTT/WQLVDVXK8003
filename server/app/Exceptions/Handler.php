<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });

        // Handle 404 errors
        $this->renderable(function (NotFoundHttpException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Resource not found',
                'errors' => ['Not found' => 'The requested resource could not be found']
            ], 404);
        });

        // Handle authentication errors
        $this->renderable(function (AuthenticationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthenticated',
                'errors' => ['Authentication' => 'Please login to access this resource']
            ], 401);
        });

        // Handle validation errors
        $this->renderable(function (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        });

        // Handle general errors in API
        $this->renderable(function (Throwable $e) {
            if (request()->expectsJson()) {
                $status = 500;

                // Check if exception implements HttpExceptionInterface
                if ($e instanceof HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                }

                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage(),
                    'errors' => [
                        'exception' => config('app.debug') ? [
                            'message' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => $e->getTrace()
                        ] : 'Server Error'
                    ]
                ], $status);
            }
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }
}
