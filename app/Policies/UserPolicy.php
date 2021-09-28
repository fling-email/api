<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\User;

class UserPolicy extends Policy
{
    /**
     * Checks if a user is allowed to yew other users permissions
     *
     * @param User $current_user The current user
     * @param ?User $user The user being viewed
     *
     * @return Response
     */
    public function viewPermissions(User $current_user, ?User $user): Response
    {
        $can_view = $user !== null
            && $current_user->organisation_id === $user->organisation_id
            && ($current_user->id === $user->id
            || $current_user->hasPermission("view_user_permissions"));

        return ($can_view)
            ? Response::allow()
            : Response::deny("You do not have permission to view user permissions");
    }
}
