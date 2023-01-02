<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutsourceCompanyUsersTable extends Migration
{
	use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outsource_company_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('ユーザーID');
            $table->unsignedBigInteger('outsource_company_id')->comment('業務委託会社ID');
            $table->string('name', 100)->comment('名前');
            $table->string('logo', 255)->nullable()->comment('ロゴ');
            $this->historyColumns($table);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('outsource_company_id')->references('id')->on('outsource_companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::dropIfExists('outsource_company_users');
    }
}
