<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\User;
use App\Models\Domain;

class DomainPolicy extends Policy
{
    /**
     * Checks if a user is allowed to verify a domain
     *
     * @param User $user The user trying to verify the domain
     * @param Domain|null $domain The domain being verified
     *
     * @return Response
     */
    public function verify(User $user, ?Domain $domain): Response
    {
        $can_verify = $domain !== null
                   && $user->organisation_id === $domain->organisation_id;;

        return ($can_verify)
            ? Response::allow()
            : Response::deny("You do not have permission to verify this domain");
    }
}
