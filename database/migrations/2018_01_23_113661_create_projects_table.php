<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('name');
            $table->string('location');
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->unsignedDecimal('price', 11, 2)->nullable();
            $table->unsignedDecimal('site_area', 8, 2)->nullable();
            $table->unsignedDecimal('construction_area', 8, 2)->nullable();
            $table->unsignedDecimal('gdv', 11, 2)->nullable();
            $table->unsignedDecimal('equity', 11, 2)->nullable();
            $table->unsignedDecimal('bank', 11, 2)->nullable();
            $table->string('period')->nullable();
            $table->string('irr')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('contact_id')->nullable()->index();
            $table->unsignedTinyInteger('status')->default(0);
            $table->softDeletes();

            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
