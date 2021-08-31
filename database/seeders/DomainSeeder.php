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
        // Create a domain we know the UUID of and that has valid verification
        // details in DNS on the first organisation.
        Domain::factory()->create([
            "uuid" => "a41dfeaa-0299-47be-a5e9-77447fe6bf9f",
            "organisation_id" => Organisation::first()->id,
            "name" => "should-never-send.email",
            "verified" => false,
            "verification_token" => "YqvdHfqoqaDWrbWbQSEfJjyKvAPHAyGb1jgs7BKECNS6LZy7tlWnKHqBR4nS",
        ]);

        foreach (Organisation::get() as $organisation) {
            for ($i = 0; $i < 2; ++$i) {
                Domain::factory()->create([
                    "organisation_id" => $organisation->id,
                ]);
            }
        }
    }
}
