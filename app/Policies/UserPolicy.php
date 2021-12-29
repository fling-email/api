<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use App\Models\User;

class UserPolicy extends Policy
{
    /**
     * Checks if a user is allowed to view other users permissions
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

    /**
     * Checks if a user is allowed to update other users permissions
     *
     * @param User $current_user The current user
     * @param ?User $user The user beining viewed
     *
     * @return Response
     */
    public function editPermissions(User $current_user, ?User $user): Response
    {
        $can_edit = $user !== null
            && $current_user->id !== $user->id
            && $current_user->organisation_id === $user->organisation_id
            && $current_user->hasPermission("edit_user_permissions");

        return ($can_edit)
            ? Response::allow()
            : Response::deny("You do not have permissions to edit user permissions");
    }

    /**
     * Checks if a user is allowed to view details of a user
     *
     * @param User $current_user The authenticated user
     * @param ?User $user The user being viewed
     *
     * @return Response
     */
    public function view(User $current_user, ?User $user): Response
    {
        $can_view = $user !== null
            && $current_user->organisation_id === $user->organisation_id
            && ($current_user->id === $user->id
            || $current_user->hasPermission("view_user"));

        return ($can_view)
            ? Response::allow()
            : Response::deny("You do not have permission to view this user");
    }

    /**
     * Checks if a user is allowd to create new user accounts
     *
     * @param User $current_user The user trying to create the account
     *
     * @return Response
     */
    public function create(User $current_user): Response
    {
        return ($current_user->hasPermission("create_user"))
            ? Response::allow()
            : Response::deny("You do not have permission to create new users");
    }

    /**
     * Checks if a user is allowed to update user account details
     *
     * @param User $current_user The user trying to perform the update
     * @param ?User $user The user being updated
     *
     * @return Response
     */
    public function edit(User $current_user, ?User $user): Response
    {
        $can_edit = $user !== null
            && $current_user->organisation_id === $user->organisation_id
            && $current_user->hasPermission("update_user");

        return ($can_edit)
            ? Response::allow()
            : Response::deny("You do not have permission to update this user");
    }

    /**
     * Checks if a user is allowed to delete user accounts
     *
     * @param User $current_user The user trying to delete the account
     * @param ?User $user The user being deleted
     *
     * @return Response
     */
    public function delete(User $current_user, ?User $user): Response
    {
        $can_delete = $user !== null
            && $current_user->organisation_id === $user->organisation_id
            && $current_user->hasPermission("delete_user");

        return ($can_delete)
            ? Response::allow()
            : Response::deny("You do not have permission to delete this user");
    }
}
