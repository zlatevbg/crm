<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('source_id')->nullable()->index();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->enum('gender', ['not-known', 'male', 'female', 'not-applicable'])->nullable(); // ISO/IEC 5218
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->boolean('newsletters')->default(1);
            $table->unsignedInteger('project_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('source_id')->references('id')->on('sources')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guests');
    }
}
