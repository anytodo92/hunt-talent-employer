<?php

namespace Database\Seeders;


use App\Models\OutsourceJobSeekerApplyMgts;
use App\Models\OutsourceOfferInfo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OutsourceJobSeekerApplyMgtsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 業務委託_求職者_応募管理(1)
        $mgt = new OutsourceJobSeekerApplyMgts();
        $mgt->fill([
            'outsource_job_seeker_id' => 1,
            'outsource_offer_info_id' => 1,
            'selection_status' => 1, // 選考中

            'proposal' => 1, // 1:応募
            'proposal_date' => Carbon::now()->addDays(-15)->format('Y-m-d'),

            'document_confirmation' => 1, // 1:確認済、2:書類未確認（！要対応）
            'document_confirmation_date' => Carbon::now()->addDays(-12)->format('Y-m-d'),

            'applicant_screening' => 6, // 選考結果未送付（！要対応）
            'applicant_screening_date' => null,
            'applicant_screening_send_off_date' => null,
        ]);
        $mgt->last_selection_flow_number = 3;
        $mgt->last_selection_flow_date = $mgt->{'document_confirmation_date'};
        $mgt->proposal_unit_price = 500000;

        $mgt->save();

        $this->_updateSelectionFlow(1, [1, 2, 3, 4, 5, 6, 7, 8, 9]);

        // 業務委託_求職者_応募管理(2)
        $mgt = new OutsourceJobSeekerApplyMgts();
        $mgt->fill([
            'outsource_job_seeker_id' => 2,
            'outsource_offer_info_id' => 2,
            'selection_status' => 5, // 成約(参画開始待ち)

            'proposal' => 1, // 1:応募
            'proposal_date' => Carbon::now()->addDays(-18)->format('Y-m-d'),

            'document_confirmation' => 1, // 1:確認済、2:書類未確認（！要対応）
            'document_confirmation_date' => Carbon::now()->addDays(-16)->format('Y-m-d'),

            'applicant_screening' => 2, // 通過
            'applicant_screening_date' => Carbon::now()->addDays(-14)->format('Y-m-d'),
            'applicant_screening_send_off_date' => null,

            '1st_interview' => 2, // 通過
            '1st_interview_date' => Carbon::now()->addDays(-8)->format('Y-m-d'),
            '1st_refusal_reason' => '',
            '1st_refusal_reason_date' => null,
            '1st_send_off_date' => null,

            '2nd_interview' => 2, // 通過
            '2nd_interview_date' => Carbon::now()->addDays(-6)->format('Y-m-d'),
            '2nd_refusal_reason' => '',
            '2nd_refusal_reason_date' => null,
            '2nd_send_off_date' => null,

            'last_interview' => 2, // 通過
            'last_interview_date' => Carbon::now()->addDays(-5)->format('Y-m-d'),
            'last_refusal_reason' => '',
            'last_refusal_reason_date' => null,
            'last_send_off_date' => null,

            'contract' => 1,
            'offer_date' => Carbon::now()->addDays(-3)->format('Y-m-d'),
            'contract_satisfied_date' => Carbon::now()->addDays(-3)->format('Y-m-d'),
            'joining_scheduled_date' => Carbon::now()->addDays(-3)->format('Y-m-d'),
            'joining_confirmation' => 1,
            'joining_confirmation_start_date' => Carbon::now()->addDays(-2)->format('Y-m-d'),
            'current_state' => null,
            'joining_end_date' => null,
        ]);
        $mgt->last_selection_flow_number = 10;
        $mgt->last_selection_flow_date = $mgt->joining_confirmation_start_date;
        $mgt->proposal_unit_price = 400000;
        $mgt->save();

        $this->_updateSelectionFlow(2, [1, 2, 3, 4, 5, 6, 8, 9, 10]);

        // 業務委託_求職者_応募管理(3~75)
        $MAX_STEP_COUNT = 10;
        for ($i = 3; $i <= 75; $i++) {
            $mgt = new OutsourceJobSeekerApplyMgts();

            $selectionStatus = rand(1, 7); // 1:選考中、2:見送り/辞退、3:内定(参画開始待ち)、4:参画中、5:参画終了
            $stepCount = rand(2, $MAX_STEP_COUNT);
            $diffDate = $stepCount * 2 + rand(0, 3);
            $mgt->fill([
                'outsource_job_seeker_id' => $i,
                'outsource_offer_info_id' => $i,
                'selection_status' => $selectionStatus,
                'proposal' => 1, // 1:エントリー
                'proposal_date' => Carbon::now()->addDays(-$diffDate)->format('Y-m-d'),
            ]);
            $diffDate -= rand(1,2);

            // Step2: 書類確認
            $mgt->document_confirmation = 1; // 1:確認済、2:書類未確認（！要対応）
            $mgt->document_confirmation_date = $mgt->document_confirmation == 1 ? Carbon::now()->addDays(-$diffDate)->format('Y-m-d') : null;
            if ($stepCount == 2) {
                $mgt->selection_status = 1;
                $mgt->document_confirmation = 2;
                $mgt->last_selection_flow_number = 2;
                $mgt->last_selection_flow_date = $mgt->document_confirmation_date;
                $mgt->save();

                $this->_updateSelectionFlow($i, [1, 2]);
                continue;
            }
            $diffDate -= rand(1,2);

            $mgt->selection_status = $selectionStatus;

            // 選考の流れを生成する
            $flows = []; // {step: 3 ~ 11, status: 1~8}
            if ($selectionStatus == 1) { // 選考中
                $MAX_STEP = $MAX_STEP_COUNT - 3;
                $stepCount = min($stepCount, $MAX_STEP_COUNT - 3);
            } else if ($selectionStatus == 2) { // 見送り/辞退
                $MAX_STEP = $stepCount <= 4 ? $MAX_STEP_COUNT - 3 : $MAX_STEP_COUNT - 1;
                $stepCount = min($stepCount, $MAX_STEP_COUNT - 1);
            } else if ($selectionStatus == 3) { // 内定(参画開始待ち)
                $MAX_STEP = $MAX_STEP_COUNT - 2;
                $stepCount = min($stepCount, $MAX_STEP_COUNT - 2);
            } else if ($selectionStatus == 4 || $selectionStatus == 5) { // 4: 参画中、5: 参画終了
                $stepCount = max(7, $stepCount);
                $MAX_STEP = $MAX_STEP_COUNT;
            }
            $stepNumbers = range(3, $MAX_STEP);
            shuffle($stepNumbers);
            $stepCount -= 2; // passed step1,2 already
            $stepNumbers = array_splice($stepNumbers, 0, $stepCount);
            sort($stepNumbers);

            foreach ($stepNumbers as $stepNumber) {
                $flows[] = [
                    'step' => $stepNumber,
                    'status' => 3, // 通過
                    'date' => Carbon::now()->addDays(-$diffDate)->format('Y-m-d')
                ];
                $diffDate -= rand(1,2);
            }

            switch ($selectionStatus) {
                case 1: // 選考中
                    if (rand(0,1) == 0) {
                        $flows[$stepCount-1]['status'] = 1; // オファー
                    } else {
                        $flows[$stepCount-1]['status'] = $flows[$stepCount-1]['step'] == 3 ? 6 : rand(6,8);
                    }
                    break;
                case 2: // 見送り/辞退
                    if ($flows[$stepCount-1]['step'] >= $MAX_STEP_COUNT-2) {
                        $flows[$stepCount-2]['step'] = $MAX_STEP_COUNT-2;
                        $flows[$stepCount-2]['status'] = 3; // 入社条件返答待ち

                        $flows[$stepCount-1]['step'] = $MAX_STEP_COUNT-1;
                        $flows[$stepCount-1]['status'] = rand(2,3); // 2:辞退、3:見送り
                    } else {
                        $flows[$stepCount-1]['status'] = rand(4,5); // 辞退、見送り
                    }
                    break;
                case 3: // 内定(参画開始待ち)
                    if ($flows[$stepCount-1]['step'] == $MAX_STEP_COUNT-2) {
                        $flows[$stepCount-1]['status'] = rand(1,3); // 契約ステータス
                    } else {
                        $flows[$stepCount-1]['status'] = 2; // 成約
                    }
                    break;
                case 4: // 参画中
                case 5: // 参画終了
                    $flows[$stepCount-4]['status'] = 2; // 成約

                    $flows[$stepCount-3]['step'] = $MAX_STEP_COUNT-2;
                    $flows[$stepCount-3]['status'] = 1; // 参画予定日あり

                    $flows[$stepCount-2]['step'] = $MAX_STEP_COUNT-1;
                    $flows[$stepCount-2]['status'] = 1; // 参画開始

                    $flows[$stepCount-1]['step'] = $MAX_STEP_COUNT;
                    $flows[$stepCount-1]['status'] = $selectionStatus == 4 ? 1/*参画中*/ : 2/*終了*/;
                    break;
            }

            $interviewPrefixes = ['1st', '2nd', '3rd', 'last'];
            foreach ($flows as $flow) {
                $step = $flow['step'];
                $status = $flow['status'];
                $date = $flow['date'];

                switch ($step) {
                    case 3: // 書類選考
                        $mgt->applicant_screening = $status; // 1:オファー、2:成約、3:通過、4:辞退、5:見送り、6:日程未確定（！要対応）
                        if ($status == 1 || $status == 2 || $status == 3) {
                            $mgt->applicant_screening_date = $date;
                        } else if ($status == 3 || $status == 4) {
                            //$mgt->applicant_screening_refusal_reason = '辞退・見送り理由' . rand(1, 100);
                            $mgt->applicant_screening_send_off_date = $date;
                        }
                        break;
                    case 4: // 筆記、webテスト
                        $mgt->writing_web_test = $status; // 1:オファー、2:成約、3:通過、4:辞退、5:見送り、6:日程未確定（！要対応）、7:日程設定済み、8:選考結果未送付（！要対応）
                        if (in_array($status, [1,2,3,7,8])) {
                            $mgt->writing_web_test_date = $date;
                        } else if ($status == 4 || $status == 5) {
                            $mgt->writing_web_test_send_off_date = $date;
                        }
                        break;
                    case 5: // 1次面接
                    case 6: // 2次面接
                    case 7: // 3次面接
                    case 8: // 最終選考
                        $prefix = $interviewPrefixes[$step-5];

                        $mgt->{$prefix . '_interview'} = $status; // 1:オファー、2:成約、3:通過、4:辞退、5:見送り、6:日程未確定（！要対応）、7:日程設定済み、8:選考結果未送付（！要対応）
                        if (in_array($status, [1,2,3,7,8])) {
                            $mgt->{$prefix . '_interview_date'} = $date;
                        } else if ($status == 4 || $status == 5) {
                            $mgt->{$prefix . '_refusal_reason'} = '辞退・見送り理由' . rand(1, 100);
                            if ($status == 4) {
                                $mgt->{$prefix . '_refusal_reason_date'} = $date;
                            } else {
                                if ($step == 8) {
                                    $mgt->last_send_off_date = $date;
                                } else {
                                    $mgt->{$prefix . '_send_off_date'} = $date;
                                }
                            }
                        }
                        break;
                    case 9: // 契約
                        $mgt->contract = $status; // 1:参画予定日あり、2:契約条件提示・交渉（！要対応）、3:契約条件同意待ち
                        if ($status == 1) {
                            $mgt->contract_satisfied_date = $date;
                            $mgt->joining_scheduled_date = date('Y-m-d', strtotime($date) + 86400);
                        }
                        break;
                    case 10: // 参画確認
                        $mgt->joining_confirmation = $status; // 1:参画開始、2:辞退、3:見送り
                        if ($status == 1) {
                            $mgt->joining_confirmation_start_date = $date;
                        } else if ($status == 2) {
                            $mgt->joining_confirmation_refusal_reason = '辞退・見送り理由' . rand(1, 100);
                            $mgt->joining_confirmation_refusal_reason_date = $date;
                        } else {
                            $mgt->joining_confirmation_send_off_date = $date;
                        }
                        break;
                    case 11: // 現況
                        $mgt->current_state = $status; // 1:参画中、2:終了
                        if ($status == 2) {
                            $mgt->joining_end_date = $date;
                        }
                        break;
                }
                $diffDate -= rand(1,2);
            }

            $lastFlow = last($flows);
            $mgt->last_selection_flow_number = $lastFlow['step'];
            $mgt->last_selection_flow_date = $lastFlow['date'];
            $mgt->proposal_unit_price = rand(1,9) * 100000;
            $mgt->save();

            // 選考フローを設定する
            $stepNumbers = [1,2];
            foreach ($flows as $flow) {
                $stepNumbers[] = $flow['step'];
            }
            $this->_updateSelectionFlow($i, $stepNumbers);
        }

    }

    /**
     * 選考フローを更新する
     *
     * @param $offerInfoID
     * @param $stepNumbers
     */
    protected function _updateSelectionFlow($offerInfoID, $stepNumbers)
    {
        // 業務委託_求人情報
        $offerInfo = OutsourceOfferInfo::findOrFail($offerInfoID);

        // 選考フローを訂正する
        $lastStep = max($stepNumbers);
        if ($lastStep < 8) {
            $additionalStepCount = rand(0, 8 - $lastStep);
            if ($additionalStepCount > 0) {
                $additionalSteps = range($lastStep + 1, 8);
                shuffle($additionalSteps);
                $additionalSteps = array_splice($additionalSteps, 0, $additionalStepCount);
                $stepNumbers = array_merge($stepNumbers, $additionalSteps);

                sort($stepNumbers);
                $lastStep = max($stepNumbers);
            }
        }
        for ($step = 9; $step <= 11; $step ++) { // Required steps 9, 10, 11
            if ($lastStep < $step) {
                $stepNumbers[] = $step;
                $lastStep = $step;
            }
        }

        // 選考フローを更新する
        $offerInfo->selection_flow = implode(',', $stepNumbers);
        $offerInfo->save();
    }
}
