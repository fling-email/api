<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class AddUserAndDomainPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table("permissions")->insert($this->appendCreatedNow([
            "name" => "view_users",
            "description" => "View user list",
        ]));

        DB::table("permissions")->insert($this->appendCreatedNow([
            "name" => "create_user",
            "description" => "Create new users",
        ]));

        DB::table("permissions")->insert($this->appendCreatedNow([
            "name" => "view_domains",
            "description" => "View domain list",
        ]));

        DB::table("permissions")->insert($this->appendCreatedNow([
            "name" => "create_domain",
            "description" => "Create new domains",
        ]));
    }

    /**
     * Helper function to append created_at and updated_at attributes with the current time to an array
     *
     * @param array $input The input fields
     * @phan-param array<string, mixed>
     *
     * @return array
     * @phan-return array<string, mixed>
     */
    private function appendCreatedNow(array $input): array
    {
        return \array_merge($input, [
            "created_at" => Date::now(),
            "updated_at" => Date::now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table("permissions")
            ->whereIn("name", [
                "view_users",
                "create_user",
                "view_domains",
                "create_domain",
            ])
            ->delete();;
    }
}
