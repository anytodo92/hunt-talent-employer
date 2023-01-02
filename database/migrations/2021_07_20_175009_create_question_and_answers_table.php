<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionAndAnswersTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_and_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->comment('会社ID');
            $table->unsignedBigInteger('offer_info_id')->comment('求人情報ID');
            $table->unsignedBigInteger('offer_info_type')->comment('求人情報タイプ');
            $table->unsignedBigInteger('question_person_id')->comment('質問者ID');
            $table->datetime('question_datetime')->comment('質問日');
            $table->unsignedBigInteger('question_type')->comment('質問タイプ');
            $table->text('question_title')->comment('質問タイトル');
            $table->text('question_content')->comment('質問内容');
            $table->unsignedBigInteger('answer_person_id')->comment('回答者ID');
            $table->datetime('answer_datetime')->comment('回答日');
            $table->text('answer_content')->comment('回答内容');
            $table->unsignedBigInteger('status')->comment('ステータス')->default(1);
            $table->unsignedBigInteger('reject')->comment('拒否')->default(1);

            $this->historyColumns($table);

            $table->foreign('company_id')->references('id')->on('companies');
//            $table->foreign('question_person_id')->references('id')->on('company_users');
            $table->foreign('answer_person_id')->references('id')->on('company_users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_and_answers');
    }
}
