<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameEmailVerificationColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->renameColumn(
                "email_address_verified",
                "activated",
            );

            $table->renameColumn(
                "email_address_verification_token",
                "activation_token",
            );
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("users", function (Blueprint $table): void {
            $table->renameColumn(
                "activated",
                "email_address_verified",
            );

            $table->renameColumn(
                "activation_token",
                "email_address_verification_token",
            );
        });
    }
}
