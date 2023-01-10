<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Company\RegisterController as CompanyRegisterController;
use App\Http\Controllers\Company\HomeController as CompanyHomeController;
use App\Http\Controllers\Company\RecruitController as CompanyRecruitController;
use App\Http\Controllers\Company\CalendarController as CompanyCalendarController;
use App\Http\Controllers\Company\OutsourceController as CompanyOutsourceController;
use App\Http\Controllers\Recruit\LoginController as RecruitLoginController;
use App\Http\Controllers\Recruit\HomeController as RecruitHomeController;
use App\Http\Controllers\Recruit\ApplyController as RecruitApplyController;
use App\Http\Controllers\Recruit\CalendarController as RecruitCalendarController;
use App\Http\Controllers\Recruit\JobSeekerController as RecruitJobSeekerController;
use App\Http\Controllers\Outsource\LoginController as OutsourceLoginController;
use App\Http\Controllers\Outsource\HomeController as OutsourceHomeController;
use App\Http\Controllers\Outsource\ApplyController as OutsourceApplyController;
use App\Http\Controllers\Outsource\CalendarController as OutsourceCalendarController;
use App\Http\Controllers\Outsource\JobSeekerController as OutsourceJobSeekerController;
use App\Http\Controllers\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ログイン機能
Auth::routes(['register' => false]);
Route::get('logout', [LoginController::class, 'logout']);

// トップ
Route::get('/', [CompanyRegisterController::class, 'create'])->name('company.register.create');

