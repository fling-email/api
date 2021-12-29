<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Domain;
use App\Utils\GeneratesDkimKeys;

class DomainFactory extends Factory
{
    use GeneratesDkimKeys;

    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     * @phan-var class-string<Model>
     */
    protected $model = Domain::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @phan-return array<string, mixed>
     */
    public function definition(): array
    {
        $created_at = $this->faker->dateTimeInInterval("-1 year", "-2 weeks");
        [$dkim_private_key, $dkim_public_key] = $this->generateDkimKeys();

        return [
            "created_at" => $created_at,
            "updated_at" => $created_at,
            "deleted_at" => null,
            "uuid" => $this->faker->uuid,
            "name" => $this->faker->domainName,
            "verification_token" => $this->faker->regexify("[a-zA-Z0-9]{60}"),
            "verified" => true,
            "dkim_private_key" => $dkim_private_key,
            "dkim_public_key" => $dkim_public_key,
        ];
    }
}
