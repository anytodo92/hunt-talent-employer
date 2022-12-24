<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitingCompany extends Model
{
    use SoftDeletes;

    protected $table = 'recruiting_companies';
}
