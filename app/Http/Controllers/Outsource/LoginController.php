<?php

namespace App\Http\Controllers\Outsource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    function showLoginForm() {

        return view('outsource.login');

    }
}
