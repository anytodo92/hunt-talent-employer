<?php

namespace Database\Seeders;


use App\Models\OutsourceOfferInfo;
use App\Models\RecruitOfferInfo;
use App\Models\User;
use Illuminate\Database\Seeder;

class OutsourceOfferInfoTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $companyID = 1;
        $recruitingCompanyID = 1;

        // 業務委託_求人情報
        for ($i = 1 ; $i <= 75 ; $i++) {

            $outsourceOfferInfo = new OutsourceOfferInfo();
            $outsourceOfferInfo->company_id = $companyID;
            $outsourceOfferInfo->recruiting_company_id = $recruitingCompanyID;
            $outsourceOfferInfo->recruit_occupation = 'エンジニア';
            $outsourceOfferInfo->open_status = ($i % 4) + 1;
            $outsourceOfferInfo->job_title = 'テスト' . $i;
            $outsourceOfferInfo->image_main = '1.png';
            $outsourceOfferInfo->recruit_period = date('Y-m-d');
            $outsourceOfferInfo->occupation_category_1 = ($i % 15) + 1;
            $outsourceOfferInfo->occupation_category_2 = ($i % 3) + 1;
            $outsourceOfferInfo->requirements_skills_languages = 'テスト';
            $outsourceOfferInfo->requirements_skills_framework = 'テスト';
            $outsourceOfferInfo->requirements_skills_tool = 'テスト';
            $outsourceOfferInfo->requirements_skills_detail = 'テスト';
            $outsourceOfferInfo->apply_age_from = $i;
            $outsourceOfferInfo->apply_age_to = $i + 1;
            $outsourceOfferInfo->sex = ($i % 3) + 1;
            $outsourceOfferInfo->foreign_nationality = ($i % 2) + 1;
            $outsourceOfferInfo->english_level = ($i % 4) + 1;
            $outsourceOfferInfo->unit_price = ($i % 2) + 1;
            $outsourceOfferInfo->unit_price_start = $i * 100;
            $outsourceOfferInfo->unit_price_end = ($i + 1) * 100;
            $outsourceOfferInfo->pay_off_start = $i * 100;
            $outsourceOfferInfo->pay_off_end = ($i + 1) * 100;
            $outsourceOfferInfo->estimated_working_days_week = ($i % 6) + 1;
            $outsourceOfferInfo->estimated_uptime_month_start = $i * 100;
            $outsourceOfferInfo->estimated_uptime_month_end = ($i + 1) * 100;
            $outsourceOfferInfo->telework = ($i % 3) + 1;
            $outsourceOfferInfo->counterplan_second_hand_smok = ($i % 3) + 1;
            $outsourceOfferInfo->proposition = 'テスト';

            $outsourceOfferInfo->recruiting_plan_count = ($i % 8) + 1;
            $outsourceOfferInfo->selection_flow = 'テスト';

            $outsourceOfferInfo->save();
        }
    }
}
