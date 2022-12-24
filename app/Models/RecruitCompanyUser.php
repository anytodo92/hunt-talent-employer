<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitCompanyUser extends Model
{
    protected $table = 'recruit_company_users';

    // Relationship
    public function company() {

        return $this->hasOne(RecruitCompany::class, 'id', 'recruit_company_id');

    }

    public function getFullLogoAttribute() {

        if(empty($this->logo)) {
            // ここはデフォルトの会社画像をセットしてください
            return '/assets/static/images/no_image.png';
        }

        //return $value;
        return '/storage/recruit/' . $this->recruit_company_id . '/company_user/' . $this->id . '/logo/' . $this->logo;

    }
}
