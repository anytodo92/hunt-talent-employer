<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Traits\MigrationHistoryTrait;

class CreateCompanyStatusesTable extends Migration
{
    use MigrationHistoryTrait;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_statuses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id')->comment('会社ID');
            $table->unsignedInteger('company_status_id')->comment('会社ステータスID');
            $table->dateTime('status_changed_at')->comment('ステータス変更日時');
            $table->unsignedBigInteger('status_changed_by')->nullable()->comment('ステータス変更者');
            $this->historyColumns($table);

            $table->foreign('status_changed_by')->references('id')->on('users');
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
        Schema::dropIfExists('company_statuses');
    }
}
