<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CampaignMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        string $subject,
        string $html,
    ) {
        $this->subject = $subject;
        $this->html = $html;
    }

    public function build(): self
    {
        return $this;
    }
}
