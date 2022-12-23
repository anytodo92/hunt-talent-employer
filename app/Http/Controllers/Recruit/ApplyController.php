<?php

namespace App\Http\Controllers\Recruit;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use App\Models\RecruitCompanyUser;
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

class ApplyController extends Controller
{
    /**
     * ApplyController constructor.
     */
    public function __construct()
    {
    }

    /**
     * 【人材紹介会社】選考状況一覧
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        // 求職者の名称を取得する（検索項目のサジェストとして使う）
        $login_company_id = Auth::user()->recruit_user->recruit_company_id;
        $jobSeekers = RecruitJobSeeker::where('recruit_company_id', $login_company_id)
            ->select('last_name', 'first_name', 'last_name_kana', 'first_name_kana')
            ->get();

        $recruitJobSeekers = array();
        foreach ($jobSeekers as $jobSeeker) {
            $recruitJobSeekers[] = $jobSeeker->last_name . $jobSeeker->first_name;
            $recruitJobSeekers[] = $jobSeeker->last_name_kana . $jobSeeker->first_name_kana;
        }
        $recruitJobSeekers = array_unique($recruitJobSeekers);

        // 人材紹介会社の担当者を取得する（検索条件として使う）
        $recruitCompanyUsers = RecruitCompanyUser::where('recruit_company_id', $login_company_id)
            ->select('id', 'user_id', 'name')
            ->get();

        return view('recruit.apply.index')->with([
            'recruitJobSeekers' => $recruitJobSeekers,
            'recruitCompanyUsers' => $recruitCompanyUsers
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
        $result = $recruitJobSeekerMgts->datatableForRecruit($params);

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
            $html = $html . "<span class='recruit_status " . ($i == $currentStatus ? 'bold' : '') . "' data-id='" . $i . "'>" . g_enum('recruit_status', $i) . " " . $result['statusCount'][$i] . "  </span>";
        }

        $result['data'] = $records;
        $result['statusCountHtml'] = $html;

        return response()->json($result);
    }

    /**
     * 【人材紹介会社】選考詳細
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
            $todo->todo_transition_target = 7;  // 7:人材紹介：選考詳細画面
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
                    $todo->todo_transition_target = 7;  // 7:人材紹介：選考詳細画面
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

        return view('recruit.apply.detail')->with([
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

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            $mgt->selection_status = 3; // 3:落選/辞退

            if ($mgt->last_selection_flow_number == 12) {
                $mgt->last_selection_flow_number = 13;
                $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
                $mgt->{$result_key} = 2; // 2:辞退
            }
            else {
                $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
                $mgt->{$result_key} = 3; // 3:辞退
            }
            $result_key = str_replace('_interview', '', $result_key);
            $mgt->{$result_key.'_refusal_reason'} = g_enum('recruit_refusal_reason', $reason)."\r\n".$details;
            $mgt->{$result_key.'_refusal_reason_date'} = date('Y-m-d');
            $mgt->last_selection_flow_date = date('Y-m-d');

            $mgt->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 2; // 2:人材紹介会社担当者
            // ・求職者（人材紹介会社）が選考を辞退した時。「〇〇さんが選考を辞退しました。」辞退理由も表示。
            $timeline->message_title = $jobSeeker->last_name . ' ' . $jobSeeker->first_name."さんが選考を辞退しました。";
            $timeline->message_detail = "辞退理由：".g_enum('recruit_refusal_reason', $reason)."\r\n";
            $timeline->message_detail .= $details;
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
            $selectionResult->interview_setting_person_type = 2;    // 1:求人企業 2:候補者

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
            $timeline->sender_type = 2; // 2:人材紹介会社担当者
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

            // Todoに追加
            if ($mgt->last_selection_flow_number >= 4/*筆記/webテスト*/ && $mgt->last_selection_flow_number <= 11/*最終選考*/) {
                // 削除：日程調整が完了して送信後
                Todo::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                    ->where('offer_info_type', Constants::BIZ_TYPE_RECRUIT)
                    ->delete();

                // TODO発生：候補者から面接・面談の日程が届いた時。
                $todo = new Todo();
                $todo->company_id = $offerInfo->company_id;
                $todo->offer_info_id = $offerInfo->id;
                $todo->offer_info_type = Constants::BIZ_TYPE_RECRUIT;
                $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
                $todo->question_and_answer_id = null;
                $todo->calendar_id = null;
                // 「〇〇求職者名〇〇さんの〇〇面接の候補日が届きました。日程の確定をお願い致します。」
                $todo->todo_content = $jobSeeker->last_name . ' ' . $jobSeeker->first_name."さんの".g_enum('recruit_interview_flow', $mgt->last_selection_flow_number)."の候補日が届きました。日程の確定をお願い致します。";
                $todo->todo_complete = 1;   // 1:未完了
                // 遷移先：該当する候補者の「日程調整」ページ
                $todo->todo_transition_target = 8;  // 8:人材紹介：日程調整①
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
            $timeline->sender_type = 2; // 2:人材紹介会社担当者
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
            $selectionResult->interview_setting_person_type = 2;    // 1:求人企業 2:候補者

            $selectionResult->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 2; // 2:人材紹介会社担当者
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

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

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
                $mgt->{$result_key.'_date'} = date('Y-m-d', strtotime($interviewDate));
            }

            $mgt->save();

            // 調整中の候補日時を削除する
            InterviewSchedule::where('job_seeker_apply_mgt_id', $apply_mgt_id)
                ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
                ->where('interview_phase', $mgt->last_selection_flow_number)
                ->whereIn('interview_date_type', array(1, 2))
                ->whereNull('deleted_at')
                ->whereNull('deleted_by')
                ->update(['interview_date_type'=>3]); // 3:確定しなかった日(=X)

            $schedule = new InterviewSchedule();
            $schedule->company_id = $offerInfo->company_id;
            $schedule->job_seeker_apply_mgt_id   = $apply_mgt_id;
            $schedule->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $schedule->interview_candidates_name = $jobSeeker->last_name . ' ' . $jobSeeker->first_name;
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
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 2; // 2:人材紹介会社担当者
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

            // TODO発生：候補者から面接・面談の日程が届いた時。
            $todo = new Todo();
            $todo->company_id = $offerInfo->company_id;
            $todo->offer_info_id = $offerInfo->id;
            $todo->offer_info_type = Constants::BIZ_TYPE_RECRUIT;
            $todo->job_seeker_apply_mgt_id = $apply_mgt_id;
            $todo->question_and_answer_id = null;
            $todo->calendar_id = null;
            // 「〇〇求職者名〇〇さんの〇〇面接の候補日が届きました。日程の確定をお願い致します。」
            $todo->todo_content = $jobSeeker->last_name . ' ' . $jobSeeker->first_name."さんの".g_enum('recruit_interview_flow', $mgt->last_selection_flow_number)."の候補日が届きました。日程の確定をお願い致します。";
            $todo->todo_complete = 1;   // 1:未完了
            // 遷移先：該当する候補者の「日程調整」ページ
            $todo->todo_transition_target = 8;  // 8:人材紹介：日程調整①
            $todo->read_flg = 1;    // 1:未読
            $todo->created_at = date('Y-m-d H:i:s');
            $todo->created_by = Auth::user()->id;

            $todo->save();

            return response()->json(['success'=>true,
                'recruitApplyMgt' => $mgt,
                'interviewSchedules' => $mgt->calendars,
                'timelines' => $mgt->timelines
            ]);
        }

        return response()->json(['success'=>false]);
    }

    /**
     * 入社条件を交渉する
     *
     * @return \Illuminate\Http\Response
     */
    public function sendJoiningCondition(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $annualIncome = $request->input('annualIncome');
        $firstDayAttendanceDate = $request->input('firstDayAttendanceDate');
        $other_desired = $request->input('other_desired');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            $mgt->selection_status = 4; // 4:内定(入社待ち)

            $result_key = g_enum('recruit_apply_mgt_selection_result_key', $mgt->last_selection_flow_number);
            // 候補者の希望入社条件を提示
            if ($mgt->last_selection_flow_number == 12) {
                $mgt->{$result_key} = 3;    // 3:入社条件提示・交渉（！要対応）
            }

            $mgt->save();

            // 候補者の希望入社条件を提示
            $final_condition = JoiningConditionPresent::where('recruit_job_seeker_apply_mgt_id', $apply_mgt_id)
                ->orderBy('created_at', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();

            $final_condition->job_changer_desired_annual_income = $annualIncome;
            $final_condition->job_changer_first_day_attendance_date = $firstDayAttendanceDate;
            $final_condition->other_desired = $other_desired;

            $final_condition->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 2; // 2:人材紹介会社担当者
            // ・内定後、入社条件を提示した時。「入社条件を提示」
            $timeline->message_title = "希望する入社条件の提示";
            $timeline->message_detail = "希望年収：".number_format($annualIncome)."円\r\n";
            $timeline->message_detail .= "初日出勤日：".date('Y年n月j日', strtotime($firstDayAttendanceDate))."\r\n";
            $timeline->message_detail .= "その他希望：".$other_desired;
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
     * 入社条件に同意
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

            $final_condition->job_changer_desired_annual_income = $final_condition->offer_amount;
            $final_condition->job_changer_first_day_attendance_date = $final_condition->first_day_attendance_date;
            $final_condition->first_day_work_schedule_date = $final_condition->first_day_attendance_date;   // 初日出勤日
            $final_condition->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 2; // 2:人材紹介会社担当者
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
            $timeline->sender_type = 2; // 2:人材紹介会社担当者
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
            $todo->todo_content = "ご採用おめでとうございます！".$jobSeeker->last_name . ' ' . $jobSeeker->first_name."さんに提示した入社条件が同意されました。初日出勤日になりましたら確認の操作をお願い致します。";
            $todo->todo_complete = 1;   // 1:未完了
            // 遷移先：該当する「選考詳細ページ」
            $todo->todo_transition_target = 7;  // 7:人材紹介：選考詳細画面
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
     * 初日出勤日の変更
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
            $timeline->sender_type = 2; // 2:人材紹介会社担当者
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
     * 返金に同意・不同意する
     *
     * @return \Illuminate\Http\Response
     */
    public function sendAgreeRefund(Request $request)
    {
        $apply_mgt_id = $request->input('apply_mgt_id');
        $isAgree = $request->input('isAgree');
        $refundAmount = $request->input('refundAmount');

        $mgt = RecruitJobSeekerApplyMgts::findOrFail($apply_mgt_id);

        $isAgree = ($isAgree === true || $isAgree == 'true') ? true : false;

        if (!empty($mgt)) {

            // 関連情報を取得する
            $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

            if ($isAgree) {
                $mgt->refund_agreement_date = date('Y-m-d'); // 返金同意日
                $mgt->refund_amount = $refundAmount; // 返金額
                $mgt->refund_status = 2; // 2:同意した状態
            }
            else {
                $mgt->refund_disagreement_date = date('Y-m-d'); // 返金不同意日
                $mgt->refund_amount = null; // 返金額
                $mgt->refund_status = 3; // 3:同意しなかった状態
            }
            $mgt->save();

            // タイムラインに追加
            $timeline = new Timeline();
            $timeline->company_id = $offerInfo->company_id;
            $timeline->job_seeker_apply_mgt_id = $apply_mgt_id;
            $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
            $timeline->message_type = 1;    // 1:自動送信メッセージ
            $timeline->message_sender = Auth::user()->id;
            $timeline->sender_type = 2; // 2:人材紹介会社担当者
            if ($isAgree) {
                // ・返金申請に同意した時（された時）。「返金申請を同意」と同意日時、返金額、退職日などを記載
                $timeline->message_title = "返金申請を同意";
                $timeline->message_detail = "同意日時：".date('Y年n月j日', strtotime($mgt->refund_agreement_date))."\r\n";
                $timeline->message_detail .= "返金額：".number_format($mgt->refund_amount)."\r\n";
                $timeline->message_detail .= "退職日：".date('Y年n月j日', strtotime($mgt->retirement_date));
            }
            else {
                 // ・返金申請に同意しなかった時（されなかった時）。「返金申請が同意されませんでした。」と不同意日時などを記載
                $timeline->message_title = "返金申請が同意されませんでした。";
                $timeline->message_detail = "不同意日時：".date('Y年n月j日', strtotime($mgt->refund_disagreement_date));
           }
            $timeline->timeline_complete = 1;   // 1:未完了
            $timeline->timeline_transition_target = 1;  // 1.選考詳細画面
            $timeline->read_flg = 1;    // 1:未読
            $timeline->created_at = date('Y-m-d H:i:s');
            $timeline->created_by = Auth::user()->id;

            $timeline->save();

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
        $timeline->company_id = Auth::user()->recruit_user->recruit_company_id;
        $timeline->job_seeker_apply_mgt_id = $params['JobSeekerApplyMgtID'];
        $timeline->job_seeker_apply_mgt_type = Constants::BIZ_TYPE_RECRUIT;
        $timeline->message_type = 2; //送信者メッセージ
        $timeline->message_sender = Auth::user()->id;
        $timeline->sender_type = 2; // 2:人材紹介会社担当者
        $timeline->message_detail = $params['MessageDetail'];
        $timeline->attachment = $params['fileName'];
        $timeline->attachment_name = $params['realName'];
        $timeline->timeline_complete = 1; //未完了
        $timeline->timeline_transition_target = 1; //選考詳細画面
        $timeline->read_flg = 1; //未読

        $timeline->save();

        //$timeline = $timeline->relationRecruitAvatarInfo($timeline->id);
        $timeline->recruit_company_id = Auth::user()->recruit_user->recruit_company_id;
        $timeline->recruit_user_id = Auth::user()->recruit_user->id;
        $timeline->recruit_user_logo = Auth::user()->recruit_user->logo;

        return response()->json($timeline);
    }
}
