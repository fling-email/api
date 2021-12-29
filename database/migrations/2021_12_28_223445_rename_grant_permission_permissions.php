<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameGrantPermissionPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->renamePermission(
            "grant_user_permissions",
            "edit_user_permissions",
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->renamePermission(
            "edit_user_permissions",
            "grant_user_permissions",
        );
    }

    /**
     * Renames a permission in the database
     *
     * @param string $old_name The current name of the permission
     * @param string $new_name The new name for the permission
     *
     * @return void
     */
    private function renamePermission(string $old_name, string $new_name): void
    {
        DB::table("permissions")
            ->where("name", $old_name)
            ->update(["name" => $new_name]);
    }
}
