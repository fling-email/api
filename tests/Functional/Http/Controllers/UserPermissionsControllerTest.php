<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;

class UserPermissionsControllerTest extends TestCase
{
    public function testGet(): void
    {
        $user = $this->getTestUser();

        $this->actingAsTestUser()
            ->get("/users/{$user->uuid}/permissions")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200);
    }
}