// 求人企業
Route::prefix('company')->group(function(){

    // ユーザー登録
    Route::post('register', [CompanyRegisterController::class, 'store'])->name('company.register.store');
    Route::get('register/complete/{type?}', [CompanyRegisterController::class, 'complete'])->name('company.register.complete');

    Route::middleware(['auth', 'is_company'])->group(function(){ // 要ログイン

        Route::get('/', [CompanyHomeController::class, 'index'])->name('company.home');

        Route::get('/chat_detail/{id}', [CompanyHomeController::class, 'chat_detail'])->name('company.chat_detail');

        Route::get('/todo_detail/{id}', [CompanyHomeController::class, 'todo_detail'])->name('company.todo_detail');

        Route::get('/calendar_detail/{id}', [CompanyHomeController::class, 'calendar_detail'])->name('company.calendar_detail');

        Route::get('/job_list', [CompanyHomeController::class, 'job_list'])->name('company.job_list');

        Route::get('/job_list_search/{type}', [CompanyHomeController::class, 'job_list_search'])->name('company.job_list_search');
        Route::get('/job_list_count/{type}', [CompanyHomeController::class, 'job_list_count'])->name('company.job_list_count');

        Route::get('/job_public/{type}/{id}', [CompanyHomeController::class, 'job_public'])->name('company.job_public');
        Route::get('/job_stop/{type}/{id}', [CompanyHomeController::class, 'job_stop'])->name('company.job_stop');



        Route::get('/job_add/{type}', [CompanyHomeController::class, 'job_add'])->name('company.job_add');
        Route::get('/job_edit/{type}/{id}', [CompanyHomeController::class, 'job_edit'])->name('company.job_edit');
        Route::get('/job_show/{type}/{id}', [CompanyHomeController::class, 'job_show'])->name('company.job_show');

        # 人材紹介／選考一覧
        Route::get('/recruit', [CompanyRecruitController::class, 'index'])->name('company.recruit.index');
        Route::post('/recruit/datatable', [CompanyRecruitController::class, 'datatable'])->name('company.recruit.datatable');
        Route::get('/recruit/{id}', [CompanyRecruitController::class, 'detail'])->name('company.recruit.detail')->where('id', '[0-9]+');

        Route::get('/recruit/calendar1',[CompanyCalendarController::class, 'recruitCalendar1'])->name('company.recruit.calendar1');
        Route::get('/recruit/calendar2',[CompanyCalendarController::class, 'recruitCalendar2'])->name('company.recruit.calendar2');

        Route::post('/recruit/sendNotAdoptedReason',[CompanyRecruitController::class, 'sendNotAdoptedReason'])->name('company.recruit.send_not_adopted_reason');
        Route::post('/recruit/sendPassSelection',[CompanyRecruitController::class, 'sendPassSelection'])->name('company.recruit.send_pass_selection');
        Route::post('/recruit/sendHire',[CompanyRecruitController::class, 'sendHire'])->name('company.recruit.send_hire');
        Route::post('/recruit/sendInterviewDetail',[CompanyRecruitController::class, 'sendInterviewDetail'])->name('company.recruit.send_interview_detail');
        Route::post('/recruit/sendInterviewDates',[CompanyRecruitController::class, 'sendInterviewDates'])->name('company.recruit.send_interview_date');
        Route::post('/recruit/sendConfirmInterviewDates',[CompanyRecruitController::class, 'sendConfirmInterviewDates'])->name('company.recruit.send_confirm_interview_date');
        Route::post('/recruit/sendClearInterviewDates',[CompanyRecruitController::class, 'sendClearInterviewDates'])->name('company.recruit.send_clear_interview_date');
        Route::post('/recruit/sendJoiningCondition',[CompanyRecruitController::class, 'sendJoiningCondition'])->name('company.recruit.send_joining_condition');
        Route::post('/recruit/sendAllowJoining',[CompanyRecruitController::class, 'sendAllowJoining'])->name('company.recruit.send_allow_joining');
        Route::post('/recruit/sendChangePresentDate',[CompanyRecruitController::class, 'sendChangePresentDate'])->name('company.recruit.send_change_presentdate');
        Route::post('/recruit/sendPresented',[CompanyRecruitController::class, 'sendPresented'])->name('company.recruit.send_presented');
        Route::post('/recruit/sendRetirementDate',[CompanyRecruitController::class, 'sendRetirementDate'])->name('company.recruit.send_retirement_date');

        Route::post('/recruit/saveTimelineRecord',[CompanyRecruitController::class, 'saveTimelineRecord'])->name('company.recruit.saveTimelineRecord');
        Route::post('/recruit/getTimelineRecords',[CompanyRecruitController::class, 'getTimelineRecords'])->name('company.recruit.getTimelineRecords');

        # 業務委託／選考一覧
        Route::get('/outsource', [CompanyOutsourceController::class, 'index'])->name('company.outsource.index');
        Route::post('/outsource/datatable', [CompanyOutsourceController::class, 'datatable'])->name('company.outsource.datatable');
        Route::get('/outsource/{id}', [CompanyOutsourceController::class, 'detail'])->name('company.outsource.detail')->where('id', '[0-9]+');

        Route::get('/outsource/calendar1',[CompanyCalendarController::class, 'outsourceCalendar1'])->name('company.outsource.calendar1');
        Route::get('/outsource/calendar2',[CompanyCalendarController::class, 'outsourceCalendar2'])->name('company.outsource.calendar2');

        Route::post('/outsource/sendNotAdoptedReason',[CompanyOutsourceController::class, 'sendNotAdoptedReason'])->name('company.outsource.send_not_adopted_reason');
        Route::post('/outsource/sendPassSelection',[CompanyOutsourceController::class, 'sendPassSelection'])->name('company.outsource.send_pass_selection');
        Route::post('/outsource/sendHire',[CompanyOutsourceController::class, 'sendHire'])->name('company.outsource.send_hire');
        Route::post('/outsource/sendInterviewDetail',[CompanyOutsourceController::class, 'sendInterviewDetail'])->name('company.outsource.send_interview_detail');
        Route::post('/outsource/sendInterviewDates',[CompanyOutsourceController::class, 'sendInterviewDates'])->name('company.outsource.send_interview_date');
        Route::post('/outsource/sendConfirmInterviewDates',[CompanyOutsourceController::class, 'sendConfirmInterviewDates'])->name('company.outsource.send_confirm_interview_date');
        Route::post('/outsource/sendClearInterviewDates',[CompanyOutsourceController::class, 'sendClearInterviewDates'])->name('company.outsource.send_clear_interview_date');
        Route::post('/outsource/sendJoiningCondition',[CompanyOutsourceController::class, 'sendJoiningCondition'])->name('company.outsource.send_joining_condition');
        Route::post('/outsource/sendChangeStartDate',[CompanyOutsourceController::class, 'sendChangeStartDate'])->name('company.outsource.send_change_startdate');
        Route::post('/outsource/sendFinishContract',[CompanyOutsourceController::class, 'sendFinishContract'])->name('company.outsource.send_finish_contract');
        Route::post('/outsource/sendAgreeFinish',[CompanyOutsourceController::class, 'sendAgreeFinish'])->name('company.outsource.send_agree_finish');

        Route::post('/outsource/saveTimelineRecord',[CompanyOutsourceController::class, 'saveTimelineRecord'])->name('company.outsource.saveTimelineRecord');
        Route::post('/outsource/getTimelineRecords',[CompanyOutsourceController::class, 'getTimelineRecords'])->name('company.outsource.getTimelineRecords');

        Route::get('/calendar',[CompanyCalendarController::class, 'index'])->name('company.calendar');

        Route::get('/job_qa/{type}/{id}', [CompanyHomeController::class, 'job_qa'])->name('company.job_qa');

        Route::get('/qa_list', [CompanyHomeController::class, 'qa_list'])->name('company.qa_list');
        Route::post('/qa_answer', [CompanyHomeController::class, 'qa_answer'])->name('company.qa_answer');
        Route::get('/qa_reject/{id}', [CompanyHomeController::class, 'qa_reject'])->name('company.qa_reject');

        Route::get('/qa_list_search', [CompanyHomeController::class, 'qa_list_search'])->name('company.qa_list_search');
        Route::get('/qa_list_count', [CompanyHomeController::class, 'qa_list_count'])->name('company.qa_list_count');
    });

});

