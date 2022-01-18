<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class DeleteDomainController extends Controller
{
    public static ?string $method = "delete";
    public static ?string $path = "/domains/{uuid}";

    /**
     * Handles requests to the delete domain endpoint
     *
     * @param string $uuid The UUID of the domain being delete
     *
     * @return Response|JsonResponse
     */
    public function __invoke(string $uuid): Response|JsonResponse
    {
        $domain = Domain::query()
            ->where("uuid", $uuid)
            ->first();

        $this->authorize("delete", [Domain::class, $domain]);

        $domain->delete();

        return \response("", 204);
    }
}
