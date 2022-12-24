<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OutsourceJobSeeker extends Model
{
    protected $table = 'outsource_job_seekers';
    // Relationship

    public function outsourceCompany()
    {
        return $this->belongsTo(OutsourceCompany::class, 'outsource_company_id', 'id');
    }

    public function getImageAttribute($value) {

        $url = '/storage/app/public/outsource/job_seeker/' . $this->id . '/image/' . $value;
        if(empty($value) || !file_exists($url)) {

            // ここはデフォルトの会社画像をセットしてください
            return '/assets/static/images/no_image.png';

        }

        //return $value;
        return $url;

    }

    // Relationship
    public function outsourceCompanyUser()
    {
        return $this->belongsTo(OutsourceCompanyUser::class, 'outsource_company_user_id', 'id');
    }

    // Scopes ----------------------------

    public function scopeDatatable(Builder $builder, $params)
    {
        // get records
        $subQuery = OutsourceJobSeekerApplyMgts::select('outsource_job_seeker_apply_mgts.outsource_job_seeker_id', DB::raw('COUNT(outsource_job_seeker_apply_mgts.id) as cnt'))
            ->groupBy('outsource_job_seeker_apply_mgts.outsource_job_seeker_id');
        $query = OutsourceJobSeeker::leftJoin('outsource_company_users', 'outsource_job_seekers.outsource_company_user_id', '=', 'outsource_company_users.id')
            ->leftJoin('outsource_job_seeker_apply_mgts', 'outsource_job_seekers.id', '=', 'outsource_job_seeker_apply_mgts.outsource_job_seeker_id')
            ->joinSub($subQuery, 'sub', 'sub.outsource_job_seeker_id', 'outsource_job_seekers.id');

        // Total count
        $totalCount = $query->count();

        // Filtering

        if (isset($params['extra']['keyword']) && !empty($params['extra']['keyword'])) {
            $query->where(function ($query) use($params) {
                $query->orWhereRaw("concat_ws('', outsource_job_seekers.last_name, outsource_job_seekers.first_name) like '%" . $params['extra']['keyword'] . "%'");
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

        $query->select('outsource_job_seekers.id AS job_seeker_id', 'outsource_job_seekers.last_name',
            'outsource_job_seekers.first_name', 'outsource_job_seekers.birthday', 'outsource_job_seekers.sex',
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

        $records = $query->get(['outsource_job_seekers.last_name', 'outsource_job_seekers.first_name', 'outsource_job_seekers.birthday',
            'outsource_company_users.name',
            'outsource_job_seeker_apply_mgts.selection_status', 'outsource_job_seeker_apply_mgts.application', 'outsource_job_seeker_apply_mgts.document_confirmation', 'outsource_job_seeker_apply_mgts.last_selection_flow_number',
            'cnt']);

        return [
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records,
            'statusCount' => $statusCount,
            'error' => 0,
        ];
    }

}
