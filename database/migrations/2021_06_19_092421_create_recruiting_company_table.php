<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitingCompanyTable extends Migration
{
	use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruiting_companies', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('company_id')->comment('会社ID');
			$table->string('name', 100)->comment('会社名');
			$table->unsignedInteger('prefecture')->comment('都道府県');
			$table->string('address', 300)->comment('市区町村・番地・建物名');
			$table->string('home_page', 100)->comment('会社HP');
			$table->unsignedInteger('industry1')->comment('業界1');
			$table->unsignedInteger('industry2')->comment('業界2');
			$table->unsignedInteger('employee_number')->comment('従業員数');
			$table->unsignedInteger('establish_year')->comment('設立年');
			$table->unsignedInteger('establish_month')->comment('設立月');
			$table->string('company_profile', 1000)->comment('会社概要');
			$table->string('business_content', 1000)->comment('事業内容');
			$table->string('logo', 100)->comment('企業ロゴ');
			$this->historyColumns($table);

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
        Schema::dropIfExists('recruiting_companies');
    }
}
