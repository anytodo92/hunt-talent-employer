<?php

namespace App\Models;

use App\Constants;
use App\Mail\PasswordResetRequested;
use App\Notifications\ResetPasswordNotification;
use App\Traits\ModelHistoryTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, ModelHistoryTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relationship
    public function company_user()
    {
        return $this->hasOne(CompanyUser::class, 'user_id', 'id');
    }
    public function recruit_user()
    {
        return $this->hasOne(RecruitCompanyUser::class, 'user_id', 'id');
    }
    public function outsource_user()
    {
        return $this->hasOne(OutsourceCompanyUser::class, 'user_id', 'id');
    }
    public function admin_user()
    {
        return $this->hasOne(AdminUser::class, 'user_id', 'id');
    }

    // Accessor
    public function getIsCompanyAttribute() { // is_company
        return (intval($this->user_type_id) === Constants::USER_TYPE_COMPANY);
    }

    public function getIsRecruitAttribute() { // is_recruit
        return (intval($this->user_type_id) === Constants::USER_TYPE_RECRUIT);
    }

    public function getIsOutsourceAttribute() { // is_outsource
        return (intval($this->user_type_id) === Constants::USER_TYPE_OUTSOURCE);
    }
    
    public function getIsAdminAttribute() { // is_admin
        return (intval($this->user_type_id) === Constants::USER_TYPE_ADMIN);
    }

    public function getUserTypeAttribute() {
        $id = $this->user_type_id;
        $user_types = config('constants.user_types');
        return $user_types[$id] ?? '';
    }

    // Override
    public function sendPasswordResetNotification($token)
    {
        $reset_url = route('password.reset', $token);
        \Mail::to($this)->send(new PasswordResetRequested($this, $reset_url));
    }
}
