<?php

namespace Database\Seeders;

use App\Models\SelectionResult;
use App\Models\RecruitJobSeekerApplyMgts;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SelectionResultsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=1; $i<=2; $i++) {
            $res = new SelectionResult();
            $res->fill([
                'job_seeker_apply_mgt_id'=> 35,
                'job_seeker_apply_mgt_type' => 1,
                'phase' => $i,
                'unseated_reason' => 1,
                'unseated_reason_sub' => 1,
                'unseated_cause_detail' => 'unseated_cause_detail',
                'next_phase' => 2,
                'current_evaluation' => 5,
                'evaluation_point' => '評価点',
                'concern_point' => '懸念点',
                'interviewer' => '面接担当者名',
                'interview_address' => '面接場所住所',
                'belongings' => '持ち物',
                'emergency_contact_address' => '緊急連絡先（転職者様のみご利用可）',
                'else_special_note' => 'その他特記事項',
                'interview_setting_person_type' => 2,

            ]);
            $res->save();
        }
    }
}
