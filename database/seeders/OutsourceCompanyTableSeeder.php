<?php

namespace Database\Seeders;


use App\Constants;
use App\Models\OutsourceCompany;
use App\Models\OutsourceCompanyUser;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OutsourceCompanyTableSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $password = 'xxxxxxxx';

        // 業務委託会社１ -----------------------------------------
        $outsourceCompany = new OutsourceCompany();
        $outsourceCompany->fill([
            'name' => '業務委託会社１',
            'logo' => 'outsource-company1.png',
            'email' => 'outsource-company1@yahoo.co.jp'
        ])
        ->save();

        // 会員：　担当者１－１
        $user = new User();
        $user->fill([
            'email' => 'outsource-staff1-1@incul.jp',
            'password' => Hash::make($password),
            'user_type_id' => Constants::USER_TYPE_OUTSOURCE
        ])
        ->save();

        // 業務委託会社１：　担当者１－１
        $outsourceCompanyUser = new OutsourceCompanyUser();
        $outsourceCompanyUser->fill([
            'user_id' => $user->id,
            'outsource_company_id' => $outsourceCompany->id,
            'name' => '業務委託１１',
            'logo' => 'outsource-staff1-1.png',
        ])
        ->save();

        // 会員：　担当者１－２
        $user = new User();
        $user->fill([
            'email' => 'outsource-staff1-2@incul.jp',
            'password' => Hash::make($password),
            'user_type_id' => Constants::USER_TYPE_OUTSOURCE
        ])
        ->save();

        // 業務委託会社１：　担当者１－２
        $outsourceCompanyUser = new OutsourceCompanyUser();
        $outsourceCompanyUser->fill([
            'user_id' => $user->id,
            'outsource_company_id' => $outsourceCompany->id,
            'name' => '業務委託１２',
            'logo' => 'outsource-staff1-2.png',
        ])
        ->save();

        // 業務委託会社２ -----------------------------------------
        $outsourceCompany = new OutsourceCompany();
        $outsourceCompany->fill([
            'name' => '業務委託会社２',
            'logo' => 'outsource-company2.png',
            'email' => 'outsource-company2@yahoo.co.jp'
        ])
        ->save();

        // 会員：　担当者２－１
        $user = new User();
        $user->fill([
            'email' => 'outsource-staff2-1@incul.jp',
            'password' => Hash::make($password),
            'user_type_id' => Constants::USER_TYPE_OUTSOURCE
        ])
        ->save();

        // 業務委託会社２：　担当者２－１
        $outsourceCompanyUser = new OutsourceCompanyUser();
        $outsourceCompanyUser->fill([
            'user_id' => $user->id,
            'outsource_company_id' => $outsourceCompany->id,
            'name' => '業務委託２１',
            'logo' => 'outsource-staff2-1.png',
        ])
        ->save();

        // 会員：　担当者２－２
        $user = new User();
        $user->fill([
            'email' => 'outsource-staff2-2@incul.jp',
            'password' => Hash::make($password),
            'user_type_id' => Constants::USER_TYPE_OUTSOURCE
        ])
        ->save();

        // 業務委託会社２：　担当者２－２
        $outsourceCompanyUser = new OutsourceCompanyUser();
        $outsourceCompanyUser->fill([
            'user_id' => $user->id,
            'outsource_company_id' => $outsourceCompany->id,
            'name' => '業務委託２２',
            'logo' => 'outsource-staff2-2.png',
        ])
        ->save();
    }
}
