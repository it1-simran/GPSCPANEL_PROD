<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SendAccountRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $link;
    public $email;
    public $expirationMinutes = 720;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $email)
    {
        $this->user = $user;
        $this->email = $email;

        // Generate a signed URL with email parameter
        $this->link = URL::temporarySignedRoute(
            'register.user', // your named route
            Carbon::now()->addMinutes($this->expirationMinutes),
            ['name'=> $user ,'email' => $email] // ← capture email here
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Account Creation Request-GPS Cpanel')
            ->view('emails.account_request');
    }
}
