<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\User;

class UsersControllerTest extends TestCase
{
    public function testGet(): void
    {
        $test_user = User::query()
            ->where("username", "test")
            ->first();

        $this->actingAs($test_user)
            ->get("/users")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200);
    }
}
