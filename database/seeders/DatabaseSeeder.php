<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(IndustriesTableSeeder::class);

        if(app()->environment(['local', 'staging'])) { // テスト環境のみ

            $this->call(AdminUsersTableSeeder::class);
            $this->call(CompanyUsersTableSeeder::class);

            $this->call(RecruitOfferInfoTableSeeder::class);
            $this->call(RecruitCompanyTableSeeder::class);
            $this->call(RecruitJobSeekerTableSeeder::class);
            $this->call(RecruitJobSeekerApplyMgtsTableSeeder::class);

            $this->call(OutsourceOfferInfoTableSeeder::class);
            $this->call(OutsourceCompanyTableSeeder::class);
            $this->call(OutsourceJobSeekerTableSeeder::class);
            $this->call(OutsourceJobSeekerApplyMgtsTableSeeder::class);

            $this->call(QuestionAnswerTableSeeder::class);
            $this->call(TimelineSeeder::class);
            //$this->call(InterviewScheduleTableSeeder::class);
            $this->call(TodoTableSeeder::class);

            $this->call(WorkPlacesTableSeeder::class);
        }
    }
}
