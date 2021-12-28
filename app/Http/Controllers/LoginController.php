<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginToken;
use App\Exceptions\ForbiddenException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public static ?string $method = "post";
    public static ?string $path = "/auth";
    public static bool $auth = false;
    public static bool $paginated = false;

    /**
     * Handles post requests to the /auth endpoint
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $this->validate($request, [
            "username" => ["required"],
            "password" => ["required"],
        ]);

        $user = User::query()
            ->where("username", $request->input("username"))
            ->first();

        if (!$this->checkPasswordHash($request->input("password"), $user)) {
            throw new ForbiddenException("Incorrect username or password");
        }

        $token = new LoginToken();

        $token->uuid = (string) Str::uuid();
        $token->user_id = $user->id;
        $token->expires_at = Date::now()->modify("+1 hour");
        $token->token = Str::random(60);

        $token->save();
        $token->refresh();

        return \response()->json($token);
    }

    /**
     * Checks a value against the users password hash. Also updates the hash
     * in the database if it's outdated.
     *
     * @param string $password The password to check
     * @param ?User $user The user to get the hash from
     *
     * @retuen boolean If the password matched the hash
     */
    private function checkPasswordHash(string $password, ?User $user): bool
    {
        // User should be found
        if ($user === null) {
            return false;
        }

        // Password should be right
        if (!Hash::check($password, $user->password_hash)) {
            return false;
        }

        // Account and organisation should be enabled
        if (!(bool) $user->enabled || !(bool) $user->organisation->enabled) {
            return false;
        }

        // Update the hash if required
        if (Hash::needsRehash($user->password_hash)) {
            $user->password_hash = Hash::make($password);
            $user->save();
        }

        return true;
    }
}
