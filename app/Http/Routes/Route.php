<?php

declare(strict_types=1);

namespace App\Http\Routes;

use \Laravel\Lumen\Routing\Router;

abstract class Route
{
    /**
     * Register this route with the Lumen router
     *
     * @param Router $router A router intance
     *
     * @return void
     */
    abstract public function register(Router $router): void;
}
