<?php

namespace Database\Seeders;


use App\Models\RecruitOfferInfo;
use App\Models\User;
use Illuminate\Database\Seeder;

class RecruitOfferInfoTableSeeder extends Seeder
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

        // 人材紹介_求人情報
        for ($i = 1 ; $i <= 75 ; $i++) {

            $recruitOfferInfo = new RecruitOfferInfo();
            $recruitOfferInfo->company_id = $companyID;
            $recruitOfferInfo->recruiting_company_id = $recruitingCompanyID;
            $recruitOfferInfo->recruit_occupation = 'エンジニア';
            $recruitOfferInfo->open_status = ($i % 4) + 1;
            $recruitOfferInfo->job_feature = (($i-1) % 25)+1;
            $recruitOfferInfo->job_title = '人材募集' . $i;
            $recruitOfferInfo->image_main = 'recruit-offer' . $i . '.png';
            $recruitOfferInfo->job_requirement = 'テスト';
            $recruitOfferInfo->final_education = ($i % 8) + 1;
            $recruitOfferInfo->applicable_age_from = $i;
            $recruitOfferInfo->applicable_age_to = $i + 1;
            $recruitOfferInfo->company_number = ($i % 5) + 1;
            $recruitOfferInfo->sex = ($i % 3) + 1;
            $recruitOfferInfo->inexperienced = ($i % 4) + 1;
            $recruitOfferInfo->foreign_nationality = ($i % 2) + 1;
            $recruitOfferInfo->english_level = ($i % 4) + 1;
            $recruitOfferInfo->chinese_level = ($i % 4) + 1;
            $recruitOfferInfo->recruit_period = date('Y-m-d');
            $recruitOfferInfo->occupation_category_1 = ($i % 15) + 1;
            $recruitOfferInfo->occupation_category_2 = ($i % 3) + 1;
            $recruitOfferInfo->recruiting_plan_count = ($i % 8) + 1;
            $recruitOfferInfo->success_reward_calculation_method = ($i % 2) + 1;
            $recruitOfferInfo->refund_policy = 'テスト';
            $recruitOfferInfo->warning_text = 'テスト';
            $recruitOfferInfo->job_description = 'テスト';
            $recruitOfferInfo->mgt_supervisor = ($i % 2) + 1;
            $recruitOfferInfo->employment = ($i % 3) + 1;
            $recruitOfferInfo->working_time_type = 'テスト';
            $recruitOfferInfo->working_time = 'テスト';
            $recruitOfferInfo->working_hours_system = ($i % 3) + 1;
            $recruitOfferInfo->short_working_hours_system = ($i % 2) + 1;
            $recruitOfferInfo->selection_flow = 'テスト';
            $recruitOfferInfo->salary_type = ($i % 4) + 1;
            $recruitOfferInfo->trial_period = ($i % 2) + 1;
            $recruitOfferInfo->annual_holiday = 'テスト';
            $recruitOfferInfo->holiday_vacation = 'テスト';
            $recruitOfferInfo->welfare = 'テスト';
            $recruitOfferInfo->passive_smoking_solution = ($i % 3) + 1;

            $recruitOfferInfo->save();
        }
    }
}
