<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJoiningConditionPresentAttachmentTable extends Migration
{
	use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('joining_condition_present_attachments', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('joining_condition_presents_id')->comment('入社条件提示ID');
			$table->string('attachment', 100)->comment('添付ファイル');
            $table->string('attachment_name', 100)->nullable()->comment('添付ファイル名（画面表示用）');
			$table->dateTime('upload_datetime')->comment('アップロード日時');
			$this->historyColumns($table);

			$table->foreign('joining_condition_presents_id', 'jcpa_join_cond_id')->references('id')->on('joining_condition_presents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('joining_condition_present_attachments');
    }
}
