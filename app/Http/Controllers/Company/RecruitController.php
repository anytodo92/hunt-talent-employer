<?php

namespace App\Http\Controllers\Company;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use App\Models\CompanyUser;
use App\Models\JobSeekerAttachment;
use App\Models\JoiningConditionPresent;
use App\Models\JoiningConditionPresentAttachments;
use App\Models\RecruitJobSeeker;
use App\Models\RecruitJobSeekerApplyMgts;
use App\Models\RecruitOfferInfo;
use App\Models\SelectionResult;
use App\Models\Timeline;
use App\Models\Todo;
use App\Models\FirstIndustry;
use App\Models\SecondIndustry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use RecursiveIterator;

class RecruitController extends Controller
{
    /**
     * RecruitController constructor.
     */
    public function __construct()
    {
    }

    /**
     * 【求人企業】人材紹介の選考一覧
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        // 求人情報のタイトルを取得する（検索条件として使う）
        $login_company_id = Auth::user()->company_user->company_id;
        $jobTitles = RecruitOfferInfo::where('company_id', $login_company_id)
            ->select('id', 'job_title')
            ->get();

        // 更新日の検索条件に表示する年・月のリスト
        $updated_range = RecruitJobSeekerApplyMgts::select(DB::raw('max(updated_at) as maxDate, min(updated_at) as minDate'))->first();

        $maxDate = $updated_range->maxDate;
        $minDate = $updated_range->minDate;

        $maxYear  = date('Y', strtotime($maxDate));
        $maxMonth = date('m', strtotime($maxDate));
        $minYear  = date('Y', strtotime($minDate));
        $minMonth = date('m', strtotime($minDate));

        $dateArray = [];

        while ($minYear < $maxYear || $minMonth <= $maxMonth) {
            $dateArray[] = [
                'key'   => sprintf("%d-%02d", $minYear, $minMonth),
                'value' => sprintf("%d年 %02d月", $minYear, $minMonth)
            ];
            $minMonth++;
            if ($minMonth == 13) {
                $minMonth = 1;
                $minYear ++;
            }
        }
        $dateArray = array_reverse($dateArray);

        return view('company.recruit.index')->with([
            'jobTitles' => $jobTitles,
            'dateArray' => $dateArray
        ]);
    }

    /**
     * DataTableからの要請に応答する
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function datatable(Request $request)
    {
        $params = $request->all();

        $recruitJobSeekerMgts = new RecruitJobSeekerApplyMgts();
        $result = $recruitJobSeekerMgts->datatableForCompany($params);

        // 性別、生年月日、職種カテゴリーの表示データを取得する
        $records = $result['data'];

        foreach($records as &$record) {
            $record->sex = g_enum('sex', $record->sex);
            $record->birthday = g_age($record->birthday);

            // 「面談日程未確定」の時などで、相手側の日程待ちの状態の場合（求人企業側で対応することではない状態の場合は全て）には一覧の「！要対応」の文字を非表示
            $record->is_pending = false;
            if ($record->last_selection_flow_number >= 4/*筆記、webテスト*/ && $record->last_selection_flow_number <= 11/*最終面接*/) {
                $result_key = g_enum('recruit_apply_mgt_selection_result_key', $record->last_selection_flow_number);
                if ($record->{$result_key} == 5) { // 5/*5:面接日程未確定（！要対応）*/
                    // 1:求人企業
                    if ($record->interview_setting_person_type == 1) {
                        $interview_date_registerd = InterviewSchedule::where('job_seeker_apply_mgt_id', $record->id)
                            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                            ->where('interview_phase', $record->last_selection_flow_number)
                            ->where('interview_date_type', 1)   // 1:候補日
                            ->whereNull('deleted_at')
                            ->whereNull('deleted_by')
                            ->count();
                        // 人材紹介会社に候補日を提示した場合
                        if ($interview_date_registerd > 0) {
                            $record->is_pending = true;
                        }
                    }
                    // 2:候補者（※人材紹介会社担当者または業務委託会社担当者が管理する候補者）
                    else if ($record->interview_setting_person_type == 2) {
                        $interview_date_registerd = InterviewSchedule::where('job_seeker_apply_mgt_id', $record->id)
                            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                            ->where('interview_phase', $record->last_selection_flow_number)
                            ->whereIn('interview_date_type', array(1, 2))   // 1:候補日, 2:確定した日
                            ->whereNull('deleted_at')
                            ->whereNull('deleted_by')
                            ->count();
                        // 人材紹介会社から候補日が提示されなかった場合
                        if ($interview_date_registerd == 0) {
                            $record->is_pending = true;
                        }
                    }
                }
            }

            $record->occupation_category_2 = config('constants.category_2')[$record->occupation_category_1][$record->occupation_category_2];
            $record->occupation_category_1 = config('constants.category_1')[$record->occupation_category_1];
        }

        // ステータス別の件数を表示するHTML
        $html = '';
        $currentStatus = $params['extra']['status'];
        for ($i = 1 ; $i <= 6 ; $i ++) {
            if ($i == 2) { continue; }  // 未応募のステータスは非表示（求人企業）
            $html = $html . "<span class='recruit_status " . ($i == $currentStatus ? 'bold' : '') . "' data-id='" . $i . "'>" . g_enum('recruit_status', $i) . " " . $result['statusCount'][$i] . "  </span>";
        }

        $result['data'] = $records;
        $result['statusCountHtml'] = $html;

        return response()->json($result);
    }

    /**
     * 【求人企業】人材紹介＿選考詳細
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function detail($id)
    {
        $mgt = RecruitJobSeekerApplyMgts::findOrFail($id);

        // 関連情報を取得する
        $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
        $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報
        $recruitCompany = $jobSeeker->recruitCompany; // 人材紹介会社
        $recruitingCompany = $offerInfo->recruitingCompany; // 募集企業

        // まだ「STEP1 応募」、「STEP2 書類確認：未」段階の場合
        if ($mgt->last_selection_flow_number < 3 && $mgt->selection_status != 2/*2:未応募*/) {
            $mgt->selection_status = 1; // 1:選考中

            // 「STEP3 書類選考」段階に自動的に進む
            $mgt->document_confirmation = 1;
            $mgt->document_confirmation_date = date('Y-m-d');

            $mgt->last_selection_flow_number = 3;   // g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);
            $mgt->applicant_screening = $mgt->last_selection_flow_number == 3 ? 5 : 7; // 5:選考結果未送付（！要対応） 7:選考結果未送付（！要対応）
            $mgt->applicant_screening_date = date('Y-m-d');
            $mgt->last_selection_flow_date = date('Y-m-d');

            $mgt->save();

            // 選考結果レコードを生成する
            $selectionResult = new SelectionResult();
            $selectionResult->job_seeker_apply_mgt_id = $id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $selectionResult->phase = $mgt->last_selection_flow_number;
            $selectionResult->next_phase = g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);

            $selectionResult->save();

            // TODO削除
            Todo::where('job_seeker_apply_mgt_id', $id)
                ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                ->delete();

            // TODO発生：面接などで設定した日時が経過した後。
            $todo = new Todo();
            $todo->company_id = $offerInfo->company_id;
            $todo->offer_info_id = $offerInfo->id;
            $todo->offer_info_type = Constants::BIZ_TYPE_RECRUIT;
            $todo->job_seeker_apply_mgt_id = $id;
            $todo->question_and_answer_id = null;
            $todo->calendar_id = null;
            // 「〇〇求職者名〇〇さんの〇〇面接の選考結果の送信をお願い致します。」
            $todo->todo_content = $jobSeeker->last_name . ' ' . $jobSeeker->first_name."さんの".g_enum('recruit_interview_flow', $mgt->last_selection_flow_number)."の選考結果の送信をお願い致します。";
            $todo->todo_complete = 1;   // 1:未完了
            // 遷移先：該当する「選考詳細ページ」
            $todo->todo_transition_target = 1;  // 1:求人企業：人材紹介選考詳細画面
            $todo->read_flg = 1;    // 1:未読
            $todo->created_at = date('Y-m-d H:i:s');
            $todo->created_by = Auth::user()->id;

            $todo->save();
        }
        else if ($mgt->last_selection_flow_number >= 4 && $mgt->last_selection_flow_number <= 11) {
            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            if ($mgt->{$result_key} == 6 && !empty($mgt->{$result_key.'_date'})) { // 6:面接設定済み
                $interview_date = InterviewSchedule::where('job_seeker_apply_mgt_id', $id)
                    ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                    ->where('interview_phase', $mgt->last_selection_flow_number)
                    ->where('interview_date_type', 2)   // 2:確定した日(=◯)
                    ->whereNull('deleted_at')
                    ->whereNull('deleted_by')
                    ->first();

                if (!empty($interview_date) && date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($interview_date->interview_candidates_date)).' '.$interview_date->interview_candidates_to)) < date('Y-m-d H:i:s')) {
                    $mgt->{$result_key} = 7; // 7:選考結果未送付（！要対応）
                    $mgt->save();

                    // TODO削除
                    Todo::where('job_seeker_apply_mgt_id', $id)
                        ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                        ->delete();

                    // TODO発生：面接などで設定した日時が経過した後。
                    $todo = new Todo();
                    $todo->company_id = $offerInfo->company_id;
                    $todo->offer_info_id = $offerInfo->id;
                    $todo->offer_info_type = Constants::BIZ_TYPE_RECRUIT;
                    $todo->job_seeker_apply_mgt_id = $id;
                    $todo->question_and_answer_id = null;
                    $todo->calendar_id = null;
                    // 「〇〇求職者名〇〇さんの〇〇面接の選考結果の送信をお願い致します。」
                    $todo->todo_content = $jobSeeker->last_name . ' ' . $jobSeeker->first_name."さんの".g_enum('recruit_interview_flow', $mgt->last_selection_flow_number)."の選考結果の送信をお願い致します。";
                    $todo->todo_complete = 1;   // 1:未完了
                    // 遷移先：該当する「選考詳細ページ」
                    $todo->todo_transition_target = 1;  // 1:求人企業：人材紹介選考詳細画面
                    $todo->read_flg = 1;    // 1:未読
                    $todo->created_at = date('Y-m-d H:i:s');
                    $todo->created_by = Auth::user()->id;

                    $todo->save();
                }
            }
        }
        else if ($mgt->last_selection_flow_number == 12) {
            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            if ($mgt->{$result_key} == 2) { // 2:オファー面談設定済み
                $interview_date = InterviewSchedule::where('job_seeker_apply_mgt_id', $id)
                    ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                    ->where('interview_phase', $mgt->last_selection_flow_number)
                    ->where('interview_date_type', 2)   // 2:確定した日(=◯)
                    ->whereNull('deleted_at')
                    ->whereNull('deleted_by')
                    ->first();

                if (!empty($interview_date) && date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($interview_date->interview_candidates_date)).' '.$interview_date->interview_candidates_to)) < date('Y-m-d H:i:s')) {
                    $mgt->{$result_key} = 3; // 3:入社条件提示・交渉（！要対応）
                    $mgt->save();
                }
            }
        }

        //60日経過したファイルを削除する
        // /storage/timeline/
        $timeline_files = @scandir(storage_path('/app/public/timeline/'));
        foreach ($timeline_files as $file) {
            $path = storage_path('/app/public/timeline/').$file;
            if (is_file($path) && file_exists($path) && filemtime($path) < time()-60*24*60*60) {
                unlink($path);
            }
        }

        // 関連情報を取得する
        $workPlaces = $offerInfo->workPlaces; // 勤務地
        $selectionResults = $mgt->selectionResults; // 選考結果
        $interviewSchedules = $mgt->calendars; // 面接日程
        $timelines = $mgt->timelines; // タイムライン
        $joiningConditionPresents = $mgt->joiningConditionPresents;
        $offerCompanyUser = $offerInfo->offerCompanyUser;

        $attachments = JobSeekerAttachment::where('job_seeker_id', '=', $mgt->recruit_job_seeker_id)
            ->where('job_seeker_type', '=', Constants::BIZ_TYPE_RECRUIT)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->select('attachment_type', 'attachment', 'attachment_name', 'upload_datetime')
            ->get();

        // 入社条件の添付ファイル
        $joinConditionAttachments = null;
        if (count($joiningConditionPresents) > 0) {
            $joinConditionAttachments = JoiningConditionPresentAttachments::where('joining_condition_presents_id', $joiningConditionPresents[0]->id)
                ->whereNull('deleted_at')
                ->whereNull('deleted_by')
                ->select('attachment', 'attachment_name', 'upload_datetime')
                ->get();
        }

        $first_industry = FirstIndustry::find($recruitingCompany->industry1)->name;
        $second_industry = SecondIndustry::find($recruitingCompany->industry2)->name;

        return view('company.recruit.detail')->with([
            'id' => $id,
            'recruitJobSeekerApplyMgt' => $mgt,
            'recruitCompany' => $recruitCompany,
            'recruitingCompany' => $recruitingCompany,
            'jobSeeker' => $jobSeeker,
            'offerInfo' => $offerInfo,
            'workPlaces' => $workPlaces,
            'selectionFlow' => $offerInfo->selection_flow,
            'attachments' => $attachments,
            'selectionResults' => $selectionResults,
            'interviewSchedules' => $interviewSchedules,
            'timelines' => $timelines,
            'joiningConditionPresents' => $joiningConditionPresents,
            'joinConditionAttachments' => $joinConditionAttachments,
            'offerCompanyUser' => $offerCompanyUser,
            'first_industry' => $first_industry,
            'second_industry' => $second_industry,
            'now' => date('Y-m-d'),
        ]);
    }

    /**
     * 不採用
     *
     * @return \Illuminate\Http\Response
     */
    public function sendNotAdoptedReason(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $unseated_reason = $request->input('unseated_reason');
        $unseated_reason_sub = $request->input('unseated_reason_sub');
        $unseated_cause_detail = $request->input('unseated_cause_detail');

        $unseated_cause_detail = trim($unseated_cause_detail);

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            $mgt->selection_status = 3; // 3:落選/辞退

            if ($mgt->last_selection_flow_number == 12) {
                $mgt->last_selection_flow_number = 13;
                $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
                $mgt->{$result_key} = 3; // 3:不採用
            }
            else {
                $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
                $mgt->{$result_key} = 4; // 4:不採用
            }
            $result_key = str_replace('_interview', '', $result_key);
            $mgt->{$result_key.'_not_adopted_date'} = date('Y-m-d');
            $mgt->last_selection_flow_date = date('Y-m-d');

            $mgt->save();

            // 選考結果レコードを生成する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('phase', $mgt->last_selection_flow_number)
                ->first();
            if (empty($selectionResult)) {
                $selectionResult = new SelectionResult();
                $selectionResult->created_by = Auth::user()->id;
            }
            $selectionResult->job_seeker_apply_mgt_id = $apply_mgt_id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $selectionResult->phase = $mgt->last_selection_flow_number;
            $selectionResult->next_phase = g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);
            $selectionResult->unseated_reason = $unseated_reason;
            $selectionResult->unseated_reason_sub = $unseated_reason_sub;
            $selectionResult->unseated_cause_detail = $unseated_cause_detail;
            $selectionResult->updated_by = Auth::user()->id;

            $selectionResult->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            if ($mgt->last_selection_flow_number == 13) {
                // ・内定が取り消された時。「内定が取消されました。」。理由などを表示。
                $timeline->message_title = "内定が取消されました。";
                $timeline->message_detail = "取消理由：";
            }
            else {
                // ・書類選考不合格時。「書類選考結果「不採用」」
                // ・面接で不採用時。「〇〇（一次や最終など）〇〇面接結果「不採用」」
                $timeline->message_title = g_enum('recruit_interview_flow', $mgt->last_selection_flow_number)."結果「不採用」";
                $timeline->message_detail = "不採用理由：";
            }
            $timeline->message_detail .= g_enum('recruit_unseated_reason', $selectionResult->unseated_reason)."／";
            $timeline->message_detail .= g_enum('recruit_unseated_reason_sub', $selectionResult->unseated_reason)[$selectionResult->unseated_reason_sub]."\r\n";
            $timeline->message_detail .= $selectionResult->unseated_cause_detail;
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // TODO削除：選考結果の送信完了後
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                ->delete();

            return response()->json(['success'=>true,
                'recruitApplyMgt' => $mgt,
                'selectionResults' => $mgt->selectionResults,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 選考通過 次の選考へ
     *
     * @return \Illuminate\Http\Response
     */
    public function sendPassSelection(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $next_phase = $request->input('next_phase');
        $current_evaluation = $request->input('current_evaluation');
        $evaluation_point = $request->input('evaluation_point');
        $concern_point = $request->input('concern_point');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            // 選考結果レコードを生成する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('phase', $mgt->last_selection_flow_number)
                ->first();
            if (empty($selectionResult)) {
                $selectionResult = new SelectionResult();
                $selectionResult->created_by = Auth::user()->id;
            }
            $selectionResult->job_seeker_apply_mgt_id = $apply_mgt_id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $selectionResult->phase = $mgt->last_selection_flow_number;
            $selectionResult->next_phase = $next_phase; // g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);
            $selectionResult->current_evaluation = $current_evaluation;
            $selectionResult->evaluation_point = $evaluation_point;
            $selectionResult->concern_point = $concern_point;
            $selectionResult->updated_by = Auth::user()->id;

            $selectionResult->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・書類選考通過時。「書類選考「通過」。次の選考は〇〇（一次や最終など）〇〇面接となります。」
            $timeline->message_title = g_enum('recruit_selection_flow', $mgt->last_selection_flow_number)."「通過」";
            $timeline->message_detail = "次の選考は".g_enum('recruit_selection_flow', $next_phase)."となります。"."\r\n";
            $timeline->message_detail .= "現状の評価：".g_enum('recruit_evaluation', $current_evaluation)."\r\n";
            $timeline->message_detail .= "評価点：".$evaluation_point."\r\n";
            $timeline->message_detail .= "懸念点：".$concern_point;
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // Todoに追加
            if ($next_phase >= 4/*筆記/webテスト*/ && $next_phase <= 11/*最終選考*/) {
                // TODO削除：選考結果の送信完了後
                Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                    ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                    ->delete();

                // TODO発生：書類選考などの選考通過後、次の選考が面接または面談の場合、候補日をどうするか設定していない時（１　面接設定時の画面）
                $todo = new Todo();
                $todo->company_id = $offerInfo->company_id;
                $todo->offer_info_id = $offerInfo->id;
                $todo->offer_info_type = Constants::BIZ_TYPE_RECRUIT;
                $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
                $todo->question_and_answer_id = null;
                $todo->calendar_id = null;
                // 「〇〇求職者名〇〇さんの〇〇面接の候補日に関する設定をお願い致します。」
                $todo->todo_content = $jobSeeker->last_name . ' ' . $jobSeeker->first_name."さんの".g_enum('recruit_interview_flow', $next_phase)."の候補日に関する設定をお願い致します。";
                $todo->todo_complete = 1;   // 1:未完了
                // 遷移先：該当する「選考詳細ページ」
                $todo->todo_transition_target = 1;  // 1:求人企業：人材紹介選考詳細画面
                $todo->read_flg = 1;    // 1:未読
                $todo->created_at = date('Y-m-d H:i:s');
                $todo->created_by = Auth::user()->id;

                $todo->save();

                // TODO発生：上記の後に「面接の詳細」を入力していない時。
                $todo = new Todo();
                $todo->company_id = $offerInfo->company_id;
                $todo->offer_info_id = $offerInfo->id;
                $todo->offer_info_type = Constants::BIZ_TYPE_RECRUIT;
                $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
                $todo->question_and_answer_id = null;
                $todo->calendar_id = null;
                // 「〇〇求職者名〇〇さんとの「〇〇面接の詳細」についてご入力と送信をお願い致します。」
                $todo->todo_content = $jobSeeker->last_name . ' ' . $jobSeeker->first_name."さんとの「".g_enum('recruit_interview_flow', $next_phase)."の詳細」についてご入力と送信をお願い致します。";
                $todo->todo_complete = 1;   // 1:未完了
                // 遷移先：該当する「選考詳細ページ」
                $todo->todo_transition_target = 1;  // 1:求人企業：人材紹介選考詳細画面
                $todo->read_flg = 1;    // 1:未読
                $todo->created_at = date('Y-m-d H:i:s');
                $todo->created_by = Auth::user()->id;

                $todo->save();
            }

            // update apply-management
            $mgt->selection_status = 1; // 1:選考中

            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 2; // 2:通過
            if ($mgt->last_selection_flow_number == 3) {
                $mgt->{$result_key.'_date'} = date('Y-m-d');
            }
            $mgt->last_selection_flow_number = $selectionResult->next_phase;
            $mgt->last_selection_flow_date = date('Y-m-d');

            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            if ($mgt->last_selection_flow_number <= 11) {
                $mgt->{$result_key} = 5;    // 5:日程未確定（！要対応）
            }
            else {
                $mgt->{$result_key} = 3;    // 3:入社条件提示・交渉（！要対応）
            }

            $mgt->save();

            return response()->json(['success'=>true,
                'recruitApplyMgt' => $mgt,
                'selectionResults' => $mgt->selectionResults,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 内定する
     *
     * @return \Illuminate\Http\Response
     */
    public function sendHire(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            // 選考結果レコードを生成する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('phase', $mgt->last_selection_flow_number)
                ->first();
            if (empty($selectionResult)) {
                $selectionResult = new SelectionResult();
                $selectionResult->created_by = Auth::user()->id;
            }
            $selectionResult->job_seeker_apply_mgt_id = $apply_mgt_id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $selectionResult->phase = $mgt->last_selection_flow_number;
            $selectionResult->next_phase = 12; // 採用 // g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);
            $selectionResult->updated_by = Auth::user()->id;

            $selectionResult->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・内定にした時。「選考結果「内定」が確定」
            $timeline->message_title = g_enum('recruit_selection_flow', $mgt->last_selection_flow_number)."結果「内定」が確定";
            $timeline->message_detail = "内定日：".date('Y年n月j日');
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // update apply-management
            $mgt->selection_status = 4; // 4:内定(入社待ち)

            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 1; // 1:内定
            if ($mgt->last_selection_flow_number == 3) {
                $mgt->{$result_key.'_date'} = date('Y-m-d');
            }
            $mgt->last_selection_flow_number = $selectionResult->next_phase;
            $mgt->last_selection_flow_date = date('Y-m-d');

            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 1; // 1:オファー面談日程未確定(！要対応)
            $mgt->{$result_key.'_date'} = date('Y-m-d'); // 内定日

            $mgt->save();

            // TODO削除：選考結果の送信完了後
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                ->delete();

            // TODO発生：内定後、入社手続きの案内（オファー面談or入社条件提示）が未入力の場合。
            $todo = new Todo();
            $todo->company_id = $offerInfo->company_id;
            $todo->offer_info_id = $offerInfo->id;
            $todo->offer_info_type = Constants::BIZ_TYPE_RECRUIT;
            $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
            $todo->question_and_answer_id = null;
            $todo->calendar_id = null;
            // 「〇〇求職者名〇〇さんの入社手続きに関する案内をお願いします。」
            $todo->todo_content = $jobSeeker->last_name . ' ' . $jobSeeker->first_name."さんの入社手続きに関する案内をお願いします。";
            $todo->todo_complete = 1;   // 1:未完了
            // 遷移先：該当する「選考詳細ページ」
            $todo->todo_transition_target = 1;  // 1:求人企業：人材紹介選考詳細画面
            $todo->read_flg = 1;    // 1:未読
            $todo->created_at = date('Y-m-d H:i:s');
            $todo->created_by = Auth::user()->id;

            $todo->save();

            return response()->json(['success'=>true,
                'recruitApplyMgt' => $mgt,
                'selectionResults' => $mgt->selectionResults,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 面接詳細を入力する
     *
     * @return \Illuminate\Http\Response
     */
    public function sendInterviewDetail(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $interviewer = $request->input('interviewer');
        $interview_address = $request->input('interview_address');
        $belongings = $request->input('belongings');
        $emergency_contact_address = $request->input('emergency_contact_address');
        $else_special_note = $request->input('else_special_note');
        $interview_setting_person_type = $request->input('interview_setting_person_type');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            // オファー面談をする（日程を調整）
            if ($mgt->last_selection_flow_number == 12) {
                $mgt->selection_status = 4; // 4:内定(入社待ち)
                $mgt->{$result_key} = 1;    // 1:オファー面談日程未確定（！要対応）

                $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                    ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                    ->where('interview_phase', $mgt->last_selection_flow_number)
                    ->delete();
            }
            // 選考面接日程を調整
            else {
                $mgt->selection_status = 1; // 1:選考中
                $mgt->{$result_key} = 5; // 5:面談日程未確定（！要対応）
            }

            $mgt->save();

            // 選考結果レコードを生成する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('phase', $mgt->last_selection_flow_number)
                ->first();
            if (empty($selectionResult)) {
                $selectionResult = new SelectionResult();
                $selectionResult->created_by = Auth::user()->id;
            }
            $selectionResult->job_seeker_apply_mgt_id = $apply_mgt_id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $selectionResult->phase = $mgt->last_selection_flow_number;
            $selectionResult->next_phase = g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);

            if (!empty($interview_setting_person_type)) {

                $selectionResult->interview_setting_person_type = $interview_setting_person_type;
                $selectionResult->updated_by = Auth::user()->id;

                $selectionResult->save();

                // タイムラインに追加
                if ($interview_setting_person_type == 2) {
                    $timeline = new Timeline();
                    $timeline->company_id = $offerInfo->company_id;
                    $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
                    $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
                    $timeline->message_type = 1;    // 1:自動送信メッセージ
                    $timeline->message_sender = Auth::user()->id;
                    $timeline->sender_type = 1; // 1:求人企業担当者
                    // ・面接日程を相手から提案してもらうように依頼時。「面接日程の候補日を提示してもらうよう依頼」
                    $timeline->message_title = g_enum('recruit_interview_flow', $mgt->last_selection_flow_number)."日程の候補日を提示してもらうよう依頼";
                    $timeline->message_detail = null;
                    $timeline->timeline_complete = 1;   // 1:未完了
                    $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
                    $timeline->read_flg = 1;    // 1:未読
                    $timeline->created_at = date('Y-m-d H:i:s');
                    $timeline->created_by = Auth::user()->id;

                    $timeline->save();

                    // TODO削除：「１　面接設定時の画面」で、「候補者に候補日を提示してもらう」又は「候補日を提示する」のどちらかの設定が完了した後
                    Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                        ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                        ->delete();
                }
            }
            else {
                $selectionResult->interviewer = $interviewer;
                $selectionResult->interview_address = $interview_address;
                $selectionResult->belongings = $belongings;
                $selectionResult->emergency_contact_address = $emergency_contact_address;
                $selectionResult->else_special_note = $else_special_note;
                $selectionResult->updated_by = Auth::user()->id;

                $selectionResult->save();

                // タイムラインに追加
                $timeline = new Timeline();
                $timeline->company_id = $offerInfo->company_id;
                $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
                $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
                $timeline->message_type = 1;    // 1:自動送信メッセージ
                $timeline->message_sender = Auth::user()->id;
                $timeline->sender_type = 1; // 1:求人企業担当者
                // ・面接の詳細を提示。「面接詳細を提示」
                $timeline->message_title = "面接詳細を提示";
                $timeline->message_detail = "面接担当者名：".$interviewer."\r\n";
                $timeline->message_detail .= "面接場所住所：".$interview_address."\r\n";
                $timeline->message_detail .= "持ち物：".$belongings."\r\n";
                $timeline->message_detail .= "緊急連絡先：".$emergency_contact_address."\r\n";
                $timeline->message_detail .= "その他特記事項：".$else_special_note;
                $timeline->timeline_complete = 1;   // 1:未完了
                $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
                $timeline->read_flg = 1;    // 1:未読
                $timeline->created_at = date('Y-m-d H:i:s');
                $timeline->created_by = Auth::user()->id;

                $timeline->save();

                // TODO削除：「面接の詳細」の送信完了後
                Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                    ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                    ->delete();
            }

            return response()->json(['success'=>true,
                'recruitApplyMgt' => $mgt,
                'selectionResults' => $mgt->selectionResults,
                'interviewSchedules' => $mgt->calendars,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 候補日を提示する
     *
     * @return \Illuminate\Http\Response
     */
    public function sendInterviewDates(Request $request)
    {
        $apply_mgt_id = $request->input('mgt_id');
        $schedules = $request->input('schedules');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            // オファー面談をする（日程を調整）
            if ($mgt->last_selection_flow_number == 12) {
                $mgt->selection_status = 4; // 4:内定(入社待ち)
                $mgt->{$result_key} = 1;    // 1:オファー面談日程未確定（！要対応）
            }
            // 選考面接日程を調整
            else {
                $mgt->selection_status = 1; // 1:選考中
                $mgt->{$result_key} = 5; // 5:面談日程未確定（！要対応）
            }

            $mgt->save();

            // 選考結果レコードを生成する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('phase', $mgt->last_selection_flow_number)
                ->first();
            if (empty($selectionResult)) {
                $selectionResult = new SelectionResult();
                $selectionResult->created_by = Auth::user()->id;
            }
            else {
                $selectionResult->updated_by = Auth::user()->id;
            }
            $selectionResult->job_seeker_apply_mgt_id = $apply_mgt_id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $selectionResult->phase = $mgt->last_selection_flow_number;
            $selectionResult->next_phase = g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);
            $selectionResult->interview_setting_person_type = 1;    // 1:求人企業 2:候補者

            $selectionResult->save();

            $schedule_changed = InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('interview_phase', $mgt->last_selection_flow_number)
                ->whereNull('deleted_at')
                ->whereNull('deleted_by')
                ->count() > 0;

            if ($schedule_changed) {
                // 過去の候補日時を削除する
                $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                    ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                    ->where('interview_phase', $mgt->last_selection_flow_number)
                    ->whereNull('deleted_at')
                    ->whereNull('deleted_by')
                    ->update(['deleted_by'=>Auth::user()->id]);
            }

            $schedule_lists = '';
            foreach ($schedules as $schedule) {
                // 面接日付レコードを生成する
                if (!empty($schedule['id'])) {
                    $interviewSchedule = InterviewSchedule::findOrFail($schedule['id']);
                    $interviewSchedule->updated_at = date('Y-m-d H:i:s');
                    $interviewSchedule->updated_by = Auth::user()->id;
                    $interviewSchedule->deleted_at = null;
                    $interviewSchedule->deleted_by = null;
                }
                else {
                    $interviewSchedule = new InterviewSchedule();
                    $interviewSchedule->created_at = date('Y-m-d H:i:s');
                    $interviewSchedule->created_by = Auth::user()->id;
                }

                $interviewSchedule->company_id = $offerInfo->company_id;
                $interviewSchedule->job_seeker_apply_mgt_id   = $apply_mgt_id;
                $interviewSchedule->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
                $interviewSchedule->interview_candidates_name = $jobSeeker->last_name . ' ' . $jobSeeker->first_name;
                $interviewSchedule->interview_candidates_date = date('Y-m-d', strtotime($schedule['start']));
                $interviewSchedule->interview_candidates_from = date('H:i:s', strtotime($schedule['start']));
                $interviewSchedule->interview_candidates_to   = date('H:i:s', strtotime($schedule['end']));
                $interviewSchedule->interview_date_type       = empty($schedule['type']) ? 1 : $schedule['type']; //1:候補日
                $interviewSchedule->interview_phase           = $mgt->last_selection_flow_number;

                $interviewSchedule->save();

                $schedule_lists .= sprintf("%s(%s) %s~%s\r\n", date('Y年n月j日', strtotime($schedule['start'])), g_enum('week_days', date('w', strtotime($schedule['start']))), date('H:i', strtotime($schedule['start'])), date('H:i', strtotime($schedule['end'])));
            }

            $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('interview_phase', $mgt->last_selection_flow_number)
                ->whereNotNull('deleted_by')
                ->delete();

            $flow_name = g_enum('recruit_interview_flow', $mgt->last_selection_flow_number);

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・面接日程を送信時。「面接の日程を提案」
            // ・面接日程の折り合いがつかなかったため別の候補日を提案。「面接の日程を再提案」
            $timeline->message_title = $schedule_changed ? $flow_name."の日程を再提案" : $flow_name."の日程を提案";
            $timeline->message_detail = $schedule_lists;
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // TODO削除：「１　面接設定時の画面」で、「候補者に候補日を提示してもらう」又は「候補日を提示する」のどちらかの設定が完了した後
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                ->delete();

            return response()->json(['success'=>true]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 候補日を決定する
     *
     * @return \Illuminate\Http\Response
     */
    public function sendConfirmInterviewDates(Request $request)
    {
        $apply_mgt_id = $request->input('mgt_id');
        $target_id = $request->input('target_id');
        $status = $request->input('status');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);
        $schedule = InterviewSchedule::findOrFail($target_id);

        if (!empty($mgt) && !empty($schedule)) {

            // 関連情報を取得する
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            if ($status == 1) { // 1:候補日
                return response()->json(['success'=>false]);
            }
            else if ($status == 3 || $status == 4) { // 3:確定しなかった日(=X), 4: NG日
                $schedule->interview_date_type = $status;
                $schedule->save();

                return response()->json(['success'=>true]);
            }

            $schedule->interview_date_type = 2; // 2:確定した日(=◯)
            $schedule->save();

            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            // オファー面談をする（日程を調整）
            if ($mgt->last_selection_flow_number == 12) {
                $mgt->selection_status = 4; // 4:内定(入社待ち)
                $mgt->{$result_key} = 2;    // 2:オファー面談設定済み
            }
            // 選考面接日程を調整
            else {
                $mgt->selection_status = 1; // 1:選考中
                $mgt->{$result_key} = 6; // 6:日程設定済み
                $mgt->{$result_key.'_date'} = date('Y-m-d', strtotime($schedule->interview_candidates_date));
            }

            $mgt->save();

            // 他の候補日時をNGにする
            InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('interview_phase', $mgt->last_selection_flow_number)
                ->where('id', '!=', $target_id)
                ->whereIn('interview_date_type', array(1, 2))
                ->whereNull('deleted_at')
                ->whereNull('deleted_by')
                ->update(['interview_date_type'=>3]); // 3:確定しなかった日(=X)

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・面接の日程が確定した時。「〇〇（一次や最終など）〇〇面接の日程を確定」面接の日時も表示。
            $timeline->message_title = g_enum('recruit_interview_flow', $mgt->last_selection_flow_number)."の日程を確定";
            $timeline->message_detail = date('Y年n月j日 H:i', strtotime(date('Y-m-d', strtotime($schedule->interview_candidates_date)).' '.$schedule->interview_candidates_from))."から";
            $timeline->message_detail .= date('H:i', strtotime(date('Y-m-d', strtotime($schedule->interview_candidates_date)).' '.$schedule->interview_candidates_to))."まで。";
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // TODO削除：日程調整が完了して送信後
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                ->delete();

            return response()->json(['success'=>true]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * すべてNGして、新しい候補日を提示する
     *
     * @return \Illuminate\Http\Response
     */
    public function sendClearInterviewDates(Request $request)
    {
        $apply_mgt_id = $request->input('mgt_id');
        $schedule = $request->input('schedule');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            // オファー面談をする（日程を調整）
            if ($mgt->last_selection_flow_number == 12) {
                $mgt->selection_status = 4; // 4:内定(入社待ち)
                $mgt->{$result_key} = 1; // 1:オファー面談日程未確定(！要対応)
            }
            // 選考面接日程を調整
            else {
                $mgt->selection_status = 1; // 1:選考中
                $mgt->{$result_key} = 5; // 5:面接日程未確定（！要対応）
                $mgt->{$result_key.'_date'} = null;
            }

            $mgt->save();

            // すべての候補日時をNGにする
            InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('interview_phase', $mgt->last_selection_flow_number)
                ->whereNull('deleted_at')
                ->whereNull('deleted_by')
                ->update([
                    'interview_date_type'=>4,  // 4: NG日
                    'deleted_at'=>date('Y-m-d H:i:s'),
                    'deleted_by'=>Auth::user()->id
                ]);

            // 選考結果レコードを変更する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('phase', $mgt->last_selection_flow_number)
                ->first();
            if (empty($selectionResult)) {
                $selectionResult = new SelectionResult();
            }
            $selectionResult->interview_setting_person_type = 1;    // 1:求人企業 2:候補者

            $selectionResult->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            $timeline->message_title = g_enum('recruit_interview_flow', $mgt->last_selection_flow_number)."の日程を全てNGにして別の日程を提示";
            $timeline->message_detail = null;
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            return response()->json(['success'=>true]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 入社条件を提示する
     *
     * @return \Illuminate\Http\Response
     */
    public function sendJoiningCondition(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $offerAmount = $request->input('offerAmount');
        $firstDayAttendanceDate = $request->input('firstDayAttendanceDate');
        $replyDeadline = $request->input('replyDeadline');

        $condition_files = array();
        foreach ($request->files as $files) {
            foreach ($files as $key => $file) {
                if ($file != 'undefined') {
                    $temp_name = time().$key.'.'.$file->getClientOriginalExtension();
                    $real_name = $file->getClientOriginalName();
                    $file->move(storage_path('/app/public/recruit/attachment/'), $temp_name);

                    if (strtolower($file->getClientOriginalExtension()) != 'pdf' || filesize(storage_path('/app/public/recruit/attachment/'.$temp_name)) > 5 * 1024 * 1024) {
                        return response()->json(['success'=>false, 'msg'=>'入社手続きに必要な書類は５MB以下のPDFファイルを選択してください。']);
                    }

                    $condition_files[] = array('temp_name' => $temp_name, 'real_name' => $real_name);
                }
            }
        }

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            $mgt->selection_status = 4; // 4:内定(入社待ち)

            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            // 入社条件を提示
            if ($mgt->last_selection_flow_number == 12) {
                $mgt->{$result_key} = 4;    // 4:入社条件返答待ち
            }

            $mgt->save();

            // 入社条件を提示
            $condition = new JoiningConditionPresent();
            $condition->recruit_job_seeker_apply_mgt_id = $apply_mgt_id;
            $condition->offer_amount = $offerAmount;
            $condition->first_day_attendance_date = $firstDayAttendanceDate;
            $condition->reply_deadline = $replyDeadline;
            $condition->job_changer_desired_annual_income = 0;
            $condition->job_changer_first_day_attendance_date = null;
            $condition->other_desired = null;

            $condition->save();

            $condition_changed = JoiningConditionPresent::where('recruit_job_seeker_apply_mgt_id', $apply_mgt_id)
                ->whereNull('deleted_at')->whereNull('deleted_by')
                ->count() > 1;

            $attach_files = '';
            $attach_names = '';

            JoiningConditionPresentAttachments::where('joining_condition_presents_id', $condition->id)->delete();
            foreach ($condition_files as $attachment) {
                $condition_attachment = new JoiningConditionPresentAttachments();
                $condition_attachment->joining_condition_presents_id = $condition->id;
                $condition_attachment->attachment = $attachment['temp_name'];
                $condition_attachment->attachment_name = $attachment['real_name'];
                $condition_attachment->upload_datetime = date('Y-m-d H:i:s');

                $condition_attachment->save();

                $attach_files .= $attachment['temp_name'].'|';
                $attach_names .= $attachment['real_name'].'|';
            }

            // 入社条件の添付ファイル
            $joinConditionAttachments = null;
            $joinConditionAttachments = JoiningConditionPresentAttachments::where('joining_condition_presents_id', $condition->id)
                ->select('attachment', 'attachment_name', 'upload_datetime')
                ->get();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・内定後、入社条件を提示した時。「入社条件を提示」
            $timeline->message_title = $condition_changed ? "入社条件を変更" : "入社条件を提示";
            $timeline->message_detail = "オファー金額：".number_format($offerAmount)."円\r\n";
            $timeline->message_detail .= "初日出勤日：".date('Y年n月j日', strtotime($firstDayAttendanceDate))."\r\n";
            $timeline->message_detail .= "返答期限：".date('Y年n月j日', strtotime($replyDeadline));
            if (!empty(trim($attach_files))) {
                // 最後の｜文字を削除する。
                $attach_files = substr($attach_files, 0, -1);
                $attach_names = substr($attach_names, 0, -1);

                $timeline->message_detail .= "\r\n";
                $timeline->message_detail .= "入社手続きに必要な書類";
                $timeline->attachment =  $attach_files;
                $timeline->attachment_name = $attach_names;
            }
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // TODO削除：「オファー面談」または「入社条件」を入力して送信後
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                ->delete();

            return response()->json(['success'=>true,
                'recruitApplyMgt' => $mgt,
                'joiningConditions' => $mgt->joiningConditionPresents,
                'joinConditionAttachments' => $joinConditionAttachments,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 同意する（採用決定）
     *
     * @return \Illuminate\Http\Response
     */
    public function sendAllowJoining(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            $mgt->selection_status = 4; // 4:内定(入社待ち)

            $mgt->last_selection_flow_number = 12; // 採用
            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 5; // 5:入社予定日あり
            $mgt->{$result_key.'_date'} = date('Y-m-d'); // 採用日

            $mgt->save();

            // 初日出勤予定日を設定
            $final_condition = JoiningConditionPresent::where('recruit_job_seeker_apply_mgt_id', $apply_mgt_id)
                ->whereNull('deleted_at')->whereNull('deleted_by')
                ->orderBy('created_at', 'desc')->orderBy('id', 'desc')
                ->first();

            $final_condition->first_day_work_schedule_date = $final_condition->job_changer_first_day_attendance_date;   // 転職者_希望初日出勤日
            $final_condition->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・入社条件が承諾された時。「入社条件を承諾」
            $timeline->message_title = "入社条件を承諾";
            $timeline->message_detail = null;
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・入社日が確定した時。「入社日が確定」入社日を表示。
            $timeline->message_title = "入社日が確定";
            $timeline->message_detail = "初日出勤予定日：".date('Y年n月j日', strtotime($final_condition->first_day_work_schedule_date));
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // TODO削除
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                ->delete();

            // TODO発生：提示した入社条件に対して同意された時。
            $todo = new Todo();
            $todo->company_id = $offerInfo->company_id;
            $todo->offer_info_id = $offerInfo->id;
            $todo->offer_info_type = Constants::BIZ_TYPE_RECRUIT;
            $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
            $todo->question_and_answer_id = null;
            $todo->calendar_id = null;
            // 「ご採用おめでとうございます！〇〇求職者名〇〇さんに提示した入社条件が同意されました。初日出勤日になりましたら確認の操作をお願い致します。」
            $todo->todo_content = "ご採用おめでとうございます！".$jobSeeker->last_name . ' ' . $jobSeeker->first_name."さんから提示された入社条件に同意しました。初日出勤日になりましたら確認の操作をお願い致します。";
            $todo->todo_complete = 1;   // 1:未完了
            // 遷移先：該当する「選考詳細ページ」
            $todo->todo_transition_target = 1;  // 1:求人企業：人材紹介選考詳細画面
            $todo->read_flg = 1;    // 1:未読
            $todo->created_at = date('Y-m-d H:i:s');
            $todo->created_by = Auth::user()->id;

            $todo->save();

            return response()->json(['success'=>true,
                'recruitApplyMgt' => $mgt,
                'joiningConditions' => $mgt->joiningConditionPresents,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 入社日変更
     *
     * @return \Illuminate\Http\Response
     */
    public function sendChangePresentDate(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $present_date = $request->input('present_date');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            $mgt->selection_status = 4; // 4:内定(入社待ち)

            $mgt->last_selection_flow_number = 12; // 採用
            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 5; // 5:入社予定日あり
            $mgt->joining_confirmation_date = null; // 入社確認日

            $mgt->save();

            // 入社状態の更新　（初日出勤予定日の変更）
            $final_condition = JoiningConditionPresent::where('recruit_job_seeker_apply_mgt_id', $apply_mgt_id)
                ->whereNull('deleted_at')->whereNull('deleted_by')
                ->orderBy('created_at', 'desc')->orderBy('id', 'desc')
                ->first();

            $final_condition->first_day_work_schedule_date = $present_date;
            $final_condition->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・入社日が確定した時。「入社日が確定」入社日を表示。
            $timeline->message_title = "入社日が変更";
            $timeline->message_detail = "初日出勤予定日：".date('Y年n月j日', strtotime($final_condition->first_day_work_schedule_date));
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            return response()->json(['success'=>true,
                'recruitApplyMgt' => $mgt,
                'joiningConditions' => $mgt->joiningConditionPresents,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 出勤した/出勤しなかった
     *
     * @return \Illuminate\Http\Response
     */
    public function sendPresented(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $isPresent = $request->input('isPresent');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        $isPresent = ($isPresent === true || $isPresent == 'true') ? true : false;

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            $mgt->selection_status = 5; // 5:入社確定

            $mgt->last_selection_flow_number = 13; // 入社確認
            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 1; // 1:入社

            if ($isPresent) {
                // 入社状態の更新　（確定初日出勤日の設定）
                $final_condition = JoiningConditionPresent::where('recruit_job_seeker_apply_mgt_id', $apply_mgt_id)
                    ->whereNull('deleted_at')->whereNull('deleted_by')
                    ->orderBy('created_at', 'desc')->orderBy('id', 'desc')
                    ->first();

                $final_condition->fixed_first_day_attendance_date = $final_condition->first_day_work_schedule_date;
                $final_condition->save();

                $mgt->{$result_key.'_date'} = date('Y-m-d'); // 入社確認日
            }
            else {
                $mgt->{$result_key.'_date'} = null; // 入社確認日
            }
            $mgt->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            if ($isPresent) {
                // ・入社日を経過して、求人企業が入社確認の操作をした時。「入社確認済み。入社が確定しました。」
                $timeline->message_title = "入社確認済み。入社が確定しました。";
                $timeline->message_detail = "入社確認日：".date('Y年n月j日');
            }
            else {
                // ・入社日を経過して、求人企業が入社確認の操作をした時。「入社確認済み。入社が確定しました。」
                $timeline->message_title = "入社未確認";
                $timeline->message_detail = "出勤しませんでした。";
            }
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // TODO削除：「４　採用決定後（入社条件に同意後）」で初日出勤の確認操作後
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                ->delete();

            return response()->json(['success'=>true,
                'recruitApplyMgt' => $mgt,
                'joiningConditions' => $mgt->joiningConditionPresents,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 返金申請
     *
     * @return \Illuminate\Http\Response
     */
    public function sendRetirementDate(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $retirement_date = $request->input('retirement_date');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・返金申請をした時（された時）。「返金申請」と退職日などを記載
            $timeline->message_title = "返金申請";
            $timeline->message_detail = "退職日：".date('Y年n月j日', strtotime($retirement_date));
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            $mgt->selection_status = 5; // 5:入社確定

            $mgt->last_selection_flow_number = 13; // 入社確認
            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 1; // 1:入社
            $mgt->retirement_date = $retirement_date; // 退職日
            $mgt->refund_apply_date = date('Y-m-d'); // 返金申請日

            $mgt->refund_agreement_date = null; // 返金同意日
            $mgt->refund_disagreement_date = null; // 返金不同意日
            $mgt->refund_amount = null; // 返金額
            $mgt->refund_status = 1; // 1:選択前状態（申請した後）

            $mgt->save();

            return response()->json(['success'=>true,
                'recruitApplyMgt' => $mgt,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    public function getTimelineRecords(Request $request)
    {
        $id = $request->input('id');
        $mgt = RecruitJobSeekerApplyMgts::findOrFail($id);
        $timelines = $mgt->timelines;

        return response()->json($timelines);
    }

    public function saveTimelineRecord(Request $request)
    {
        $params['JobSeekerApplyMgtID'] = $request->input('jobSeekerApplyMgtID');
        $params['MessageDetail'] = $request->input('messageDetail');

        $params['fileName'] = '';
        $params['realName'] = '';

        if ($request->file != 'undefined') {
            $params['fileName'] = time().'.'.$request->file->getClientOriginalExtension();
            $params['realName'] = $request->file->getClientOriginalName();
            //$request->file->move(public_path('upload'), $params['fileName']);
            $request->file->move(storage_path('/app/public/timeline/'), $params['fileName']);
        }

        $timeline = new Timeline();
        $timeline->company_id = Auth::user()->company_user->company_id;
        $timeline->job_seeker_apply_mgt_id = $params['JobSeekerApplyMgtID'];
        $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT; //人材紹介
        $timeline->message_type = 2; //送信者メッセージ
        $timeline->message_sender = Auth::user()->id;
        $timeline->sender_type = 1; //求人企業担当者
        $timeline->message_detail = $params['MessageDetail'];
        $timeline->attachment = $params['fileName'];
        $timeline->attachment_name = $params['realName'];
        $timeline->timeline_complete = 1; //未完了
        $timeline->timeline_transition_target = 1; //選考詳細画面
        $timeline->read_flg = 1; //未読

        $timeline->save();

        $timeline = $timeline->relationCompanyLogoInfo($timeline->id);
        //$timeline->company_logo = Auth::user()->company_user->company->logo;

        return response()->json($timeline);
    }
}
