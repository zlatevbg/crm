<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statuses', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('parent')->nullable()->index();
            $table->string('name');
            $table->enum('action', ['viewing', 'deposit', 'promissory-payment', 'final-balance', 'one-time-payment', 'reserve', 'see', 'complete', 'no-show', 'future-viewing'])->nullable();
            $table->boolean('default')->nullable()->default(0);
            $table->boolean('conversion')->nullable()->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->softDeletes();

            $table->foreign('parent')->references('id')->on('statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('statuses');
    }
}
