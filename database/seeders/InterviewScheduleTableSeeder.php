<?php

namespace Database\Seeders;

use App\Models\InterviewSchedule;

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class InterviewScheduleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // InterviewSchedule
        for($i = 1 ; $i <= 8 ; $i++) {

            $calendar = new InterviewSchedule();
            $calendar->company_id = 1;
            $calendar->job_seeker_apply_mgt_id = 1;
            $calendar->job_seeker_apply_mgt_type = rand(1,2);
            $calendar->interview_candidates_name = 'テスト'.$i.'太郎';
            $calendar->interview_candidates_date = Carbon::now()->addDays(rand(3,10))->format('Y-m-d');
            $calendar->interview_candidates_from = '10:00:00';
            $calendar->interview_candidates_to = '12:00:00';
            $calendar->interview_content = '面接について';
            $calendar->interview_date_type = rand(1,4);
            $calendar->interview_phase = rand(1,12);

            $calendar->save();
        }
    }
}
