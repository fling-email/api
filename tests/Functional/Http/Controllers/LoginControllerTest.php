<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    public function testLogin(): void
    {
        $this->post("/auth", [
                "username" => "test",
                "password" => "secret",
            ])
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200)
            ->seeInDatabase("login_tokens", [
                "uuid" => $this->response->json("uuid"),
            ]);
    }

    /**
     * @dataProvider dataFailsWithWrongCredentials
     */
    public function testFailsWithWrongCredentials(string $username, string $password): void
    {
        $this->post("/auth", [
                "username" => $username,
                "password" => $password,
            ])
            ->dontSeeJsonSchemaError()
            ->seeJsonEquals([
                "status" => 403,
                "error" => "Forbidden",
                "message" => "Incorrect username or password",
            ])
            ->seeStatusCode(403);
    }

    /**
     * @phan-return list<array{string, string}>
     */
    public function dataFailsWithWrongCredentials(): array
    {
        return [
            ["i_dont_exist", "secret"],
            ["test", "im_wrong"],
            ["i_dont_exist", "im_wrong"],
        ];
    }
}
