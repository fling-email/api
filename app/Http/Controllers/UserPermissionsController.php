<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Permission;

class UserPermissionsController extends Controller
{
    public static ?string $method = "get";
    public static ?string $path = "/users/{uuid}/permissions";

    /**
     * Handles requests to the user permissions endpoint
     *
     * @param string $uuid The UUID of the user
     *
     * @return JsonResponse
     */
    public function __invoke(string $uuid): JsonResponse
    {
        $user = User::query()
            ->where("uuid", $uuid)
            ->first();

        $this->authorize("viewPermissions", [User::class, $user]);

        return $this->jsonResponse(
            Permission::query()->whereIn(
                "id",
                DB::table("user_permissions")
                    ->select("permission_id")
                    ->where("user_id", $user->id)
            )
        );
    }
}
