<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DomainsController extends Controller
{
    public static ?string $method = "get";
    public static ?string $path = "/domains";

    /**
     * Handles requests for the /domains endpoint
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        return \response()->json(
            $user->organisation->domains
        );
    }
}
