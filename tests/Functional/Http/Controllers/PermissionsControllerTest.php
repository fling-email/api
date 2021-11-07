<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\Permission;

class PermissionsControllerTest extends TestCase
{
    public function testGet(): void
    {
        $this->actingAsTestUser()
            ->get("/permissions")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(200)
            ->seeJsonSubset([
                "data" => Permission::all()->map(
                    /**
                     * @phan-return array<string, mixed>
                     */
                    fn (Permission $permission): array => (
                        $permission->jsonSerialize()
                    )
                ),
            ]);
    }
}
