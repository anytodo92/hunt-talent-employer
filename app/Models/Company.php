<?php

namespace App\Models;

use App\Traits\ModelHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use SoftDeletes, ModelHistoryTrait;

    protected $appends = [
        'prefecture',
        'employee_number',
        'annual_recruit_number',
        'consideration_status',
        'current_status'
    ];

    // Relationship
    public function first_industry() {

        return $this->belongsTo(FirstIndustry::class, 'first_industry_id', 'id');

    }

    public function second_industry() {

        return $this->belongsTo(SecondIndustry::class, 'second_industry_id', 'id');

    }

    public function statuses() {

        return $this->hasMany(CompanyStatus::class, 'company_id', 'id');

    }

    // Accessor
    public function getPrefectureAttribute() {

        $id = $this->prefecture_id;
        $prefectures = config('constants.prefectures');
        return $prefectures[$id] ?? '';

    }

    public function getEmployeeNumberAttribute() {

        $id = $this->employee_number_id;
        $employee_numbers = config('constants.employee_numbers');
        return $employee_numbers[$id] ?? '';

    }

    public function getAnnualRecruitNumberAttribute() {

        $id = $this->annual_recruit_number_id;
        $annual_recruit_numbers = config('constants.annual_recruit_numbers');
        return $annual_recruit_numbers[$id] ?? '';

    }

    public function getConsiderationStatusAttribute() {

        $id = $this->consideration_status_id;
        $consideration_statuses = config('constants.consideration_statuses');
        return $consideration_statuses[$id] ?? '';

    }

    public function getEstablishMonthAttribute($value) {

        return Str::padLeft($value, 2, '0');

    }

    public function getCurrentStatusAttribute() {

        if($this->statuses->isNotEmpty()) {

            return $this->statuses->sortByDesc('id')->first();

        }

        return null;

    }
    /*
    public function getLogoAttribute($value) {

        if(empty($value)) {

            // ここはデフォルトの会社画像をセットしてください
            return '/assets/static/images/no_image.png';

        }

        //return $value;
        return '/storage/company/' . $this->id . '/logo/' . $value;

    }
    */
    public function getFullLogoAttribute() {

        if(empty($this->logo)) {
            // ここはデフォルトの会社画像をセットしてください
            return '/assets/static/images/no_image.png';
        }

        //return $value;
        return '/storage/company/' . $this->id . '/logo/' . $this->logo;

    }
}
