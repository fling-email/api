<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Models\LoginToken;
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

        $login_token = LoginToken::query()
            ->where("token", $token)
            ->first();

        if ($login_token === null) {
            return null;
        }

        $login_token->expires_at = (new \DateTime())->modify("+1 hour");
        $login_token->save();

        return $login_token->user;
    }
}
