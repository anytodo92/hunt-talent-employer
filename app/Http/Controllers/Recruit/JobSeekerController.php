<?php

namespace App\Http\Controllers\Recruit;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use App\Models\CompanyUser;
use App\Models\JobSeekerAttachment;
use App\Models\QuestionAnswer;
use App\Models\RecruitCompanyUser;
use App\Models\RecruitInterview;
use App\Models\RecruitJobSeeker;
use App\Models\RecruitJobSeekerApplyMgts;
use App\Models\RecruitOfferInfo;
use App\Models\SelectionResult;
use App\Models\Timeline;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use RecursiveIterator;

class JobSeekerController extends Controller
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
    public function index(Request $request)
    {
        $keywword = $request->input('keywword');

        // 人材紹介_求人情報のタイトルを取得する（検索条件として使う）
        $recruit_company_users_name = RecruitJobSeeker::leftjoin('recruit_company_users', 'recruit_job_seekers.recruit_company_user_id', '=', 'recruit_company_users.id')
            ->select('recruit_company_users.name')
            ->groupBy('recruit_company_users.name')
            ->get();

        $recruit_jobseeker_status = config('constants.recruit_status');

		return view('recruit.jobseeker')->with([
			'keywword' => $keywword,
			'userNames' => $recruit_company_users_name,
			'status' => $recruit_jobseeker_status,
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
		$recruitJobSeeker = new RecruitJobSeeker();

		$result = $recruitJobSeeker->datatable($params);

        // 性別、生年月日、職種カテゴリーの表示データを取得する
		$records = $result['data'];

		//$category = config('constants.category_2');
		foreach($records as &$record) {
			$record->sex = g_enum('sex', $record->sex);
            $record->status = g_enum('job_seekers_status', $record->status);
            $record->selection_status = g_enum('recruit_status', $record->selection_status);
            $record->register_type = ($record->register_type != null || !empty($record->register_type)) ? g_enum('job_seekers_register_type', $record->register_type) : 'ー';
            $record->workable_date = ($record->workable_date != null || !empty($record->workable_date)) ?
                (date('Y/m/d', strtotime($record->workable_date)) . 'から可') : '即日可';

			$record->birthday = g_age($record->birthday);
            $record->job_seeker_memo = ($record->job_seeker_memo != null || !empty($record->job_seeker_memo)) ? $record->job_seeker_memo : 'ー';
			/*$record->updated_at = ($record->updated_at != null || !empty($record->updated_at)) ? ('前回更新：' . date('Y.m.d', strtotime($record->updated_at)) . '担当者名') : 'ー';*/
		}

        // ステータス別の件数を表示するHTML
        /*$html = '';
		$currentStatus = $params['extra']['status'];
		for ($i = 1 ; $i <= 6 ; $i ++) {
			$html = $html . "<span class='recruit_status " . ($i == $currentStatus ? 'bold' : '') . "' data-id='" . $i . "'>" . g_enum('recruit_status', $i) . " " . $result['statusCount'][$i] . "  </span>";
		}
        */

		$result['data'] = $records;
		//$result['statusCountHtml'] = $html;

		return response()->json($result);
	}

    /**
     * 人材紹介＿選考詳細
     *
     * @param $id
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
	public function detail($id)
    {
		$jobSeeker = RecruitJobSeeker::findOrFail($id);

		$interviewSchedules = RecruitInterview::leftjoin('recruit_company_users','recruit_company_users.id', 'recruit_interview.recruit_company_users_id')
            ->where('recruit_interview.recruit_job_seekers_id', '=', $id)
            ->get(['recruit_interview.*', 'recruit_company_users.name']);

		$totalJob = RecruitJobSeekerApplyMgts::where('recruit_job_seeker_apply_mgts.recruit_job_seeker_id', $id)
            ->count();

        $appliedJobRecords = RecruitJobSeekerApplyMgts::leftjoin('recruit_offer_infos', 'recruit_offer_infos.id', 'recruit_job_seeker_apply_mgts.recruit_offer_info_id')
            ->where('recruit_job_seeker_apply_mgts.recruit_job_seeker_id', $id)
            ->where('recruit_job_seeker_apply_mgts.application', 1)//応募
            ->orderBy('recruit_job_seeker_apply_mgts.updated_at', 'desc')
            ->limit(10)
            ->get(['recruit_job_seeker_apply_mgts.*', 'recruit_offer_infos.job_title']);
        $notappliedJobRecords = RecruitJobSeekerApplyMgts::leftjoin('recruit_offer_infos', 'recruit_offer_infos.id', 'recruit_job_seeker_apply_mgts.recruit_offer_info_id')
            ->where('recruit_job_seeker_apply_mgts.recruit_job_seeker_id', $id)
            ->where('recruit_job_seeker_apply_mgts.application', 2)//未応募
            ->orderBy('recruit_job_seeker_apply_mgts.updated_at', 'desc')
            ->limit(10)
            ->get(['recruit_job_seeker_apply_mgts.*', 'recruit_offer_infos.job_title']);

        return response()->json([
            'success' => true,
			'jobSeeker' => $jobSeeker,
			'interviewSchedules' => $interviewSchedules,
            'appliedJobRecords' => $appliedJobRecords,
            'notappliedJobRecords' => $notappliedJobRecords,
            'totalJob' => $totalJob,
			]);
	}

    public function breadcrumb($id) {
        $mgt = RecruitJobSeekerApplyMgts::findOrFail($id);

        return response()->json($mgt);
    }

    /**
     * サーバー時間を返す
     */
    public function getServerTime()
    {
        $now = date('Y-m-d H:i:s');
        return response()->json(['now' => $now]);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendJoiningCondition(Request $request)
    {
        $fileName = time().'.'.$request->file->getClientOriginalExtension();
        $request->file->move(public_path('upload'), $fileName);

        return response()->json(['success'=>'You have successfully upload file.']);
    }

    public function saveSchedule(Request $request)
    {
        $params['jobSeekerID'] = $request->input('jobSeekerID');
        $params['scheduleDate'] = $request->input('scheduleDate');
        $params['scheduleTimeFrom'] = $request->input('scheduleTimeFrom');
        $params['scheduleTimeTo'] = $request->input('scheduleTimeTo');
        $params['scheduleInterviewer'] = $request->input('scheduleInterviewer');//??

        $company_users_id = RecruitCompanyUser::where('recruit_company_users.name', '=', $params['scheduleInterviewer'])
            ->get(['recruit_company_users.id']);
        $params['company_users_id'] = $company_users_id[0]->id;

        $record = new RecruitInterview();
        $record->recruit_job_seekers_id = $params['jobSeekerID'];
        $record->recruit_company_users_id = $params['company_users_id'];
        $record->interview_date = $params['scheduleDate'];
        $record->interview_time_from = $params['scheduleTimeFrom'];
        $record->interview_time_to = $params['scheduleTimeTo'];
        $record->status = 1; //面談予定
        $record->created_by = Auth::user()->id;

        $record->save();

        return response()->json(['success'=>true]);
    }
}
