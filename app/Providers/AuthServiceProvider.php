<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        Auth::viaRequest("api", fn (Request $request): ?User => (
            $this->getUserFromRequest($request)
        ));
    }

    /**
     * Gets the user making an incomming API request
     *
     * @param Request $request The request being made
     *
     * @return User|null
     */
    private function getUserFromRequest(Request $request): ?User
    {
        $token = $request->bearerToken() ?? $request->header("X-App-Token") ?? "";

        if ($token === "" || $token === null) {
            return null;
        }

        return User::query()->where("api_token", $request->input("api_token"))
                   ->first();
    }
}
