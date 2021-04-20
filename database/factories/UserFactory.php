<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     * @phan-var class-string<Model>
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @phan-return array<string, mixed>
     */
    public function definition(): array
    {
        $created_at = $this->faker->dateTimeInInterval("-1 year", "-2 weeks");

        return [
            "created_at" => $created_at,
            "updated_at" => $created_at,
            "deleted_at" => null,
            "uuid" => $this->faker->uuid,
            "name" => $this->faker->name,
            "username" => $this->faker->unique()->username,
            "email_address" => $this->faker->unique()->safeEmail,
            "password_hash" => Hash::make("secret"),
            "enabled" => true,
            "email_address_verified" => true,
            "email_address_verification_token" => $this->faker->regexify("[a-zA-Z0-9]{60}"),
        ];
    }
}