// 人材紹介
Route::prefix('agent')->group(function(){

    Route::get('login', [RecruitLoginController::class, 'showLoginForm'])->name('recruit.login');

    Route::middleware(['auth', 'is_recruit'])->group(function(){ // 要ログイン

        //Route::get('/', function(){ return 'Logged in successfully as recruit.'; })->name('recruit.home');
        Route::get('/', [RecruitHomeController::class, 'index'])->name('recruit.home');

        Route::get('/jobseeker', [RecruitJobSeekerController::class, 'index'])->name('recruit.jobseeker');
        Route::post('/jobseeker/datatable', [RecruitJobSeekerController::class, 'datatable'])->name('recruit.jobseeker.datatable');
        Route::get('/jobseeker/{id}', [RecruitJobSeekerController::class, 'detail'])->name('recruit.jobseeker.detail');
        Route::post('/jobseeker/saveSchedule', [RecruitJobSeekerController::class, 'saveSchedule'])->name('recruit.jobseeker.saveSchedule');

        Route::get('/job_list', [RecruitHomeController::class, 'job_list'])->name('recruit.job_list');

        Route::get('/job_list_search/{type}', [RecruitHomeController::class, 'job_list_search'])->name('recruit.job_list_search');
        Route::get('/job_list_count/{type}', [RecruitHomeController::class, 'job_list_count'])->name('recruit.job_list_count');

        Route::get('/job_public/{type}/{id}', [RecruitHomeController::class, 'job_public'])->name('recruit.job_public');
        Route::get('/job_stop/{type}/{id}', [RecruitHomeController::class, 'job_stop'])->name('recruit.job_stop');

        Route::get('/job_add/{type}', [RecruitHomeController::class, 'job_add'])->name('recruit.job_add');
        Route::get('/job_edit/{type}/{id}', [RecruitHomeController::class, 'job_edit'])->name('recruit.job_edit');
        Route::get('/job_show/{type}/{id}', [RecruitHomeController::class, 'job_show'])->name('recruit.job_show');

        # 人材紹介／選考一覧
        Route::get('/apply', [RecruitApplyController::class, 'index'])->name('recruit.apply.index');
        Route::post('/apply/datatable', [RecruitApplyController::class, 'datatable'])->name('recruit.apply.datatable');
        Route::get('/apply/{id}', [RecruitApplyController::class, 'detail'])->name('recruit.apply.detail')->where('id', '[0-9]+');

        Route::get('/calendar1',[RecruitCalendarController::class, 'calendar1'])->name('recruit.calendar1');
        Route::get('/calendar2',[RecruitCalendarController::class, 'calendar2'])->name('recruit.calendar2');
        Route::get('/calendar',[RecruitCalendarController::class, 'index'])->name('recruit.calendar');

        Route::post('/sendRefusalReason',[RecruitApplyController::class, 'sendRefusalReason'])->name('recruit.apply.send_refusal_reason');
        Route::post('/sendInterviewDates',[RecruitApplyController::class, 'sendInterviewDates'])->name('recruit.apply.send_interview_date');
        Route::post('/sendConfirmInterviewDates',[RecruitApplyController::class, 'sendConfirmInterviewDates'])->name('recruit.apply.send_confirm_interview_date');
        Route::post('/sendClearInterviewDates',[RecruitApplyController::class, 'sendClearInterviewDates'])->name('recruit.apply.send_clear_interview_date');
        Route::post('/sendFixedInterviewDate',[RecruitApplyController::class, 'sendFixedInterviewDate'])->name('recruit.apply.send_fixed_interview_date');
        Route::post('/sendJoiningCondition',[RecruitApplyController::class, 'sendJoiningCondition'])->name('recruit.apply.send_joining_condition');
        Route::post('/sendAllowJoining',[RecruitApplyController::class, 'sendAllowJoining'])->name('recruit.apply.send_allow_joining');
        Route::post('/sendChangePresentDate',[RecruitApplyController::class, 'sendChangePresentDate'])->name('recruit.apply.send_change_presentdate');
        Route::post('/sendAgreeRefund',[RecruitApplyController::class, 'sendAgreeRefund'])->name('company.recruit.send_agree_refund');

        Route::post('/saveTimelineRecord',[RecruitApplyController::class, 'saveTimelineRecord'])->name('recruit.apply.saveTimelineRecord');
        Route::post('/getTimelineRecords',[RecruitApplyController::class, 'getTimelineRecords'])->name('recruit.apply.getTimelineRecords');

        Route::get('/job_qa/{type}/{id}', [RecruitHomeController::class, 'job_qa'])->name('recruit.job_qa');

        Route::get('/qa_list', [RecruitHomeController::class, 'qa_list'])->name('recruit.qa_list');
        Route::post('/qa_answer', [RecruitHomeController::class, 'qa_answer'])->name('recruit.qa_answer');
        Route::get('/qa_reject/{id}', [RecruitHomeController::class, 'qa_reject'])->name('recruit.qa_reject');

        Route::get('/qa_list_search', [RecruitHomeController::class, 'qa_list_search'])->name('recruit.qa_list_search');
        Route::get('/qa_list_count', [RecruitHomeController::class, 'qa_list_count'])->name('recruit.qa_list_count');

    });

});

