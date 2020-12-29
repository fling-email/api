<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("organisations", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string("uuid")->unique();
            $table->string("name");
            $table->boolean("enabled");
        });

        Schema::create("users", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId("organisation_id");
            $table->string("uuid")->unique();
            $table->string("name");
            $table->string("username")->unique();
            $table->string("email_address")->unique();
            $table->string("password_hash");
            $table->boolean("enabled");
        });

        Schema::create("login_tokens", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("uuid")->unique();
            $table->dateTime("expires_at");
            $table->foreignId("user_id");
            $table->string("token")->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("organisations");
        Schema::dropIfExists("users");
        Schema::dropIfExists("login_tokens");
    }
}
