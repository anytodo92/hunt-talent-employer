<?php

namespace Database\Seeders;

use App\Models\Todo;

use App\Models\User;
use Illuminate\Database\Seeder;

class TodoTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // To-do
        for($i = 1 ; $i <= 10 ; $i++) {

            $todo = new Todo();
            $todo->company_id = 1;
            $todo->offer_info_id = 1;
            $todo->offer_info_type = rand(1,2);
            $todo->job_seeker_apply_mgt_id = $i;

            $todo->question_and_answer_id = 0;
            $todo->calendar_id = 0;

            $todo->todo_content = 'todoお知らせテスト' . $i;
            $todo->todo_complete = $i % 2;
            $todo->todo_transition_target = 1;
            $todo->read_flg = 1;

            $todo->save();
        }


    }
}
