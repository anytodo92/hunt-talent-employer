<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutsourceCompanyUser extends Model
{
    protected $table = 'outsource_company_users';

    // Relationship
    public function company() {

        return $this->hasOne(OutsourceCompany::class, 'id', 'outsource_company_id');

    }

    public function getFullLogoAttribute() {

        if(empty($this->logo)) {
            // ここはデフォルトの会社画像をセットしてください
            return '/assets/static/images/no_image.png';
        }

        //return $value;
        return '/storage/outsource/' . $this->outsource_company_id . '/company_user/' . $this->id . '/logo/' . $this->logo;

    }
}
