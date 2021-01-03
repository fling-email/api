<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Organisation;
use Illuminate\Database\Seeder;

class OrganisationSeeder extends Seeder
{
    /**
     * Runs the organisation seeder
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 5; ++$i) {
            Organisation::factory()
                ->create();
        }
    }
}
