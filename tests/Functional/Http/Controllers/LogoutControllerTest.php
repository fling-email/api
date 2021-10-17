<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\LoginToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Date;

/** @group current */
class LogoutControllerTest extends TestCase
{
    public function testLogout(): void
    {
        $user = $this->getTestUser();

        $token = LoginToken::create([
            "uuid" => (string) Str::uuid(),
            "expires_at" => Date::now()->modify("+1 hour"),
            "user_id" => $user->id,
            "token" => \str_repeat("lol", 60 / 3),
        ]);

        // Avoid using $this->actingAs() here so we can test for the correct token
        $this->delete("/auth", ["HTTP_Authorization" => "Bearer {$token->token}"])
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(204)
            ->notSeeInDatabase("login_tokens", [
                "uuid" => $token->uuid,
            ]);
    }
}
