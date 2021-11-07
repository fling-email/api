<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    // phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(\Throwable $exception)
    {
        // phpcs:enable
        // Sentry reporting will go here - remove the // phpcs:disable

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request  $request
     * @param \Throwable $exception
     * @return SymfonyResponse
     *
     * @throws \Throwable
     */
    public function render($request, \Throwable $exception)
    {
        if ($exception instanceof AppException) {
            return \response()->json(
                $exception->json(),
                $exception->status(),
            );
        }

        return parent::render($request, $exception);
    }
}
