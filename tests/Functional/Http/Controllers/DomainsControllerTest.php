<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;

class DomainsControllerTest extends TestCase
{
    public function testGet(): void
    {
        $this->actingAsTestUser()
            ->get("/domains")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200);
    }
}
