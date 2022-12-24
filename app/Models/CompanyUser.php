<?php

namespace App\Models;

use App\Traits\ModelHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyUser extends Model
{
    use SoftDeletes, ModelHistoryTrait;

    // Relationship
    public function company() {

        return $this->hasOne(Company::class, 'id', 'company_id');

    }

    // Relationship
    public function user() {

        return $this->belongsTo(User::class, 'user_id', 'id');

    }
}
