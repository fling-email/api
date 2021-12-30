<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;

/**
 * @covers App\Http\Controllers\CreateUserController
 */
class CreateUserControllerTest extends TestCase
{
    public function testCreate(): void
    {
        $this->actingAsTestUser()
            ->json("POST", "/users", [
                "name" => "Unit Test User",
                "username" => "unit_test_user",
                "email_address" => "unit_test@fling.email",
            ])
            ->dontSeeJsonSchemaError()
            ->seeJson([
                "username" => "unit_test_user",
                "enabled" => true,
                "activated" => false,
            ])
            ->seeStatusCode(201);
    }

    public function testCreateWithDuplicateUsername(): void
    {
        $test_user = $this->getTestUser();

        $this->actingAsTestUser()
            ->json("POST", "/users", [
                "name" => "Any Name",
                "username" => $test_user->username,
                "email_address" => "email@address.come",
            ])
            ->dontSeeJsonSchemaError()
            ->seeJson([
                "message" => "Username already in use",
            ])
            ->seeStatusCode(400);
    }

    public function testCreateWithDuplicateEmailAddress(): void
    {
        $test_user = $this->getTestUser();

        $this->actingAsTestUser()
            ->json("POST", "/users", [
                "name" => "Any Name",
                "username" => "unused_username",
                "email_address" => $test_user->email_address,
            ])
            ->dontSeeJsonSchemaError()
            ->seeJson([
                "message" => "Email address already in use",
            ])
            ->seeStatusCode(400);
    }
}
