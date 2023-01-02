<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJoiningConditionPresentTable extends Migration
{
	use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('joining_condition_presents', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('recruit_job_seeker_apply_mgt_id')->comment('人材紹介_求職者_応募管理ID');
			$table->unsignedInteger('offer_amount')->comment('オファー金額');
			$table->date('first_day_attendance_date')->comment('初日出勤日');
			$table->date('reply_deadline')->comment('返答期限');
			$table->unsignedInteger('job_changer_desired_annual_income')->comment('転職者_希望年収（円）');
			$table->date('job_changer_first_day_attendance_date')->nullable()->comment('転職者_希望初日出勤日');
			$table->date('first_day_work_schedule_date')->nullable()->comment('初日出勤予定日');
			$table->date('fixed_first_day_attendance_date')->nullable()->comment('確定初日出勤日');
			$table->string('other_desired', 100)->nullable()->comment('その他希望欄');

			$this->historyColumns($table);
			$table->foreign('recruit_job_seeker_apply_mgt_id', 'jcp_recruit_mgt_id')->references('id')->on('recruit_job_seeker_apply_mgts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('joining_condition_presents');
    }
}
