<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginToken;
use App\Exceptions\ForbiddenException;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class DeleteUserController extends Controller
{
    public static ?string $method = "delete";
    public static ?string $path = "/users/{uuid}";

    /**
     * Handles delete requests to the user endpoint
     *
     * @param string $uuid The UUID of the user being deleted
     *
     * @return Response|JsonResponse
     */
    public function __invoke(string $uuid): Response|JsonResponse
    {
        $current_user = $this->request->user();
        $user = User::query()
            ->where("uuid", $uuid)
            ->first();

        $this->authorize("delete", [User::class, $user]);

        // Prevent people deleting all of the accounts on an organisation
        if ($user->id === $current_user->id) {
            throw new ForbiddenException("You cannot delete your own user");
        }

        $user->enabled = false;
        $user->save();

        $user->delete();

        LoginToken::query()
            ->where("user_id", $user->id)
            ->delete();

        return \response("", 204);
    }
}
