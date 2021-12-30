<?php

declare(strict_types=1);

namespace App;

use Laravel\Lumen\Application as BaseApplication;
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

class Application extends BaseApplication
{
    /**
     * Creates a new instance of our application
     */
    public function __construct()
    {
        $this->loadEnv();

        // The .env file needs to be loaded before Lumen's setup stuff, make
        // sure the call to $this->loadEnv() stays above this.
        parent::__construct(\dirname(__DIR__));

        $this->withFacades();
        $this->withEloquent();

        $this->configureDates();
        $this->registerContainerBindings();
        $this->loadConfig();
        $this->registerMiddleware();
        $this->registerServiceProviders();
        $this->registerRoutes();
    }

    /**
     * Reads the .env file. Must be called before the parent constructor
     *
     * @return void
     */
    private function loadEnv(): void
    {
        (new LoadEnvironmentVariables(\dirname(__DIR__)))
            ->bootstrap();
    }

    /**
     * Configures PHP and the Date facade to be UTC by default
     *
     * @return void
     */
    private function configureDates(): void
    {
        \date_default_timezone_set("UTC");

        Date::useCallable(
            fn (mixed $date): mixed => ($date instanceof CarbonInterface)
                ? $date->setTimezone("UTC")
                : $date
        );
    }

    /**
     * Registers our shared bindings
     *
     * @return void
     */
    private function registerContainerBindings(): void
    {
        $this->singleton(ExceptionHandlerContract::class, Handler::class);
        $this->singleton(KernelContract::class, Kernel::class);
    }

    /**
     * Loads the app config
     *
     * @return void
     */
    private function loadConfig(): void
    {
        $this->configure("app");
    }

    /**
     * Registers our middleware
     *
     * @return void
     */
    private function registerMiddleware(): void
    {
        $this->routeMiddleware([
            "auth" => AuthenticateMiddleware::class,
            "validate_schemas" => SchemaValidatorMiddleware::class,
        ]);
    }

    /**
     * Registers service providers
     *
     * @return void
     */
    private function registerServiceProviders(): void
    {
        $this->register(AppServiceProvider::class);
        $this->register(AuthServiceProvider::class);
        $this->register(EventServiceProvider::class);
    }

    /**
     * Registers all application routes
     *
     * @return void
     */
    private function registerRoutes(): void
    {
        foreach (Controller::all() as $controller_class) {
            $controller_route = $controller_class::getRoute();

            if (!$controller_route instanceof ControllerRoute) {
                throw new \UnexpectedValueException(
                    "Controller did not return a valid route"
                );
            }

            $controller_route->register($this->router);
        }
    }
}
