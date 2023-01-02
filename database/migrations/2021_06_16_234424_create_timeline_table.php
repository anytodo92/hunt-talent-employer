<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimelineTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('timeline', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->comment('会社ID');
            $table->unsignedBigInteger('job_seeker_apply_mgt_id')->comment('求職者_応募管理ID');
            $table->unsignedInteger('job_seeker_apply_mgt_type')->comment('求職者_応募管理タイプ');
            $table->unsignedBigInteger('message_type')->comment('メッセージタイプ'); // 1:自動送信メッセージ、2:送信者メッセージ
            $table->unsignedBigInteger('message_sender')->comment('メッセージ送信者');
            $table->unsignedInteger('sender_type')->comment('送信者タイプ');
            $table->string('message_title', 1000)->nullable()->comment('メッセージタイトル');
            $table->string('message_detail', 1000)->nullable()->comment('メッセージ内容');
            $table->string('attachment', 1000)->nullable()->comment('添付ファイル');
            $table->string('attachment_name', 1000)->nullable()->comment('添付ファイル名（画面表示用）');
            $table->unsignedInteger('timeline_complete')->nullable()->comment('タイムライン完了');
            $table->unsignedInteger('timeline_transition_target')->nullable()->comment('タイムライン遷移先');
            $table->unsignedInteger('read_flg')->default(1)->comment('既読');

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
        Schema::dropIfExists('timeline');
    }
}
