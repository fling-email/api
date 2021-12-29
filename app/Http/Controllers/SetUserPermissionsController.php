<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use App\Exceptions\ForbiddenException;
use App\Models\User;
use App\Models\Permission;

class SetUserPermissionsController extends Controller
{
    public static ?string $method = "put";
    public static ?string $path = "/users/{uuid}/permissions";

    /**
     * Handles requests to set a users permissions
     *
     * @param string $uuid The UUID of the user
     *
     * @return Response
     */
    public function __invoke(string $uuid): Response
    {
        $user = User::query()
            ->where("uuid", $uuid)
            ->first();

        $this->authorize("editPermissions", [User::class, $user]);

        // This is mostly to make sure Phan knows the type for $user the call to
        // $this->authorize() should make sure $user is not null so it makes
        // sense to do a 500 error if that's somehow not the case here.
        if (!$user instanceof User) {
            throw new \UnexpectedValueException("User was not found after authorisation");
        }

        // Permissions being set on the user account
        $desired_permissions = \collect($this->request->json()->all());

        // Existing permissions of the user
        $current_permissions = $user->getPermissions()->map(
            fn (Permission $permission): string => $permission->name
        );

        // Permissions of the user trying to do the update
        $user_permissions = $this->getRequestUser()->getPermissionNames();

        // Any permissions trying to be granted that the user does not have
        $missing_permissions = $desired_permissions->diff($user_permissions);

        if (!$missing_permissions->isEmpty()) {
            throw new ForbiddenException(
                "You cannot grant permissiosn that you do not have yourself"
            );
        }

        $added_permissions = $desired_permissions->diff($current_permissions);
        $removed_permissions = $current_permissions->diff($desired_permissions);

        foreach ($added_permissions as $permission) {
            $user->grantPermission($permission);
        }

        foreach ($removed_permissions as $permission) {
            $user->revokePermission($permission);
        }

        return \response("", 201);
    }
}
