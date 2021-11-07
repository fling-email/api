<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateTimestampPrecision extends Migration
{
    private $tables = [
        "organisations" => [],
        "users" => [],
        "login_tokens" => [
            "expires_at",
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->setPrecision(6);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->setPrecision(0);
    }

    /**
     * Sets the precision of any timestamp fields
     *
     * @param integer $precision The desired precision
     *
     * @return void
     */
    private function setPrecision(int $precision): void
    {
        Schema::table("organisations", function (Blueprint $table) use ($precision) {
            $table->timestamp("created_at", $precision)->change();
            $table->timestamp("updated_at", $precision)->change();
            $table->timestamp("deleted_at", $precision)->change();
        });

        Schema::table("users", function (Blueprint $table) use ($precision) {
            $table->timestamp("created_at", $precision)->change();
            $table->timestamp("updated_at", $precision)->change();
            $table->timestamp("deleted_at", $precision)->change();
        });

        Schema::table("login_tokens", function (Blueprint $table) use ($precision) {
            $table->timestamp("created_at", $precision)->change();
            $table->timestamp("updated_at", $precision)->change();
        });

        // $table->dateTime()-> change doesn't recognise the change in precision as something to do
        DB::statement("ALTER TABLE login_tokens MODIFY COLUMN expires_at DATETIME({$precision}) NOT NULL");
    }
}
