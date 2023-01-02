<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterviewSchedulesTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->comment('会社ID');
			$table->unsignedBigInteger('job_seeker_apply_mgt_id')->comment('求職者_応募管理ID');
			$table->unsignedInteger('job_seeker_apply_mgt_type')->comment('求職者_応募管理タイプ');
            $table->string('interview_candidates_name',100)->comment('面接候補者名');
            $table->date('interview_candidates_date')->comment('面接候補日');
            $table->time('interview_candidates_from')->comment('面接候補日開始');
            $table->time('interview_candidates_to')->comment('面接候補日終了');
            $table->string('interview_content', 1000)->nullable()->comment('面接内容');
            $table->unsignedInteger('interview_date_type')->comment('面接日タイプ');
            $table->unsignedInteger('interview_phase')->comment('面接フェーズ');

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
        Schema::dropIfExists('interview_schedules');
    }
}
