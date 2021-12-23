<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\LoginToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Date;

class LogoutControllerTest extends TestCase
{
    public function testLogout(): void
    {
        $user = $this->getTestUser();

        $token = LoginToken::create([
            "uuid" => (string) Str::uuid(),
            "user_id" => $user->id,
            "expires_at" => Date::now()->modify("+1 hour"),
            "token" => Str::random(60),
        ]);

        // Avoid using $this->actingAs() here so we can test for the correct token
        $this->delete(
            uri: "/auth",
            headers: ["HTTP_Authorization" => "Bearer {$token->token}"],
        )
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(204)
            ->notSeeInDatabase("login_tokens", [
                "uuid" => $token->uuid,
            ]);
    }
}
