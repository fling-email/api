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

    /**
     * Validates the domain name from the request
     *
     * @param string $domain_name The name from the request
     *
     * @throws BadRequestException
     *
     * @return void
     */
    private function validateDomainName(string $domain_name): void
    {
        $suffix_list = $this->getSuffixList();

        $matched_suffix_rules = \collect([]);

        foreach ($suffix_list as $suffix) {
            if ($this->domainMatchesSuffix($domain_name, $suffix)) {
                $matched_suffix_rules->add($suffix);
            }
        }

        // Sort by rule priority
        $matched_suffix_rules = $matched_suffix_rules->sort(
            function (string $a, string $b): int {
                $a_is_exception = \substr($a, 0, 1) === "!";
                $b_is_exception = \substr($b, 0, 1) === "!";

                if ($a_is_exception && !$b_is_exception) {
                    return -1;
                }

                if ($b_is_exception && !$a_is_exception) {
                    return 1;
                }

                return \substr_count($b, ".") <=> \substr_count($a, ".");
            }
        );

        $prevailing_rule = $matched_suffix_rules->first();

        if ($prevailing_rule === null) {
            // no matches at all means a fully invalid domain
            throw new BadRequestException("Invalid hostname at #->properties:name");
        }

        if (\substr($prevailing_rule, 0, 1) === "!") {
            // Exception rule means invalid domain
            throw new BadRequestException("Invalid hostname at #->properties:name");
        }
    }

    /**
     * Gets the list of TLDs from the public suffix list, excluding private domains
     *
     * @return string[]
     */
    private function getSuffixList(): array
    {
        return Cache::remember(
            "public_suffix_list",
            86400,
            function (): array {
                $raw_data = \file_get_contents("https://publicsuffix.org/list/public_suffix_list.dat");

                return \collect(explode("\n", $raw_data))
                    ->map(fn (string $line): string => \trim($line))
                    ->takeUntil("// ===BEGIN PRIVATE DOMAINS===")
                    ->filter(fn (string $line): bool => (
                        $line !== "" && !Str::startsWith($line, "//")
                    ))
                    ->toArray();
            }
        );
    }

    /**
     * Checks if a domain matches a suffix from the public suffix list
     *
     * @param string $domain_name The domain to check
     * @param string $suffix The suffix to check against
     *
     * @return boolean
     */
    private function domainMatchesSuffix(string $domain_name, string $suffix): bool
    {
        $is_exception_rule = \substr($suffix, 0, 1) === "!";

        // Stop off the ! for exception rules so we can use the same parsing
        if ($is_exception_rule) {
            $suffix = \substr($suffix, 1);
        }

        // Ignore case
        $domain_name = \strtolower($domain_name);

        // Matching works one "part" (or label) at a time starting from the right
        $domain_labels = \array_reverse(\explode(".", $domain_name));
        $suffix_labels = \array_reverse(\explode(".", $suffix));

        // If the domain is shorter than the suffix it can't match
        if (\count($domain_labels) < \count($suffix_labels)) {
            return false;
        }

        $total_suffix_labels = \count($suffix_labels);

        $matched = true;

        for ($i = 0; $i < $total_suffix_labels; ++$i) {
            $domain_label = $domain_labels[$i];
            $suffix_label = $suffix_labels[$i];

            // Needs to be a wildcard or a exact match.
            if ($suffix_label !== "*" && $suffix_label !== $domain_label) {
                $matched = false;
                break;
            }
        }

        // Matched all if we got to the last itteration
        $checked_all = $total_suffix_labels === $i;

        return ($is_exception_rule)
            // Exeption rules need to be a full match
            ? $matched && $checked_all
            // Normal rules just need to not not match
            : $matched;
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
