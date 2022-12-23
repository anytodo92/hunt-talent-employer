<?php

namespace App\Http\Controllers\Company;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use App\Models\CompanyUser;
use App\Models\JobSeekerAttachment;
use App\Models\ContractTerms;
use App\Models\OutsourceJobSeeker;
use App\Models\OutsourceJobSeekerApplyMgts;
use App\Models\OutsourceOfferInfo;
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

class OutsourceController extends Controller
{
    /**
     * OutsourceController constructor.
     */
    public function __construct()
    {
    }

    /**
     * 【求人企業】業務委託の選考一覧
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        // 求人情報のタイトルを取得する（検索条件として使う）
        $login_company_id = Auth::user()->company_user->company_id;
        $jobTitles = OutsourceOfferInfo::where('company_id', $login_company_id)
            ->select('id', 'job_title')
            ->get();

        // 更新日の検索条件に表示する年・月のリスト
        $updated_range = OutsourceJobSeekerApplyMgts::select(DB::raw('max(updated_at) as maxDate, min(updated_at) as minDate'))->first();

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

        return view('company.outsource.index')->with([
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

        $outsourceJobSeekerMgts = new OutsourceJobSeekerApplyMgts();
        $result = $outsourceJobSeekerMgts->datatableForCompany($params);

        // 性別、生年月日、職種カテゴリーの表示データを取得する
        $records = $result['data'];

        foreach($records as &$record) {
            $record->sex = g_enum('sex', $record->sex);
            $record->birthday = g_age($record->birthday);

            // 「面談日程未確定」の時などで、相手側の日程待ちの状態の場合（求人企業側で対応することではない状態の場合は全て）には一覧の「！要対応」の文字を非表示
            $record->is_pending = false;
            if ($record->last_selection_flow_number >= 4/*１次面談*/ && $record->last_selection_flow_number <= 7/*最終選考*/) {
                $result_key = g_enum('outsource_apply_mgt_selection_result_key', $record->last_selection_flow_number);
                if ($record->{$result_key} == 5) { // 5/*5:面談日程未確定（！要対応）*/
                    // 1:求人企業
                    if ($record->interview_setting_person_type == 1) {
                        $interview_date_registerd = InterviewSchedule::where('job_seeker_apply_mgt_id', $record->id)
                            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                            ->where('interview_phase', $record->last_selection_flow_number)
                            ->where('interview_date_type', 1)   // 1:候補日
                            ->whereNull('deleted_at')
                            ->whereNull('deleted_by')
                            ->count();
                        // 業務委託会社に候補日を提示した場合
                        if ($interview_date_registerd > 0) {
                            $record->is_pending = true;
                        }
                    }
                    // 2:候補者（※業務委託会社担当者または業務委託会社担当者が管理する候補者）
                    else if ($record->interview_setting_person_type == 2) {
                        $interview_date_registerd = InterviewSchedule::where('job_seeker_apply_mgt_id', $record->id)
                            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                            ->where('interview_phase', $record->last_selection_flow_number)
                            ->whereIn('interview_date_type', array(1, 2))   // 1:候補日, 2:確定した日
                            ->whereNull('deleted_at')
                            ->whereNull('deleted_by')
                            ->count();
                        // 業務委託会社から候補日が提示されなかった場合
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
        for ($i = 1 ; $i <= 8 ; $i ++) {
            if ($i == 2) { continue; }  // 未エントリーのステータスは非表示（求人企業）
            $html = $html . "<span class='outsource_status " . ($i == $currentStatus ? 'bold' : '') . "' data-id='" . $i . "'>" . g_enum('outsource_status', $i) . " " . $result['statusCount'][$i] . "  </span>";
        }

        $result['data'] = $records;
        $result['statusCountHtml'] = $html;

        return response()->json($result);
    }

    /**
     * 【求人企業】業務委託＿選考詳細
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function detail($id)
    {
        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($id);

        // 関連情報を取得する
        $jobSeeker = $mgt->jobSeeker; // 業務委託_参画者
        $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報
        $outsourceCompany = $jobSeeker->outsourceCompany; // 業務委託会社
        $recruitingCompany = $offerInfo->recruitingCompany; // 募集企業

        // まだ「STEP1 応募」、「STEP2 書類確認：未」段階の場合
        if ($mgt->last_selection_flow_number < 3 && $mgt->selection_status != 2/*2:未エントリー*/) {
            $mgt->selection_status = 1; // 1:選考中

            // 「STEP3 書類選考」段階に自動的に進む
            $mgt->document_confirmation = 1;
            $mgt->document_confirmation_date = date('Y-m-d');

            $mgt->last_selection_flow_number = 3;   // g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);
            $mgt->applicant_screening = $mgt->last_selection_flow_number == 3 ? 6 : 8; // 6:選考結果未送付（！要対応） 8:選考結果未送付（！要対応）
            $mgt->applicant_screening_date = date('Y-m-d');
            $mgt->last_selection_flow_date = date('Y-m-d');

            $mgt->save();

            // 選考結果レコードを生成する
            $selectionResult = new SelectionResult();
            $selectionResult->job_seeker_apply_mgt_id = $id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $selectionResult->phase = $mgt->last_selection_flow_number;
            $selectionResult->next_phase = g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);

            $selectionResult->save();

            // TODO削除
            Todo::where('job_seeker_apply_mgt_id', $id)
                ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->delete();

            // TODO発生：面談などで設定した日時が経過した後。
            $todo = new Todo();
            $todo->company_id = $offerInfo->company_id;
            $todo->offer_info_id = $offerInfo->id;
            $todo->offer_info_type = Constants::BIZ_TYPE_OUTSOURCE;
            $todo->job_seeker_apply_mgt_id = $id;
            $todo->question_and_answer_id = null;
            $todo->calendar_id = null;
            // 「〇〇参画者名〇〇さんの〇〇面談の選考結果の送信をお願い致します。」
            $todo->todo_content = $jobSeeker->initial."さんの".g_enum('outsource_interview_flow', $mgt->last_selection_flow_number)."の選考結果の送信をお願い致します。";
            $todo->todo_complete = 1;   // 1:未完了
            // 遷移先：該当する「選考詳細ページ」
            $todo->todo_transition_target = 2;  // 2:求人企業：業務委託選考詳細画面
            $todo->read_flg = 1;    // 1:未読
            $todo->created_at = date('Y-m-d H:i:s');
            $todo->created_by = Auth::user()->id;

            $todo->save();
        }
        else if ($mgt->last_selection_flow_number >= 4 && $mgt->last_selection_flow_number <= 7) {
            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            if ($mgt->{$result_key} == 7 && !empty($mgt->{$result_key.'_date'})) { // 7:日程設定済み
                $interview_date = InterviewSchedule::where('job_seeker_apply_mgt_id', $id)
                    ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                    ->where('interview_phase', $mgt->last_selection_flow_number)
                    ->where('interview_date_type', 2)   // 2:確定した日(=◯)
                    ->whereNull('deleted_at')
                    ->whereNull('deleted_by')
                    ->first();

                if (!empty($interview_date) && date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime($interview_date->interview_candidates_date)).' '.$interview_date->interview_candidates_to)) < date('Y-m-d H:i:s')) {
                    $mgt->{$result_key} = 8; // 8:選考結果未送付（！要対応）
                    $mgt->save();

                    // TODO削除
                    Todo::where('job_seeker_apply_mgt_id', $id)
                        ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
                        ->delete();

                    // TODO発生：面談などで設定した日時が経過した後。
                    $todo = new Todo();
                    $todo->company_id = $offerInfo->company_id;
                    $todo->offer_info_id = $offerInfo->id;
                    $todo->offer_info_type = Constants::BIZ_TYPE_OUTSOURCE;
                    $todo->job_seeker_apply_mgt_id = $id;
                    $todo->question_and_answer_id = null;
                    $todo->calendar_id = null;
                    // 「〇〇参画者名〇〇さんの〇〇面談の選考結果の送信をお願い致します。」
                    $todo->todo_content = $jobSeeker->initial."さんの".g_enum('outsource_interview_flow', $mgt->last_selection_flow_number)."の選考結果の送信をお願い致します。";
                    $todo->todo_complete = 1;   // 1:未完了
                    // 遷移先：該当する「選考詳細ページ」
                    $todo->todo_transition_target = 2;  // 2:求人企業：業務委託選考詳細画面
                    $todo->read_flg = 1;    // 1:未読
                    $todo->created_at = date('Y-m-d H:i:s');
                    $todo->created_by = Auth::user()->id;

                    $todo->save();
                }
            }
        }
        else if ($mgt->last_selection_flow_number == 8) {
            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            if ($mgt->{$result_key} == 1) { // 1:参画予定日
                if (!empty($mgt->joining_scheduled_date) && date('Y-m-d', strtotime($mgt->joining_scheduled_date)) <= date('Y-m-d')) {
                    $mgt->last_selection_flow_number = 9;
                    $mgt->joining_confirmation = 1;// 1:参画開始
                    $mgt->joining_confirmation_start_date = $mgt->joining_scheduled_date;
                    $mgt->last_selection_flow_date = date('Y-m-d');
                    $mgt->save();
                }
            }
        }
        else if ($mgt->last_selection_flow_number == 10) {
            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            if ($mgt->{$result_key} == 1) { // 1:参画中
                if (!empty($mgt->joining_end_date) && date('Y-m-d', strtotime($mgt->joining_end_date)) <= date('Y-m-d')) {
                    $mgt->selection_status = 7; // 7:参画終了
                    $mgt->{$result_key} = 2; // 2:終了
                    $mgt->last_selection_flow_date = date('Y-m-d');
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
        $interviewSchedules = $mgt->calendars; // 面談日程
        $timelines = $mgt->timelines; // タイムライン
        $contractTerms = $mgt->contractTerms;
        $offerCompanyUser = $offerInfo->offerCompanyUser;

        $attachments = JobSeekerAttachment::where('job_seeker_id', '=', $mgt->outsource_job_seeker_id)
            ->where('job_seeker_type', '=', Constants::BIZ_TYPE_OUTSOURCE)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->select('attachment_type', 'attachment', 'attachment_name', 'upload_datetime')
            ->get();

        $first_industry = FirstIndustry::find($recruitingCompany->industry1)->name;
        $second_industry = SecondIndustry::find($recruitingCompany->industry2)->name;

        return view('company.outsource.detail')->with([
            'id' => $id,
            'outsourceJobSeekerApplyMgt' => $mgt,
            'outsourceCompany' => $outsourceCompany,
            'recruitingCompany' => $recruitingCompany,
            'jobSeeker' => $jobSeeker,
            'offerInfo' => $offerInfo,
            'workPlaces' => $workPlaces,
            'selectionFlow' => $offerInfo->selection_flow,
            'attachments' => $attachments,
            'selectionResults' => $selectionResults,
            'interviewSchedules' => $interviewSchedules,
            'timelines' => $timelines,
            'contractTerms' => $contractTerms,
            'offerCompanyUser' => $offerCompanyUser,
            'first_industry' => $first_industry,
            'second_industry' => $second_industry,
            'now' => date('Y-m-d'),
        ]);
    }

    /**
     * 見送り
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

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            $mgt->selection_status = 3; // 3:見送り/辞退

            if ($mgt->last_selection_flow_number == 8) {
                $mgt->last_selection_flow_number = 9;
                $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
                $mgt->{$result_key} = 3; // 3:見送り
            }
            else {
                $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
                $mgt->{$result_key} = 5; // 5:見送り
            }
            $result_key = str_replace('_interview', '', $result_key);
            $mgt->{$result_key.'_send_off_date'} = date('Y-m-d');
            $mgt->last_selection_flow_date = date('Y-m-d');

            $mgt->save();

            // 選考結果レコードを生成する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->where('phase', $mgt->last_selection_flow_number)
                ->first();
            if (empty($selectionResult)) {
                $selectionResult = new SelectionResult();
                $selectionResult->created_by = Auth::user()->id;
            }
            $selectionResult->job_seeker_apply_mgt_id = $apply_mgt_id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
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
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            if ($mgt->last_selection_flow_number == 8) {
                // ・契約オファーが取り消された時。「オファーが取消されました。」。理由などを表示。
                $timeline->message_title = "オファーが取消されました。";
                $timeline->message_detail = "取消理由：";
            }
            else {
                // ・書類選考不合格時。「書類選考結果「お見送り」」
                // ・面談で見送り時。「〇〇（一次や最終など）〇〇面談結果「お見送り」」"
                $timeline->message_title = g_enum('outsource_interview_flow', $mgt->last_selection_flow_number)."結果「お見送り」";
                $timeline->message_detail = "見送り理由：";
            }
            $timeline->message_detail .= g_enum('outsource_unseated_reason', $selectionResult->unseated_reason)."\r\n";
            $timeline->message_detail .= g_enum('outsource_unseated_reason_sub', $selectionResult->unseated_reason)[$selectionResult->unseated_reason_sub]."\r\n";
            $timeline->message_detail .= $selectionResult->unseated_cause_detail;
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // TODO削除：選考結果の送信完了後
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->delete();

            return response()->json(['success'=>true,
                'outsourceApplyMgt' => $mgt,
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

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 業務委託_参画者
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            // 選考結果レコードを生成する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->where('phase', $mgt->last_selection_flow_number)
                ->first();
            if (empty($selectionResult)) {
                $selectionResult = new SelectionResult();
                $selectionResult->created_by = Auth::user()->id;
            }
            $selectionResult->job_seeker_apply_mgt_id = $apply_mgt_id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
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
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・書類選考通過時。「書類選考「通過」。次の選考は〇〇（一次や最終など）〇〇面談となります。」
            $timeline->message_title = g_enum('outsource_selection_flow', $mgt->last_selection_flow_number)."「通過」";
            $timeline->message_detail = "次の選考は".g_enum('outsource_selection_flow', $next_phase)."となります。"."\r\n";
            $timeline->message_detail .= "現状の評価：".g_enum('outsource_evaluation', $current_evaluation)."\r\n";
            $timeline->message_detail .= "評価点：".$evaluation_point."\r\n";
            $timeline->message_detail .= "懸念点：".$concern_point;
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // Todoに追加
            if ($next_phase >= 4/*1次面談*/ && $next_phase <= 7/*最終選考*/) {
                // TODO削除：選考結果の送信完了後
                Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                    ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
                    ->delete();

                // TODO発生：書類選考などの選考通過後、次の選考が面談または面談の場合、候補日をどうするか設定していない時（１　面談設定時の画面）
                $todo = new Todo();
                $todo->company_id = $offerInfo->company_id;
                $todo->offer_info_id = $offerInfo->id;
                $todo->offer_info_type = Constants::BIZ_TYPE_OUTSOURCE;
                $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
                $todo->question_and_answer_id = null;
                $todo->calendar_id = null;
                // 「〇〇参画者名〇〇さんの〇〇面談の候補日に関する設定をお願い致します。」
                $todo->todo_content = $jobSeeker->initial."さんの".g_enum('outsource_interview_flow', $next_phase)."の候補日に関する設定をお願い致します。";
                $todo->todo_complete = 1;   // 1:未完了
                // 遷移先：該当する「選考詳細ページ」
                $todo->todo_transition_target = 2;  // 2:求人企業：業務委託選考詳細画面
                $todo->read_flg = 1;    // 1:未読
                $todo->created_at = date('Y-m-d H:i:s');
                $todo->created_by = Auth::user()->id;

                $todo->save();

                // TODO発生：上記の後に「面談の詳細」を入力していない時。
                $todo = new Todo();
                $todo->company_id = $offerInfo->company_id;
                $todo->offer_info_id = $offerInfo->id;
                $todo->offer_info_type = Constants::BIZ_TYPE_OUTSOURCE;
                $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
                $todo->question_and_answer_id = null;
                $todo->calendar_id = null;
                // 「〇〇参画者名〇〇さんとの「〇〇面談の詳細」についてご入力と送信をお願い致します。」
                $todo->todo_content = $jobSeeker->initial."さんとの「".g_enum('outsource_interview_flow', $next_phase)."の詳細」についてご入力と送信をお願い致します。";
                $todo->todo_complete = 1;   // 1:未完了
                // 遷移先：該当する「選考詳細ページ」
                $todo->todo_transition_target = 2;  // 2:求人企業：業務委託選考詳細画面
                $todo->read_flg = 1;    // 1:未読
                $todo->created_at = date('Y-m-d H:i:s');
                $todo->created_by = Auth::user()->id;

                $todo->save();
            }

            // update apply-management
            $mgt->selection_status = 1; // 1:選考中

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 3; // 3:通過
            if ($mgt->last_selection_flow_number == 3) {
                $mgt->{$result_key.'_date'} = date('Y-m-d');
            }
            $mgt->last_selection_flow_number = $selectionResult->next_phase;
            $mgt->last_selection_flow_date = date('Y-m-d');

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            if ($mgt->last_selection_flow_number <= 7) {
                $mgt->{$result_key} = 6;    // 6:日程未確定（！要対応）
            }
            else {
                $mgt->{$result_key} = 2;    // 2:契約条件提示・交渉（！要対応）
                $mgt->offer_date = date('Y-m-d'); // オファー日
            }

            $mgt->save();

            return response()->json(['success'=>true,
                'outsourceApplyMgt' => $mgt,
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

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 業務委託_参画者
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            // 選考結果レコードを生成する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->where('phase', $mgt->last_selection_flow_number)
                ->first();
            if (empty($selectionResult)) {
                $selectionResult = new SelectionResult();
                $selectionResult->created_by = Auth::user()->id;
            }
            $selectionResult->job_seeker_apply_mgt_id = $apply_mgt_id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $selectionResult->phase = $mgt->last_selection_flow_number;
            $selectionResult->next_phase = 8; // 契約 // g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);
            $selectionResult->updated_by = Auth::user()->id;

            $selectionResult->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・結果を「契約する」（オファー）にした時。「選考結果「契約オファー（採用）」」
            $timeline->message_title = g_enum('outsource_selection_flow', $mgt->last_selection_flow_number)."結果「契約オファー（採用）」";
            $timeline->message_detail = "ファー日：".date('Y年n月j日');
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // update apply-management
            $mgt->selection_status = 4; // 4:オファー

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 1; // 1:オファー
            if ($mgt->last_selection_flow_number == 3) {
                $mgt->{$result_key.'_date'} = date('Y-m-d');
            }
            $mgt->last_selection_flow_number = $selectionResult->next_phase;
            $mgt->last_selection_flow_date = date('Y-m-d');

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 2;    // 2:契約条件提示・交渉（！要対応）
            $mgt->offer_date = date('Y-m-d'); // オファー日

            $mgt->save();

            // TODO削除：選考結果の送信完了後
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->delete();

            // TODO発生：内定後、入社手続きの案内（オファー面談or契約条件提示）が未入力の場合。
            $todo = new Todo();
            $todo->company_id = $offerInfo->company_id;
            $todo->offer_info_id = $offerInfo->id;
            $todo->offer_info_type = Constants::BIZ_TYPE_OUTSOURCE;
            $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
            $todo->question_and_answer_id = null;
            $todo->calendar_id = null;
            // 「〇〇参画者名〇〇さんの入社手続きに関する案内をお願いします。」
            $todo->todo_content = $jobSeeker->initial."さんの契約条件の提示をお願い致します。";
            $todo->todo_complete = 1;   // 1:未完了
            // 遷移先：該当する「選考詳細ページ」
            $todo->todo_transition_target = 2;  // 2:求人企業：業務委託選考詳細画面
            $todo->read_flg = 1;    // 1:未読
            $todo->created_at = date('Y-m-d H:i:s');
            $todo->created_by = Auth::user()->id;

            $todo->save();

            return response()->json(['success'=>true,
                'outsourceApplyMgt' => $mgt,
                'selectionResults' => $mgt->selectionResults,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 面談詳細を入力する
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

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->selection_status = 1; // 1:選考中
            $mgt->{$result_key} = 6; // 6:日程未確定（！要対応）

            $mgt->save();

            // 選考結果レコードを生成する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->where('phase', $mgt->last_selection_flow_number)
                ->first();
            if (empty($selectionResult)) {
                $selectionResult = new SelectionResult();
                $selectionResult->created_by = Auth::user()->id;
            }
            $selectionResult->job_seeker_apply_mgt_id = $apply_mgt_id;
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
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
                    $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
                    $timeline->message_type = 1;    // 1:自動送信メッセージ
                    $timeline->message_sender = Auth::user()->id;
                    $timeline->sender_type = 1; // 1:求人企業担当者
                    // ・面談日程を相手から提案してもらうように依頼時。「面談日程の候補日を提示してもらうよう依頼」
                    $timeline->message_title = g_enum('outsource_interview_flow', $mgt->last_selection_flow_number)."日程の候補日を提示してもらうよう依頼";
                    $timeline->message_detail = null;
                    $timeline->timeline_complete = 1;   // 1:未完了
                    $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
                    $timeline->read_flg = 1;    // 1:未読
                    $timeline->created_at = date('Y-m-d H:i:s');
                    $timeline->created_by = Auth::user()->id;

                    $timeline->save();

                    // TODO削除：「１　面談設定時の画面」で、「候補者に候補日を提示してもらう」又は「候補日を提示する」のどちらかの設定が完了した後
                    Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                        ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
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
                $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
                $timeline->message_type = 1;    // 1:自動送信メッセージ
                $timeline->message_sender = Auth::user()->id;
                $timeline->sender_type = 1; // 1:求人企業担当者
                // ・面談の詳細を提示。「面談詳細を提示」
                $timeline->message_title = "面談詳細を提示";
                $timeline->message_detail = "面談担当者名：".$interviewer."\r\n";
                $timeline->message_detail .= "面談場所住所：".$interview_address."\r\n";
                $timeline->message_detail .= "持ち物：".$belongings."\r\n";
                $timeline->message_detail .= "緊急連絡先：".$emergency_contact_address."\r\n";
                $timeline->message_detail .= "その他特記事項：".$else_special_note;
                $timeline->timeline_complete = 1;   // 1:未完了
                $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
                $timeline->read_flg = 1;    // 1:未読
                $timeline->created_at = date('Y-m-d H:i:s');
                $timeline->created_by = Auth::user()->id;

                $timeline->save();

                // TODO削除：「面談の詳細」の送信完了後
                Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                    ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
                    ->delete();
            }

            return response()->json(['success'=>true,
                'outsourceApplyMgt' => $mgt,
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

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 業務委託_参画者
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->selection_status = 1; // 1:選考中
            $mgt->{$result_key} = 6; // 6:日程未確定（！要対応）

            $mgt->save();

            // 選考結果レコードを生成する
            $selectionResult = SelectionResult::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
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
            $selectionResult->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $selectionResult->phase = $mgt->last_selection_flow_number;
            $selectionResult->next_phase = g_nextPhase($offerInfo->selection_flow, $mgt->last_selection_flow_number);
            $selectionResult->interview_setting_person_type = 1;    // 1:求人企業 2:候補者

            $selectionResult->save();

            $schedule_changed = InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->where('interview_phase', $mgt->last_selection_flow_number)
                ->whereNull('deleted_at')
                ->whereNull('deleted_by')
                ->count() > 0;

            if ($schedule_changed) {
                // 過去の候補日時を削除する
                $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                    ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                    ->where('interview_phase', $mgt->last_selection_flow_number)
                    ->whereNull('deleted_at')
                    ->whereNull('deleted_by')
                    ->update(['deleted_by'=>Auth::user()->id]);
            }

            $schedule_lists = '';
            foreach ($schedules as $schedule) {
                // 面談日付レコードを生成する
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
                $interviewSchedule->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
                $interviewSchedule->interview_candidates_name = $jobSeeker->initial;
                $interviewSchedule->interview_candidates_date = date('Y-m-d', strtotime($schedule['start']));
                $interviewSchedule->interview_candidates_from = date('H:i:s', strtotime($schedule['start']));
                $interviewSchedule->interview_candidates_to   = date('H:i:s', strtotime($schedule['end']));
                $interviewSchedule->interview_date_type       = empty($schedule['type']) ? 1 : $schedule['type']; //1:候補日
                $interviewSchedule->interview_phase           = $mgt->last_selection_flow_number;

                $interviewSchedule->save();

                $schedule_lists .= sprintf("%s(%s) %s~%s\r\n", date('Y年n月j日', strtotime($schedule['start'])), g_enum('week_days', date('w', strtotime($schedule['start']))), date('H:i', strtotime($schedule['start'])), date('H:i', strtotime($schedule['end'])));
            }

            $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->where('interview_phase', $mgt->last_selection_flow_number)
                ->whereNotNull('deleted_by')
                ->delete();

            $flow_name = g_enum('outsource_interview_flow', $mgt->last_selection_flow_number);

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・面談日程を送信時。「面談の日程を提案」
            // ・面談日程の折り合いがつかなかったため別の候補日を提案。「面談の日程を再提案」
            $timeline->message_title = $schedule_changed ? $flow_name."の日程を再提案" : $flow_name."の日程を提案";
            $timeline->message_detail = $schedule_lists;
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // TODO削除：「１　面談設定時の画面」で、「候補者に候補日を提示してもらう」又は「候補日を提示する」のどちらかの設定が完了した後
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
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

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);
        $schedule = InterviewSchedule::findOrFail($target_id);

        if (!empty($mgt) && !empty($schedule)) {

            // 関連情報を取得する
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

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

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->selection_status = 1; // 1:選考中
            $mgt->{$result_key} = 7; // 7:日程設定済み
            $mgt->{$result_key.'_date'} = date('Y-m-d', strtotime($schedule->interview_candidates_date));

            $mgt->save();

            // 他の候補日時をNGにする
            InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
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
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・面談の日程が確定した時。「〇〇（一次や最終など）〇〇面談の日程を確定」面談の日時も表示。
            $timeline->message_title = g_enum('outsource_interview_flow', $mgt->last_selection_flow_number)."の日程を確定";
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
                ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
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

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->selection_status = 1; // 1:選考中
            $mgt->{$result_key} = 6; // 6:日程未確定（！要対応）
            $mgt->{$result_key.'_date'} = null;

            $mgt->save();

            // すべての候補日時をNGにする
            InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
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
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
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
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            $timeline->message_title = g_enum('outsource_interview_flow', $mgt->last_selection_flow_number)."の日程を全てNGにして別の日程を提示";
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
     * 契約条件を提示する
     *
     * @return \Illuminate\Http\Response
     */
    public function sendJoiningCondition(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $unit_price = $request->input('unit_price');
        $unit_price_amount = $request->input('unit_price_amount');
        $pay_off_start = $request->input('pay_off_start');
        $pay_off_end = $request->input('pay_off_end');
        $estimated_working_days_week = $request->input('estimated_working_days_week');
        $special_notes = $request->input('special_notes');
        $joining_start_date = $request->input('joining_start_date');
        $reply_deadline = $request->input('reply_deadline');

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            $mgt->selection_status = 4; // 4:オファー

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            // 契約条件を提示
            if ($mgt->last_selection_flow_number == 8) {
                $mgt->{$result_key} = 3;    // 3:契約条件同意待ち
            }

            $mgt->save();

            // 契約条件を提示
            $condition = new ContractTerms();
            $condition->outsource_job_seeker_apply_mgt_id = $apply_mgt_id;
            $condition->unit_price = $unit_price;
            $condition->unit_price_amount = $unit_price_amount;
            $condition->pay_off_start = $pay_off_start;
            $condition->pay_off_end = $pay_off_end;
            $condition->estimated_working_days_week = $estimated_working_days_week;
            $condition->special_notes = $special_notes;
            $condition->joining_start_date = $joining_start_date;
            $condition->reply_deadline = $reply_deadline;

            $condition->save();

            $condition_changed = ContractTerms::where('outsource_job_seeker_apply_mgt_id', $apply_mgt_id)
                ->whereNull('deleted_at')->whereNull('deleted_by')
                ->count() > 1;

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・内定後、契約条件を提示した時。「契約条件を提示」
            $timeline->message_title = $condition_changed ? "契約条件を変更" : "契約条件を提示";
            $timeline->message_detail = "単価（円）：".g_enum('unit_price', $unit_price).number_format($unit_price_amount)."円\r\n";
            $timeline->message_detail .= "清算時間（月）：".$pay_off_start."～".$pay_off_end."\r\n";
            $timeline->message_detail .= "想定稼働日数/週：".$estimated_working_days_week."日\r\n";
            $timeline->message_detail .= "特記事項（その他条件）：".$special_notes."\r\n";
            $timeline->message_detail .= "参画開始日：".date('Y年n月j日', strtotime($joining_start_date))."\r\n";
            $timeline->message_detail .= "返答期限：".date('Y年n月j日', strtotime($reply_deadline));
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // TODO削除：「オファー面談」または「契約条件」を入力して送信後
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->delete();

            return response()->json(['success'=>true,
                'outsourceApplyMgt' => $mgt,
                'contractTerms' => $mgt->contractTerms,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 参画開始日の変更
     *
     * @return \Illuminate\Http\Response
     */
    public function sendChangeStartDate(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $joining_scheduled_date = $request->input('joining_scheduled_date');

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            $mgt->selection_status = 5; // 5:成約(参画開始待ち)

            $mgt->last_selection_flow_number = 8; // 契約
            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 1; // 1:参画予定日
            $mgt->joining_scheduled_date = $joining_scheduled_date; // 参画開始予定日

            $mgt->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・
            $timeline->message_title = "参画開始日が変更";
            $timeline->message_detail = "参画開始日：".date('Y年n月j日', strtotime($joining_scheduled_date));
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            return response()->json(['success'=>true,
                'outsourceApplyMgt' => $mgt,
                'contractTerms' => $mgt->contractTerms,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 参画終了申請
     *
     * @return \Illuminate\Http\Response
     */
    public function sendFinishContract(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $joining_end_date = $request->input('joining_end_date');

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者
            // ・参画を終了する場合でどちらかから申し出があった場合。「参画終了申請」。参画終了日などを表示。
            $timeline->message_title = "参画終了申請";
            $timeline->message_detail = "参画終了日：".date('Y年n月j日', strtotime($joining_end_date));
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            $mgt->selection_status = 6; // 6:参画中

            $mgt->last_selection_flow_number = 10; // 現況
            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 1; // 1:参画中
            $mgt->joining_end_applicant = 1; // 1:求人企業
            $mgt->joining_end_date = $joining_end_date; // 参画終了日

            $mgt->save();

            return response()->json(['success'=>true,
                'outsourceApplyMgt' => $mgt,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 参画終了申請 - 確認/取消済み
     *
     * @return \Illuminate\Http\Response
     */
    public function sendAgreeFinish(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $isAgree = $request->input('isAgree');

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        $isAgree = ($isAgree === true || $isAgree == 'true') ? true : false;

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            if ($isAgree) {
                if (!empty($mgt->joining_end_date) && date('Y-m-d', strtotime($mgt->joining_end_date)) <= date('Y-m-d')) {
                    $mgt->selection_status = 7; // 7:参画終了
                    $mgt->{$result_key} = 2; // 2:終了
                }
            }
            else {
                $mgt->selection_status = 6; // 6:参画中
                $mgt->{$result_key} = 1; // 1:参画中
                $mgt->joining_end_applicant = null;
                $mgt->joining_end_date = null; // 参画終了日
            }
            $mgt->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 1; // 1:求人企業担当者

            if ($isAgree) {
                // ・参画終了の申し出を受諾した時。「参画終了日が確定しました。」。参画終了日を表示。
                $timeline->message_title = "参画終了日が確定しました。";
                $timeline->message_detail = "参画終了日：".date('Y年n月j日', strtotime($mgt->joining_end_date));
            }
            else {
                // ・参画終了の申し出を取消した時。
                $timeline->message_title = "参画終了申請を取消しました。";
           }
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            return response()->json(['success'=>true,
                'outsourceApplyMgt' => $mgt,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    public function getTimelineRecords(Request $request)
    {
        $id = $request->input('id');
        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($id);
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
        $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE; //業務委託
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
