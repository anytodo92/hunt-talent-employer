<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSelectionResultTable extends Migration
{
	use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('selection_results', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('job_seeker_apply_mgt_id')->comment('求職者_応募管理ID');
			$table->unsignedInteger('job_seeker_apply_mgt_type')->comment('求職者_応募管理タイプ');
			$table->unsignedInteger('phase')->comment('現状の選考フェーズ');
			$table->unsignedInteger('unseated_reason')->nullable()->comment('落選理由');
			$table->unsignedInteger('unseated_reason_sub')->nullable()->comment('落選理由サブ');
			$table->string('unseated_cause_detail', 100)->nullable()->comment('落選理由詳細');
			$table->unsignedInteger('next_phase')->nullable()->comment('次回の選考フェーズ');
			$table->unsignedInteger('current_evaluation')->nullable()->comment('現状の評価');
			$table->string('evaluation_point', 100)->nullable()->comment('評価点');
			$table->string('concern_point', 100)->nullable()->comment('懸念点');
			$table->string('interviewer', 100)->nullable()->comment('面接担当者名');
			$table->string('interview_address', 100)->nullable()->comment('面接場所住所');
			$table->string('belongings', 100)->nullable()->comment('持ち物');
			$table->string('emergency_contact_address', 100)->nullable()->comment('緊急連絡先（転職者様のみご利用可）');
			$table->string('else_special_note', 100)->nullable()->comment('その他特記事項');
            $table->unsignedInteger('interview_setting_person_type')->nullable()->comment('面接設定者タイプ');

			$this->historyColumns($table);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('selection_results');
    }
}
