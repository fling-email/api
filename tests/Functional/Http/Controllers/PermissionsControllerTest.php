<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use App\Models\Permission;

/**
 * @covers App\Http\Controllers\PermissionsController
 */
class PermissionsControllerTest extends TestCase
{
    public function testGet(): void
    {
        $this->actingAsTestUser()
            ->get("/permissions?per_page=100")
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
                )->toArray(),
            ]);
    }
}
