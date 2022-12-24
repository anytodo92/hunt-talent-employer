<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RecruitJobSeeker extends Model
{
    protected $table = 'recruit_job_seekers';

    // Relationship

    public function recruitCompany()
    {
        return $this->belongsTo(RecruitCompany::class, 'recruit_company_id', 'id');
    }

    public function getImageAttribute($value) {

        $url = '/storage/app/public/recruit/job_seeker/' . $this->id . '/image/' . $value;
        if(empty($value) || !file_exists($url)) {

            // ここはデフォルトの会社画像をセットしてください
            return '/assets/static/images/no_image.png';

        }

        //return $value;
        return $url;

    }

    // Relationship
    public function recruitCompanyUser()
    {
        return $this->belongsTo(RecruitCompanyUser::class, 'recruit_company_user_id', 'id');
    }

    // Scopes ----------------------------

    public function scopeDatatable(Builder $builder, $params)
    {
        // get records
        $query = RecruitJobSeeker::leftjoin('recruit_company_users', 'recruit_job_seekers.recruit_company_user_id', '=', 'recruit_company_users.id')
            ->leftjoin('recruit_job_seeker_apply_mgts', 'recruit_job_seekers.id', '=', 'recruit_job_seeker_apply_mgts.recruit_job_seeker_id');

        // Total count
        $totalCount = $query->count();

        // Filtering

        if (isset($params['extra']['keyword']) && !empty($params['extra']['keyword'])) {
            $query->where(function ($query) use($params) {
                $query->orWhereRaw("concat_ws('', recruit_job_seekers.last_name, recruit_job_seekers.first_name) like '%" . $params['extra']['keyword'] . "%'");
            });
        }

        // ステータス別の件数を確認する
        $countQuery = clone $query;
        $countRecords = $countQuery->select(DB::raw('selection_status, count(*) as status_count'))
            ->groupBy('selection_status')
            ->get();

        $statusCount = array_fill(0, 6, 0);
        foreach($countRecords as $record) {
            $statusCount[$record->selection_status] = $record->status_count;
        }

        // ソート
        /*if (isset($params['extra']['order']) && !empty($params['extra']['order'])) {
            if ($params['extra']['order'] == 1) {
                $query->orderByDesc('sub.application_date');
            }
            if ($params['extra']['order'] == 2) {
                $query->orderBy('sub.application_date', 'Asc');
            }
            if ($params['extra']['order'] == 3) {
                $query->orderBy('sub.last_selection_flow_number', 'desc')
                    ->orderBy('sub.last_selection_flow_date', 'asc');
            }
            if ($params['extra']['order'] == 4) {
                $query->orderBy('sub.last_selection_flow_number', 'asc')
                    ->orderBy('sub.last_selection_flow_date', 'desc');
            }
        }

        $query->select('recruit_job_seekers.id AS job_seeker_id', 'recruit_job_seekers.last_name',
            'recruit_job_seekers.first_name', 'recruit_job_seekers.birthday', 'recruit_job_seekers.sex',
            'recruit_companies.id AS recruit_company_id',
            'sub.*', 'recruitInfo.id AS recruit_offer_info_id', 'recruitInfo.job_title', 'recruitInfo.occupation_category_1',
            'recruitInfo.occupation_category_2', 'recruit_companies.name');

        if (isset($params['extra']['status']) && !empty($params['extra']['status']) & $params['extra']['status'] != 5) {
            $query->where('selection_status', '=', $params['extra']['status']);
        }
        */

        // filtered count
        $recordsFiltered = $query->count();
        $statusCount[5] = $totalCount;

        // offset & limit
        if (!empty($params['start']) && $params['start'] > 0) {
            $query->skip($params['start']);
        }

        if (!empty($params['length']) && $params['length'] > 0) {
            $query->take($params['length']);
        }

        $records = $query->get(['recruit_job_seekers.*', 'recruit_job_seekers.id as job_seeker_id',
            'recruit_company_users.name',
            'recruit_job_seeker_apply_mgts.selection_status',]);

        return [
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'statusCount' => $statusCount,
            'error' => 0,
        ];
    }
}
