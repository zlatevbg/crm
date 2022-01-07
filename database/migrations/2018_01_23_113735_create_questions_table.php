<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('parent')->nullable()->index();
            $table->string('name')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->text('content')->nullable();
            $table->boolean('featured')->nullable()->default(0);
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('website_id')->index();

            $table->foreign('website_id')->references('id')->on('websites')->onDelete('cascade');
            $table->foreign('parent')->references('id')->on('questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
