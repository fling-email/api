<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organisation;
use App\Models\Domain;

class DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Organisation::get() as $organisation) {
            for ($i = 0; $i < 2; ++$i) {
                Domain::factory()->create([
                    "organisation_id" => $organisation->id,
                ]);
            }
        }
    }
}
