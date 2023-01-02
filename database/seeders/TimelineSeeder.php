<?php

namespace Database\Seeders;

use App\Models\Timeline;

use App\Models\User;
use Illuminate\Database\Seeder;

class TimelineSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Timeline
        for ($i = 1 ; $i <= 10 ; $i++) {

            $timeline = new Timeline();
            $timeline->company_id = 1;
            $timeline->job_seeker_apply_mgt_id = 1;
            $timeline->job_seeker_apply_mgt_type = rand(1,2);
            $timeline->message_type = rand(1,2);
            $timeline->message_sender = $i;

            $user = User::find($i);
            $timeline->sender_type = $user->user_type_id;
            $timeline->message_title = 'お知らせテスト' . $i;
            $timeline->message_detail = 'timelineお知らせテスト' . $i;
            $timeline->timeline_complete = $i % 2;
            $timeline->timeline_transition_target = 1;
            $timeline->read_flg = 1;

            $timeline->save();
        }
    }
}
