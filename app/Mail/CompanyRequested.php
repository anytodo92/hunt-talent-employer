<?php

namespace App\Mail;

use App\Models\CompanyUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyRequested extends Mailable
{
    use Queueable, SerializesModels;

    private $company_user, $mode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mode, CompanyUser $company_user)
    {
        $this->mode = $mode;
        $this->company_user = $company_user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view_name = '';
        $to = '';
        $subject = '';

        if($this->mode === 'company') {

            $view_name = 'emails.company_request';
            $to = $this->company_user->user->email;
            $subject = '【インクル求人システム】申し込み完了連絡';

        } else if($this->mode === 'admin') {

            $view_name = 'emails.admin_request';
            $to = config('constants.emails.admin');
            $subject = '【インクル求人システム】承認依頼連絡';

        }

        return $this
            ->to($to)
            ->subject($subject)
            ->view($view_name)
            ->with([
                'company_user' => $this->company_user
            ]);
    }
}
