<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public static ?string $method = "get";
    public static ?string $path = "/users/{uuid}";

    /**
     * Handles requests to the /users/{uuid} endpoint
     *
     * @param string $uuid The UUID of the user
     *
     * @return Response|JsonResponse
     */
    public function __invoke(string $uuid): Response|JsonResponse
    {
        $user = User::where("uuid", $uuid)->first();

        $this->authorize("view", [User::class, $user]);

        $user->load("userPermissions.permission");

        return \response()->json($user);
    }
}
