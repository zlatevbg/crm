<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvestorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('investors', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fund_size_id')->nullable()->index();
            $table->unsignedInteger('investment_range_id')->nullable()->index();
            $table->unsignedInteger('source_id')->nullable()->index();
            $table->unsignedInteger('category_id')->nullable()->index();
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone_code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->enum('gender', ['not-known', 'male', 'female', 'not-applicable'])->nullable(); // ISO/IEC 5218
            $table->unsignedInteger('country_id')->nullable()->index();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('bank')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_phone')->nullable();
            $table->string('website')->nullable();
            $table->boolean('newsletters')->default(1);
            $table->boolean('sms')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('fund_size_id')->references('id')->on('fund_size')->onDelete('set null');
            $table->foreign('investment_range_id')->references('id')->on('investment_range')->onDelete('set null');
            $table->foreign('source_id')->references('id')->on('sources')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
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
        Schema::dropIfExists('investors');
    }
}
