<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\Permission;

class UsersControllerTest extends TestCase
{
    public function testGet(): void
    {
        $this->actingAsTestUser()
            ->get("/users")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200);
    }
}
