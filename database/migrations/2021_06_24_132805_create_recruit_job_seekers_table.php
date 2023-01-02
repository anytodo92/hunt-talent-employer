<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitJobSeekersTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruit_job_seekers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recruit_company_id')->comment('人材紹介会社ID');
            $table->unsignedBigInteger('recruit_company_user_id')->comment('人材紹介会社担当者');
            $table->unsignedInteger('status')->default(1)->comment('ステータス');
            $table->unsignedInteger('job_offer_count')->default(0)->comment('求人票送付数');
            $table->unsignedInteger('apply_count')->default(0)->comment('応募総数');
            $table->string('last_name', 100)->nullable()->comment('姓名（姓）');
            $table->string('first_name', 100)->nullable()->comment('姓名（名）');
            $table->string('last_name_kana', 100)->nullable()->comment('かな（姓）');
            $table->string('first_name_kana', 100)->nullable()->comment('かな（名）');
            $table->string('email', 100)->nullable()->comment('メールアドレス');
            $table->unsignedInteger('reminder_setting')->default(1)->comment('面談・面接前日の「リマインドメール」設定');
            $table->string('phone_number', 50)->nullable()->comment('電話番号');
            $table->unsignedInteger('prefecture_id')->nullable()->comment('現住所・都道府県');
            $table->string('address', 100)->nullable()->comment('現住所・住所');
            $table->string('closest_station', 100)->nullable()->comment('路線・最寄駅');
            $table->date('birthday')->nullable()->comment('生年月日');
            $table->unsignedInteger('age')->nullable()->comment('年齢');
            $table->unsignedInteger('sex')->nullable()->comment('性別');
            $table->unsignedInteger('final_education')->nullable()->comment('最終学歴');
            $table->unsignedInteger('working_company_number')->nullable()->comment('現在までの就業社数');
            $table->unsignedInteger('nationality')->nullable()->comment('国籍');
            $table->unsignedInteger('japanese_level')->nullable()->comment('日本語レベル');
            $table->unsignedInteger('english_level')->nullable()->comment('英語レベル');
            $table->unsignedInteger('chinese_level')->nullable()->comment('中国語レベル');
            $table->string('desired_job_category', 1000)->nullable()->comment('希望職種');
            $table->string('desired_industry', 1000)->nullable()->comment('希望業界');
            $table->string('experience_job_category', 1000)->nullable()->comment('経験職種');
            $table->string('experience_industry', 1000)->nullable()->comment('経験業界');
            $table->unsignedInteger('employment_status')->nullable()->comment('現在の雇用形態');
            $table->unsignedInteger('annual_income')->nullable()->comment('希望給与（年収）');
            $table->unsignedInteger('desired_income_type')->nullable()->comment('希望給与(タイプ)');
            $table->unsignedInteger('desired_income')->nullable()->comment('希望給与');
            $table->string('current_annual_income', 100)->nullable()->comment('現在年収');
            $table->string('suggested_working_place', 1000)->nullable()->comment('希望勤務地');
            $table->date('joining_date')->nullable()->comment('入社可能時期');
            $table->unsignedInteger('home_working')->nullable()->comment('在宅勤務');
            $table->unsignedInteger('feature_desired')->nullable()->comment('特徴・希望');
            $table->string('recommendation', 1000)->nullable()->comment('推薦文');
            $table->string('job_seeker_memo', 1000)->nullable()->comment('転職者メモ');
            $table->boolean('accepted')->default(true)->comment('同意');
            $table->unsignedInteger('register_type')->nullable()->comment('送客/自社登録');
            $table->date('workable_date')->nullable()->comment('勤務可能時期');

            $this->historyColumns($table);
            $table->foreign('recruit_company_id')->references('id')->on('recruit_companies');
            $table->foreign('recruit_company_user_id')->references('id')->on('recruit_company_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruit_job_seekers');
    }
}
