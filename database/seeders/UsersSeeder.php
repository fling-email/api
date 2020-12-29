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
        $organisation = Organisation::factory()->create();

        User::factory()->create([
            "username" => "test",
            "organisation_id" => $organisation->id,
        ]);
    }
}
