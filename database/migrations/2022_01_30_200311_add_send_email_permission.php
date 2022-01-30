<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class AddSendEmailPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table("permissions")->insert([
            "created_at" => Date::now(),
            "updated_at" => Date::now(),
            "name" => "send_email",
            "description" => "send emails from one of the organisations domaisn",
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
            ->whereIn("name", "send_email")
            ->delete();
    }
}
