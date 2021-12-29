<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var string[]
     * @phan-var class-string[]
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
     * @param \Throwable $exception The exception to log
     *
     * @return void
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
     * @param Request $request The request that triggered the exception
     * @param \Throwable $exception The exception to render
     *
     * @return SymfonyResponse
     */
    public function render($request, \Throwable $exception)
    {
        // Render AppException instances for the user to see
        if ($exception instanceof AppException) {
            return \response()->json(
                $exception->json(),
                $exception->status(),
            );
        }

        // Convert exceptions from $this->authorize() to HTTP Forbidden responses
        if ($exception instanceof AuthorizationException) {
            return $this->render(
                $request,
                new ForbiddenException($exception->getMessage())
            );
        }

        // Otherwise render it as normal
        return parent::render($request, $exception);
    }
}
