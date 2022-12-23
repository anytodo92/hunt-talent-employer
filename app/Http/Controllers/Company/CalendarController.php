<?php

namespace App\Http\Controllers\Company;

use App\Constants;
use App\Http\Controllers\Controller;
use App\Models\InterviewSchedule;
use App\Models\CompanyUser;
use App\Models\JobSeekerAttachment;
use App\Models\QuestionAnswer;
use App\Models\RecruitJobSeeker;
use App\Models\RecruitJobSeekerApplyMgts;
use App\Models\RecruitOfferInfo;
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
     * 【求人企業】カレンダー
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // 面接・面談日程一覧
        $fixedSchedules = InterviewSchedule::where('company_id', Auth::user()->company_user->company_id)
//            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
            ->where('interview_date_type', 2)  //2:確定した日(=◯)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->get();

        // 現在調整中の日程（候補日程）
        $pendingSchedules = InterviewSchedule::where('company_id', Auth::user()->company_user->company_id)
//            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
            ->where('interview_date_type', 1)  //1:候補日
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->get();

        return view('company.calendar')->with([
            'fixedSchedules' => $fixedSchedules,
            'pendingSchedules' => $pendingSchedules,
            'now' => date('Y-m-d'),
        ]);
    }

    /**
     * ★【求人企業】日程調整①
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function recruitCalendar1(Request $request)
    {
        $id = $request->input('id');
        $mgt = RecruitJobSeekerApplyMgts::findOrFail($id);

        // 関連情報を取得する
        $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
        $recruitCompany = $jobSeeker->recruitCompany; // 求人企業
        $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

        // 候補日時
        $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $id)
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
            ->where('interview_phase', $mgt->last_selection_flow_number)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->get();

        // 他の候補者の選考日程
        $otherSchedules = InterviewSchedule::where('company_id', $offerInfo->company_id)
            ->whereIn('interview_date_type', array(1, 2))   // 1:候補日 2:確定した日(=◯) 3:確定しなかった日(=X) 4: NG日
            ->where('job_seeker_apply_mgt_id', '!=', $id)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->where('interview_candidates_date', '>=', date('Y-m-01', strtotime("-2 month")))
            ->get();

        return view('company.recruit.calendar1')->with([
            'id' => $id,
            'recruitJobSeekerApplyMgt' => $mgt,
            'recruitCompany' => $recruitCompany,
            'jobSeeker' => $jobSeeker,
            'offerInfo' => $offerInfo,
            'interviewSchedules' => $interviewSchedules,
            'otherSchedules' => $otherSchedules,
            'now' => date('Y-m-d'),
        ]);
    }

    /**
     * ★【求人企業】日程調整②
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function recruitCalendar2(Request $request)
    {
        $id = $request->input('id');
        $mgt = RecruitJobSeekerApplyMgts::findOrFail($id);

        // 関連情報を取得する
        $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
        $recruitCompany = $jobSeeker->recruitCompany; // 求人企業
        $offerInfo = $mgt->recruitOfferInfo; // 人材紹介_求人情報

        // 候補日時
        $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $id)
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
            ->where('interview_phase', $mgt->last_selection_flow_number)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->get();

        // 他の候補者の選考日程
        $otherSchedules = InterviewSchedule::where('company_id', $offerInfo->company_id)
            ->whereIn('interview_date_type', array(1, 2))   // 1:候補日 2:確定した日(=◯) 3:確定しなかった日(=X) 4: NG日
            ->where('job_seeker_apply_mgt_id', '!=', $id)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->where('interview_candidates_date', '>=', date('Y-m-01', strtotime("-2 month")))
            ->get();

        return view('company.recruit.calendar2')->with([
            'id' => $id,
            'recruitJobSeekerApplyMgt' => $mgt,
            'recruitCompany' => $recruitCompany,
            'jobSeeker' => $jobSeeker,
            'offerInfo' => $offerInfo,
            'interviewSchedules' => $interviewSchedules,
            'otherSchedules' => $otherSchedules,
            'now' => date('Y-m-d'),
        ]);
    }

    /**
     * ★【求人企業】日程調整①
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function outsourceCalendar1(Request $request)
    {
        $id = $request->input('id');
        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($id);

        // 関連情報を取得する
        $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
        $outsourceCompany = $jobSeeker->outsourceCompany; // 求人企業
        $offerInfo = $mgt->outsourceOfferInfo; // 人材紹介_求人情報

        // 候補日時
        $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $id)
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
            ->where('interview_phase', $mgt->last_selection_flow_number)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->get();

        // 他の候補者の選考日程
        $otherSchedules = InterviewSchedule::where('company_id', $offerInfo->company_id)
            ->whereIn('interview_date_type', array(1, 2))   // 1:候補日 2:確定した日(=◯) 3:確定しなかった日(=X) 4: NG日
            ->where('job_seeker_apply_mgt_id', '!=', $id)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->where('interview_candidates_date', '>=', date('Y-m-01', strtotime("-2 month")))
            ->get();

        return view('company.outsource.calendar1')->with([
            'id' => $id,
            'outsourceJobSeekerApplyMgt' => $mgt,
            'outsourceCompany' => $outsourceCompany,
            'jobSeeker' => $jobSeeker,
            'offerInfo' => $offerInfo,
            'interviewSchedules' => $interviewSchedules,
            'otherSchedules' => $otherSchedules,
            'now' => date('Y-m-d'),
        ]);
    }

    /**
     * ★【求人企業】日程調整②
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function outsourceCalendar2(Request $request)
    {
        $id = $request->input('id');
        $mgt = OutsourceJobSeekerApplyMgts::findOrFail($id);

        // 関連情報を取得する
        $jobSeeker = $mgt->jobSeeker; // 人材紹介_求職者
        $outsourceCompany = $jobSeeker->outsourceCompany; // 求人企業
        $offerInfo = $mgt->outsourceOfferInfo; // 人材紹介_求人情報

        // 候補日時
        $interviewSchedules = InterviewSchedule::where('job_seeker_apply_mgt_id', $id)
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
            ->where('interview_phase', $mgt->last_selection_flow_number)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->get();

        // 他の候補者の選考日程
        $otherSchedules = InterviewSchedule::where('company_id', $offerInfo->company_id)
            ->whereIn('interview_date_type', array(1, 2))   // 1:候補日 2:確定した日(=◯) 3:確定しなかった日(=X) 4: NG日
            ->where('job_seeker_apply_mgt_id', '!=', $id)
            ->whereNull('deleted_at')
            ->whereNull('deleted_by')
            ->where('interview_candidates_date', '>=', date('Y-m-01', strtotime("-2 month")))
            ->get();

        return view('company.outsource.calendar2')->with([
            'id' => $id,
            'outsourceJobSeekerApplyMgt' => $mgt,
            'outsourceCompany' => $outsourceCompany,
            'jobSeeker' => $jobSeeker,
            'offerInfo' => $offerInfo,
            'interviewSchedules' => $interviewSchedules,
            'otherSchedules' => $otherSchedules,
            'now' => date('Y-m-d'),
        ]);
    }
}
