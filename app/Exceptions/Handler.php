<?php

namespace App\Exceptions;

use App\Traits\ResponseTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ResponseTrait;

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
     *
     * Common API errors are answered in our { error: { message } } shape with a friendly message.
     * Validation errors (422) keep Laravel's own { message, errors } shape, which the frontend reads.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Not signed in, or the token expired.
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return $this->dataResponse('error', 401, 'Please sign in.');
            }
        });

        // Unknown URL, or e.g. /api/products/999 when product 999 doesn't exist.
        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return $this->dataResponse('error', 404, 'Record not found.');
            }
        });
    }
}
