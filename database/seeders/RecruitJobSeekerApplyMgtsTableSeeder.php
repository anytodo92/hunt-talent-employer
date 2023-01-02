<?php

namespace Database\Seeders;


use App\Models\RecruitJobSeekerApplyMgts;
use App\Models\RecruitOfferInfo;
use App\Models\SelectionResult;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RecruitJobSeekerApplyMgtsTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 人材紹介_求職者_応募管理(1)
        $mgt = new RecruitJobSeekerApplyMgts();
        $mgt->fill([
            'recruit_job_seeker_id' => 1,
            'recruit_offer_info_id' => 1,
            'selection_status' => 1, // 選考中

            'application' => 1, // 1:応募
            'application_date' => Carbon::now()->addDays(-15)->format('Y-m-d'),

            'document_confirmation' => 1, // 1:確認済、2:書類未確認（！要対応）
            'document_confirmation_date' => Carbon::now()->addDays(-12)->format('Y-m-d'),

            'applicant_screening' => 5, // 選考結果未送付（！要対応）
            'applicant_screening_date' => null,
            'applicant_screening_refusal_reason' => null,
            'applicant_screening_refusal_reason_date' => null,
            'applicant_screening_not_adopted_date' => null,
        ]);
        $mgt->last_selection_flow_number = 3;
        $mgt->last_selection_flow_date = $mgt->{'document_confirmation_date'};
        $mgt->save();

        $this->_updateSelectionFlow(1, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]);

        // 人材紹介_求職者_応募管理(2)
        $mgt = new RecruitJobSeekerApplyMgts();
        $mgt->fill([
            'recruit_job_seeker_id' => 2,
            'recruit_offer_info_id' => 2,
            'selection_status' => 5, // 入社確定
            'application' => 1, // 1:応募
            'application_date' => Carbon::now()->addDays(-18)->format('Y-m-d'),
            'document_confirmation' => 1, // 1:確認済、2:書類未確認（！要対応）
            'document_confirmation_date' => Carbon::now()->addDays(-16)->format('Y-m-d'),
            'applicant_screening' => 2, // 通過
            'applicant_screening_date' => Carbon::now()->addDays(-14)->format('Y-m-d'),
            'applicant_screening_refusal_reason' => '',
            'applicant_screening_refusal_reason_date' => null,
            'applicant_screening_not_adopted_date' => null,
            'writing_web_test' => 2, // 通過
            'writing_web_test_date' => Carbon::now()->addDays(-12)->format('Y-m-d'),
            'writing_web_test_refusal_reason' => '',
            'writing_web_test_refusal_reason_date' => null,
            'writing_web_test_not_adopted_date' => null,
            'interview' => 2, // 通過
            'interview_date' => Carbon::now()->addDays(-10)->format('Y-m-d'),
            'interview_refusal_reason' => '',
            'interview_refusal_reason_date' => null,
            'interview_not_adopted_date' => null,
            '1st_interview' => 2, // 通過
            '1st_interview_date' => Carbon::now()->addDays(-8)->format('Y-m-d'),
            '1st_refusal_reason' => '',
            '1st_refusal_reason_date' => null,
            '1st_not_adopted_date' => null,
            '2nd_interview' => 2, // 通過
            '2nd_interview_date' => Carbon::now()->addDays(-6)->format('Y-m-d'),
            '2nd_refusal_reason' => '',
            '2nd_refusal_reason_date' => null,
            '2nd_not_adopted_date' => null,
            'last_interview' => 2, // 通過
            'last_interview_date' => Carbon::now()->addDays(-5)->format('Y-m-d'),
            'last_refusal_reason' => '',
            'last_refusal_reason_date' => null,
            'last_not_adopted_date' => null,
            'recruitment' => 1,
            'recruitment_date' => Carbon::now()->addDays(-3)->format('Y-m-d'),
            'joining_confirmation' => 1,
            'joining_confirmation_date' => Carbon::now()->addDays(-2)->format('Y-m-d'),
            'joining_confirmation_not_adopted_date' => null,
            'retirement_date' => null,
            'refund_status' => null,
            'refund_apply_date' => null,
            'refund_agreement_date' => null,
            'refund_disagreement_date' => null,
            'refund_amount' => null,
        ]);
        $mgt->last_selection_flow_number = 13;
        $mgt->last_selection_flow_date = $mgt->joining_confirmation_date;
        $mgt->save();

        $this->_updateSelectionFlow(2, [1, 2, 3, 4, 5, 6, 7, 11, 12, 13]);
        $this->_addSelectionResults(2, 1, [1, 2, 3, 4, 5, 6, 7, 11, 12, 13], $mgt->selection_status);

        // 人材紹介_求職者_応募管理(3~75)
        $MAX_STEP_COUNT = 13;
        for ($i = 3; $i <= 75; $i++) {
            $mgt = new RecruitJobSeekerApplyMgts();

            $selectionStatus = rand(1, 5); // 1:選考中、2:落選/辞退、3:内定(入社待ち)、4:入社確定
            $stepCount = rand(2, $MAX_STEP_COUNT);
            $diffDate = $stepCount * 2 + rand(0, 3);
            $mgt->fill([
                'recruit_job_seeker_id' => $i,
                'recruit_offer_info_id' => rand(1,50),
                'selection_status' => $selectionStatus,
                'application' => 1, // 1:応募
                'application_date' => Carbon::now()->addDays(-$diffDate)->format('Y-m-d'),
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
            $flows = []; // {step: 3 ~ 13, status: 1~7}
            if ($selectionStatus == 1) {
                $MAX_STEP = $MAX_STEP_COUNT - 2;
                $stepCount = min($stepCount, $MAX_STEP_COUNT - 2);
            } else if ($selectionStatus == 2) { // 落選/辞退
                $stepCount = max(3, $stepCount);
                $MAX_STEP = $stepCount <= 4 ? $MAX_STEP_COUNT - 2 : $MAX_STEP_COUNT;
            } else if ($selectionStatus == 3) { // 内定(入社待ち)
                $MAX_STEP = $MAX_STEP_COUNT - 1;
                $stepCount = min($stepCount, $MAX_STEP_COUNT - 1);
            } else { // 4: 入社確定
                $stepCount = max(5, $stepCount);
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
                    'status' => 2, // 通過
                    'date' => Carbon::now()->addDays(-$diffDate)->format('Y-m-d'),
                ];
                $diffDate -= rand(1,2);
            }

            switch ($selectionStatus) {
                case 1: // 選考中
                    $flows[$stepCount-1]['status'] = $flows[$stepCount-1]['step'] == 3 ? 5 : rand(5,7);
                    break;
                case 2: // 落選/辞退
                    if ($flows[$stepCount-1]['step'] >= $MAX_STEP_COUNT-1) {
                        $flows[$stepCount-2]['step'] = $MAX_STEP_COUNT-1;
                        $flows[$stepCount-2]['status'] = 3; // 入社条件返答待ち

                        $flows[$stepCount-1]['step'] = $MAX_STEP_COUNT;
                        $flows[$stepCount-1]['status'] = rand(2,3); // 辞退、不採用
                    } else {
                        $flows[$stepCount-1]['status'] = rand(3,4); // 辞退、不採用
                    }
                    break;
                case 3: // 内定(入社待ち)
                    if ($flows[$stepCount-1]['step'] == $MAX_STEP_COUNT-1) {
                        $flows[$stepCount-1]['status'] = rand(1,3); // 採用ステータス
                    } else {
                        $flows[$stepCount-1]['status'] = 1; // 内定
                    }
                    break;
                case 4: // 入社確定
                    $flows[$stepCount-3]['status'] = 1; // 内定

                    $flows[$stepCount-2]['step'] = $MAX_STEP_COUNT-1;
                    $flows[$stepCount-2]['status'] = 1; // 入社予定日あり

                    $flows[$stepCount-1]['step'] = $MAX_STEP_COUNT;
                    $flows[$stepCount-1]['status'] = 1; // 入社
                    break;
            }

            $interviewPrefixes = ['1st', '2nd', '3rd', '4th', '5th'];
            foreach ($flows as $flow) {
                $step = $flow['step'];
                $status = $flow['status'];
                $date = $flow['date'];

                switch ($step) {
                    case 3: // 書類選考
                        $mgt->applicant_screening = $status; // 1:内定、2:通過、3:辞退、4:不採用、5:選考結果未送付（！要対応）
                        if ($status == 1 || $status == 2) {
                            $mgt->applicant_screening_date = $date;
                        } else if ($status == 3 || $status == 4) {
                            $mgt->applicant_screening_refusal_reason = '辞退・不採用理由' . rand(1, 100);
                            if ($status == 3) {
                                $mgt->applicant_screening_refusal_reason_date = $date;
                            } else {
                                $mgt->applicant_screening_not_adopted_date = $date;
                            }
                        }
                    break;
                    case 4: // 筆記、webテスト
                    case 5: // 面談
                        $prefix = $step == 4 ? 'writing_web_test' : 'interview';

                        $mgt->{$prefix} = $status; // 1:内定、2:通過、3:辞退、4:不採用、5:日程未確定（！要対応）、6:日程設定済み、7:選考結果未送付（！要対応）
                        if (in_array($status, [1,2,6,7])) {
                            $mgt->{$prefix . '_date'} = $date;
                        } else if ($status == 3 || $status == 4) {
                            $mgt->{$prefix . '_refusal_reason'} = '辞退・不採用理由' . rand(1, 100);
                            if ($status == 3) {
                                $mgt->{$prefix . '_refusal_reason_date'} = $date;
                            } else {
                                $mgt->{$prefix . '_not_adopted_date'} = $date;
                            }
                        }
                        break;
                    case 6: // 1次面接
                    case 7: // 2次面接
                    case 8: // 3次面接
                    case 9: // 4次面接
                    case 10: // 5次面接
                        $prefix = $interviewPrefixes[$step-6];

                        $mgt->{$prefix . '_interview'} = $status; // 1:内定、2:通過、3:辞退、4:不採用、5:日程未確定（！要対応）、6:日程設定済み、7:選考結果未送付（！要対応）
                        if (in_array($status, [1,2,6,7])) {
                            $mgt->{$prefix . '_interview_date'} = $date;
                        } else if ($status == 3 || $status == 4) {
                            $mgt->{$prefix . '_refusal_reason'} = '辞退・不採用理由' . rand(1, 100);
                            if ($status == 3) {
                                $mgt->{$prefix . '_refusal_reason_date'} = $date;
                            } else {
                                $mgt->{$prefix . '_not_adopted_date'} = $date;
                            }
                        }
                        break;
                    case 11: // 最終面接
                        $mgt->last_interview = $status; // 1:内定、2:通過、3:辞退、4:不採用、5:選考結果未送付（！要対応）
                        if (in_array($status, [1,2,6,7])) {
                            $mgt->last_interview_date = $date;
                        } else if ($status == 3 || $status == 4) {
                            $mgt->last_refusal_reason = '辞退・不採用理由' . rand(1, 100);
                            if ($status == 3) {
                                $mgt->last_refusal_reason_date = $date;
                            } else {
                                $mgt->last_not_adopted_date = $date;
                            }
                        }
                        break;
                    case 12: // 採用
                        $mgt->recruitment = $status; // 1:入社予定日あり、2:入社条件提示・交渉（！要対応）、3:入社条件返答待ち
                        $mgt->recruitment_date = $date;
                        break;
                    case 13: // 入社確認
                        $mgt->joining_confirmation = $status; // 1:入社、2:辞退、3:不採用
                        if ($status == 1) {
                            $mgt->joining_confirmation_date = $date;
                        } else if ($status == 2) {
                            $mgt->joining_confirmation_refusal_reason = '辞退・不採用理由' . rand(1, 100);
                            $mgt->joining_confirmation_refusal_reason_date = $date;
                        } else {
                            $mgt->joining_confirmation_not_adopted_date = $date;
                        }
                        //'retirement_date' => null,
                        //'refund_apply_date' => null,
                        //'refund_agreement_date' => null,
                        break;
                }
            }

            $lastFlow = last($flows);
            $mgt->last_selection_flow_number = $lastFlow['step'];
            $mgt->last_selection_flow_date = $lastFlow['date'];
            $mgt->save();

            // 選考フローを設定する
            $stepNumbers = [1,2];
            foreach ($flows as $flow) {
                $stepNumbers[] = $flow['step'];
            }
            $this->_updateSelectionFlow($i, $stepNumbers);
            $this->_addSelectionResults($i, 1, $stepNumbers, $selectionStatus);
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
        // 人材紹介_求人情報
        $offerInfo = RecruitOfferInfo::findOrFail($offerInfoID);

        // 選考フローを訂正する
        $lastStep = max($stepNumbers);
        if ($lastStep < 12) {
            $additionalStepCount = rand(0, 12 - $lastStep);
            if ($additionalStepCount > 0) {
                $additionalSteps = range($lastStep + 1, 8);
                shuffle($additionalSteps);
                $additionalSteps = array_splice($additionalSteps, 0, $additionalStepCount);
                $stepNumbers = array_merge($stepNumbers, $additionalSteps);

                sort($stepNumbers);
                $lastStep = max($stepNumbers);
            }
        }
        for ($step = 12; $step <= 13; $step ++) { // Required steps 12, 13
            if ($lastStep < $step) {
                $stepNumbers[] = $step;
                $lastStep = $step;
            }
        }

        // 選考フローを更新する
        $offerInfo->selection_flow = implode(',', $stepNumbers);
        $offerInfo->save();
    }

    /**
     * 選考結果を追加する
     *
     * @param $mgtId
     * @param $mgtType
     * @param @stepNumbers
     * @param @selectionStatus // 1:選考中、2:落選/辞退、3:内定(入社待ち)、4:入社確定
     */
    protected function _addSelectionResults($mgtId, $mgtType, $stepNumbers, $selectionStatus)
    {
        for ($i=0; $i<count($stepNumbers); $i++) {
            // 選考結果
            $selectionResult = new SelectionResult();

            $selectionResult->job_seeker_apply_mgt_id = $mgtId;
            $selectionResult->job_seeker_apply_mgt_type = $mgtType;
            $selectionResult->phase = $stepNumbers[$i];

            $selectionResult->next_phase = $i < (count($stepNumbers) - 1) ?? $stepNumbers[$i+1];

            if ($selectionResult->next_phase == 0 && $selectionStatus != 1) { // last step,
                $selectionResult->unseated_reason = rand(1, 8);
                $selectionResult->unseated_reason_sub = rand(1, 3);
                $selectionResult->unseated_cause_detail = 'あああ';
            } else {
                $selectionResult->evaluation_point = rand(1,4);
                $selectionResult->concern_point = 'あああ';
                $selectionResult->interviewer = 'あああ';
                $selectionResult->interview_address = 'あああ';
                $selectionResult->belongings = 'あああ';
                $selectionResult->emergency_contact_address = 'あああ';
                $selectionResult->else_special_note = '面接詳細';
                $selectionResult->interview_setting_person_type = rand(1, 2);
            }

            $selectionResult->save();
        }
    }
}
