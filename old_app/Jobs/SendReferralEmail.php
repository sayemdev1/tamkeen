<?php

namespace App\Jobs;

use App\Mail\ReferralEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail; // Importing Mail facade
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendReferralEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $email;
    protected $referralLink;
    protected $user_name; // Add this line
    protected $package_name; // Add this line

   
    public function __construct(string $email, string $referralLink, string $user_name, string $package_name)
    {
        $this->email = $email;
        $this->referralLink = $referralLink;
        $this->user_name = $user_name; // Add this line
        $this->package_name = $package_name; // Add this line
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Send the referral email
        Mail::to($this->email)->send(new ReferralEmail($this->referralLink, $this->user_name, $this->package_name));
    }
}
