<?php

declare(strict_types=1);

namespace Tests\Functional\Http\Controllers;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use App\Models\Organisation;

class CreateDomainControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Cache::forget("public_suffix_list");

        Cache::remember(
            "public_suffix_list",
            86400,
            fn (): array => [
                "biz",
                "co.uk",
                "org",
                "com",
                "email",
            ],
        );
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Cache::forget("public_suffix_list");
    }

    public function testCreate(): void
    {
        $this->actingAsTestUser()
            ->json("POST", "/domains", [
                "name" => "test.biz",
            ])
            ->dontSeeJsonSchemaError()
            ->seeJson([
                "name" => "test.biz",
                "verified" => false,
            ])
            ->seeStatusCode(201);
    }

    public function testCreateWhereAlreadyExists(): void
    {
        $organisation = Organisation::query()->first();
        $existing_domains = $organisation->domains;

        $this->actingAsTestUser()
            ->json("POST", "/domains", [
                "name" => $existing_domains->first()->name,
            ])
            ->dontSeeJsonSchemaError()
            ->seeJson([
                "message" => "Domain name already exists",
            ])
            ->seeStatusCode(400);
    }

    /**
     * @dataProvider dataCreateWithInvalidName
     */
    public function testCreateWithInvalidName(string $domain_name): void
    {
        $this->actingAsTestUser()
            ->json("POST", "/domains", [
                "name" => $domain_name,
            ])
            ->dontSeeJsonSchemaError()
            ->seeJson([
                "message" => "Invalid hostname at #->properties:name",
            ])
            ->seeStatusCode(400);
    }

    /**
     * @phan-return list<array{string}>
     */
    public function dataCreateWithInvalidName(): array
    {
        return [
            [""],
            ["no_tld"],
            [\str_repeat("really-long", 20)],
            ["invalid.tld"],
        ];
    }
}
