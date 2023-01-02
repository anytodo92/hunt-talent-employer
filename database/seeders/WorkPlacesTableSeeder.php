<?php

namespace Database\Seeders;

use App\Models\WorkPlace;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class WorkPlacesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $prefectures = config('constants.prefectures');
        $prefecture_ids = collect($prefectures)->keys();

        // 勤務地
        for($i = 1 ; $i <= 10 ; $i++) {

            $place = new WorkPlace();
            $place->offer_info_id = $i;
            $place->offer_info_type = $i % 2;
            $place->post_number = '123456'.$i;
            $place->prefecture = $prefecture_ids->random();
            $place->address = '墨田区押上1-1-'. $i;
            $place->nearest_station_line = '〇〇〇線-'. $i;
            $place->nearest_station = '〇〇〇駅-'. $i;
            $place->nearest_station_distance = $i;
            $place->work_location_detail = 'コメントコメントコメント';

            $place->save();
        }
    }
}
