<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Exceptions\BadRequestException;
use App\Exceptions\InternalServerErrorException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Exception\ObjectException;

class SchemaValidator
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request the request
     * @param \Closure $next the next handler in the chain
     * @param string $controller_class The controller being called
     * @phan-param class-string<Controller> $controller_class
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, string $controller_class)
    {
        // Do nothing if it's not one of our controllers
        if (!\class_exists($controller_class) || !\is_subclass_of($controller_class, Controller::class)) {
            return $next($request);
        }

        // Validate the request if there should be a body
        if (\in_array($controller_class::$method, ["post", "patch", "put"], true)) {
            $schema = $controller_class::getRequestSchema();

            try {
                $schema->in(\json_decode($request->getContent()));
            } catch (InvalidValue $exception) {
                // Strip the input data from this error type. The client should
                // know what it sent and it's not very readable to users.
                $message = ($exception instanceof ObjectException)
                    ? \explode(", data: {", $exception->getMessage(), 2)[0]
                    : $exception->getMessage();

                throw new BadRequestException($message);
            }
        }

        // Actually do the request
        $response = $next($request);

        // Validate the response if it was a json type
        if ($response instanceof JsonResponse) {
            $schema = $controller_class::getResponseSchema();

            try {
                $schema->in(\json_decode($response->getContent()));
            } catch (InvalidValue $exception) {
                throw new InternalServerErrorException(
                    "Backend returned an unexpected response",
                    $exception->getMessage(),
                );
            }
        }

        return $response;
    }
}
