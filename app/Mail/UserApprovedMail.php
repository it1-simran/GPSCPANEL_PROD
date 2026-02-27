<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Approval Confirmation & Account Credentials - GPS Cpanel')
                    ->view('emails.user_approved');
    }
}
