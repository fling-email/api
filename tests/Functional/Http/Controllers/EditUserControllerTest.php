<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;
use App\Models\LoginToken;

class EditUserControllerTest extends TestCase
{
    public function testEdit(): void
    {
        $user = $this->getTestUser();

        $this->actingAsTestUser()
            ->json("patch", "/users/{$user->uuid}", [
                "name" => "Cool new name",
            ])
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(200)
            ->seeInDatabase("users", [
                "id" => $user->id,
                "name" => "Cool new name",
            ]);
    }

    public function testDisableUser(): void
    {
        $user = $this->getTestUser();

        $token = new LoginToken();
        $token->uuid = (string) Str::uuid();
        $token->user_id = $user->id;
        $token->expires_at = Date::now()->modify("+1 hour");
        $token->token = Str::random(60);
        $token->save();

        $this->actingAsTestUser()
            ->json("patch", "/users/{$user->uuid}", [
                "enabled" => false,
            ])
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(200)
            ->seeInDatabase("users", [
                "id" => $user->id,
                "enabled" => false,
            ])
            ->notSeeInDatabase("login_tokens", [
                "id" => $token->id,
            ])
            ->notSeeInDatabase("login_tokens", [
                "user_id" => $user->id,
            ]);
    }

    public function testEditNotFound(): void
    {
        $user = $this->getTestUser();

        $this->actingAsTestUser()
            ->json("patch", "/users/wrong-uuid", [
                "name" => "Cool new name",
            ])
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(403, "You do not have permission to update this user");
    }

    public function testEditWithoutPermission(): void
    {
        $user = $this->getTestUser();

        $user->revokePermission("update_user");

        $this->actingAsTestUser()
            ->json("patch", "/users/{$user->uuid}", [
                "name" => "Cool new name",
            ])
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(403, "You do not have permission to update this user");
    }
}
