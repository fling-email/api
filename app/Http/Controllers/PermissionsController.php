<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\JsonResponse;

class PermissionsController extends Controller
{
    public static ?string $method = "get";
    public static ?string $path = "/permissions";

    /**
     * Handles requests to get all available permissions
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        return \response()->json(
            Permission::all()
        );
    }
}
