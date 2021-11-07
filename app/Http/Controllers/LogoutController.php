<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LoginToken;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LogoutController extends Controller
{
    public static ?string $method = "delete";
    public static ?string $path = "/auth";

    public function __invoke(Request $request): Response
    {
        $token = LoginToken::query()
            ->where("token", $request->bearerToken())
            ->first();

        $token->delete();

        return \response("", 204);
    }
}
