<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitCompaniesTable extends Migration
{
	use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruit_companies', function (Blueprint $table) {
            $table->id();
			$table->string('name', 100)->comment('会社名');
			$table->string('logo', 255)->comment('ロゴ');
			$table->string('email', 255)->comment('通知用メールアドレス');
			$this->historyColumns($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Schema::dropIfExists('recruit_companies');
    }
}
