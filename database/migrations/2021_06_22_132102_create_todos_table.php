<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTodosTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('todo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->comment('会社ID');
			$table->unsignedBigInteger('offer_info_id')->nullable()->comment('求人情報ID');
			$table->unsignedInteger('offer_info_type')->nullable()->comment('求人情報タイプ');
			$table->unsignedBigInteger('job_seeker_apply_mgt_id')->nullable()->comment('求職者_応募管理ID');
			//$table->unsignedInteger('job_seeker_apply_mgt_type')->nullable()->comment('求職者_応募管理タイプ');
            $table->unsignedBigInteger('question_and_answer_id')->nullable()->comment('Q&AID');
			$table->unsignedBigInteger('calendar_id')->nullable()->comment('面接日程');
            $table->string('todo_content', 100)->nullable()->nullable()->comment('Todo内容');
            $table->unsignedInteger('todo_complete')->nullable()->comment('Todo完了');
            $table->unsignedInteger('todo_transition_target')->comment('Todo遷移先');
            $table->unsignedInteger('read_flg')->comment('既読');

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
        Schema::dropIfExists('todo');
    }
}
