<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Swaggest\JsonSchema\Schema;

class SchemaValidator
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request the request
     * @param \Closure $next the next handler in the chain
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, string $controller_class)
    {
        // Also do nothing if it's not one of our controllers
        if (!\class_exists($controller_class) || !\is_subclass_of($controller_class, Controller::class)) {
            return $next($request);
        }

        $controller_namespace = Str::beforeLast(Controller::class, "\\");
        $controller_base_name = Str::substr($controller_class, Str::length($controller_namespace) + 1);

        $schema_root = __DIR__ . "/../../../schemas/";

        if ($controller_class::$method === "post" || $controller_class::$method === "patch") {
            // Validate the request if there should be a body
            $requst_schema_path = $schema_root . Str::snake($controller_base_name) . "_request.json";

            $schema = Schema::import(\json_decode(\file_get_contents($requst_schema_path)));

            $schema->in(\json_decode($request->getContent()));
        }


        // Actually do the request
        $response = $next($request);

        // Validate that too

        return $response;
    }
}
