<?php

namespace App\Models;

use App\Constants;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecruitJobSeekerApplyMgts extends Model
{
    protected $table = 'recruit_job_seeker_apply_mgts';

    // Relations ----------------------------
    public function recruitOfferInfo()
    {
        return $this->belongsTo(RecruitOfferInfo::class, 'recruit_offer_info_id', 'id');
    }

    public function jobSeeker()
    {
        return $this->hasOne(RecruitJobSeeker::class, 'id', 'recruit_job_seeker_id');
    }

    public function selectionResults()
    {
        return $this->hasMany(SelectionResult::class, 'job_seeker_apply_mgt_id', 'id')
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT);
    }

    public function calendars()
    {
        return $this->hasMany(InterviewSchedule::class, 'job_seeker_apply_mgt_id', 'id')
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
            ->orderBy('interview_candidates_date', 'ASC')
            ->orderBy('interview_candidates_from', 'ASC')
            ->orderBy('interview_candidates_to', 'ASC');
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class, 'job_seeker_apply_mgt_id', 'id')
            ->leftJoin('recruit_job_seeker_apply_mgts', 'recruit_job_seeker_apply_mgts.id', '=', 'timeline.job_seeker_apply_mgt_id')
            ->leftJoin('recruit_job_seekers', 'recruit_job_seekers.id', '=', 'recruit_job_seeker_apply_mgts.recruit_job_seeker_id')
            ->leftJoin('recruit_company_users', 'recruit_company_users.id', '=', 'recruit_job_seekers.recruit_company_user_id')
            ->leftJoin('recruit_companies', 'recruit_companies.id', '=', 'recruit_job_seekers.recruit_company_id')
            ->leftJoin('companies', 'companies.id', '=', 'timeline.company_id')
            ->where('timeline.job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT)
            ->orderBy('timeline.created_at', 'DESC')
            ->select([
                'timeline.*',
                'companies.id as company_id',
                'companies.logo as company_logo',
                'recruit_company_users.id as recruit_user_id',
                'recruit_company_users.logo as recruit_user_logo',
                'recruit_companies.id as recruit_company_id',
                'recruit_companies.logo as recruit_company_logo',
                'recruit_job_seekers.sex',
                'recruit_job_seeker_apply_mgts.retirement_date'
            ]);
    }

    public function joiningConditionPresents()
    {
        return $this->hasMany(JoiningConditionPresent::class, 'recruit_job_seeker_apply_mgt_id', 'id')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC');
    }

    // Scopes ----------------------------
    public function scopeDatatableForCompany(Builder $builder, $params)
    {
        // ログインした求人企業の求人情報だけを表示する。
        $login_company_id = Auth::user()->company_user->company_id;

        // 【求人企業】人材紹介の選考一覧に表示するデータを取得する。
        $query = $this->query()
            ->join('recruit_offer_infos', $this->table.'.recruit_offer_info_id', '=', 'recruit_offer_infos.id')
            ->join('recruit_job_seekers', $this->table.'.recruit_job_seeker_id', '=', 'recruit_job_seekers.id')
            ->join('recruit_companies', 'recruit_job_seekers.recruit_company_id', '=', 'recruit_companies.id');

        // 日程調整の！要対応の状態を判断するためにJOINする。
        $query = $query->leftJoin('selection_results', function ($join) {
                    $join->on('selection_results.job_seeker_apply_mgt_id', '=', $this->table.'.id')
                        ->where('selection_results.job_seeker_apply_mgt_type', '=', Constants::BIZ_TYPE_RECRUIT)
                        ->whereRaw('selection_results.phase = '.$this->table.'.last_selection_flow_number');
                });

        // 該当求人企業の求人情報だけを取得する。
        $query = $query->where('recruit_offer_infos.company_id', $login_company_id);
        // 未応募のステータスは非表示（求人企業）
        $query = $query->where($this->table.'.selection_status', '!=', 2);

        // 求人情報での絞込み
        if (isset($params['extra']['recruit_offer_info_id']) && !empty($params['extra']['recruit_offer_info_id'])) {
            $query->where('recruit_offer_infos.id', '=', $params['extra']['recruit_offer_info_id']);
        }

        // →　「更新日」での絞込みは、選択した年月に該当する人のみを表示。対象は最後の選考の日（その人の最後の選考日。例えば書類選考で不採用になったらその日）で絞込みをする。年月のみ選択可。
        if (isset($params['extra']['search_date']) && !empty($params['extra']['search_date'])) {
            $dateBegin = $params['extra']['search_date'] . '-01';
            $dateEnd = date('Y-m-t', strtotime($dateBegin)) . ' 23:59:59';
            $query->whereBetween($this->table.'.updated_at', [$dateBegin, $dateEnd]);
        }

        // →　キーワード検索の対象は以下：転職者名、人材紹介会社名、求人タイトル
        if (isset($params['extra']['keyword']) && !empty($params['extra']['keyword'])) {
            $keywords = str_replace('　', ' ', $params['extra']['keyword']);
            $keywords = explode(' ', $keywords);
            foreach ($keywords as $keyword) {
                $query->whereRaw("(CONCAT_WS('', recruit_job_seekers.last_name, recruit_job_seekers.first_name) LIKE '%$keyword%' OR CONCAT_WS('', recruit_job_seekers.last_name_kana, recruit_job_seekers.first_name_kana) LIKE '%$keyword%' OR recruit_companies.name LIKE '%$keyword%' OR recruit_offer_infos.job_title LIKE '%$keyword%')");
            }
        }

        // 全体検索件数
        $totalCount = $query->count();

        // ステータス別の件数
        $countQuery = clone $query;
        $countRecords = $countQuery->select(DB::raw('selection_status, count(*) as status_count'))
            ->groupBy('selection_status')
            ->get();

        $statusCount = array_fill(0, 6, 0);
        foreach($countRecords as $record) {
            $statusCount[$record->selection_status] = $record->status_count;
        }

        // →　「選考中」などをクリックでそのステータスで絞込みして表示
        if (isset($params['extra']['status']) && !empty($params['extra']['status']) & $params['extra']['status'] != 6) {
            $query->where('selection_status', '=', $params['extra']['status']);
        }

        // →　並び替えは、応募の新しい順（デフォルト）、応募の古い順、選考の進んでいる順、選考の進んでいない順
        if (isset($params['extra']['order']) && !empty($params['extra']['order'])) {
            if ($params['extra']['order'] == 1) {
                $query->orderByDesc($this->table.'.application_date', 'DESC');
            }
            if ($params['extra']['order'] == 2) {
                $query->orderBy($this->table.'.application_date', 'ASC');
            }
            if ($params['extra']['order'] == 3) {
                $query->orderBy($this->table.'.last_selection_flow_number', 'DESC')
                    ->orderBy($this->table.'.last_selection_flow_date', 'ASC');
            }
            if ($params['extra']['order'] == 4) {
                $query->orderBy($this->table.'.last_selection_flow_number', 'ASC')
                    ->orderBy($this->table.'.last_selection_flow_date', 'DESC');
            }
        }

        $query->select($this->table.'.*',
            'recruit_job_seekers.id AS job_seeker_id',
            'recruit_job_seekers.last_name',
            'recruit_job_seekers.first_name',
            'recruit_job_seekers.birthday',
            'recruit_job_seekers.sex',
            'recruit_companies.id AS recruit_company_id',
            'recruit_companies.name AS recruit_company_name',
            'recruit_offer_infos.id AS recruit_offer_info_id',
            'recruit_offer_infos.job_title',
            'recruit_offer_infos.occupation_category_1',
            'recruit_offer_infos.occupation_category_2',
            'selection_results.interview_setting_person_type');


        // filtered count
        $recordsFiltered = $query->count();
        $statusCount[6] = $totalCount;

        // offset & limit
        if (!empty($params['start']) && $params['start'] > 0) {
            $query->skip($params['start']);
        }

        if (!empty($params['length']) && $params['length'] > 0) {
            $query->take($params['length']);
        }

        $records = $query->get();

        return [
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'statusCount' => $statusCount,
            'error' => 0,
        ];
    }

    public function scopeDatatableForRecruit(Builder $builder, $params)
    {
        // ログインした人材紹介会社の求人情報だけを表示する。
        $login_company_id = Auth::user()->recruit_user->recruit_company_id;

        // 【求人企業】人材紹介の選考一覧に表示するデータを取得する。
        $query = $this->query()
            ->join('recruit_offer_infos', $this->table.'.recruit_offer_info_id', '=', 'recruit_offer_infos.id')
            ->join('recruit_job_seekers', $this->table.'.recruit_job_seeker_id', '=', 'recruit_job_seekers.id')
            ->join('recruit_company_users', 'recruit_company_users.id', '=', 'recruit_job_seekers.recruit_company_user_id')
            ->join('recruiting_companies', 'recruiting_companies.id', '=', 'recruit_offer_infos.recruiting_company_id');

        // 日程調整の！要対応の状態を判断するためにJOINする。
        $query = $query->leftJoin('selection_results', function ($join) {
                    $join->on('selection_results.job_seeker_apply_mgt_id', '=', $this->table.'.id')
                        ->where('selection_results.job_seeker_apply_mgt_type', '=', Constants::BIZ_TYPE_RECRUIT)
                        ->whereRaw('selection_results.phase = '.$this->table.'.last_selection_flow_number');
                });

        // 該当人材紹介会社の求人情報だけを取得する。
        $query = $query->where('recruit_job_seekers.recruit_company_id', $login_company_id);

        // →　「転職者で絞込み」は、文字を入力したらサジェストで候補の転職者を表示。それを選択して検索できる。
        if (isset($params['extra']['job_seeker']) && !empty($params['extra']['job_seeker'])) {
            $query->whereRaw("concat_ws('', recruit_job_seekers.last_name, recruit_job_seekers.first_name) like '%" . $params['extra']['job_seeker'] . "%'");
        }

        // →　「担当で絞込み」は、エージェント担当者で絞り込める。デフォルトは現在ログインしているエージェントアカウント担当の選考を表示。
        if (isset($params['extra']['recruit_user_id']) && !empty($params['extra']['recruit_user_id'])) {
            $query->where('recruit_company_users.id', '=', $params['extra']['recruit_user_id']);
        }

        // →　キーワード検索の対象は以下：求人企業名、求人タイトル
        if (isset($params['extra']['keyword']) && !empty($params['extra']['keyword'])) {
            $keywords = str_replace('　', ' ', $params['extra']['keyword']);
            $keywords = explode(' ', $keywords);
            foreach ($keywords as $keyword) {
                $query->whereRaw("(recruiting_companies.name LIKE '%$keyword%' OR recruit_offer_infos.job_title LIKE '%$keyword%')");
            }
        }

        // 全体検索件数
        $totalCount = $query->count();

        // ステータス別の件数
        $countQuery = clone $query;
        $countRecords = $countQuery->select(DB::raw('selection_status, count(*) as status_count'))
            ->groupBy('selection_status')
            ->get();

        $statusCount = array_fill(0, 6, 0);
        foreach($countRecords as $record) {
            $statusCount[$record->selection_status] = $record->status_count;
        }

        // →　「選考中」などをクリックでそのステータスで絞込みして表示
        if (isset($params['extra']['status']) && !empty($params['extra']['status']) & $params['extra']['status'] != 6) {
            $query->where('selection_status', '=', $params['extra']['status']);
        }

        // →　並び替えは、応募の新しい順（デフォルト）、応募の古い順、選考の進んでいる順、選考の進んでいない順
        if (isset($params['extra']['order']) && !empty($params['extra']['order'])) {
            if ($params['extra']['order'] == 1) {
                $query->orderByDesc($this->table.'.application_date', 'DESC');
            }
            if ($params['extra']['order'] == 2) {
                $query->orderBy($this->table.'.application_date', 'ASC');
            }
            if ($params['extra']['order'] == 3) {
                $query->orderBy($this->table.'.last_selection_flow_number', 'DESC')
                    ->orderBy($this->table.'.last_selection_flow_date', 'ASC');
            }
            if ($params['extra']['order'] == 4) {
                $query->orderBy($this->table.'.last_selection_flow_number', 'ASC')
                    ->orderBy($this->table.'.last_selection_flow_date', 'DESC');
            }
        }

        $query->select($this->table.'.*',
            'recruit_job_seekers.id AS job_seeker_id',
            'recruit_job_seekers.last_name',
            'recruit_job_seekers.first_name',
            'recruit_job_seekers.birthday',
            'recruit_job_seekers.sex',
            'recruit_company_users.id as recruit_user_id',
            'recruit_company_users.name as recruit_user_name',
            'recruiting_companies.id AS recruiting_company_id',
            'recruiting_companies.name AS recruiting_company_name',
            'recruit_offer_infos.id AS recruit_offer_info_id',
            'recruit_offer_infos.job_title',
            'recruit_offer_infos.occupation_category_1',
            'recruit_offer_infos.occupation_category_2',
            'selection_results.interview_setting_person_type');

        // filtered count
        $recordsFiltered = $query->count();
        $statusCount[6] = $totalCount;

        // offset & limit
        if (!empty($params['start']) && $params['start'] > 0) {
            $query->skip($params['start']);
        }

        if (!empty($params['length']) && $params['length'] > 0) {
            $query->take($params['length']);
        }

        $records = $query->get();

        return [
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'statusCount' => $statusCount,
            'error' => 0,
        ];
    }
}
