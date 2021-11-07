<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\User;
use App\Models\LoginToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Date;

/** @group current */
class AuthControllerTest extends TestCase
{
    public function testGet(): void
    {
        $user = User::query()->first();

        $token = LoginToken::create([
            "uuid" => (string) Str::uuid(),
            "user_id" => $user->id,
            "expires_at" => Date::now()->modify("+1 hour"),
            "token" => \str_repeat("lol", 60 / 3),
        ]);

        // Avoid using $this->actingAs() here so we can test for the correct token
        $this->get("/auth", ["HTTP_Authorization" => "Bearer {$token->token}"])
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200);
    }
}
