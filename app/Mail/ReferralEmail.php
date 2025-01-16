<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReferralEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $referralLink;
    public $user_name; // Add this line
    public $package_name; // Add this line

  
    public function __construct(string $referralLink, string $user_name, string $package_name)
    {
        $this->referralLink = $referralLink;
        $this->user_name = $user_name; // Add this line
        $this->package_name = $package_name; // Add this line
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('You have been referred to join Tamkeen!')
            ->view('emails.referral'); // Ensure this view exists
    }
}
