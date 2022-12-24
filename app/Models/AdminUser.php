<?php

namespace App\Models;

use App\Traits\ModelHistoryTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminUser extends Model
{
    use SoftDeletes, ModelHistoryTrait;
}
