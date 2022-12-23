<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Rules\IsCompanyVerified;
use App\Rules\LoginPassword;
use App\Traits\AuthRedirectTrait;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords, AuthRedirectTrait;

    // Override
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => [
                'required',
                'email',
                new IsCompanyVerified()
            ],
            'password' => [
                'required',
                'confirmed',
                new LoginPassword()
            ],
        ];
    }

    // Override
    public function reset(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());

        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        if($response == Password::PASSWORD_RESET) {

            $redirect_to = $this->redirectAfterLogoutTo();
            $this->guard()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect($redirect_to)->with('message', 'パスワードの再設定が完了しました');

        }

        return $this->sendResetFailedResponse($request, $response);
    }

}
