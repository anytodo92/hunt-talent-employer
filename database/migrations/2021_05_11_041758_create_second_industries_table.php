<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecondIndustriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('second_industries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('first_industry_id')->comment('産業１ ID');
            $table->string('name')->comment('産業名２');
            $table->timestamps();

            $table->foreign('first_industry_id')->references('id')->on('first_industries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('second_industries');
    }
}
