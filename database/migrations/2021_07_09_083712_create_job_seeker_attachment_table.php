<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobSeekerAttachmentTable extends Migration
{
	use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_seeker_attachments', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('job_seeker_id')->comment('求職者ID');
			$table->unsignedInteger('job_seeker_type')->comment('求職者タイプ');
			$table->unsignedInteger('attachment_type')->comment('添付ファイルタイプ');
			$table->string('attachment', 100)->comment('添付ファイル');
            $table->string('attachment_name', 100)->nullable()->comment('添付ファイル名（画面表示用）');
			$table->dateTime('upload_datetime')->comment('アップロード日時');
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
        Schema::dropIfExists('job_seeker_attachments');
    }
}
