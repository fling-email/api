<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\Organisation;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Runs the seeder
     *
     * @return void
     */
    public function run()
    {
        // Create a user we know the login details for on the first organisation
        User::factory()->create([
            "username" => "test",
            "organisation_id" => Organisation::first()->id
        ]);

        // Create 10 random users on all organisations
        foreach (Organisation::get() as $organisation) {
            for ($i = 0; $i < 10; ++$i) {
                User::factory()->create([
                    "organisation_id" => $organisation->id,
                ]);
            }
        }
    }
}
