<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;

/**
 * @covers App\Http\Controllers\UserPermissionsController
 */
class UserPermissionsControllerTest extends TestCase
{
    public function testGet(): void
    {
        $user = $this->getTestUser();

        $this->actingAsTestUser()
            ->get("/users/{$user->uuid}/permissions?per_page=100")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200);

        $expected_permission_names = $user->getPermissionNames()->toArray();
        $response_permission_names = \array_column($this->response->json()["data"], "name");

        $this->assertEqualsCanonicalizing($response_permission_names, $expected_permission_names);
    }
}
