<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitOfferInfosTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruit_offer_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->comment('会社ID');
            $table->unsignedBigInteger('recruiting_company_id')->comment('募集企業ID');
            $table->unsignedBigInteger('recruit_offer_info_id')->nullable()->comment('人材紹介_求人情報ID（※検討中のカラム）');
            $table->string('recruit_occupation', 100)->comment('募集職種名');
            $table->unsignedInteger('open_status')->comment('公開状況');
            $table->string('job_title', 200)->comment('求人タイトル');
            $table->string('job_feature', 100)->nullable()->comment('特徴・訴求ポイント');
            $table->string('image_main', 100)->comment('画像（メイン）');
            $table->string('image_sub_1', 100)->nullable()->comment('画像（サブ1）');
            $table->string('image_sub_2', 100)->nullable()->comment('画像（サブ2）');
            $table->string('image_sub_3', 100)->nullable()->comment('画像（サブ3）');
            $table->string('image_sub_4', 100)->nullable()->comment('画像（サブ4）');
            $table->string('image_sub_5', 100)->nullable()->comment('画像（サブ5）');
            $table->unsignedInteger('marketing_use')->nullable()->comment('集客利用の可否');

            $table->text('job_requirement')->comment('必須要件・応募資格 ');
            $table->unsignedInteger('final_education')->comment('最終学歴');
            $table->unsignedInteger('applicable_age_from')->comment('応募可能年齢_開始');
            $table->unsignedInteger('applicable_age_to')->comment('応募可能年齢_終了');
            $table->unsignedInteger('company_number')->comment('就業経験社数');
            $table->unsignedInteger('sex')->comment('性別');
            $table->unsignedInteger('inexperienced')->comment('未経験の可否');
            $table->unsignedInteger('foreign_nationality')->comment('外国籍の可否');
            $table->unsignedInteger('english_level')->comment('英語レベル');
            $table->unsignedInteger('chinese_level')->comment('中国語レベル');
            $table->date('recruit_period')->comment('募集期間');
            $table->unsignedInteger('occupation_category_1')->comment('職種カテゴリー1');
            $table->unsignedInteger('occupation_category_2')->comment('職種カテゴリー2');
            $table->unsignedInteger('recruiting_plan_count')->comment('募集予定人数');
            $table->unsignedInteger('pv_count')->nullable()->comment('PVカウント');
            $table->unsignedInteger('success_reward_calculation_method')->comment('成功報酬の算定方法');
            $table->unsignedInteger('theory_annual_income')->nullable()->comment('理論年収');
            $table->string('theory_annual_income_definition', 100)->nullable()->comment('理論年収の定義');
            $table->string('fixed_reward', 100)->nullable()->comment('固定報酬');
            $table->string('refund_policy', 100)->comment('返金規定');
            $table->string('warning_text', 100)->comment('注意文言');

            $table->text('job_description')->comment('仕事内容');
            $table->text('job_attraction')->nullable()->comment('この仕事の醍醐味、得られるもの、魅力');
            $table->text('job_experience')->nullable()->comment('活躍できる経験');

            $table->unsignedInteger('mgt_supervisor')->comment('管理監督者の求人');
            $table->string('department', 100)->nullable()->comment('部署名');
            $table->text('department_detail')->nullable()->comment('部署詳細');
            $table->unsignedInteger('employment')->comment('雇用形態');
            $table->text('working_time_type')->comment('勤務時間タイプ');
            $table->text('working_time')->comment('勤務時間');

            $table->unsignedInteger('working_hours_system')->comment('労働時間制・固定残業代');
            $table->unsignedInteger('deemed_working_hours_system')->nullable()->comment('みなし労働時間制の種類');
            $table->string('deemed_working_hours', 100)->nullable()->comment('みなし労働時間（/1day）');
            $table->text('overtime_hours')->nullable()->comment('残業時間の詳細');
            $table->unsignedInteger('short_working_hours_system')->comment('時短勤務');
            $table->string('short_working_hours_system_detail', 1000)->nullable()->comment('時短勤務詳細');
            $table->string('selection_flow', 100)->comment('選考フロー');
            $table->text('selection_detail')->nullable()->comment('選考詳細');
            $table->unsignedInteger('telework')->nullable()->comment('在宅勤務');
            $table->text('telework_info')->nullable()->comment('在宅勤務詳細');
            $table->unsignedInteger('salary_type')->comment('給与タイプ');
            $table->unsignedInteger('yearly_pay_amount_from')->nullable()->comment('年俸額_開始');
            $table->unsignedInteger('yearly_pay_amount_to')->nullable()->comment('年俸額_終了');
            $table->unsignedInteger('payment_method')->nullable()->comment('支払い方法');
            $table->unsignedInteger('monthly_salary_from')->nullable()->comment('月給_開始');
            $table->unsignedInteger('monthly_salary_to')->nullable()->comment('月給_終了');
            $table->unsignedInteger('monthly_pay_assumption_annual_income_from')->nullable()->comment('月給の想定年収（万円）_開始');
            $table->unsignedInteger('monthly_pay_assumption_annual_incometom')->nullable()->comment('月給の想定年収（万円）_終了');
            $table->unsignedInteger('daily_salary_from')->nullable()->comment('日給_開始');
            $table->unsignedInteger('daily_salary_to')->nullable()->comment('日給_終了');
            $table->unsignedInteger('hourly_wage_from')->nullable()->comment('時給_開始');
            $table->unsignedInteger('hourly_wage_to')->nullable()->comment('時給_終了');
            $table->string('basic_salary', 100)->nullable()->comment('基本給の金額');
            $table->string('fixed_overtime_fee', 100)->nullable()->comment('固定残業代の金額');
            $table->string('fixed_overtime_hours', 100)->nullable()->comment('固定残業時間');
            $table->string('payment_for_fixed_overtime', 100)->nullable()->comment('固定残業時間超過分の支給');
            $table->text('salary_treatment_detail')->nullable()->comment('給与・待遇の詳細');
            $table->unsignedInteger('trial_period')->comment('試用期間');

            $table->text('trial_period_detail')->nullable()->comment('試用期間詳細');
            $table->text('annual_holiday')->comment('年間休日');
            $table->text('holiday_vacation')->comment('休日休暇');
            $table->text('welfare')->comment('福利厚生');

            $table->unsignedInteger('passive_smoking_solution')->comment('受動喫煙対策');
            $table->text('passive_smoking_solution_detail')->nullable()->comment('受動喫煙対策（詳細）');

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
        Schema::dropIfExists('recruit_offer_infos');
    }
}
