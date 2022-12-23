<?php

namespace App\Http\Controllers\Outsource;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use App\Models\CompanyUser;
use App\Models\JobSeekerAttachment;
use App\Models\QuestionAnswer;
use App\Models\RecruitCompanyUser;
use App\Models\RecruitJobSeeker;
use App\Models\RecruitJobSeekerApplyMgts;
use App\Models\RecruitOfferInfo;
use App\Models\SelectionResult;
use App\Models\Timeline;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    public function index()
    {
        // 人材紹介_求人情報のタイトルを取得する（検索条件として使う）
        $outsource_company_users_name = OutsourceJobSeeker::leftjoin('outsource_company_users', 'outsource_job_seekers.outsource_company_user_id', '=', 'outsource_company_users.id')
            ->select('outsource_company_users.name')
            ->groupBy('outsource_company_users.name')
            ->get();

        $outsource_jobseeker_status = config('constants.recruit_status');

		return view('outsource.jobseeker')->with([
			'userNames' => $outsource_company_users_name,
			'status' => $outsource_jobseeker_status
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
		$outsourceJobSeeker = new OutsouceJobSeeker();

		$result = $outsourceJobSeeker->datatable($params);

        // 性別、生年月日、職種カテゴリーの表示データを取得する
		$records = $result['data'];
		//$category = config('constants.category_2');
		foreach($records as &$record) {
			$record->sex = g_enum('sex', $record->sex);
			$record->birthday = g_age($record->birthday);
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
		$mgt = OutsourceJobSeekerApplyMgts::findOrFail($id);

		// まだ「STEP1 応募」、「STEP2 書類確認：未」段階の場合
        if ($mgt->last_selection_flow_number < 3) {
            // 「STEP3 書類選考」段階に自動的に進む
            $mgt->document_confirmation = 2;
            $mgt->document_confirmation_date = date('Y-m-d');

            $mgt->last_selection_flow_number = 3;
            $mgt->applicant_screening = 5; // 5:選考結果未送付（！要対応）
            $mgt->save();

            // 選考結果レコードを生成する
            $selectionResult = new SelectionResult();
            $selectionResult->fill([
                'job_seeker_apply_mgt_id' => $id,
                'job_seeker_apply_mgt_type' => Constants::BIZ_TYPE_RECRUIT,
                'phase' => 3,
            ]);
            $selectionResult->save();
        }

        // 関連情報を取得する
		$jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
        $outsourceCompany = $jobSeeker->outsourceCompany; // 求人企業
		$offerInfo = $mgt->outsourceOfferInfo; // 人材紹介_求人情報
        $workPlaces = $offerInfo->workPlaces; // 勤務地
        $selectionResults = $mgt->selectionResults; // 選考結果
        $interviewSchedules = $mgt->calendars; // 面接日程
        $timelines = $mgt->timelines; // タイムライン

		$attachments = JobSeekerAttachment::where('job_seeker_id', '=', $mgt->outsource_job_seeker_id)
			->where('job_seeker_type', '=', Constants::BIZ_TYPE_RECRUIT)
			->select('attachment_type', 'attachment', 'attachment_name', 'upload_datetime')
			->get();

		return view('outsource.recruit.detail')->with([
            'id' => $id,
            'outsourceJobSeekerApplyMgt' => $mgt,
			'outsourceCompany' => $outsourceCompany,
            'jobSeeker' => $jobSeeker,
			'offerInfo' => $offerInfo,
			'workPlaces' => $workPlaces,
            'selectionFlow' => $offerInfo->selection_flow,
            'attachments' => $attachments,
            'selectionResults' => $selectionResults,
            'interviewSchedules' => $interviewSchedules,
            'timelines' => $timelines,
            'now' => date('Y-m-d'),
		]);
	}

    public function breadcrumb($id) {
        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($id);

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
}
