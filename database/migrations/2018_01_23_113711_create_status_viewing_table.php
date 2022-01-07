<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusViewingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_viewing', function (Blueprint $table) {
            $table->unsignedInteger('viewing_id')->nullable()->index();
            $table->unsignedInteger('status_id')->nullable()->index();

            $table->foreign('viewing_id')->references('id')->on('viewings')->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('status_viewing');
    }
}
