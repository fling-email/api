<?php

declare(strict_types=1);

namespace App\Http\Routes;

use App\Http\Controllers\Controller;
use App\Exceptions\InternalServerErrorException;
use Laravel\Lumen\Routing\Router;

class ControllerRoute extends Route
{
    /**
     * @phan-var class-string<Controller>
     */
    private string $controller_class;

    /**
     * @param string $controller_class The controller class name
     * @phan-param class-string<Controller> $controller_class
     */
    public function __construct(string $controller_class)
    {
        $this->controller_class = $controller_class;
    }

    /**
     * Register the controllers router with the Lumen router
     *
     * @param Router $router The router instance
     *
     * @return void
     */
    public function register(Router $router): void
    {
        $this->validateControllerProperties();

        $middleware = [];

        if ($this->controller_class::$auth) {
            $middleware[] = "auth";
        }

        $middleware[] = "validate_schemas:{$this->controller_class}";

        $router->group(
            ["middleware" => $middleware],
            function () use ($router): void {
                $method = \strtolower($this->controller_class::$method);
                $path = $this->controller_class::$path;

                $router->$method($path, $this->controller_class);
            }
        );
    }

    /**
     * Validates the controller route properties
     *
     * @return void
     */
    private function validateControllerProperties(): void
    {
        $method = $this->controller_class::$method;
        $path = $this->controller_class::$path;

        $valid_methods = ["get", "post", "patch", "put", "delete"];

        if ($method === null || $path === null || !\in_array($method, $valid_methods, true)) {
            throw new InternalServerErrorException("Invalid controller route");
        }
    }
}
