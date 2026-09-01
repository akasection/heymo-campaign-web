<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subject,
        public string $html,
    ) {}

    public function build(): self
    {
        return $this
            ->subject($this->subject)
            ->html($this->html);
    }
}
