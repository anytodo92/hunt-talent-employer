<?php

namespace App\Models;

use App\Traits\ModelHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyStatus extends Model
{
    use SoftDeletes, ModelHistoryTrait;

    protected $appends = [
        'label'
    ];

    // Accessor
    public function getLabelAttribute() {

        $id = $this->company_status_id;
        $company_statuses = config('constants.company_statuses');
        return $company_statuses[$id] ?? '';

    }
}
