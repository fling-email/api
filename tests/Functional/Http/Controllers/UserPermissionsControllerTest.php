<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\UserPermission;

class UserPermissionsControllerTest extends TestCase
{
    public function testGet(): void
    {
        $user = $this->getTestUser();

        $this->actingAsTestUser()
            ->get("/users/{$user->uuid}/permissions")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200)
            ->seeJsonSubset([
                "data" => $user->userPermissions->map(
                    /** @phan-return array<string, string> */
                    fn (UserPermission $user_permission): array => [
                        "name" => $user_permission->permission->name,
                        "description" => $user_permission->permission->description,
                    ]
                )->toArray()
            ]);
    }
}
