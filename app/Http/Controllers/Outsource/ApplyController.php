<?php

namespace App\Http\Controllers\Outsource;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use App\Models\OutsourceCompanyUser;
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

class ApplyController extends Controller
{
    /**
     * ApplyController constructor.
     */
    public function __construct()
    {
    }

    /**
     * 【業務委託会社】選考状況一覧
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        // 参画者の名称を取得する（検索項目のサジェストとして使う）
        $login_company_id = Auth::user()->outsource_user->outsource_company_id;
        $jobSeekers = OutsourceJobSeeker::where('outsource_company_id', $login_company_id)
            ->select('initial')
            ->get();

        $outsourceJobSeekers = array();
        foreach ($jobSeekers as $jobSeeker) {
            $outsourceJobSeekers[] = $jobSeeker->initial;
        }
        $outsourceJobSeekers = array_unique($outsourceJobSeekers);

        // 業務委託会社の担当者を取得する（検索条件として使う）
        $outsourceCompanyUsers = OutsourceCompanyUser::where('outsource_company_id', $login_company_id)
            ->select('id', 'user_id', 'name')
            ->get();

        return view('outsource.apply.index')->with([
            'outsourceJobSeekers' => $outsourceJobSeekers,
            'outsourceCompanyUsers' => $outsourceCompanyUsers
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
        $result = $outsourceJobSeekerMgts->datatableForOutsource($params);

        // 性別、生年月日、職種カテゴリーの表示データを取得する
        $records = $result['data'];

        foreach($records as &$record) {
            $record->sex = g_enum('sex', $record->sex);
            $record->birthday = g_age($record->birthday);

            // 「面談日程未確定」の時などで、相手側の日程待ちの状態の場合（求人企業側で対応することではない状態の場合は全て）には一覧の「！要対応」の文字を非表示
            $record->is_pending = false;
            if ($record->last_selection_flow_number >= 4/*１次面談*/ && $record->last_selection_flow_number <= 7/*最終面談*/) {
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
            $html = $html . "<span class='outsource_status " . ($i == $currentStatus ? 'bold' : '') . "' data-id='" . $i . "'>" . g_enum('outsource_status', $i) . " " . $result['statusCount'][$i] . "  </span>";
        }

        $result['data'] = $records;
        $result['statusCountHtml'] = $html;

        return response()->json($result);
    }

    /**
     * 【業務委託会社】選考詳細
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
            $todo->todo_transition_target = 12;  // 12:業務委託：選考詳細画面
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
                    $todo->todo_transition_target = 12;  // 12:業務委託：選考詳細画面
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

        return view('outsource.apply.detail')->with([
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
     * 選考辞退
     *
     * @return \Illuminate\Http\Response
     */
    public function sendRefusalReason(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $reason = $request->input('reason');
        $details = $request->input('details');

        $details = trim($details);

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 業務委託_参画者
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            $mgt->selection_status = 3; // 3:見送り/辞退

            if ($mgt->last_selection_flow_number == 8) {
                $mgt->last_selection_flow_number = 9;
                $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
                $mgt->{$result_key} = 2; // 2:辞退
            }
            else {
                $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
                $mgt->{$result_key} = 4; // 4:辞退
            }
            $result_key = str_replace('_interview', '', $result_key);
            $mgt->{$result_key.'_refusal_reason'} = g_enum('outsource_refusal_reason', $reason)."\r\n".$details;
            $mgt->{$result_key.'_refusal_reason_date'} = date('Y-m-d');
            $mgt->last_selection_flow_date = date('Y-m-d');

            $mgt->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 3; // 3:業務委託会社担当者
            // ・候補者（エージェント側）が辞退した時。「〇イニシャル〇さんが選考を辞退しました。」辞退理由も表示。
            $timeline->message_title = $jobSeeker->initial."さんが選考を辞退しました。";
            $timeline->message_detail = "辞退理由：".g_enum('outsource_refusal_reason', $reason)."\r\n";
            $timeline->message_detail .= $details;
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
            $selectionResult->interview_setting_person_type = 2;    // 1:求人企業 2:候補者

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
            $timeline->sender_type = 3; // 3:業務委託会社担当者
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

            // Todoに追加
            if ($mgt->last_selection_flow_number >= 4/*1次面談*/ && $mgt->last_selection_flow_number <= 7/*最終選考*/) {
                // 削除：日程調整が完了して送信後
                Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                    ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
                    ->delete();

                // TODO発生：候補者から面談・面談の日程が届いた時。
                $todo = new Todo();
                $todo->company_id = $offerInfo->company_id;
                $todo->offer_info_id = $offerInfo->id;
                $todo->offer_info_type = Constants::BIZ_TYPE_OUTSOURCE;
                $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
                $todo->question_and_answer_id = null;
                $todo->calendar_id = null;
                // 「〇〇参画者名〇〇さんの〇〇面談の候補日が届きました。日程の確定をお願い致します。」
                $todo->todo_content = $jobSeeker->initial."さんの".g_enum('outsource_interview_flow', $mgt->last_selection_flow_number)."の候補日が届きました。日程の確定をお願い致します。";
                $todo->todo_complete = 1;   // 1:未完了
                // 遷移先：該当する候補者の「日程調整」ページ
                $todo->todo_transition_target = 13;  // 13:業務委託：日程調整①
                $todo->read_flg = 1;    // 1:未読
                $todo->created_at = date('Y-m-d H:i:s');
                $todo->created_by = Auth::user()->id;

                $todo->save();
            }

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
            $timeline->sender_type = 3; // 3:業務委託会社担当者
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
            $selectionResult->interview_setting_person_type = 2;    // 1:求人企業 2:候補者

            $selectionResult->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 3; // 3:業務委託会社担当者
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
     * 確定した日程を送信
     *
     * @return \Illuminate\Http\Response
     */
    public function sendFixedInterviewDate(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $interviewDate = $request->input('interviewDate');
        $interviewTimeFrom = $request->input('interviewTimeFrom');
        $interviewTimeTo = $request->input('interviewTimeTo');

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 業務委託_参画者
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->selection_status = 1; // 1:選考中
            $mgt->{$result_key} = 7; // 7:日程設定済み
            $mgt->{$result_key.'_date'} = date('Y-m-d', strtotime($interviewDate));

            $mgt->save();

            // 調整中の候補日時を削除する
            InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->where('interview_phase', $mgt->last_selection_flow_number)
                ->whereIn('interview_date_type', array(1, 2))
                ->whereNull('deleted_at')
                ->whereNull('deleted_by')
                ->update(['interview_date_type'=>3]); // 3:確定しなかった日(=X)

            $schedule = new InterviewSchedule();
            $schedule->company_id = $offerInfo->company_id;
            $schedule->job_seeker_apply_mgt_id   = $apply_mgt_id;
            $schedule->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $schedule->interview_candidates_name = $jobSeeker->initial;
            $schedule->interview_candidates_date = $interviewDate;
            $schedule->interview_candidates_from = $interviewTimeFrom;
            $schedule->interview_candidates_to   = $interviewTimeTo;
            $schedule->interview_date_type       = 2; //2:確定した日(=◯)
            $schedule->interview_phase           = $mgt->last_selection_flow_number;
            $schedule->created_at = date('Y-m-d H:i:s');
            $schedule->created_by = Auth::user()->id;

            $schedule->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 3; // 3:業務委託会社担当者
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

            // TODO発生：候補者から面談・面談の日程が届いた時。
            $todo = new Todo();
            $todo->company_id = $offerInfo->company_id;
            $todo->offer_info_id = $offerInfo->id;
            $todo->offer_info_type = Constants::BIZ_TYPE_OUTSOURCE;
            $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
            $todo->question_and_answer_id = null;
            $todo->calendar_id = null;
            // 「〇〇参画者名〇〇さんの〇〇面談の候補日が届きました。日程の確定をお願い致します。」
            $todo->todo_content = $jobSeeker->initial."さんの".g_enum('outsource_interview_flow', $mgt->last_selection_flow_number)."の候補日が届きました。日程の確定をお願い致します。";
            $todo->todo_complete = 1;   // 1:未完了
            // 遷移先：該当する候補者の「日程調整」ページ
            $todo->todo_transition_target = 13;  // 13:業務委託：日程調整①
            $todo->read_flg = 1;    // 1:未読
            $todo->created_at = date('Y-m-d H:i:s');
            $todo->created_by = Auth::user()->id;

            $todo->save();

            return response()->json(['success'=>true,
                'outsourceApplyMgt' => $mgt,
                'interviewSchedules' => $mgt->calendars,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 契約条件に同意
     *
     * @return \Illuminate\Http\Response
     */
    public function sendAllowJoining(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');

        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 業務委託_参画者
            $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

            $mgt->selection_status = 5; // 5:成約(参画開始待ち)

            $mgt->last_selection_flow_number = 8; // 契約
            $result_key = g_enum('outsource_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            $mgt->{$result_key} = 1; // 1:参画予定日
            $mgt->contract_satisfied_date = date('Y-m-d'); // 契約成立日

            // 参画開始予定日を設定
            $final_condition = ContractTerms::where('outsource_job_seeker_apply_mgt_id', $apply_mgt_id)
                ->whereNull('deleted_at')->whereNull('deleted_by')
                ->orderBy('created_at', 'desc')->orderBy('id', 'desc')
                ->first();

            $mgt->joining_scheduled_date = $final_condition->joining_start_date; // 参画開始日

            $mgt->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 3; // 3:業務委託会社担当者
            // ・エージェント側がオファーを受け入れた時。「契約成立」。契約条件（参画予定日や単価、清算時間など）を表示。
            $timeline->message_title = "契約成立";
            $timeline->message_detail = "単価（円）：".g_enum('unit_price', $final_condition->unit_price).number_format($final_condition->unit_price_amount)."円\r\n";
            $timeline->message_detail .= "清算時間（月）：".$final_condition->pay_off_start."～".$final_condition->pay_off_end."\r\n";
            $timeline->message_detail .= "想定稼働日数/週：".$final_condition->estimated_working_days_week."日\r\n";
            $timeline->message_detail .= "特記事項（その他条件）：".$final_condition->special_notes."\r\n";
            $timeline->message_detail .= "参画開始日：".date('Y年n月j日', strtotime($final_condition->joining_start_date))."\r\n";
            $timeline->message_detail .= "返答期限：".date('Y年n月j日', strtotime($final_condition->reply_deadline));
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

            // TODO削除
            Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE)
                ->delete();

            // TODO発生：提示した契約条件に対して同意された時。
            $todo = new Todo();
            $todo->company_id = $offerInfo->company_id;
            $todo->offer_info_id = $offerInfo->id;
            $todo->offer_info_type = Constants::BIZ_TYPE_OUTSOURCE;
            $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
            $todo->question_and_answer_id = null;
            $todo->calendar_id = null;
            // 「ご採用おめでとうございます！〇〇参画者名〇〇さんに提示した契約条件が同意されました。初日出勤日になりましたら確認の操作をお願い致します。」
            $todo->todo_content = "ご採用おめでとうございます！".$jobSeeker->initial."さんに提示した契約条件が同意されました。";
            $todo->todo_complete = 1;   // 1:未完了
            // 遷移先：該当する「選考詳細ページ」
            $todo->todo_transition_target = 12;  // 12:業務委託：選考詳細画面
            $todo->read_flg = 1;    // 1:未読
            $todo->created_at = date('Y-m-d H:i:s');
            $todo->created_by = Auth::user()->id;

            $todo->save();

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
            $timeline->sender_type = 3; // 3:業務委託会社担当者
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
            $timeline->sender_type = 3; // 3:業務委託会社担当者
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
            $mgt->joining_end_applicant = 2; // 2:業務委託会社
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
            $timeline->sender_type = 3; // 3:業務委託会社担当者
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
        $timeline->company_id = Auth::user()->outsource_user->outsource_company_id;
        $timeline->job_seeker_apply_mgt_id = $params['JobSeekerApplyMgtID'];
        $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_OUTSOURCE;
        $timeline->message_type = 2; //送信者メッセージ
        $timeline->message_sender = Auth::user()->id;
        $timeline->sender_type = 3; // 3:業務委託会社担当者
        $timeline->message_detail = $params['MessageDetail'];
        $timeline->attachment = $params['fileName'];
        $timeline->attachment_name = $params['realName'];
        $timeline->timeline_complete = 1; //未完了
        $timeline->timeline_transition_target = 1; //選考詳細画面
        $timeline->read_flg = 1; //未読

        $timeline->save();

        //$timeline = $timeline->relationOutsourceAvatarInfo($timeline->id);
        $timeline->outsource_company_id = Auth::user()->outsource_user->outsource_company_id;
        $timeline->outsource_user_id = Auth::user()->outsource_user->id;
        $timeline->outsource_user_logo = Auth::user()->outsource_user->logo;

        return response()->json($timeline);
    }
}
