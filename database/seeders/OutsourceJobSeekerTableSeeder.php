<?php

namespace Database\Seeders;

use App\Models\OutsourceJobSeeker;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OutsourceJobSeekerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 業務委託_求職者(1)
        $outsourceJobSeeker = new OutsourceJobSeeker();
        $outsourceJobSeeker->fill([
            'outsource_company_id' => 1,
            'outsource_company_user_id' => 1,
            'last_name' => '豊田',
            'first_name' => '太郎',
            'last_name_kana' => 'トヨタ',
            'first_name_kana' => 'タロウ',
            'birthday' => '1985-03-10',
            'sex' => 1,
            'closest_station' => '地下鉄から５分',
            'recommendation_statement' => '〇〇〇〇〇〇〇〇',
            'skill_detail_comment' => 'スキルコメントスキルコメントスキルコメントスキルコメントスキルコメント',

            'desired_unit_price_type' => 1,
            'desired_unit_price' => 500000,
        ])
        ->save();

        // 人材紹介_求職者(2)
        $outsourceJobSeeker = new OutsourceJobSeeker();
        $outsourceJobSeeker->fill([
            'outsource_company_id' => 1,
            'outsource_company_user_id' => 2,
            'last_name' => '武久',
            'first_name' => '太郎',
            'last_name_kana' => 'タケヒサ',
            'first_name_kana' => 'タロウ',
            'birthday' => '1981-06-20',
            'sex' => 2,
            'closest_station' => '地下鉄から５分',
            'recommendation_statement' => '〇〇〇〇〇〇〇〇',
            'skill_detail_comment' => 'スキルコメントスキルコメントスキルコメントスキルコメントスキルコメント',

            'desired_unit_price_type' => 2,
            'desired_unit_price' => 3000,
        ])
        ->save();

        // 人材紹介_求職者(3~75)
        for ($i = 3; $i <= 75; $i++) {
            $outsourceJobSeeker = new OutsourceJobSeeker();
            $outsourceJobSeeker->fill([
                'outsource_company_id' => 1,
                'outsource_company_user_id' => (($i-1) % 2)+1,
                'last_name' => '人材',
                'first_name' => '太郎',
                'last_name_kana' => 'ジンザイ',
                'first_name_kana' => 'タロウ',
                'initial' => chr(ord('A') + rand(0,25)) . '.' . chr(ord('A') + rand(0,25)),
                'birthday' => Carbon::createFromDate(1990, 1, 1)->addDays(rand(200, 1000))->format('Y-m-d'),
                'sex' => rand(1,3),
                'closest_station' => '地下鉄から５分',
                'recommendation_statement' => '〇〇〇〇〇〇〇〇',
                'skill_detail_comment' => 'スキルコメントスキルコメントスキルコメントスキルコメントスキルコメント',

                'desired_unit_price_type' => 1,
                'desired_unit_price' => 250000,
            ])
            ->save();
        }
    }
}
