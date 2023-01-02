<?php

namespace Database\Seeders;


use App\Constants;
use App\Models\RecruitCompany;
use App\Models\RecruitCompanyUser;
use App\Models\RecruitOfferInfo;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RecruitCompanyTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $password = 'xxxxxxxx';

        // 人材紹介会社１ -----------------------------------------
        $recruitCompany = new RecruitCompany();
        $recruitCompany->fill([
            'name' => '人材紹介会社１',
            'logo' => 'recruit-company1.png',
            'email' => 'recruit-company1@yahoo.co.jp'
        ])
        ->save();

        // 会員：　担当者１－１
        $user = new User();
        $user->fill([
            'email' => 'recruit-staff1-1@incul.jp',
            'password' => Hash::make($password),
            'user_type_id' => Constants::USER_TYPE_RECRUIT
        ])
        ->save();

        // 人材紹介会社１：　担当者１－１
        $recruitCompanyUser = new RecruitCompanyUser();
        $recruitCompanyUser->fill([
            'user_id' => $user->id,
            'recruit_company_id' => $recruitCompany->id,
            'name' => '人材紹介１１',
            'logo' => 'recruit-staff1-1.png',
        ])
        ->save();

        // 会員：　担当者１－２
        $user = new User();
        $user->fill([
            'email' => 'recruit-staff1-2@incul.jp',
            'password' => Hash::make($password),
            'user_type_id' => Constants::USER_TYPE_RECRUIT
        ])
        ->save();

        // 人材紹介会社１：　担当者１－２
        $recruitCompanyUser = new RecruitCompanyUser();
        $recruitCompanyUser->fill([
            'user_id' => $user->id,
            'recruit_company_id' => $recruitCompany->id,
            'name' => '人材紹介１２',
            'logo' => 'recruit-staff1-2.png',
        ])
        ->save();

        // 人材紹介会社２ -----------------------------------------
        $recruitCompany = new RecruitCompany();
        $recruitCompany->fill([
            'name' => '人材紹介会社２',
            'logo' => 'recruit-company2.png',
            'email' => 'recruit-company2@yahoo.co.jp'
        ])
        ->save();

        // 会員：　担当者２－１
        $user = new User();
        $user->fill([
            'email' => 'recruit-staff2-1@incul.jp',
            'password' => Hash::make($password),
            'user_type_id' => Constants::USER_TYPE_RECRUIT
        ])
        ->save();

        // 人材紹介会社２：　担当者２－１
        $recruitCompanyUser = new RecruitCompanyUser();
        $recruitCompanyUser->fill([
            'user_id' => $user->id,
            'recruit_company_id' => $recruitCompany->id,
            'name' => '人材紹介２１',
            'logo' => 'recruit-staff2-1.png',
        ])
        ->save();

        // 会員：　担当者２－２
        $user = new User();
        $user->fill([
            'email' => 'recruit-staff2-2@incul.jp',
            'password' => Hash::make($password),
            'user_type_id' => Constants::USER_TYPE_RECRUIT
        ])
        ->save();

        // 人材紹介会社２：　担当者２－２
        $recruitCompanyUser = new RecruitCompanyUser();
        $recruitCompanyUser->fill([
            'user_id' => $user->id,
            'recruit_company_id' => $recruitCompany->id,
            'name' => '人材紹介２２',
            'logo' => 'recruit-staff2-2.png',
        ])
        ->save();
    }
}
