<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Exceptions\BadRequestException;
use App\Models\Domain;

class VerifyDomainController extends Controller
{
    public static ?string $method = "post";
    public static ?string $path = "/domains/{uuid}/verify";
    public static bool $paginated = false;

    /**
     * Handles requests for the /domains/{uuid}/verify endpoint
     *
     * @param Request $request The request
     * @param string $uuid The UUID of the domain
     *
     * @return Response|JsonResponse
     */
    public function __invoke(Request $request, string $uuid): Response|JsonResponse
    {
        $user = $request->user();

        $domain = Domain::query()->where("uuid", $uuid)->first();

        $this->authorize("verify", [Domain::class, $domain]);

        if ((bool) $domain->verified) {
            throw new BadRequestException("Domain is already verified");
        }

        $verification_success = $this->getDnsTokens($domain)
                                     ->contains($domain->verification_token);

        if (!$verification_success) {
            throw new BadRequestException("Domain could not be verified, check DNS records");
        }

        $domain->verified = true;
        $domain->save();

        return \response("", 201);
    }

    /**
     * Gets a list of existing verification tokens from DNS
     *
     * @param Domain $domain The domain to get the tokens for
     *
     * @return Collection
     * @phan-return Collection<string>
     */
    private function getDnsTokens(Domain $domain): Collection
    {
        $token_domain = "fling-verification.{$domain->name}";

        $txt_records = \dns_get_record($token_domain, \DNS_TXT);

        if ($txt_records === false) {
            return \collect([]);
        }

        return \collect($txt_records)
            ->map(
                /** @phan-param array{txt: string} $dns_result */
                fn (array $dns_result): string => $dns_result["txt"]
            );
    }
}
