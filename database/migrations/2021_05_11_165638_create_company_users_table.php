<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Traits\MigrationHistoryTrait;

class CreateCompanyUsersTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('ユーザーID');
            $table->unsignedBigInteger('company_id')->comment('会社ID');
            $table->string('name', 100)->comment('名前');
            $table->string('ip', 50)->nullable()->comment('IPアドレス');
            $table->string('phone_number', 50)->nullable()->comment('担当者電話番号');
            $this->historyColumns($table);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('company_id')->references('id')->on('companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_users');
    }
}
