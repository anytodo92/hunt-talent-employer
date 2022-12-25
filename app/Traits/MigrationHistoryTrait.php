<?php

namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;

Trait MigrationHistoryTrait {

    public function historyColumns(Blueprint $table) {

        $table->timestamp('created_at', 0)->nullable()->comment('レコード作成日時');
        $table->unsignedBigInteger('created_by')->nullable()->comment('レコード作成者');
        $table->timestamp('updated_at', 0)->nullable()->comment('レコード更新日時');
        $table->unsignedBigInteger('updated_by')->nullable()->comment('レコード更新者');
        $table->softDeletes()->comment('レコード削除日時');
        $table->unsignedBigInteger('deleted_by')->nullable()->comment('レコード削除者');

        $table->foreign('created_by')->references('id')->on('users');
        $table->foreign('updated_by')->references('id')->on('users');
        $table->foreign('deleted_by')->references('id')->on('users');

    }

}