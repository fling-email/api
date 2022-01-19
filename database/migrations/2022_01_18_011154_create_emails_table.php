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
            $table->string("email_address")->unique();
        });

        Schema::create("attachments", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("type");
            $table->bigInteger("size");
            $table->string("md5");
            $table->string("sha1");
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
        });

        Schema::create("email_recipients", function (Blueprint $table) {
            $table->id();
            $table->foreignId("recipient_id");
            $table->foreignId("email_id");
            $table->enum("type", ["to", "cc", "bcc"]);
        });

        Schema::create("email_attachments", function (Blueprint $table) {
            $table->id();
            $table->foreignId("email_id");
            $table->foreignId("attachment_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists("email_attachments");
        Schema::dropIfExists("email_recipients");
        Schema::dropIfExists("attachments");
        Schema::dropIfExists("recipients");
        Schema::dropIfExists("emails");
    }
}
