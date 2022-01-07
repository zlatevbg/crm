<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('apartment_id');
            $table->unsignedInteger('client_id');
            $table->unsignedInteger('agent_id')->nullable();
            $table->unsignedInteger('subagent_id')->nullable();
            $table->timestamp('closing_at')->nullable();
            $table->timestamp('promissory_at')->nullable();
            $table->unsignedDecimal('price', 9, 2)->default('0.00');
            $table->unsignedDecimal('furniture', 7, 2)->default('0.00');
            $table->unsignedDecimal('commission', 9, 2)->default('0.00');
            $table->unsignedDecimal('sub_commission', 9, 2)->default('0.00');
            $table->text('description')->nullable();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('apartment_id')->references('id')->on('apartments')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade');
            $table->foreign('subagent_id')->references('id')->on('agents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales');
    }
}
