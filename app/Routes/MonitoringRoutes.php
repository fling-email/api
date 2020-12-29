<?php

declare(strict_types=1);

namespace App\Routes;

use App\Http\Controllers\HealthController;

class MonitoringRoutes extends Routes
{
    /**
     * Registers the system monitoring routes
     *
     * @return void
     */
    public function register(): void
    {
        $this->router->get("/health", HealthController::class);
    }
}
