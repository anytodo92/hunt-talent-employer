<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractTermsTable extends Migration
{
    use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contract_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('outsource_job_seeker_apply_mgt_id')->comment('業務委託_求職者_応募管理ID');
            $table->unsignedInteger('unit_price')->comment('単価');
            $table->unsignedInteger('unit_price_amount')->comment('単価金額');
            $table->unsignedInteger('pay_off_start')->comment('精算時間_開始');
            $table->unsignedInteger('pay_off_end')->comment('精算時間_終了');
            $table->string('estimated_working_days_week', 100)->nullable()->comment('想定稼働日数/週');
            $table->string('special_notes', 100)->nullable()->comment('特記事項（その他条件）');
            $table->date('joining_start_date')->comment('参画開始日');
            $table->date('reply_deadline')->comment('返答期限');
            $this->historyColumns($table);
            $table->foreign('outsource_job_seeker_apply_mgt_id', 'ct_outsource_mgt_id')->references('id')->on('outsource_job_seeker_apply_mgts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_terms');
    }
}
