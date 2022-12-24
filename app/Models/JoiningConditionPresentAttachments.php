<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JoiningConditionPresentAttachments extends Model
{
    use SoftDeletes;

    protected $table = 'joining_condition_present_attachments';

}
