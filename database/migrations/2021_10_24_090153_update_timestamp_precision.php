<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        foreach ($this->tables as $table_name => $extra_columns) {
            $all_columns = \array_merge(
                ["created_at", "updated_at"],
                $extra_columns,
            );

            Schema::table(
                $table_name,
                function (Blueprint $table) use ($precision): void {
                    foreach ($all_columns as $column_name) {
                        $table->timestamp($column_name, $precision);
                    }
                }
            );
        }
    }
}
