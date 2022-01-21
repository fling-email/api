<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Exceptions\BadRequestException;

class CreateUserController extends Controller
{
    public static ?string $method = "post";
    public static ?string $path = "/users";
    public static bool $paginated = false;

    /**
     * Handles requests to create new user accounts
     *
     * @param Request $request The request
     *
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->authorize("create", [User::class]);

        $username_already_used = User::query()
            ->where("username", $request->json("username"))
            ->exists();

        $email_address_already_used = User::query()
            ->where("email_address", $request->json("email_address"))
            ->exists();

        if ($username_already_used) {
            throw new BadRequestException("Username already in use");
        }

        if ($email_address_already_used) {
            throw new BadRequestException("Email address already in use");
        }

        $new_user = new User();

        $new_user->name = $request->json("name");
        $new_user->username = $request->json("username");
        $new_user->email_address = $request->json("email_address");

        $new_user->password_hash = ($request->has("password"))
            ? Hash::make($request->json("password"))
            : "";

        $new_user->organisation_id = $user->organisation_id;
        $new_user->uuid = (string) Str::uuid();
        $new_user->enabled = true;
        $new_user->activated = false;
        $new_user->activation_token = Str::random(60);

        $new_user->save();

        $new_user->refresh();

        return \response()->json($new_user, 201);
    }
}
