<?php

namespace Database\Seeders;

use App\Models\RecruitJobSeeker;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RecruitJobSeekerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 人材紹介_求職者(1)
        $recruitJobSeeker = new RecruitJobSeeker();
        $recruitJobSeeker->fill([
            'recruit_company_id' => 1,
            'recruit_company_user_id' => 1,
            'last_name' => '豊田',
            'first_name' => '太郎',
            'last_name_kana' => 'トヨタ',
            'first_name_kana' => 'タロウ',
            'birthday' => '1985-03-10',
            'sex' => 1,
            'address' => '滋賀県犬上郡多賀町多賀１－１',
            'closest_station' => '地下鉄から５分',
            'final_education' => 5,
            'current_annual_income' => '800万円',
            'suggested_working_place' => 'ソフトバンク子会社１',
            'joining_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ])
        ->save();

        // 人材紹介_求職者(2)
        $recruitJobSeeker = new RecruitJobSeeker();
        $recruitJobSeeker->fill([
            'recruit_company_id' => 1,
            'recruit_company_user_id' => 2,
            'last_name' => '武久',
            'first_name' => '太郎',
            'last_name_kana' => 'タケヒサ',
            'first_name_kana' => 'タロウ',
            'birthday' => '1981-06-20',
            'sex' => 2,
            'address' => '滋賀県犬上郡多賀町多賀１－２',
            'closest_station' => '地下鉄から５分',
            'final_education' => 6,
            'current_annual_income' => '1200万円',
            'suggested_working_place' => 'ソフトバンク本社',
            'joining_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
        ])
        ->save();

        // 人材紹介_求職者(3~75)
        for ($i = 3; $i <= 75; $i++) {
            $recruitJobSeeker = new RecruitJobSeeker();
            $recruitJobSeeker->fill([
                'recruit_company_id' => 1,
                'recruit_company_user_id' => (($i-1) % 2)+1,
                'last_name' => '人材',
                'first_name' => '太郎',
                'last_name_kana' => 'ジンザイ',
                'first_name_kana' => 'タロウ',
                'birthday' => Carbon::createFromDate(1990, 1, 1)->addDays(rand(200, 1000))->format('Y-m-d'),
                'sex' => rand(1,3),
                'address' => '滋賀県犬上郡多賀町多賀１－３',
                'closest_station' => '地下鉄から５分',
                'final_education' => $i % 9 + 1,
                'current_annual_income' => '600万円',
                'suggested_working_place' => 'IT会社',
                'joining_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            ])
            ->save();
        }
    }
}
