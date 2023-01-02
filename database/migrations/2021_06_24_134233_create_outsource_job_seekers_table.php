<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutsourceJobSeekersTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outsource_job_seekers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outsource_company_id')->comment('業務委託会社ID');
            $table->unsignedBigInteger('outsource_company_user_id')->comment('業務委託会社担当者ID');
            $table->unsignedInteger('status')->default(1)->comment('ステータス');
            $table->unsignedInteger('job_offer_count')->default(0)->comment('案件票送付数');
            $table->unsignedInteger('apply_count')->default(0)->comment('エントリー総数');
            $table->string('initial', 100)->nullable()->comment('イニシャル');
            $table->string('last_name', 100)->nullable()->comment('姓名（姓）');
            $table->string('first_name', 100)->nullable()->comment('姓名（名）');
            $table->string('last_name_kana', 100)->nullable()->comment('かな（姓）');
            $table->string('first_name_kana', 100)->nullable()->comment('かな（名）');
            $table->string('email', 100)->nullable()->comment('メールアドレス');
            $table->string('phone_number', 50)->nullable()->comment('電話番号');
            $table->unsignedInteger('prefecture_id')->nullable()->comment('現住所・都道府県');
            $table->string('closest_station', 100)->nullable()->comment('路線・最寄駅');
            $table->unsignedInteger('reminder_setting')->default(1)->comment('面談・面接前日の「リマインドメール」設定');
            $table->date('birthday')->nullable()->comment('生年月日');
            $table->unsignedInteger('age')->nullable()->comment('年齢');
            $table->unsignedInteger('sex')->nullable()->comment('性別');
            $table->unsignedInteger('nationality')->nullable()->comment('国籍');
            $table->unsignedInteger('japanese_level')->nullable()->comment('日本語レベル');
            $table->unsignedInteger('english_level')->nullable()->comment('英語レベル');
            $table->unsignedInteger('chinese_level')->nullable()->comment('中国語レベル');
            $table->string('desired_job_category', 1000)->nullable()->comment('希望職種');
            $table->string('experience_job_category', 1000)->nullable()->comment('経験職種');
            $table->string('experience_industry', 1000)->nullable()->comment('経験業界');
            $table->unsignedInteger('experience_phase')->nullable()->comment('経験担当工程');
            $table->unsignedInteger('skill')->nullable()->comment('スキル');
            $table->string('skill_detail_comment', 3000)->nullable()->comment('スキル詳細コメント');
            $table->unsignedInteger('desired_unit_price_type')->nullable()->comment('希望単価(タイプ)');
            $table->unsignedInteger('desired_unit_price')->nullable()->comment('希望単価(金額)');
            $table->unsignedInteger('desired_possible_operating_days_weeks')->nullable()->comment('希望の稼働日数/週');
            $table->string('notice_other_desired_condition', 3000)->nullable()->comment('特記事項（その他希望条件）');
            $table->unsignedInteger('resident_remote_desired')->nullable()->comment('常駐・リモート希望');
            $table->string('remote_desired_detail', 1000)->nullable()->comment('リモート希望詳細');
            $table->string('suggested_working_place', 1000)->nullable()->comment('希望勤務地');
            $table->unsignedInteger('home_working')->nullable()->comment('在宅勤務');
            $table->unsignedInteger('passive_smoking_solution')->nullable()->comment('受動喫煙対策');
            $table->date('working_possible_timing')->nullable()->comment('稼働可能時期');
            $table->string('recommendation_statement', 1000)->nullable()->comment('推薦文');
            $table->string('joining_person_memo', 1000)->nullable()->comment('参画者メモ');
            $table->boolean('accepted')->default(true)->comment('同意');
            $table->unsignedInteger('register_type')->nullable()->comment('送客/自社登録');

//            $table->date('operating_start_date')->nullable()->comment('稼働開始日');
//            $table->string('possible_operating_days_weeks', 100)->nullable()->comment('可能稼働日数/週');

//            $table->string('pr_comment_from_agent', 100)->nullable()->comment('エージェントからのPRコメント');
//            $table->string('exp_skill_experience_job_category', 100)->nullable()->comment('経験・スキル（経験職種）');
//            $table->string('exp_skill_industry_experience', 100)->nullable()->comment('経験・スキル（業界経験）');
//            $table->string('exp_skill_language', 100)->nullable()->comment('経験・スキル（言語）');
//            $table->string('exp_skill_framework', 100)->nullable()->comment('経験・スキル（フレームワーク）');
//            $table->string('exp_skill_tool', 100)->nullable()->comment('経験・スキル（ツール）');
//            $table->string('exp_skill_responsible_process', 100)->nullable()->comment('経験・スキル（担当工程）');
//            $table->unsignedInteger('proposal_unit_price')->nullable()->comment('提案単価');

            $this->historyColumns($table);
            $table->foreign('outsource_company_id')->references('id')->on('outsource_companies');
            $table->foreign('outsource_company_user_id')->references('id')->on('outsource_company_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outsource_job_seekers');
    }
}
