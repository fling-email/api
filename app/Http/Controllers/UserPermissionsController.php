<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\UserPermission;
use App\Models\User;

class UserPermissionsController extends Controller
{
    public static ?string $method = "get";
    public static ?string $path = "/users/{uuid}/permissions";

    /**
     * Handles requests to the user permissions endpoint
     *
     * @param Request $request The request
     * @param string $uuid The UUID of the user
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        $user = User::with("userPermissions.permission")
            ->where("uuid", $uuid)
            ->first();

        $this->authorize("viewPermissions", [User::class, $user]);

        return \response()->json(
            $user->userPermissions->map(
                fn (UserPermission $user_permission): Permission => $user_permission->permission
            )
        );
    }
}