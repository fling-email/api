<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;

class PermissionsControllerTest extends TestCase
{
    public function testGet(): void
    {
        $this->actingAsTestUser()
            ->get("/permissions")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200)
            ->seeJsonSubset([
                "data" => Permission::all->map(
                    fn (Permission $permission): array => (
                        $permission->jsonSerialize()
                    )
                ),
            ]);
    }
}
