<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use App\Exceptions\BadRequestException;
use App\Exceptions\InternalServerErrorException;
use App\Utils\LoadsJsonSchemas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\InvalidValue;
use Swaggest\JsonSchema\Exception\ObjectException;

class SchemaValidator
{
    use LoadsJsonSchemas;

    /**
     * Handle an incoming request.
     *
     * @param Request $request The request being handled
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

        $this->validateRequestSchema($controller_class, $request);

        // Actually do the request
        $response = $next($request);

        $this->validateResponseSchema($controller_class, $response);

        return $response;
    }

    /**
     * Validates the client request against the expected json schema
     *
     * @param string $controller_class The controller class name
     * @phan-param class-string<Controller> $controller_class
     * @param Request $request The incomming request
     *
     * @return void
     * @throws BadRequestException
     */
    private function validateRequestSchema(string $controller_class, Request $request): void
    {
        // Only validate requests that should have a body
        if (!\in_array($controller_class::$method, ["post", "patch", "put"], true)) {
            return;
        }

        try {
            $schema = $controller_class::getRequestSchema();

            if (!$schema instanceof Schema) {
                throw new \UnexpectedValueException(
                    "Controller did not return a valid request schema"
                );
            }

            $schema->in(\json_decode($request->getContent()));
        } catch (InvalidValue $exception) {
            // Strip the input data from this error type. The client should know
            // what it sent and it's not very readable to users.
            $message = ($exception instanceof ObjectException)
                ? Str::before($exception->getMessage(), ", data: {")
                : $exception->getMessage();

            throw new BadRequestException($message);
        }
    }

    /**
     * Validates the response against the expected json schema
     *
     * @param string $controller_class The controller class name
     * @phan-param class-string<Controller> $controller_class
     * @param mixed $response The backend response
     *
     * @return void
     * @throws InternalServerErrorException
     */
    private function validateResponseSchema(string $controller_class, $response): void
    {
        // Validate the response if it was a json type
        if (!($response instanceof JsonResponse)) {
            return;
        }

        try {
            $schema = ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)
                ? $controller_class::getResponseSchema()
                : static::loadSchemaFile("error_response.json");

            $schema->in(\json_decode($response->getContent()));
        } catch (InvalidValue $exception) {
            $message = ($exception instanceof ObjectException)
                ? Str::before($exception->getMessage(), ", data: {")
                : $exception->getMessage();

            throw new InternalServerErrorException(
                "Backend returned an unexpected response",
                $message,
                [
                    "response" => $response->getContent(),
                ]
            );
        }
    }
}
