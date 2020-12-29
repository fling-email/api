<?php

declare(strict_types=1);

/**
 * Creates a new instance of the application
 *
 * @return \Laravel\Lumen\Application
 */
function createApp(): \Laravel\Lumen\Application
{
    (new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(dirname(__DIR__)))
        ->bootstrap();

    date_default_timezone_set(env("APP_TIMEZONE", "UTC"));

    /**
     * Create The Application
     */
    $app = new Laravel\Lumen\Application(dirname(__DIR__));

    $app->withFacades();
    $app->withEloquent();

    /**
     * Register Container Bindings
     */
    $app->singleton(
        Illuminate\Contracts\Debug\ExceptionHandler::class,
        App\Exceptions\Handler::class
    );

    $app->singleton(
        Illuminate\Contracts\Console\Kernel::class,
        App\Console\Kernel::class
    );

    /**
     * Register Config Files
     */
    $app->configure("app");

    /**
     * Register Middleware
     */
    $app->routeMiddleware([
        "auth" => App\Http\Middleware\Authenticate::class,
    ]);

    /**
     * Register Service Providers
     */
    $app->register(App\Providers\AppServiceProvider::class);
    $app->register(App\Providers\AuthServiceProvider::class);
    $app->register(App\Providers\EventServiceProvider::class);

    /**
     * Load The Application Routes
     */
    $app->router->group([], function (Laravel\Lumen\Routing\Router $router) {
        foreach (App\Routes\Routes::all() as $routes_class) {
            $routes = new $routes_class($router);
            $routes->register();
        }
    });

    return $app;
}
