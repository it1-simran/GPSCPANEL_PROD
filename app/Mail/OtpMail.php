<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;
    public $user_name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($otp, $user_name = 'User')
    {
        $this->otp = $otp;
        $this->user_name = $user_name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Password Reset OTP - ' . config('app.name'))
                    ->view('emails.registration_otp');
    }
}
