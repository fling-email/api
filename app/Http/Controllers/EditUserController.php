<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginToken;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

class EditUserController extends Controller
{
    public static ?string $method = "patch";
    public static ?string $path = "/users/{uuid}";

    /**
     * __invoke
     *
     * @param string $uuid
     */
    public function __invoke(string $uuid): Response|JsonResponse
    {
        $user = User::query()
            ->where("uuid", $uuid)
            ->first();

        $properties = $this->getEditableProperties();

        $this->authorize("edit", [User::class, $user]);

        foreach ($properties as $property) {
            if ($this->request->has($property)) {
                $user->{$property} = $this->request->json($property);
            }
        }

        // Invalidate all tokens/sessions when user accounts are disabled
        if ($this->request->has("enabled") && !(bool) $this->request->json("enabled")) {
            LoginToken::query()
                ->where("user_id", $user->id)
                ->delete();
        }

        $user->save();
        $user->refresh();

        return \response()->json($user);
    }
}
