<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('agent_id')->nullable()->index();
            $table->unsignedInteger('source_id')->nullable()->index();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->enum('gender', ['not-known', 'male', 'female', 'not-applicable'])->nullable(); // ISO/IEC 5218
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('passport')->nullable();
            $table->boolean('newsletters')->default(1);
            $table->boolean('sms')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('set null');
            $table->foreign('source_id')->references('id')->on('sources')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
