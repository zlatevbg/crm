<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->text('message');
            $table->enum('group', ['agent-contacts', 'clients', 'guests', 'mespil', 'ph'])->nullable();
            $table->text('status')->nullable();
            $table->text('projects')->nullable();
            $table->text('recipients')->nullable();
            $table->text('numbers')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms');
    }
}
