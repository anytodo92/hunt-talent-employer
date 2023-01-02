<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Traits\MigrationHistoryTrait;

class CreateCompaniesTable extends Migration
{
    use MigrationHistoryTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('会社名');
            $table->string('logo')->nullable()->comment('企業ロゴ');
            $table->unsignedInteger('prefecture_id')->default(0)->comment('都道府県ID');
            $table->string('address')->default('')->comment('住所');
            $table->unsignedBigInteger('first_industry_id')->nullable()->comment('業界１ ID');
            $table->unsignedBigInteger('second_industry_id')->nullable()->comment('業界２ ID');
            $table->unsignedInteger('employee_number_id')->nullable()->comment('従業員数');
            $table->unsignedInteger('establish_year')->nullable()->comment('設立年');
            $table->unsignedInteger('establish_month')->nullable()->comment('設立月');
            $table->string('company_profile')->nullable()->comment('会社概要');
            $table->string('business_content')->nullable()->comment('事業内容');
            $table->string('billing_email_1', 100)->nullable()->comment('請求書送付先メールアドレス1');
            $table->string('billing_email_2', 100)->nullable()->comment('請求書送付先メールアドレス2');
            $table->string('email')->nullable()->comment('通知用メールアドレス');
            $table->string('phone_number', 50)->nullable()->comment('電話番号');
            $table->string('url')->nullable()->comment('会社HP');
            $table->unsignedInteger('annual_recruit_number_id')->nullable()->comment('年間の採用予定人数');
            $table->boolean('mailing_setting')->default(false)->comment('メルマガ受信設定');
            $table->unsignedInteger('consideration_status_id')->default(0)->comment('ご検討状況');
            $table->text('inquiry')->nullable()->comment('お問い合わせ内容');
            $table->boolean('accepted')->default(false)->comment('プライバシー利用規約同意');
            $table->string('admin_memo_private', 255)->nullable()->comment('運営者メモ（非公開）');
            $table->boolean('recruiting_companies_flg')->default(false)->comment('募集企業フラグ');
            $this->historyColumns($table);

            $table->foreign('first_industry_id')->references('id')->on('first_industries');
            $table->foreign('second_industry_id')->references('id')->on('second_industries');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
