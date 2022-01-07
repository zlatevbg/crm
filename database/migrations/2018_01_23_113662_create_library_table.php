<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLibraryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('library', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('parent')->nullable()->index();
            $table->unsignedInteger('meta_id')->index();
            $table->unsignedInteger('model_id');
            $table->string('name')->nullable();
            $table->string('link')->nullable();
            $table->string('file')->nullable();
            $table->smallInteger('width')->unsigned()->nullable();
            $table->smallInteger('height')->unsigned()->nullable();
            $table->string('size')->nullable();
            $table->string('uuid')->nullable();
            $table->string('extension')->nullable();

            // $table->foreign('parent')->references('id')->on('library')->onDelete('cascade');
            $table->foreign('meta_id')->references('id')->on('meta')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('library');
    }
}
