<?php

use App\Traits\MigrationHistoryTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkPlaceTable extends Migration
{
	use MigrationHistoryTrait;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_places', function (Blueprint $table) {
            $table->id();
			$table->unsignedBigInteger('offer_info_id')->comment('求人情報ID');
			$table->unsignedInteger('offer_info_type')->comment('求人情報タイプ');
			$table->unsignedInteger('post_number')->nullable()->comment('郵便番号');
			$table->unsignedInteger('prefecture')->comment('都道府県');
			$table->string('address', 100)->comment('住所（市区町村・番地・建物）');
			$table->string('nearest_station_line', 100)->comment('最寄駅の路線');
			$table->string('nearest_station', 100)->comment('最寄駅');
			$table->string('nearest_station_distance', 100)->nullable()->comment('最寄駅までの距離');
			$table->string('work_location_detail', 100)->nullable()->comment('勤務地詳細');

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
        Schema::dropIfExists('work_places');
    }
}
