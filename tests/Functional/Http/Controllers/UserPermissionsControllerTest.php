<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\UserPermission;
use App\Models\Permission;

class UserPermissionsControllerTest extends TestCase
{
    public function testGet(): void
    {
        $user = $this->getTestUser();

        $this->actingAsTestUser()
            ->get("/users/{$user->uuid}/permissions")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200)
            ->seeJson($user->userPermissions->map(
                fn (UserPermission $user_permission): array => [
                    "name" => $user_permission->permission->name,
                    "description" => $user_permission->permission->description,
                ]
            )->toArray());
    }
}
