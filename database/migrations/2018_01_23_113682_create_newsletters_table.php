<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewslettersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('subject');
            $table->string('teaser')->nullable();
            $table->enum('group', ['agent-contacts', 'clients', 'investors', 'mespil', 'ph', 'guests', 'gvcontacts', 'rental-contacts', 'pgv'])->nullable();
            $table->text('source')->nullable();
            $table->boolean('goldenvisa')->nullable();
            $table->text('status')->nullable();
            $table->text('projects')->nullable();
            $table->text('recipients')->nullable();
            $table->unsignedTinyInteger('include_team')->default(0);
            $table->string('template')->nullable();
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
        Schema::dropIfExists('newsletters');
    }
}
