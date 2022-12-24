<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobSeekerAttachment extends Model
{
    use SoftDeletes;

    protected $table = 'job_seeker_attachments';

}
