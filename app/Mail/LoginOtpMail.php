<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class LoginOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
        public Carbon $expiresAt,
    ) {}

    public function build(): self
    {
        return $this
            ->subject('Your Heymo sign-in code')
            ->view('mail.login-otp');
    }
}
