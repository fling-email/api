<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PermissionsController extends Controller
{
    public static ?string $method = "get";
    public static ?string $path = "/permissions";

    /**
     * Handles requests to get all available permissions
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        return \response()->json(
            Permission::all()
        );
    }
}
