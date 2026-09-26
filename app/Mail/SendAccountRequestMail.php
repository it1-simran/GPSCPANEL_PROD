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
    public $deviceCategoryId;
    public $expirationMinutes = 720;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $email, $deviceCategoryId = null)
    {
        $this->user = $user;
        $this->email = $email;
        $this->deviceCategoryId = $deviceCategoryId;

        $params = ['name' => $user, 'email' => $email];
        if (!empty($deviceCategoryId)) {
            // Pre-selected by the admin at invite time — the registration
            // form locks this field instead of asking the invitee to guess it.
            $params['device_category'] = $deviceCategoryId;
        }

        // Generate a signed URL with email parameter
        $this->link = URL::temporarySignedRoute(
            'register.user', // your named route
            Carbon::now()->addMinutes($this->expirationMinutes),
            $params
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
