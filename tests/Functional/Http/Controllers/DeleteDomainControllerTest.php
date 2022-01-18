<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;

/**
 * @covers App\Http\Controllers\DeleteDomainController
 */
class DeleteDomainControllerTest extends TestCase
{
    public function testDelete(): void
    {
        $user = $this->getTestUser();
        $domain = $user->organisation->domains->first();

        $this->actingAsTestUser()
            ->delete("/domains/{$domain->uuid}")
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(204)
            ->seeSoftDeleted($domain);
    }

    public function testDomainNotFound(): void
    {
        $this->actingAsTestUser()
            ->delete("/domains/not-a-domain")
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(403);
    }

    public function testNoPermission(): void
    {
        $user = $this->getTestUser();
        $domain = $user->organisation->domains->first();

        $user->revokePermission("delete_domain");

        $this->actingAsTestUser()
            ->delete("/domains/{$domain->uuid}")
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(403);
    }
}
