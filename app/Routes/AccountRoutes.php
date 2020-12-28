<?php

declare(strict_types=1);

namespace App\Routes;

class AccountRoutes extends Routes
{
    /**
     * Registers the account routes
     *
     * @return void
     */
    public function register(): void
    {
        $this->router->get("/", fn (): string => "Test");
    }
}
