<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UsersController extends Controller
{
    public static ?string $method = "get";
    public static ?string $path = "/users";

    /**
     * Handles requests to the /users endpoint
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $users = User::query()
            ->where("organisation_id", $user->organisation_id)
            ->get();

        return \response()->json($users);
    }
}
