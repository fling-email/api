<?php

declare(strict_types=1);

namespace App\Routes;

use App\Http\Controllers\LoginController;

class AuthRoutes extends Routes
{
    /**
     * Registers the authentication routes
     *
     * @return void
     */
    public function register(): void
    {
        $this->router->post("/auth", LoginController::class);
    }
}
