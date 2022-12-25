<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class IsCompanyVerified implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = false;
        $user = User::where('email', $value)->first();

        try {

            $status_id = intval($user->company_user->company->current_status->company_status_id);
            $result = (
                !is_null($user) &&
                $user->is_company &&
                $status_id === 3
            );

        } catch (\Exception $e) {}

        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'このユーザーは承認されていません';
    }
}
