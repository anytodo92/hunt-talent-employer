<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutsourceJobSeekerApplyMgtsTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outsource_job_seeker_apply_mgts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outsource_job_seeker_id')->comment('業務委託_求職者ID');
            $table->unsignedBigInteger('outsource_offer_info_id')->comment('業務委託_求人情報ID');
            $table->unsignedInteger('selection_status')->comment('選考ステータス');

            $table->unsignedInteger('proposal')->nullable()->comment('提案');
            $table->date('proposal_date')->nullable()->comment('提案日');

            $table->unsignedInteger('document_confirmation')->nullable()->comment('書類確認');
            $table->date('document_confirmation_date')->nullable()->comment('書類確認日');

            $table->unsignedInteger('applicant_screening')->nullable()->comment('書類選考');
            $table->date('applicant_screening_date')->nullable()->comment('書類選考日');
            $table->text('applicant_screening_refusal_reason')->nullable()->comment('書類選考辞退理由');
            $table->date('applicant_screening_refusal_reason_date')->nullable()->comment('書類選考辞退日');
            $table->date('applicant_screening_send_off_date')->nullable()->comment('書類選考お見送り通知日');

            $table->unsignedInteger('writing_web_test')->nullable()->comment('筆記、webテスト');
            $table->date('writing_web_test_date')->nullable()->comment('筆記、webテスト日');
            $table->text('writing_web_test_refusal_reason')->nullable()->comment('筆記、webテスト辞退理由');
            $table->date('writing_web_test_refusal_reason_date')->nullable()->comment('筆記、webテスト辞退日');
            $table->date('writing_web_test_send_off_date')->nullable()->comment('筆記、webテストお見送り通知日');

            $table->unsignedInteger('1st_interview')->nullable()->comment('1次面接');
            $table->date('1st_interview_date')->nullable()->comment('1次面接日');
            $table->text('1st_refusal_reason')->nullable()->comment('1次辞退理由');
            $table->date('1st_refusal_reason_date')->nullable()->comment('1次辞退日');
            $table->date('1st_send_off_date')->nullable()->comment('1次お見送り通知日');

            $table->unsignedInteger('2nd_interview')->nullable()->comment('2次面接');
            $table->date('2nd_interview_date')->nullable()->comment('2次面接日');
            $table->text('2nd_refusal_reason')->nullable()->comment('2次辞退理由');
            $table->date('2nd_refusal_reason_date')->nullable()->comment('2次辞退日');
            $table->date('2nd_send_off_date')->nullable()->comment('2次辞退日');

            $table->unsignedInteger('3rd_interview')->nullable()->comment('3次面接');
            $table->date('3rd_interview_date')->nullable()->comment('3次面接日');
            $table->text('3rd_refusal_reason')->nullable()->comment('3次辞退理由');
            $table->date('3rd_refusal_reason_date')->nullable()->comment('3次辞退日');
            $table->date('3rd_send_off_date')->nullable()->comment('3次辞退日');

            $table->unsignedInteger('last_interview')->nullable()->comment('最終面接');
            $table->date('last_interview_date')->nullable()->comment('最終面接日');
            $table->text('last_refusal_reason')->nullable()->comment('最終辞退理由');
            $table->date('last_refusal_reason_date')->nullable()->comment('最終辞退日');
            $table->date('last_send_off_date')->nullable()->comment('最終お見送り通知日');

            $table->unsignedInteger('contract')->nullable()->comment('契約');
            $table->date('offer_date')->nullable()->comment('オファー日');
            $table->date('contract_satisfied_date')->nullable()->comment('契約成立日');
            $table->date('joining_scheduled_date')->nullable()->comment('参画開始予定日');

            $table->unsignedInteger('joining_confirmation')->nullable()->comment('参画確認');
            $table->date('joining_confirmation_start_date')->nullable()->comment('参画確認開始日');
            $table->text('joining_confirmation_refusal_reason')->nullable()->comment('参画確認辞退理由');
            $table->date('joining_confirmation_refusal_reason_date')->nullable()->comment('参画確認辞退日');
            $table->date('joining_confirmation_send_off_date')->nullable()->comment('参画確認お見送り通知日');

            $table->unsignedInteger('current_state')->nullable()->comment('現況');
            $table->date('joining_end_date')->nullable()->comment('参画終了日');
            $table->unsignedInteger('joining_end_applicant')->nullable()->comment('参画終了申請者');
            $table->unsignedInteger('current_month_operating_hours_number')->nullable()->comment('当月稼働時間数');
            $table->unsignedInteger('billing_price')->nullable()->comment('ご請求金額（税別）');

            $table->unsignedInteger('last_selection_flow_number')->nullable()->comment('最後の選考フロー番号');
            $table->date('last_selection_flow_date')->nullable()->comment('最後の選考フロー日付');
            $table->unsignedInteger('proposal_unit_price')->nullable()->comment('提案単価');

            $this->historyColumns($table);

            $table->foreign('outsource_job_seeker_id')->references('id')->on('outsource_job_seekers');
            $table->foreign('outsource_offer_info_id')->references('id')->on('outsource_offer_infos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('outsource_job_seeker_apply_mgts');
    }
}
