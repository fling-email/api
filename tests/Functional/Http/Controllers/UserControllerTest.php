<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\User;

/**
 * @covers App\Http\Controllers\UserController
 */
class UserControllerTest extends TestCase
{
    public function testGetSelf(): void
    {
        $user = $this->getTestUser();

        $this->actingAsTestUser()
            ->get("/users/{$user->uuid}")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200);
    }

    public function testGetNotFound(): void
    {
        $this->actingAsTestUser()
            ->get("/users/invalid-uuid")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(403);
    }

    public function testWithoutPermission(): void
    {
        $user = $this->getTestUser();
        $other_user = User::query()
            ->where("organisation_id", $user->organisation_id)
            ->where("id", "!=", $user->id)
            ->first();

        $user->revokePermission("view_user");

        $this->actingAsTestUser()
            ->get("/users/{$other_user->uuid}")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(403);
    }
}
