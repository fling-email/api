<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\LoginToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public static ?string $method = "get";
    public static ?string $path = "/auth";
    public static bool $paginated = false;

    /**
     * Handles get requests to the /auth endpoint
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $token = LoginToken::query()
            ->where("token", $request->bearerToken())
            ->first();

        return \response()->json([
            "user" => $user,
            "token" => $token,
        ]);
    }
}
