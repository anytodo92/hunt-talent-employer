<?php

namespace App\Http\Controllers\Outsource;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use App\Models\CompanyUser;
use App\Models\JobSeekerAttachment;
use App\Models\QuestionAnswer;
use App\Models\OutsourceJobSeeker;
use App\Models\OutsourceJobSeekerApplyMgts;
use App\Models\OutsourceOfferInfo;
use App\Models\SelectionResult;
use App\Models\Timeline;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use RecursiveIterator;

class CalendarController extends Controller
{
    /**
     * CalendarController constructor.
     */
    public function __construct()
    {
    }

    /**
     * 【業務委託】カレンダー
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // 面談・面談日程一覧
        $fixedSchedules = InterviewSchedule::where('company_id', Auth::user()->outsource_user->outsource_company_id)
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
            ->where('interview_date_type', 2)  //2:確定した日(=◯)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->get();

        // 現在調整中の日程（候補日程）
        $pendingSchedules = InterviewSchedule::where('company_id', Auth::user()->outsource_user->outsource_company_id)
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
            ->where('interview_date_type', 1)  //1:候補日
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->get();

        return view('outsource.calendar')->with([
            'fixedSchedules' => $fixedSchedules,
            'pendingSchedules' => $pendingSchedules,
            'now' => date('Y-m-d'),
        ]);
    }

    /**
     * ★【業務委託】日程調整①
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function calendar1(Request $request)
    {
        $id = $request->input('id');
        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($id);

        // 関連情報を取得する
        $jobSeeker = $mgt->jobSeeker; // 業務委託_参画者
        $outsourceCompany = $jobSeeker->outsourceCompany; // 求人企業
        $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

        // 候補日時
        $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $id)
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
            ->where('interview_phase', $mgt->last_selection_flow_number)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->get();

        return view('outsource.apply.calendar1')->with([
            'id' => $id,
            'outsourceJobSeekerApplyMgt' => $mgt,
            'outsourceCompany' => $outsourceCompany,
            'jobSeeker' => $jobSeeker,
            'offerInfo' => $offerInfo,
            'interviewSchedules' => $interviewSchedules,
            'now' => date('Y-m-d'),
        ]);
    }

    /**
     * ★【業務委託】日程調整②
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function calendar2(Request $request)
    {
        $id = $request->input('id');
        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($id);

        // 関連情報を取得する
        $jobSeeker = $mgt->jobSeeker; // 業務委託_参画者
        $outsourceCompany = $jobSeeker->outsourceCompany; // 求人企業
        $offerInfo = $mgt->outsourceOfferInfo; // 業務委託_求人情報

        // 候補日時
        $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $id)
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
            ->where('interview_phase', $mgt->last_selection_flow_number)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->get();

        return view('outsource.apply.calendar2')->with([
            'id' => $id,
            'outsourceJobSeekerApplyMgt' => $mgt,
            'outsourceCompany' => $outsourceCompany,
            'jobSeeker' => $jobSeeker,
            'offerInfo' => $offerInfo,
            'interviewSchedules' => $interviewSchedules,
            'now' => date('Y-m-d'),
        ]);
    }
}
