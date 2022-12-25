<?php

namespace App\Traits;

use App\Providers\RouteServiceProvider;

trait AuthRedirectTrait {

    public function redirectTo() {

        $redirect_url = RouteServiceProvider::HOME;

        if(auth()->check()) {

            $user = auth()->user();
            $user_type_id = intval($user->user_type_id);

            if($user_type_id === 1) { // 求人企業

                $redirect_url = route('company.home');

            } else if($user_type_id === 2) { // 人材紹介

                $redirect_url = route('recruit.home');

            } else if($user_type_id === 3) { // 業務委託SES

                $redirect_url = route('outsource.home');

            } else if($user_type_id === 4) { // 運営企業

                $redirect_url = route('admin.home');

            }

        }

        return $redirect_url;

    }

    public function redirectAfterLogoutTo() {

        $redirect_url = '/';

        if(auth()->check()) {

            $user = auth()->user();
            $user_type_id = intval($user->user_type_id);

            if($user_type_id === 1) { // 求人企業

                $redirect_url = route('login');

            } else if($user_type_id === 2) { // 人材紹介

                $redirect_url = route('recruit.login');

            } else if($user_type_id === 3) { // 業務委託SES

                $redirect_url = route('outsource.login');

            } else if($user_type_id === 4) { // 運営企業

                $redirect_url = route('admin.login');

            }

        }

        return $redirect_url;

    }

}