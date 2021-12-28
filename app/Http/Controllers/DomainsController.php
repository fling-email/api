<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class DomainsController extends Controller
{
    public static ?string $method = "get";
    public static ?string $path = "/domains";

    /**
     * Handles requests for the /domains endpoint
     *
     * @return JsonResponse
     */
    public function __invoke(): JsonResponse
    {
        $user = $this->getRequestUser();

        return $this->jsonResponse(
            $user->organisation->domains()->getQuery()
        );
    }
}
