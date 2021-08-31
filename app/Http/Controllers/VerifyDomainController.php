<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exceptions\ForbiddenException;
use App\Models\Domain;

class VerifyDomainController extends Controller
{
    public static ?string $method = "post";
    public static ?string $path = "/domains/{uuid}/verify";

    /**
     * Handles requests for the /domains/{uuid}/verify endpoint
     *
     * @param Request $request The request
     * @param string $uuid The UUID of the domain
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request, string $uuid): JsonResponse
    {
        $user = $request->user();

        $domain = Domain::where("uuid", $uuid)->first();

        // TODO
        // $this->authorize("verify", [Domain::class, $domain]);

        $dns_tokens = $this->getDnsTokens($domain);

        \dd($dns_tokens);

        //

        return \response()->json([]);
    }

    /**
     * Gets a list of existing verification tokens from DNS
     *
     * @param Domain $domain The domain to get the tokens for
     *
     * @return array[]
     */
    private function getDnsTokens(Domain $domain): array
    {
        $token_domain = "fling-verification.{$domain->name}";

        $txt_records = \dns_get_record($token_domain, \DNS_TXT);

        if ($txt_records === false) {
            return [];
        }

        return \array_map(
            fn (array $dns_result): string => $dns_result["txt"],
            $txt_records,
        );
    }
}
