<?php

namespace App\Models;

use App\Constants;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OutsourceJobSeekerApplyMgts extends Model
{
    protected $table = 'outsource_job_seeker_apply_mgts';

    // Relations ----------------------------
    public function outsourceOfferInfo()
    {
        return $this->belongsTo(OutsourceOfferInfo::class, 'outsource_offer_info_id', 'id');
    }

    public function jobSeeker()
    {
        return $this->hasOne(OutsourceJobSeeker::class, 'id', 'outsource_job_seeker_id');
    }

    public function selectionResults()
    {
        return $this->hasMany(SelectionResult::class, 'job_seeker_apply_mgt_id', 'id')
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE);
    }

    public function calendars()
    {
        return $this->hasMany(InterviewSchedule::class, 'job_seeker_apply_mgt_id', 'id')
            ->where('job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
            ->orderBy('interview_candidates_date', 'ASC')
            ->orderBy('interview_candidates_from', 'ASC')
            ->orderBy('interview_candidates_to', 'ASC');
    }

    public function timelines()
    {
        return $this->hasMany(Timeline::class, 'job_seeker_apply_mgt_id', 'id')
            ->leftJoin('outsource_job_seeker_apply_mgts', 'outsource_job_seeker_apply_mgts.id', '=', 'timeline.job_seeker_apply_mgt_id')
            ->leftJoin('outsource_job_seekers', 'outsource_job_seekers.id', '=', 'outsource_job_seeker_apply_mgts.outsource_job_seeker_id')
            ->leftJoin('outsource_company_users', 'outsource_company_users.id', '=', 'outsource_job_seekers.outsource_company_user_id')
            ->leftJoin('outsource_companies', 'outsource_companies.id', '=', 'outsource_job_seekers.outsource_company_id')
            ->leftJoin('companies', 'companies.id', '=', 'timeline.company_id')
            ->where('timeline.job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE)
            ->orderBy('timeline.created_at', 'DESC')
            ->select([
                'timeline.*',
                'companies.id as company_id',
                'companies.logo as company_logo',
                'outsource_company_users.id as outsource_user_id',
                'outsource_company_users.logo as outsource_user_logo',
                'outsource_companies.id as outsource_company_id',
                'outsource_companies.logo as outsource_company_logo',
                'outsource_job_seekers.sex',
                'outsource_job_seeker_apply_mgts.joining_end_date'
            ]);
    }

    public function contractTerms()
    {
        return $this->hasMany(ContractTerms::class, 'outsource_job_seeker_apply_mgt_id', 'id')
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC');
    }

    // Scopes ----------------------------
    public function scopeDatatableForCompany(Builder $builder, $params)
    {
        // ログインした求人企業の求人情報だけを表示する。
        $login_company_id = Auth::user()->company_user->company_id;

        // 【求人企業】業務委託の選考一覧に表示するデータを取得する。
        $query = $this->query()
            ->join('outsource_offer_infos', $this->table.'.outsource_offer_info_id', '=', 'outsource_offer_infos.id')
            ->join('outsource_job_seekers', $this->table.'.outsource_job_seeker_id', '=', 'outsource_job_seekers.id')
            ->join('outsource_companies', 'outsource_job_seekers.outsource_company_id', '=', 'outsource_companies.id');

        // 日程調整の！要対応の状態を判断するためにJOINする。
        $query = $query->leftJoin('selection_results', function ($join) {
                    $join->on('selection_results.job_seeker_apply_mgt_id', '=', $this->table.'.id')
                        ->where('selection_results.job_seeker_apply_mgt_type', '=', Constants::BIZ_TYPE_OUTSOURCE)
                        ->whereRaw('selection_results.phase = '.$this->table.'.last_selection_flow_number');
                });

        // 該当求人企業の求人情報だけを取得する。
        $query = $query->where('outsource_offer_infos.company_id', $login_company_id);
        // 未エントリーのステータスは非表示（求人企業）
        $query = $query->where($this->table.'.selection_status', '!=', 2);

        // 求人情報での絞込み
        if (isset($params['extra']['outsource_offer_info_id']) && !empty($params['extra']['outsource_offer_info_id'])) {
            $query->where('outsource_offer_infos.id', '=', $params['extra']['outsource_offer_info_id']);
        }

        // →　「更新日」での絞込みは、選択した年月に該当する人のみを表示。対象は最後の選考の日（その人の最後の選考日。例えば書類選考で見送りになったらその日）で絞込みをする。年月のみ選択可。
        if (isset($params['extra']['search_date']) && !empty($params['extra']['search_date'])) {
            $dateBegin = $params['extra']['search_date'] . '-01';
            $dateEnd = date('Y-m-t', strtotime($dateBegin)) . ' 23:59:59';
            $query->whereBetween($this->table.'.updated_at', [$dateBegin, $dateEnd]);
        }

        // →　キーワード検索の対象は以下：参画者名、業務委託/SES会社名、案件タイトル
        if (isset($params['extra']['keyword']) && !empty($params['extra']['keyword'])) {
            $keywords = str_replace('　', ' ', $params['extra']['keyword']);
            $keywords = explode(' ', $keywords);
            foreach ($keywords as $keyword) {
                $query->whereRaw("(outsource_job_seekers.initial LIKE '%$keyword%' OR outsource_companies.name LIKE '%$keyword%' OR outsource_offer_infos.job_title LIKE '%$keyword%')");
            }
        }

        // 全体検索件数
        $totalCount = $query->count();

        // ステータス別の件数
        $countQuery = clone $query;
        $countRecords = $countQuery->select(DB::raw('selection_status, count(*) as status_count'))
            ->groupBy('selection_status')
            ->get();

        $statusCount = array_fill(0, 8, 0);
        foreach($countRecords as $record) {
            $statusCount[$record->selection_status] = $record->status_count;
        }

        // →　「選考中」などをクリックでそのステータスで絞込みして表示
        if (isset($params['extra']['status']) && !empty($params['extra']['status']) & $params['extra']['status'] != 8) {
            $query->where('selection_status', '=', $params['extra']['status']);
        }

        // →　並び替えは、応募の新しい順（デフォルト）、応募の古い順、選考の進んでいる順、選考の進んでいない順
        if (isset($params['extra']['order']) && !empty($params['extra']['order'])) {
            if ($params['extra']['order'] == 1) {
                $query->orderByDesc($this->table.'.proposal_date', 'DESC');
            }
            if ($params['extra']['order'] == 2) {
                $query->orderBy($this->table.'.proposal_date', 'ASC');
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
            'outsource_job_seekers.id AS job_seeker_id',
            'outsource_job_seekers.initial',
            'outsource_job_seekers.last_name',
            'outsource_job_seekers.first_name',
            'outsource_job_seekers.birthday',
            'outsource_job_seekers.sex',
            'outsource_companies.id AS outsource_company_id',
            'outsource_companies.name AS outsource_company_name',
            'outsource_offer_infos.id AS outsource_offer_info_id',
            'outsource_offer_infos.job_title',
            'outsource_offer_infos.occupation_category_1',
            'outsource_offer_infos.occupation_category_2',
            'selection_results.interview_setting_person_type');


        // filtered count
        $recordsFiltered = $query->count();
        $statusCount[8] = $totalCount;

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

    public function scopeDatatableForOutsource(Builder $builder, $params)
    {
        // ログインした業務委託会社の求人情報だけを表示する。
        $login_company_id = Auth::user()->outsource_user->outsource_company_id;

        // 【求人企業】業務委託の選考一覧に表示するデータを取得する。
        $query = $this->query()
            ->join('outsource_offer_infos', $this->table.'.outsource_offer_info_id', '=', 'outsource_offer_infos.id')
            ->join('outsource_job_seekers', $this->table.'.outsource_job_seeker_id', '=', 'outsource_job_seekers.id')
            ->join('outsource_company_users', 'outsource_company_users.id', '=', 'outsource_job_seekers.outsource_company_user_id')
            ->join('recruiting_companies', 'recruiting_companies.id', '=', 'outsource_offer_infos.recruiting_company_id');

        // 日程調整の！要対応の状態を判断するためにJOINする。
        $query = $query->leftJoin('selection_results', function ($join) {
                    $join->on('selection_results.job_seeker_apply_mgt_id', '=', $this->table.'.id')
                        ->where('selection_results.job_seeker_apply_mgt_type', '=', Constants::BIZ_TYPE_OUTSOURCE)
                        ->whereRaw('selection_results.phase = '.$this->table.'.last_selection_flow_number');
                });

        // 該当業務委託会社の求人情報だけを取得する。
        $query = $query->where('outsource_job_seekers.outsource_company_id', $login_company_id);

        // →　「転職者で絞込み」は、文字を入力したらサジェストで候補の転職者を表示。それを選択して検索できる。
        if (isset($params['extra']['job_seeker']) && !empty($params['extra']['job_seeker'])) {
            $query->whereRaw("outsource_job_seekers.initial like '%" . $params['extra']['job_seeker'] . "%'");
        }

        // →　「担当で絞込み」は、エージェント担当者で絞り込める。デフォルトは現在ログインしているエージェントアカウント担当の選考を表示。
        if (isset($params['extra']['outsource_user_id']) && !empty($params['extra']['outsource_user_id'])) {
            $query->where('outsource_company_users.id', '=', $params['extra']['outsource_user_id']);
        }

        // →　キーワード検索の対象は以下：求人企業名、求人タイトル
        if (isset($params['extra']['keyword']) && !empty($params['extra']['keyword'])) {
            $keywords = str_replace('　', ' ', $params['extra']['keyword']);
            $keywords = explode(' ', $keywords);
            foreach ($keywords as $keyword) {
                $query->whereRaw("(recruiting_companies.name LIKE '%$keyword%' OR outsource_offer_infos.job_title LIKE '%$keyword%')");
            }
        }

        // 全体検索件数
        $totalCount = $query->count();

        // ステータス別の件数
        $countQuery = clone $query;
        $countRecords = $countQuery->select(DB::raw('selection_status, count(*) as status_count'))
            ->groupBy('selection_status')
            ->get();

        $statusCount = array_fill(0, 8, 0);
        foreach($countRecords as $record) {
            $statusCount[$record->selection_status] = $record->status_count;
        }

        // →　「選考中」などをクリックでそのステータスで絞込みして表示
        if (isset($params['extra']['status']) && !empty($params['extra']['status']) & $params['extra']['status'] != 8) {
            $query->where('selection_status', '=', $params['extra']['status']);
        }

        // →　並び替えは、応募の新しい順（デフォルト）、応募の古い順、選考の進んでいる順、選考の進んでいない順
        if (isset($params['extra']['order']) && !empty($params['extra']['order'])) {
            if ($params['extra']['order'] == 1) {
                $query->orderByDesc($this->table.'.proposal_date', 'DESC');
            }
            if ($params['extra']['order'] == 2) {
                $query->orderBy($this->table.'.proposal_date', 'ASC');
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
            'outsource_job_seekers.id AS job_seeker_id',
            'outsource_job_seekers.initial',
            'outsource_job_seekers.last_name',
            'outsource_job_seekers.first_name',
            'outsource_job_seekers.birthday',
            'outsource_job_seekers.sex',
            'outsource_company_users.id as outsource_user_id',
            'outsource_company_users.name as outsource_user_name',
            'recruiting_companies.id AS recruiting_company_id',
            'recruiting_companies.name AS recruiting_company_name',
            'outsource_offer_infos.id AS outsource_offer_info_id',
            'outsource_offer_infos.job_title',
            'outsource_offer_infos.occupation_category_1',
            'outsource_offer_infos.occupation_category_2',
            'selection_results.interview_setting_person_type');

        // filtered count
        $recordsFiltered = $query->count();
        $statusCount[8] = $totalCount;

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
