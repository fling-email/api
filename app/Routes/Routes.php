<?php

declare(strict_types=1);

namespace App\Routes;

use Laravel\Lumen\Routing\Router;

abstract class Routes
{
    protected Router $router;

    /**
     * List of routes classes to register
     *
     * @var string[]
     * @phan-var list<class-string<Routes>>
     */
    private static array $routes = [
        AccountRoutes::class,
    ];

    /**
     * @param Router $router The router to append our new routes to
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Registes new routes with the router
     *
     * @return void
     */
    abstract public function register(): void;

    /**
     * Gets a list of all routes classes
     *
     * @return string[]
     * @phan-return list<class-string<Routes>>
     */
    public static function all(): array
    {
        return self::$routes;
    }
}
