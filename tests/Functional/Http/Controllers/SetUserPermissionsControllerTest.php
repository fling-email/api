<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\User;

/**
 * @covers App\Http\Controllers\SetUserPermissionsController
 */
class SetUserPermissionsControllerTest extends TestCase
{
    public function testSet(): void
    {
        $user = $this->getTestUser();
        $other_user = User::query()
            ->where("organisation_id", $user->organisation_id)
            ->where("id", "!=", $user->id)
            ->first();

        // Make Phan know the type for $user
        \assert($other_user instanceof User);

        $this->actingAsTestUser()
            ->json("PUT", "/users/{$other_user->uuid}/permissions", [
                "view_user",
                "view_users",
                "view_domain",
                "view_domains",
                "view_user_permissions",
            ])
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(201);

        $this->actingAsTestUser()
            ->json("PUT", "/users/{$other_user->uuid}/permissions", [
                "view_user",
                "view_users",
                "view_domain",
            ])
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(201);

        $other_user_permission = $other_user->getPermissionNames()->toArray();

        // First request should have removed existing permissions and added new
        // ones, then the second request should remove two of those ones.
        $this->assertEqualsCanonicalizing(
            ["view_user", "view_users", "view_domain"],
            $other_user_permission,
        );
    }

    public function testEditOwnPermissions(): void
    {
        $user = $this->getTestUser();

        $this->actingAsTestUser()
            ->json("PUT", "/users/{$user->uuid}/permissions", [
                "view_user",
                "view_users",
            ])
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(403, "You do not have permissions to edit user permissions");
    }

    public function testSetWithoutPermission(): void
    {
        $user = $this->getTestUser();
        $other_user = User::query()
            ->where("organisation_id", $user->organisation_id)
            ->where("id", "!=", $user->id)
            ->first();

        $user->revokePermission("edit_user_permissions");

        $this->actingAsTestUser()
            ->json("PUT", "/users/{$other_user->uuid}/permissions", [
                "view_user",
                "view_users",
            ])
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(403, "You do not have permissions to edit user permissions");
    }

    public function testSetMorePermissions(): void
    {
        $user = $this->getTestUser();
        $other_user = User::query()
            ->where("organisation_id", $user->organisation_id)
            ->where("id", "!=", $user->id)
            ->first();

        $user->revokePermission("view_users");

        $this->actingAsTestUser()
            ->json("PUT", "/users/{$other_user->uuid}/permissions", [
                "view_user",
                "view_users",
            ])
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(403, "You cannot grant permissiosn that you do not have yourself");
    }
}
