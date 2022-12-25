<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Str;

class LoginPassword implements Rule
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
        $length = strlen($value);
        $mb_length = mb_strlen($value);

        if($length === $mb_length &&
            $length >= 10 &&
            $length <= 16 &&
            preg_match('/[0-9]+/', $value) &&
            preg_match('/[a-zA-Z]+/', $value) &&
            Str::contains($value, ['/', '_', '-', ',', ';', '!'])) {

            return true;

        }

        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'パスワードは、半角英数字と特殊文字（　/_-,;!　）を組み合わせ、10〜16文字以下で入力してください。';
    }
}
