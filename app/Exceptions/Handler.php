<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Inertia\Inertia;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        // ... existing code ...
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
            // ... existing code ...
        });
    }

    public function render($request, Throwable $e)
    {
        // Handle authorization errors from spatie/permission middleware
        if ($e instanceof AuthorizationException) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }

            return Inertia::render('Errors/403', [
                'message' => 'Anda tidak memiliki akses untuk fitur ini.'
            ])->with('error', 'Anda tidak memiliki akses untuk fitur ini.')
              ->toResponse($request)
              ->setStatusCode(403);
        }

        return parent::render($request, $e);
    }
}
