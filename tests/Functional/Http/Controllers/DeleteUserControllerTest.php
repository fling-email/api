<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\LoginToken;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Date;

/** @group current */
class DeleteUserControllerTest extends TestCase
{
    public function testDelete(): void
    {
        $user = $this->getTestUser();
        $other_user = $user->organisation->users->first(
            fn (User $org_user): bool => $org_user->id !== $user->id
        );

        $token = new LoginToken();
        $token->uuid = (string) Str::uuid();
        $token->user_id = $other_user->id;
        $token->expires_at = Date::now()->modify("+1 hour");
        $token->token = Str::random(60);
        $token->save();

        $this->actingAsTestUser()
            ->delete("/users/{$other_user->uuid}")
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(204)
            ->seeInDatabase("users", [
                "id" => $other_user->id,
                "enabled" => false,
            ])
            ->notSeeInDatabase("login_tokens", [
                "id" => $token->id,
            ])
            ->notSeeInDatabase("login_tokens", [
                "user_id" => $other_user->id,
            ]);
    }

    public function testUserNotFound(): void
    {
        $user = $this->getTestUser();

        $this->actingAsTestUser()
            ->delete("/users/not-a-user-id")
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(403, "You do not have permission to delete this user")
            ->seeInDatabase("users", [
                "id" => $user->id,
                "enabled" => true,
                "deleted_at" => null,
            ]);
    }

    public function testWithoutPermission(): void
    {
        $user = $this->getTestUser();

        $user->revokePermission("delete_user");

        $this->actingAsTestUser()
            ->delete("/users/{$user->uuid}")
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(403, "You do not have permission to delete this user")
            ->seeInDatabase("users", [
                "id" => $user->id,
                "enabled" => true,
                "deleted_at" => null,
            ]);
    }

    public function testDeleteSelf(): void
    {
        $user = $this->getTestUser();

        $this->actingAsTestUser()
            ->delete("/users/{$user->uuid}")
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(403, "You cannot delete your own user")
            ->seeInDatabase("users", [
                "id" => $user->id,
                "enabled" => true,
                "deleted_at" => null,
            ]);
    }
}
