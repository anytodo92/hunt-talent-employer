<?php

namespace App\Mail;

use App\Models\CompanyUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyContacted extends Mailable
{
    use Queueable, SerializesModels;

    private $company_user, $mode, $contact_types;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mode, CompanyUser $company_user, $with_document = false)
    {
        $this->mode = $mode;
        $this->company_user = $company_user;
        $this->with_document = $with_document;
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
        $subject = '【インクル求人システム】お問い合わせ/資料請求完了連絡';

        if($this->mode === 'company') {

            $view_name = 'emails.company_contact';
            $to = $this->company_user->user->email;

        } else if($this->mode === 'admin') {

            $view_name = 'emails.admin_contact';
            $to = config('constants.emails.admin');

        }

        return $this
            ->to($to)
            ->subject($subject)
            ->view($view_name)
            ->with([
                'company_user' => $this->company_user,
                'with_document' => $this->with_document
            ]);
    }
}
