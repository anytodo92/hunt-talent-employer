<?php

Route::prefix('test')->domain('incul-agent.test')->group(function(){

    Route::get('model_history', function() {

        $user = \App\Models\User::first();
        $user->email = \Illuminate\Support\Str::random() .'@example.com';
        $user->save();
        sleep(2);
        $user->delete();

    });
    Route::get('company', function() {

        $company = \App\Models\Company::with('first_industry', 'second_industry', 'statuses')->find(rand(1, 10));
        dump($company->toArray());

    });
    Route::get('company_user', function() {

        $company_user = \App\Models\CompanyUser::with('company', 'user')->find(rand(1, 10));
        dump($company_user->toArray());

    });
    Route::get('industry', function() {

        $industries = \App\Models\FirstIndustry::with('second_industries')->get();
        dump($industries->toArray());

    });
    Route::get('contact_email', function(){

        $company_user = \App\Models\CompanyUser::find(1);
        \Mail::send(new \App\Mail\CompanyContacted('company', $company_user, true));
        \Mail::send(new \App\Mail\CompanyContacted('company', $company_user, false));
        \Mail::send(new \App\Mail\CompanyContacted('admin', $company_user));

    });
    Route::get('request_email', function(){

        $company_user = \App\Models\CompanyUser::find(1);
        \Mail::send(new \App\Mail\CompanyRequested('company', $company_user));
        \Mail::send(new \App\Mail\CompanyRequested('admin', $company_user));

    });
    Route::get('user', function(){

        $admin_user = \App\Models\User::with('company_user', 'admin_user')->find(1);
        $company_user = \App\Models\User::with('company_user', 'admin_user')->find(3);
        dd(
            $admin_user->toArray(),
            $company_user->toArray(),
            $admin_user->user_type,
            $company_user->user_type,
        );

    });

});