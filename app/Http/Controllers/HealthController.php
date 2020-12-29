<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class HealthController extends Controller
{
    /**
     * Handles requests for the health check endpoint
     *
     * @return Response
     */
    public function __invoke(): Response
    {
        return \response("", 200);
    }
}
