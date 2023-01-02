<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutsourceOfferInfosTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outsource_offer_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->comment('会社ID');
            $table->unsignedBigInteger('recruiting_company_id')->comment('募集企業ID');
            $table->unsignedBigInteger('outsource_offer_info_id')->nullable()->comment('業務委託_求人情報ID（※検討中のカラム）');
            $table->string('recruit_occupation', 100)->comment('募集職種名');
            $table->unsignedInteger('open_status')->comment('公開状況');
            $table->string('job_title', 200)->comment('求人タイトル');
            $table->string('image_main', 100)->comment('画像（メイン）');
            $table->string('image_sub_1', 100)->nullable()->comment('画像（サブ1）');
            $table->string('image_sub_2', 100)->nullable()->comment('画像（サブ2）');
            $table->string('image_sub_3', 100)->nullable()->comment('画像（サブ3）');
            $table->string('image_sub_4', 100)->nullable()->comment('画像（サブ4）');
            $table->string('image_sub_5', 100)->nullable()->comment('画像（サブ5）');
            $table->unsignedInteger('marketing_use')->nullable()->comment('集客利用の可否');

            $table->date('recruit_period')->comment('募集期間');
            $table->unsignedInteger('occupation_category_1')->comment('職種カテゴリー1');
            $table->unsignedInteger('occupation_category_2')->comment('職種カテゴリー2');

            $table->string('requirements_skills_languages', 1000)->comment('必須要件・スキル（言語）');
            $table->string('requirements_skills_framework', 1000)->comment('必須要件・スキル（フレームワーク）');
            $table->string('requirements_skills_tool', 1000)->comment('必須要件・スキル（ツール）');
            $table->string('requirements_skills_detail', 1000)->comment('必須要件・スキル詳細');
            $table->string('welcome_skills_langages', 1000)->nullable()->comment('歓迎スキル（言語）');
            $table->string('welcome_skills_framework', 1000)->nullable()->comment('歓迎スキル（フレームワーク）');
            $table->string('welcome_skills_tool', 1000)->nullable()->comment('歓迎スキル（ツール）');
            $table->string('welcome_skills_detail', 1000)->nullable()->comment('歓迎スキル詳細');
            $table->string('phase', 100)->nullable()->comment('担当工程');

            $table->unsignedInteger('apply_age_from')->comment('応募可能年齢_開始');
            $table->unsignedInteger('apply_age_to')->comment('応募可能年齢_終了');
            $table->unsignedInteger('sex')->comment('性別');
            $table->unsignedInteger('foreign_nationality')->comment('外国籍の可否');
            $table->unsignedInteger('english_level')->comment('英語レベル');
            $table->unsignedInteger('unit_price')->comment('単価');
            $table->unsignedInteger('unit_price_start')->comment('単価_開始金額');
            $table->unsignedInteger('unit_price_end')->comment('単価_終了金額');
            $table->unsignedInteger('pay_off_start')->comment('精算_開始金額');
            $table->unsignedInteger('pay_off_end')->comment('精算_終了金額');
            $table->unsignedInteger('estimated_working_days_week')->comment('想定稼働日数/週');
            $table->unsignedInteger('estimated_uptime_month_start')->comment('想定稼働時間/月_開始');
            $table->unsignedInteger('estimated_uptime_month_end')->comment('想定稼働時間/月_終了');
            $table->unsignedInteger('telework')->comment('常駐・リモート');
            $table->text('telework_info')->nullable()->comment('リモートワーク詳細');
            $table->unsignedInteger('counterplan_second_hand_smok')->comment('受動喫煙対策');
            $table->string('counterplan_second_hand_smok_detail', 1000)->nullable()->comment('受動喫煙対策（詳細）');

            $table->text('special_notes')->nullable()->comment('特記事項(その他条件)');
            $table->text('proposition')->comment('案件内容');
            $table->text('recruit_background')->nullable()->comment('募集背景');

            $table->unsignedInteger('team_size')->nullable()->comment('チーム規模');
            $table->unsignedInteger('recruiting_plan_count')->comment('募集予定人数');
            $table->unsignedInteger('pv_count')->nullable()->comment('PVカウント');
            $table->string('selection_flow', 100)->comment('選考フロー');
            $table->text('selection_detail')->nullable()->comment('選考詳細');
            $table->unsignedInteger('interview_method')->nullable()->comment('面談方法');

            $table->dateTime('public_at')->nullable()->comment('求人公開用編集日時');

            $this->historyColumns($table);

            $table->foreign('company_id')->references('id')->on('companies');
            $table->foreign('recruiting_company_id')->references('id')->on('recruiting_companies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outsource_offer_infos');
    }
}
