<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitInterviewTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruit_interview', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recruit_job_seekers_id')->comment('人材紹介求職者ID');
            $table->unsignedBigInteger('recruit_company_users_id')->comment('人材紹介会社担当者ID');
            $table->date('interview_date')->comment('面談日時(日付)');
            $table->time('interview_time_from')->comment('面談日時(開始時間)');
            $table->time('interview_time_to')->comment('面談日時(終了時間)');
            $table->unsignedInteger('status')->comment('ステータス');
            $this->historyColumns($table);
            $table->foreign('recruit_job_seekers_id')->references('id')->on('recruit_job_seekers');
            $table->foreign('recruit_company_users_id')->references('id')->on('recruit_company_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruit_interview');
    }
}
