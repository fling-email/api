<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use betterphp\native_mock\native_mock;

class VerifyDomainControllerTest extends TestCase
{
    use native_mock;

    public function setUp(): void
    {
        parent::setUp();

        $this->nativeMockSetUp();
    }

    public function tearDown(): void
    {
        $this->nativeMockTearDown();

        parent::tearDown();
    }

    public function testVerify(): void
    {
        $user = $this->getTestUser();
        $user->grantPermission("verify_domain");

        $domain = $user->organisation->domains->first();

        $this->actingAsTestUser()
            ->post("/domains/{$domain->uuid}/verify")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(201);
    }

    public function testVerifyWithoutPermission(): void
    {
        $user = $this->getTestUser();
        $domain = $user->organisation->domains->first();

        $this->actingAsTestUser()
            ->post("/domains/{$domain->uuid}/verify")
            ->dontSeeJsonSchemaError()
            ->seeStatusCode(403);
    }

    public function testVerifyAlreadyDone(): void
    {
        $user = $this->getTestUser();
        $domain = $user->organisation->domains->first();

        $user->grantPermission("verify_domain");

        $domain->verified = true;
        $domain->save();

        $this->actingAsTestUser()
            ->post("/domains/{$domain->uuid}/verify")
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(400, "Domain is already verified");
    }

    /**
     * @param mixed $dns_results The return value for \dns_get_record()
     * @dataProvider dataVerifyWithWrongDnsRecords
     */
    public function testVerifyWithMissingDnsRecords($dns_results): void
    {
        $user = $this->getTestUser();
        $domain = $user->organisation->domains->first();

        $user->grantPermission("verify_domain");

        $this->redefineFunction(
            "dns_get_record",
            fn () => $dns_results,
        );

        $this->actingAsTestUser()
            ->post("/domains/{$domain->uuid}/verify")
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(400, "Domain could not be verified, check DNS records");
    }

    public function dataVerifyWithWrongDnsRecords(): array
    {
        return [
            [ [] ],
            [ false ],
            [ [["txt" => "some wrong txt"]] ],
        ];
    }

    public function testVerifyWithWrongDnsRecords(): void
    {
        $user = $this->getTestUser();
        $domain = $user->organisation->domains->first();

        $user->grantPermission("verify_domain");

        $this->redefineFunction(
            "dns_get_record",
            fn () => [
                ["txt" => "Wrong DNS data"],
            ],
        );

        $this->actingAsTestUser()
            ->post("/domains/{$domain->uuid}/verify")
            ->dontSeeJsonSchemaError()
            ->seeResponseStatus(400, "Domain could not be verified, check DNS records");
    }
}
