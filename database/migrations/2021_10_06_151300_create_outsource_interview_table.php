<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutsourceInterviewTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outsource_interview', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outsource_job_seekers_id')->comment('業務委託求職者ID');
            $table->unsignedBigInteger('outsource_company_users_id')->comment('業務委託会社担当者ID');
            $table->date('interview_date')->comment('面談日');
            $table->time('interview_time_from')->comment('開始時間');
            $table->time('interview_time_to')->comment('終了時間');
            $table->unsignedInteger('status')->comment('ステータス');
            $this->historyColumns($table);
            $table->foreign('outsource_job_seekers_id')->references('id')->on('outsource_job_seekers');
            $table->foreign('outsource_company_users_id')->references('id')->on('outsource_company_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outsource_interview');
    }
}
