<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGvcontactNewsletterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gvcontact_newsletter', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('newsletter_id')->index()->nullable();
            $table->unsignedInteger('gvcontact_id')->index()->nullable();

            $table->foreign('newsletter_id')->references('id')->on('newsletters')->onDelete('cascade');
            $table->foreign('gvcontact_id')->references('id')->on('gvcontacts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gvcontact_newsletter');
    }
}
