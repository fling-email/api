<?php

declare(strict_types=1);

use Laravel\Lumen\Application;
use Laravel\Lumen\Bootstrap\LoadEnvironmentVariables;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use Illuminate\Contracts\Console\Kernel as KernelContract;
use Illuminate\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use App\Console\Kernel;
use App\Exceptions\Handler;
use App\Providers\AppServiceProvider;
use App\Providers\AuthServiceProvider;
use App\Providers\EventServiceProvider;
use App\Http\Controllers\Controller;
use App\Http\Routes\ControllerRoute;
use App\Http\Middleware\Authenticate as AuthenticateMiddleware;
use App\Http\Middleware\SchemaValidator as SchemaValidatorMiddleware;

/**
 * Creates a new instance of the application
 *
 * @return Application
 */
function createApp(): Application
{
    (new LoadEnvironmentVariables(\dirname(__DIR__)))
        ->bootstrap();

    $app = new Application(\dirname(__DIR__));

    $app->withFacades();
    $app->withEloquent();

    // Make dates be UTC always
    \date_default_timezone_set("UTC");

    Date::useCallable(
        fn (mixed $date): mixed => ($date instanceof CarbonInterface)
            ? $date->setTimezone("UTC")
            : $date
    );

    // Register container bindings
    $app->singleton(ExceptionHandlerContract::class, Handler::class);
    $app->singleton(KernelContract::class, Kernel::class);

    // Register config files
    $app->configure("app");

    // Register middleware
    $app->routeMiddleware([
        "auth" => AuthenticateMiddleware::class,
        "validate_schemas" => SchemaValidatorMiddleware::class,
    ]);

    // Register service providers
    $app->register(AppServiceProvider::class);
    $app->register(AuthServiceProvider::class);
    $app->register(EventServiceProvider::class);

    // Load the application routes
    foreach (Controller::all() as $controller_class) {
        $controller_route = $controller_class::getRoute();

        if (!$controller_route instanceof ControllerRoute) {
            throw new \UnexpectedValueException(
                "Controller did not return a valid route"
            );
        }

        $controller_route->register($app->router);
    }

    return $app;
}
