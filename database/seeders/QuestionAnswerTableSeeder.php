<?php

namespace Database\Seeders;

use App\Models\QuestionAnswer;

use Illuminate\Database\Seeder;

class QuestionAnswerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // QA
        for($i = 1 ; $i <= 50 ; $i++) {

            $qa = new QuestionAnswer();
            $qa->company_id = 1;
            $qa->offer_info_id = ($i % 10) + 1;
            $qa->offer_info_type = rand(1,2);

            $qa->question_person_id = rand(1,4);
            $qa->question_datetime = date('Y-m-d H:i:s');
            $qa->question_type = ($i % 3) + 1;
            $qa->question_title = '質問タイトル' . $i;
            $qa->question_content = '質問内容' . $i;
            $qa->answer_person_id = ($i % 10) + 1;
            $qa->answer_datetime = date('Y-m-d H:i:s');
            $qa->answer_content = '回答内容' . $i;
            $qa->status = ($i % 2) + 1;
            $qa->reject = 1;

            $qa->save();
        }
    }
}
