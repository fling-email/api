<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInitialPermissions extends Migration
{
    /**
     * @var array
     * @phan-var array<string, string>
     */
    private array $new_permissions = [
        "create_user" => "create new user accounts",
        "view_user" => "view user accounts (users can always view their own details)",
        "update_user" => "update user account details",
        "delete_user" => "remove user accounts",
        "block_user" => "block/unblock user account access",
        "reset_user_security" => "set user account security details",
        "view_user_permissions" => "view permissions granted to a user",
        "grant_user_permissions" => "grant a user permissions (users cannot grant permissions that they do not have)",

        "create_domain" => "add a new domain name",
        "view_domain" => "view organisation domains",
        "update_domain" => "update organisation domains",
        "delete_domain" => "remove organisation domains",
        "verify_domain" => "verify a domain name",

        "create_app" => "create new email apps",
        "view_app" => "view app details",
        "update_app" => "update app details",
        "delete_app" => "remove an app",
        "reset_app_security" => "reset app access tokens",
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->new_permissions as $name => $description) {
            DB::insert("permissions", [
                "created_at" => new \DateTime(),
                "update_at" => new \DateTime(),
                "name" => $name,
                "description" => $description,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permission_names = \array_keys($this->new_permissions);

        DB::table("permissions")
            ->whereIn("name", $permission_names)
            ->delete();
    }
}
