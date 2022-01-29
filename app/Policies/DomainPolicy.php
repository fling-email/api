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
            && $user->organisation_id === $domain->organisation_id
            && $user->hasPermission("verify_domain");

        return ($can_verify)
            ? Response::allow()
            : Response::deny("You do not have permission to verify this domain");
    }

    /**
     * Checks if a user is allowed to delete a domain
     *
     * @param User $user The user trying to delete the domain
     * @param ?Domain $domain The domain being delete
     *
     * @return Response
     */
    public function delete(User $user, ?Domain $domain): Response
    {
        $can_delete = $domain !== null
            && $user->organisation_id === $domain->organisation_id
            && $user->hasPermission("delete_domain");

        return ($can_delete)
            ? Response::allow()
            : Response::deny("You do not have permission to delete this domain");
    }

    /**
     * Checks if a user is allowed to send emails from users at a domain
     *
     * @param User $user The user trying to send the email
     * @param ?Domain $domain The domain the email is being sent from
     *
     * @return Response
     */
    public function sendEmail(User $user, ?Domain $domain): Response
    {
        $can_send = $domain !== null
            && $user->organisation_id === $domain->organisation_id
            && $user->hasPermission("send_email");

        return ($can_send)
            ? Response::allow()
            : Response::deny("You do not have permission to send emails from this domain");
    }
}
