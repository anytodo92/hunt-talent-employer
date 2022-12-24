<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetRequested extends Mailable
{
    use Queueable, SerializesModels;

    private $user, $reset_url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $reset_url)
    {
        $this->user = $user;
        $this->reset_url = $reset_url;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('【インクル求人システム】パスワードリマインダー連絡')
            ->view('emails.auth.password_reset_requested')
            ->with([
                'user' => $this->user,
                'reset_url' => $this->reset_url
            ]);
    }
}
