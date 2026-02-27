<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class UserRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $reason;
    public $link;
    public $expirationHours = 24;

    /**
     * Create a new message instance.
     */
    public function __construct($user, $reason)
    {
        $this->user = $user;
        $this->reason = $reason;

        // Generate a resubmission link (signed URL)
        $this->link = URL::temporarySignedRoute(
            'register.user',
            Carbon::now()->addHours($this->expirationHours),
            ['name' => $user->name, 'email' => $user->email]
        );
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Your Request Has Been Rejected -GPS Cpanel')
                    ->view('emails.user_rejected');
    }
}
