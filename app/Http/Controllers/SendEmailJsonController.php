<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Models\Email;

class SendEmailJsonController extends Controller
{
    public static ?string $method = "post";
    public static ?string $path = "/emails/json";

    /**
     * Handles requests to queue new emails for delivery
     *
     * @param Request $request The request
     *
     * @return JsonResponse|Response
     */
    public function __invoke(Request $request): JsonResponse|Response
    {
        return \response("", 204);
    }
}
