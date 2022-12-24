<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecruitInterview extends Model
{
    use SoftDeletes;

    protected $table = 'recruit_interview';

}
