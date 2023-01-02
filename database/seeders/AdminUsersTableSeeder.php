<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $password = 'xxxxxxxx';

        // Admin
        $admin_roles = config('constants.admin_roles');

        foreach ($admin_roles as $role_id => $role_name) {

            $user = new User();
            $user->email = 'admin_'. $role_id .'@example.com';
            $user->password = Hash::make($password);
            $user->user_type_id = 4; // 運営企業
            $user->save();

            $admin_user = new AdminUser();
            $admin_user->user_id = $user->id;
            $admin_user->name = $role_name;
            $admin_user->admin_role_id = $role_id;
            $admin_user->save();

        }
    }
}
