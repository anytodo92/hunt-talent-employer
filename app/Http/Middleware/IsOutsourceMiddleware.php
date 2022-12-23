<?php

namespace App\Http\Middleware;

use App\Models\InterviewSchedule;
use App\Models\QuestionAnswer;
use App\Models\Timeline;
use App\Models\Todo;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class IsOutsourceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->check()) {
            $user = $request->user();
            if($user->is_outsource) {
                $this->doCommonProcess();
                return $next($request);
            }
        }

        \Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('outsource.login');
    }

    /**
     * 共通処理を行う
     */
    private function doCommonProcess()
    {
        $authUser = auth()->user();
        $companyId = $authUser->outsource_user()->first()->outsource_company_id;

        // timeline
        $timelines = Timeline::where('company_id', $companyId)->where('timeline_complete', 1)->orderBy('created_at', 'desc')->get();
        $unreadCount = Timeline::where('company_id', $companyId)->where('timeline_complete', 1)->where('read_flg', 1)->count();
        // todo
        $todos = Todo::where('company_id', $companyId)->where('todo_complete', 1)->orderBy('created_at', 'desc')->get();
        $undoCount = Todo::where('company_id', $companyId)->where('todo_complete', 1)->count();
        // calendar
        $calendarCount = InterviewSchedule::where('company_id', $companyId)->where('interview_date_type', 2)->where('interview_candidates_date', '=', date('Y-m-d'))->count();
        $calendars = InterviewSchedule::where('company_id', $companyId)->where('interview_date_type', 2)->where('interview_candidates_date', '=', date('Y-m-d'))->get();
        // Q&A
        $qaCount = QuestionAnswer::where('company_id', $companyId)->where('status', 1)->count();

        View::share(compact('timelines', 'unreadCount', 'todos', 'undoCount', 'calendarCount','calendars','qaCount'));
    }
}
