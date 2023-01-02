<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecruitJobSeekerApplyMgtsTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recruit_job_seeker_apply_mgts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recruit_job_seeker_id')->comment('人材紹介会社_求職者ID');
            $table->unsignedBigInteger('recruit_offer_info_id')->comment('人材紹介会社_求人情報ID');
            $table->unsignedInteger('selection_status')->comment('選考ステータス');

            $table->unsignedInteger('application')->nullable()->comment('応募');
            $table->date('application_date')->nullable()->comment('応募日');

            $table->unsignedInteger('document_confirmation')->nullable()->comment('書類確認');
            $table->date('document_confirmation_date')->nullable()->comment('書類確認日');

            $table->unsignedInteger('applicant_screening')->nullable()->comment('書類選考');
            $table->date('applicant_screening_date')->nullable()->comment('書類選考日');
            $table->string('applicant_screening_refusal_reason', 1000)->nullable()->comment('書類選考辞退理由');
            $table->date('applicant_screening_refusal_reason_date')->nullable()->comment('書類選考辞退日');
            $table->date('applicant_screening_not_adopted_date')->nullable()->comment('書類選考不採用日');

            $table->unsignedInteger('writing_web_test')->nullable()->comment('筆記、webテスト');
            $table->date('writing_web_test_date')->nullable()->comment('筆記、webテスト日');
            $table->string('writing_web_test_refusal_reason', 1000)->nullable()->comment('筆記、webテスト辞退理由');
            $table->date('writing_web_test_refusal_reason_date')->nullable()->comment('筆記、webテスト辞退日');
            $table->date('writing_web_test_not_adopted_date')->nullable()->comment('筆記、webテスト不採用日');

            $table->unsignedInteger('interview')->nullable()->comment('面談');
            $table->date('interview_date')->nullable()->comment('面談日');
            $table->string('interview_refusal_reason', 1000)->nullable()->comment('面談辞退理由');
            $table->date('interview_refusal_reason_date')->nullable()->comment('面談辞退日');
            $table->date('interview_not_adopted_date')->nullable()->comment('面談不採用日');

            $table->unsignedInteger('1st_interview')->nullable()->comment('1次面接');
            $table->date('1st_interview_date')->nullable()->comment('1次面接日');
            $table->text('1st_refusal_reason')->nullable()->comment('1次辞退理由');
            $table->date('1st_refusal_reason_date')->nullable()->comment('1次辞退日');
            $table->date('1st_not_adopted_date')->nullable()->comment('1次不採用日');

            $table->unsignedInteger('2nd_interview')->nullable()->comment('2次面接');
            $table->date('2nd_interview_date')->nullable()->comment('2次面接日');
            $table->text('2nd_refusal_reason')->nullable()->comment('2次辞退理由');
            $table->date('2nd_refusal_reason_date')->nullable()->comment('2次辞退日');
            $table->date('2nd_not_adopted_date')->nullable()->comment('2次不採用日');

            $table->unsignedInteger('3rd_interview')->nullable()->comment('3次面接');
            $table->date('3rd_interview_date')->nullable()->comment('3次面接日');
            $table->text('3rd_refusal_reason')->nullable()->comment('3次辞退理由');
            $table->date('3rd_refusal_reason_date')->nullable()->comment('3次辞退日');
            $table->date('3rd_not_adopted_date')->nullable()->comment('3次不採用日');

            $table->unsignedInteger('4th_interview')->nullable()->comment('4次面接');
            $table->date('4th_interview_date')->nullable()->comment('4次面接日');
            $table->text('4th_refusal_reason')->nullable()->comment('4次辞退理由');
            $table->date('4th_refusal_reason_date')->nullable()->comment('4次辞退日');
            $table->date('4th_not_adopted_date')->nullable()->comment('4次不採用日');

            $table->unsignedInteger('5th_interview')->nullable()->comment('5次面接');
            $table->date('5th_interview_date')->nullable()->comment('5次面接日');
            $table->text('5th_refusal_reason')->nullable()->comment('5次辞退理由');
            $table->date('5th_refusal_reason_date')->nullable()->comment('5次辞退日');
            $table->date('5th_not_adopted_date')->nullable()->comment('5次不採用日');

            $table->unsignedInteger('last_interview')->nullable()->comment('最終面接');
            $table->date('last_interview_date')->nullable()->comment('最終面接日');
            $table->text('last_refusal_reason')->nullable()->comment('最終辞退理由');
            $table->date('last_refusal_reason_date')->nullable()->comment('最終辞退日');
            $table->date('last_not_adopted_date')->nullable()->comment('最終不採用日');

            $table->unsignedInteger('recruitment')->nullable()->comment('採用');
            $table->date('recruitment_date')->nullable()->comment('採用日');

            $table->unsignedInteger('joining_confirmation')->nullable()->comment('入社確認');
            $table->date('joining_confirmation_date')->nullable()->comment('入社確認日');
            $table->text('joining_confirmation_refusal_reason')->nullable()->comment('入社確認辞退理由');
            $table->date('joining_confirmation_refusal_reason_date')->nullable()->comment('入社確認辞退日');
            $table->date('joining_confirmation_not_adopted_date')->nullable()->comment('入社確認不採用日');

            $table->date('retirement_date')->nullable()->comment('退職日');
            $table->unsignedInteger('refund_status')->nullable()->comment('返金状態');
            $table->date('refund_apply_date')->nullable()->comment('返金申請日');
            $table->date('refund_agreement_date')->nullable()->comment('返金同意日');
            $table->date('refund_disagreement_date')->nullable()->comment('返金不同意日');
            $table->unsignedInteger('refund_amount')->nullable()->comment('返金額');

            $table->unsignedInteger('last_selection_flow_number')->nullable()->comment('最後の選考フロー番号');
            $table->date('last_selection_flow_date')->nullable()->comment('最後の選考フロー日付');

            $this->historyColumns($table);

            $table->foreign('recruit_job_seeker_id')->references('id')->on('recruit_job_seekers');
            $table->foreign('recruit_offer_info_id')->references('id')->on('recruit_offer_infos');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruit_job_seeker_apply_mgts');
    }
}
