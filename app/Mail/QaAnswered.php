<?php

namespace App\Mail;

use App\Models\User;
use App\Models\CompanyUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QaAnswered extends Mailable
{
    use Queueable, SerializesModels;

    private $user, $content, $question;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $content, $question)
    {
        $this->user = $user;
        $this->content = $content;
        $this->question = $question;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $view_name = 'emails.qa_answer';
        $to = $this->user->email;
        $subject = '【インクル求人システム】質問の回答';

        return $this
            ->to($to)
            ->subject($subject)
            ->view($view_name)
            ->with([
                'user' => $this->user,
                'content' => $this->content,
                'question' => $this->question
            ]);

    }
}
