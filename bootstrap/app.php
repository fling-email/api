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
        "validate_schemas" => App\Http\Middleware\SchemaValidator::class,
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
    foreach (\App\Http\Controllers\Controller::all() as $controller_class) {
        $controller_route = $controller_class::getRoute();
        $controller_route->register($app->router);
    }

    return $app;
}
