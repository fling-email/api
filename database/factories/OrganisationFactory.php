<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrganisationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     * @phan-var class-string<Model>
     */
    protected $model = Organisation::class;

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
            "name" => $this->faker->company,
            "enabled" => true,
        ];
    }
}
