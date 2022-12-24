<?php

namespace App\Models;

use App\Constants;
use Illuminate\Database\Eloquent\Model;

class OutsourceOfferInfo extends Model
{
    protected $table = 'outsource_offer_infos';

    protected $appends = [
        'open_status_text',
        'selection_count',
        'full_category',
    ];

    public function outsourceJobSeekerApplyMgts() {
        return $this->hasMany(OutsourceJobSeekerApplyMgts::class, 'outsource_offer_info_id', 'id');
    }

    public function workPlaces() {
        return $this->hasMany(WorkPlace::class, 'offer_info_id', 'id')
            ->where('offer_info_type', Constants::BIZ_TYPE_OUTSOURCE);
    }

    # Relationships
    public function company() {
        return $this->hasOne(Company::class, 'id', 'company_id');
    }

    public function recruitingCompany() {
        return $this->hasOne(RecruitingCompany::class, 'id', 'recruiting_company_id');
    }

    public function offerCompanyUser() {
        return $this->hasOne(CompanyUser::class, 'company_id', 'company_id');
    }

    # Attributes
    public function getOpenStatusTextAttribute() {
        $id = $this->open_status;
        $open_status = config('constants.open_status');
        return $open_status[$id] ?? '';
    }

    public function getSelectionCountAttribute() {

        return $this->hasMany(OutsourceJobSeekerApplyMgts::class, 'outsource_offer_info_id', 'id')->count();

    }

    public function getFullCategoryAttribute() {
        $category_1 = config('constants.category_1');
        $category_2 = config('constants.category_2');

        $occupation_category_1 = $this->occupation_category_1;
        $occupation_category_2 = $this->occupation_category_2;

        $category_text_1 = $category_1[$occupation_category_1] ?? '';
        $category_text_2 = $category_2[$occupation_category_1][$occupation_category_2] ?? '';

        $full_category =  $category_text_1 . '　' . $category_text_2;
        return $full_category;
    }
}
