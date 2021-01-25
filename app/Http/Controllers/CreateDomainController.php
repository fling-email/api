<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Models\Domain;
use App\Exceptions\BadRequestException;

class CreateDomainController extends Controller
{
    public static ?string $method = "post";
    public static ?string $path = "/domains";

    /**
     * Handles requests for the /domains endpoint
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $org = $user->organisation;

        $domain_name = $request->json("name");

        $this->validateDomainName($domain_name);

        $this->validateDomainName($domain_name);

        $domain_already_exists = Domain::query()
            ->where("organisation_id", $org->id)
            ->where("name", $domain_name)
            ->exists();

        if ($domain_already_exists) {
            throw new BadRequestException("Domain name already exists");
        }

        [$dkim_private_key, $dkim_public_key] = $this->generateDkimKeys();

        $domain = Domain::create([
            "uuid" => (string) Str::uuid(),
            "name" => $domain_name,
            "organisation_id" => $org->id,
            "verification_token" => Str::random(60),
            "verified" => false,
            "dkim_private_key" => $dkim_private_key,
            "dkim_public_key" => $dkim_public_key,
        ]);

        $domain->refresh();

        return \response()->json($domain);
    }

    private function validateDomainName(string $domain_name): void
    {
        $suffix_list = Cache::remember(
            "public_suffix_list",
            86400,
            function (): array {
                $raw_data = \file_get_contents("https://publicsuffix.org/list/public_suffix_list.dat");

                return \collect(explode("\n", $raw_data))
                    ->map(fn (string $line): string => \trim($line))
                    ->filter(fn (string $line): bool => (
                        $line === "" || Str::startsWith($line, "//")
                    ));
            }
        );

        $valid_suffix = false;

        foreach ($suffix_list as $suffix) {
            if (Str::endsWith($domain_name, ".{$suffix}")) {
                $valid_suffix = true;
                break;
            }
        }

        if (!$valid_suffix) {
            throw new BadRequestException("Invalid hostname at #->properties:name");
        }
    }

    /**
     * Generates a new key pair to use for DKIM signing
     *
     * @return array
     * @phan-return array{string, string}
     */
    private function generateDkimKeys(): array
    {
        $openssl_key = \openssl_pkey_new([
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        \openssl_pkey_export($openssl_key, $private_key);

        $public_key = \openssl_pkey_get_details($openssl_key)["key"];

        return [
            \trim($private_key),
            \trim($public_key),
        ];
    }
}
