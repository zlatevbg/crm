<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apartments', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('unit');
            $table->unsignedDecimal('price', 9, 2)->nullable();
            $table->unsignedDecimal('apartment_area', 6, 2)->nullable();
            $table->unsignedDecimal('balcony_area', 6, 2)->nullable();
            $table->unsignedDecimal('parking_area', 6, 2)->nullable();
            $table->unsignedDecimal('common_area', 6, 2)->nullable();
            $table->unsignedDecimal('total_area', 6, 2)->nullable();
            $table->unsignedInteger('project_id')->index();
            $table->unsignedInteger('block_id')->index()->nullable();
            $table->unsignedInteger('floor_id')->index()->nullable();
            $table->unsignedInteger('bed_id')->index()->nullable();
            $table->unsignedInteger('view_id')->index()->nullable();
            $table->unsignedInteger('furniture_id')->index()->nullable();
            $table->boolean('reports')->default(1);
            $table->boolean('public')->default(1);
            $table->softDeletes();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('block_id')->references('id')->on('blocks')->onDelete('set null');
            $table->foreign('floor_id')->references('id')->on('floors')->onDelete('set null');
            $table->foreign('bed_id')->references('id')->on('beds')->onDelete('set null');
            $table->foreign('view_id')->references('id')->on('views')->onDelete('set null');
            $table->foreign('furniture_id')->references('id')->on('furnitures')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apartments');
    }
}