// 業務委託
Route::prefix('ses')->group(function(){

    Route::get('login', [OutsourceLoginController::class, 'showLoginForm'])->name('outsource.login');

    Route::middleware(['auth', 'is_outsource'])->group(function(){ // 要ログイン

        //Route::get('/', function(){ return 'Logged in successfully as outsource.'; })->name('outsource.home');
        Route::get('/', [OutsourceHomeController::class, 'index'])->name('outsource.home');

        Route::get('/jobseeker', [OutsourceJobSeekerController::class, 'index'])->name('outsource.jobseeker');
        Route::post('/jobseeker/datatable', [OutsourceJobSeekerController::class, 'datatable'])->name('outsource.jobseeker.datatable');

        Route::get('/job_list', [OutsourceHomeController::class, 'job_list'])->name('outsource.job_list');

        Route::get('/job_list_search/{type}', [OutsourceHomeController::class, 'job_list_search'])->name('outsource.job_list_search');
        Route::get('/job_list_count/{type}', [OutsourceHomeController::class, 'job_list_count'])->name('outsource.job_list_count');

        Route::get('/job_public/{type}/{id}', [OutsourceHomeController::class, 'job_public'])->name('outsource.job_public');
        Route::get('/job_stop/{type}/{id}', [OutsourceHomeController::class, 'job_stop'])->name('outsource.job_stop');

        Route::get('/job_add/{type}', [OutsourceHomeController::class, 'job_add'])->name('outsource.job_add');
        Route::get('/job_edit/{type}/{id}', [OutsourceHomeController::class, 'job_edit'])->name('outsource.job_edit');
        Route::get('/job_show/{type}/{id}', [OutsourceHomeController::class, 'job_show'])->name('outsource.job_show');

        # 業務委託／選考一覧
        Route::get('/apply', [OutsourceApplyController::class, 'index'])->name('outsource.apply.index');
        Route::post('/apply/datatable', [OutsourceApplyController::class, 'datatable'])->name('outsource.apply.datatable');
        Route::get('/apply/{id}', [OutsourceApplyController::class, 'detail'])->name('outsource.apply.detail')->where('id', '[0-9]+');

        Route::get('/calendar1',[OutsourceCalendarController::class, 'calendar1'])->name('outsource.calendar1');
        Route::get('/calendar2',[OutsourceCalendarController::class, 'calendar2'])->name('outsource.calendar2');
        Route::get('/calendar',[OutsourceCalendarController::class, 'index'])->name('outsource.calendar');

        Route::post('/sendRefusalReason',[OutsourceApplyController::class, 'sendRefusalReason'])->name('outsource.apply.send_refusal_reason');
        Route::post('/sendInterviewDates',[OutsourceApplyController::class, 'sendInterviewDates'])->name('outsource.apply.send_interview_date');
        Route::post('/sendConfirmInterviewDates',[OutsourceApplyController::class, 'sendConfirmInterviewDates'])->name('outsource.apply.send_confirm_interview_date');
        Route::post('/sendClearInterviewDates',[OutsourceApplyController::class, 'sendClearInterviewDates'])->name('outsource.apply.send_clear_interview_date');
        Route::post('/sendFixedInterviewDate',[OutsourceApplyController::class, 'sendFixedInterviewDate'])->name('outsource.apply.send_fixed_interview_date');
        Route::post('/sendAllowJoining',[OutsourceApplyController::class, 'sendAllowJoining'])->name('outsource.apply.send_allow_joining');
        Route::post('/sendChangeStartDate',[OutsourceApplyController::class, 'sendChangeStartDate'])->name('outsource.apply.send_change_startdate');
        Route::post('/sendFinishContract',[OutsourceApplyController::class, 'sendFinishContract'])->name('outsource.apply.send_finish_contract');
        Route::post('/sendAgreeFinish',[OutsourceApplyController::class, 'sendAgreeFinish'])->name('outsource.recruit.send_agree_finish');

        Route::post('/saveTimelineRecord',[OutsourceApplyController::class, 'saveTimelineRecord'])->name('outsource.apply.saveTimelineRecord');
        Route::post('/getTimelineRecords',[OutsourceApplyController::class, 'getTimelineRecords'])->name('outsource.apply.getTimelineRecords');

        Route::get('/job_qa/{type}/{id}', [OutsourceHomeController::class, 'job_qa'])->name('outsource.job_qa');

        Route::get('/qa_list', [OutsourceHomeController::class, 'qa_list'])->name('outsource.qa_list');
        Route::post('/qa_answer', [OutsourceHomeController::class, 'qa_answer'])->name('outsource.qa_answer');
        Route::get('/qa_reject/{id}', [OutsourceHomeController::class, 'qa_reject'])->name('outsource.qa_reject');

        Route::get('/qa_list_search', [OutsourceHomeController::class, 'qa_list_search'])->name('outsource.qa_list_search');
        Route::get('/qa_list_count', [OutsourceHomeController::class, 'qa_list_count'])->name('outsource.qa_list_count');

    });

});

// 管理者
Route::prefix('admin')->group(function(){

    Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');

    Route::middleware(['auth', 'is_admin'])->group(function(){ // 要ログイン

        Route::get('/', function(){ return 'Logged in successfully as admin.'; })->name('admin.home');

    });

});

// for TEST
require_once 'test_web.php';
