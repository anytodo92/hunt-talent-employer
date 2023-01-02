<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Company;
use App\Models\CompanyStatus;
use App\Models\CompanyUser;
use App\Models\FirstIndustry;
use App\Models\RecruitingCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanyUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $password = 'xxxxxxxx';
        $prefectures = config('constants.prefectures');
        $prefecture_ids = collect($prefectures)->keys();
        $employee_numbers = config('constants.employee_numbers');
        $employee_number_ids = collect($employee_numbers)->keys();
        $annual_recruit_numbers = config('constants.annual_recruit_numbers');
        $annual_recruit_number_ids = collect($annual_recruit_numbers)->keys();
        $consideration_statuses = config('constants.consideration_statuses');
        $consideration_status_ids = collect($consideration_statuses)->keys();
        $industries = FirstIndustry::with('second_industries')->get();
        $company_statuses = config('constants.company_statuses');
        $admin_users = AdminUser::get();

        // Company
        for($i = 1 ; $i <= 10 ; $i++) {

            $user = new User();
            $user->email = 'company_'. $i .'@example.com';
            $user->password = Hash::make($password);
            $user->user_type_id = 1; // 求人企業
            $user->save();

            $industry = $industries->random();
            $company = new Company();
            $company->name = '株式会社テスト'. $i;
            $company->logo = 'company-'.$i.'.png';
            $company->prefecture_id = $prefecture_ids->random();
            $company->address = '東京都墨田区押上1-1-'. $i;
            $company->first_industry_id = $industry->id;
            $company->second_industry_id = $industry->second_industries->random()->id;
            $company->employee_number_id = $employee_number_ids->random();
            $company->establish_year = rand(1950, date('Y'));
            $company->establish_month = rand(1, 12);
            $company->annual_recruit_number_id = $annual_recruit_number_ids->random();
            $company->consideration_status_id = $consideration_status_ids->random();
            $company->accepted = rand(0, 1);
            $company->admin_memo_private = null;
            $company->recruiting_companies_flg = rand(0, 1);
            $company->save();

            $status_count = rand(1, 5);
            $company_status_ids = collect($company_statuses)->shuffle()->keys();

            foreach ($company_status_ids as $index => $company_status_id) {

                $company_status = new CompanyStatus();
                $company_status->company_id = $company->id;
                $company_status->company_status_id = $company_status_id;
                $company_status->status_changed_at = now()->addDays(rand(-30, 30))->subHours(rand(-23, 23));
                $company_status->status_changed_by = $admin_users->random()->user_id;
                $company_status->save();

                if($index === $status_count) {
                    break;
                }
            }

            if ($i === 1) {

                $company_status = CompanyStatus::orderBy('id', 'desc')->first();
                $company_status->company_status_id = 3; // 承認済み
                $company_status->save();

                // 募集会社を登録する
                $recruitingCompany = new RecruitingCompany();
                $recruitingCompany->fill([
                    'company_id' => $company->id,
                    'name' => '募集会社１－１',
                    'prefecture' => 13,
                    'address' => '東京都墨田区押上1-111',
                    'home_page' => 'http://recruiting-1.company.jp',
                    'industry1' => 5,
                    'industry2' => 22,
                    'employee_number' => 100,
                    'establish_year' => 1999,
                    'establish_month' => 6,
                    'company_profile' => '1億円',
                    'business_content' => '電子商取引',
                    'logo' => 'recruiting-1.png',
                ])
                ->save();

            }

            $company_user = new CompanyUser();
            $company_user->user_id = $user->id;
            $company_user->company_id = $company->id;
            $company_user->name = '会社ユーザー'. $i;
            $company_user->ip = long2ip(mt_rand());
            $company_user->ip = random_int(100000000, 999999999);
            $company_user->save();

        }
    }
}
