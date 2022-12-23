<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRegisterRequest;
use App\Mail\CompanyContacted;
use App\Mail\CompanyRequested;
use App\Models\Company;
use App\Models\CompanyStatus;
use App\Models\CompanyUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    public function create() {

        $prefectures = config('constants.prefectures');
        $annual_recruit_numbers = config('constants.annual_recruit_numbers');
        $consideration_statuses = config('constants.consideration_statuses');
        $company_contact_types = config('constants.company_contact_types');

        return view('company.register.create')->with([
            'prefectures' => $prefectures,
            'annual_recruit_numbers' => $annual_recruit_numbers,
            'consideration_statuses' => $consideration_statuses,
            'company_contact_types' => $company_contact_types
        ]);

    }

    public function store(CompanyRegisterRequest $request) {

        $result = false;

        \DB::beginTransaction();

        try {

            $password = "skdmlRna1984!";//Str::random(11);

            $user = new User();
            $user->email = $request->email;
            $user->password = Hash::make($password);
            $user->user_type_id = 1; // 求人企業
            $user->save();

            $company = new Company();
            $company->name = $request->company_name;
            $company->phone_number = $request->phone_number;
            $company->url = $request->url;
            $company->annual_recruit_number_id = $request->annual_recruit_number_id;
            $company->inquiry = $request->inquiry;
            $company_status_id = -1;
            $status = $request->status;

            if($status === 'contact') { // お問い合わせ・資料請求

                $company->consideration_status_id = $request->consideration_status_id;
                $company_status_id = 0;
                $company->accepted = $request->accepted;

            } else if($status === 'request') { // 求人掲載のお申し込み

                $company->consideration_status_id = 0;
                $company->prefecture_id = $request->prefecture_id;
                $company->address = $request->address;
                $company->accepted = $request->accepted;
                $company_status_id = 1;

            }

            $company->save();

            $company_status = new CompanyStatus();
            $company_status->company_id = $company->id;
            $company_status->company_status_id = $company_status_id;
            $company_status->status_changed_at = now();
            $company_status->status_changed_by = $user->id;
            $company_status->save();

            $company_user = new CompanyUser();
            $company_user->user_id = $user->id;
            $company_user->company_id = $company->id;
            $company_user->name = $request->user_name;
            $company_user->ip = $request->ip();
            $company_user->save();

            \DB::commit();
            $this->sendMail($company_user);
            $result = true;

        } catch (\Exception $e) {

            \DB::rollBack();

        }

        return ['result' => $result];

    }

    public function complete($type) {

        if($type === 'contact') {

            return view('company.register.complete_contact');

        } else if($type === 'request') {

            return view('company.register.complete_request');

        }

        abort(404);

    }

    private function sendMail(CompanyUser $company_user) {

        $request = request();
        $status = $request->status;

        if($status === 'contact') { // お問い合わせ・資料請求

            $contact_types = $request->contact_types;
            $with_document = $this->withDocument($contact_types);
            \Mail::send(new CompanyContacted('company', $company_user, $with_document));
            \Mail::send(new CompanyContacted('admin', $company_user, $with_document));

        } else if($status === 'request') { // 求人掲載のお申し込み

            \Mail::send(new CompanyRequested('company', $company_user));
            \Mail::send(new CompanyRequested('admin', $company_user));

        }

    }

    private function withDocument($contact_types) {

        return collect($contact_types)->contains(function($contact_type){

            return (intval($contact_type) === 2);

        });

    }
}
