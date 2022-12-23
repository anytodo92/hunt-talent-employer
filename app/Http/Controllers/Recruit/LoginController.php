<?php

namespace App\Http\Controllers\Recruit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    function showLoginForm() {

        return view('recruit.login');

    }
}
