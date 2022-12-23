<?php

namespace App\Http\Requests;

use App\Rules\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $annual_recruit_numbers = config('constants.annual_recruit_numbers');
        $annual_recruit_number_ids = array_keys($annual_recruit_numbers);

        if($this->status === 'contact') { // お問い合わせ・資料請求

            $consideration_statuses = config('constants.consideration_statuses');
            $consideration_status_ids = array_keys($consideration_statuses);
            $rules = [
                'contact_types' => ['required'],
                'contact_types.*' => [Rule::in([1, 2])],
                'company_name' => ['required'],
                'user_name' => ['required'],
                'email' => ['required', 'email', 'unique:users,email'],
                'phone_number' => ['required', new PhoneNumber()],
                'url' => ['required', 'url'],
                'annual_recruit_number_id' => ['required', Rule::in($annual_recruit_number_ids)],
                'consideration_status_id' => ['required', Rule::in($consideration_status_ids)],
                'accepted' => ['accepted'],
                'status' => ['required', Rule::in(['contact', 'request'])]
            ];

        } else { // その他

            $prefectures = config('constants.prefectures');
            $prefecture_ids = array_keys($prefectures);
            $rules = [
                'company_name' => ['required'],
                'user_name' => ['required'],
                'email' => ['required', 'email', 'unique:users,email'],
                'phone_number' => ['required', new PhoneNumber()],
                'prefecture_id' => ['required', Rule::in($prefecture_ids)],
                'address' => ['required'],
                'url' => ['required', 'url'],
                'annual_recruit_number_id' => ['required', Rule::in($annual_recruit_number_ids)],
                'accepted' => ['accepted'],
                'status' => ['required', Rule::in(['contact', 'request'])]
            ];

        }

        return $rules;
    }

    public function attributes()
    {
        return [
            'contact_types' => 'お問い合わせタイプ',
            'url' => '会社HP',
            'consideration_status_id' => 'ご検討状況',
            'accepted' => 'プライバシーポリシー・利用規約への同意',
        ];
    }
}
