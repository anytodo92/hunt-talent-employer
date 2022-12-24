<?php

namespace App\Models;

use App\Constants;
use App\Traits\ModelHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Timeline extends Model
{
    use SoftDeletes, ModelHistoryTrait;

    protected $table = 'timeline';
    protected $appends = [
        'sender_type_name',
        'sender_type2_name',
        'offer_type_name',
        'timeline_complete_name',
        'read_name',
        'ago',
    ];

    // Relationship
//    public function corporations() {
//
//        return $this->belongsTo(Company::class, 'company_id', 'id');
//
//    }

    public function messageSender() {

        if (intval($this->sender_type) === Constants::USER_TYPE_COMPANY) {
            return $this->hasOne(CompanyUser::class, 'user_id', 'message_sender');
        }
        else if (intval($this->sender_type) === Constants::USER_TYPE_RECRUIT) {
            return $this->hasOne(RecruitCompanyUser::class, 'user_id', 'message_sender');
        }
        else if (intval($this->sender_type) === Constants::USER_TYPE_OUTSOURCE) {
            return $this->hasOne(OutsourceCompanyUser::class, 'user_id', 'message_sender');
        }
        else if (intval($this->sender_type) === Constants::USER_TYPE_ADMIN) {
            return $this->hasOne(OutsourceCompanyUser::class, 'user_id', 'message_sender');
        }
        return $this->belongsTo(AdminUser::class, 'message_sender', 'id');
    }

    public function company() {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    public function recruitJobSeekerApplyMgts() {
        return $this->hasOne(RecruitJobSeekerApplyMgts::class, 'id', 'job_seeker_apply_mgt_id')
            ->where('timeline.job_seeker_apply_mgt_type', Constants::BIZ_TYPE_RECRUIT);
    }

    public function outsourceJobSeekerApplyMgts() {
        return $this->hasOne(OutsourceJobSeekerApplyMgts::class, 'id', 'job_seeker_apply_mgt_id')
            ->where('timeline.job_seeker_apply_mgt_type', Constants::BIZ_TYPE_OUTSOURCE);
    }


    // Accessor
    public function getSenderTypeNameAttribute() {
        $id = $this->sender_type;
        $sender_types = config('constants.sender_types');
        return $sender_types[$id] ?? '';
    }
    public function getSenderType2NameAttribute() {
        $id = $this->sender_type;
        $sender_types = config('constants.user_types');
        return $sender_types[$id] ?? '';
    }

    public function getOfferTypeNameAttribute() {
        $id = $this->job_seeker_apply_mgt_type;
        $offerTypes = config('constants.types');
        return $offerTypes[$id] ?? '';
    }

    public function getTimelineCompleteNameAttribute() {
        $id = $this->timeline_complete;
        $timeline_completes = config('constants.completes');
        return $timeline_completes[$id] ?? '';
    }

    public function getReadNameAttribute() {
        $id = $this->read;
        $reads = config('constants.reads');
        return $reads[$id] ?? '';
    }

    public function getAgoAttribute() {
        $created_at = $this->created_at;

        $created = strtotime($created_at);
        $now = strtotime("now");

        $diff = $now - $created;

        $diff_time = intval(($diff % 86400) / 3600);
        $diff_min = intval($diff_time / 60);
        $diff_sec = intval($diff_min / 60);
        $diff_days = $diff / 86400;

        if ($diff_days >= 365) {
            return ceil($diff_days / 365) . '年前';
        } elseif ($diff_days >= 30) {
            return ceil($diff_days / 30) . 'ヶ月前';
        } elseif ($diff_days >= 7) {
            return ceil($diff_days / 7) . '週前';
        } elseif ($diff_days >= 1) {
            return ceil($diff_days) . '日前';
        } elseif ($diff_time > 0) {
            return $diff_time . '時間前';
        } elseif ($diff_min > 0) {
            return $diff_min . '分前';
        } elseif ($diff_sec > 0) {
            return $diff_sec . '秒前';
        } else {
            return '数秒前';
        }

    }

    public function relationCompanyLogoInfo($id) {
        return static::where('timeline.id', $id)
            ->leftJoin('companies', 'companies.id', '=', 'timeline.company_id')
            ->select([
                'timeline.*',
                'companies.id as company_id',
                'companies.logo as company_logo'
            ])->first();
    }

    public function relationRecruitAvatarInfo($id) {
        return static::where('timeline.id', $id)
            ->leftJoin('recruit_job_seeker_apply_mgts', 'recruit_job_seeker_apply_mgts.id', '=', 'timeline.job_seeker_apply_mgt_id')
            ->leftJoin('recruit_job_seekers', 'recruit_job_seekers.id', '=', 'recruit_job_seeker_apply_mgts.recruit_job_seeker_id')
            ->leftJoin('recruit_company_users', 'recruit_company_users.id', '=', 'recruit_job_seekers.recruit_company_user_id')
            ->select([
                'timeline.*',
                'recruit_company_users.id as recruit_user_id',
                'recruit_company_users.logo as recruit_user_logo'
            ])->first();
    }

    public function relationOutsourceAvatarInfo($id) {
        return static::where('timeline.id', $id)
            ->leftJoin('outsource_job_seeker_apply_mgts', 'outsource_job_seeker_apply_mgts.id', '=', 'timeline.job_seeker_apply_mgt_id')
            ->leftJoin('outsource_job_seekers', 'outsource_job_seekers.id', '=', 'outsource_job_seeker_apply_mgts.outsource_job_seeker_id')
            ->leftJoin('outsource_company_users', 'outsource_company_users.id', '=', 'outsource_job_seekers.outsource_company_user_id')
            ->select([
                'timeline.*',
                'outsource_company_users.id as outsource_user_id',
                'outsource_company_users.logo as outsource_user_logo'
            ])->first();
    }

}
