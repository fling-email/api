<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("recipients", function (Blueprint $table) {
            $table->id();
            $table->string("email_address");
        });

        Schema::create("email_recipients", function (Blueprint $table) {
            $table->id();
            $table->foreignId("recipient_id");
            $table->foreignId("email_id");
            $table->enum("type", ["to", "cc", "bcc"]);
        });

        Schema::create("attachments", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("type");
            $table->bigInteger("size");
        });

        DB::statement("ALTER TABLE attachments ADD data LONGBLOB");

        Schema::create("emails", function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("from_name");
            $table->string("from_email");
            $table->string("subject");
            $table->longText("message_plain");
            $table->longText("message_html");
            $table->longText("message_mjml");

            // attachments
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("emails");
        Schema::dropIfExists("attachments");
        Schema::dropIfExists("email_recipients");
        Schema::dropIfExists("recipients");
    }
}
