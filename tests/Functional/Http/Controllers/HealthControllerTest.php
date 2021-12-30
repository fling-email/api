<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;

/**
 * @covers App\Http\Controllers\HealthController
 */
class HealthControllerTest extends TestCase
{
    public function testGet(): void
    {
        $this->get("/health")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200);
    }
}
