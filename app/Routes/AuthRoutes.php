<?php

declare(strict_types=1);

namespace App\Routes;

use App\Http\Controllers\LoginController;
use App\Http\Controllers\AuthController;

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

        $this->router->group(["middleware" => "auth"], function (): void {
            $this->router->get("/auth", AuthController::class);
        });
    }
}
