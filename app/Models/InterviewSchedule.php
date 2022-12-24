<?php

namespace App\Models;

use App\Traits\ModelHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class InterviewSchedule extends Model
{
    use SoftDeletes, ModelHistoryTrait;

    protected $table = 'interview_schedules';

    protected $dates = [
        'interview_candidates_date',
    ];
}
