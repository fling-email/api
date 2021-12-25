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
        $test_user = $this->createUser([
            "username" => "test",
            "organisation_id" => Organisation::first()->id
        ]);

        $this->setupTestUser($test_user);

        // Create 10 random users on all organisations
        foreach (Organisation::get() as $organisation) {
            for ($i = 0; $i < 10; ++$i) {
                $this->createUser([
                    "organisation_id" => $organisation->id,
                ]);
            }
        }
    }

    /**
     * Creates a new user and assignes a standard set of permissions
     *
     * @param array $attributes attributes for the new user
     * @phan-param array<string, mixed> $attributes
     *
     * @return User
     */
    private function createUser(array $attributes): User
    {
        $user = User::factoryCreate($attributes);

        $user->grantPermission("view_users");

        return $user;
    }

    /**
     * Grants additional permissions to the test user account
     *
     * @param User $user The test user
     *
     * @return void
     */
    private function setupTestUser(User $user): void
    {
        $user->grantPermission("create_user");
    }
}
