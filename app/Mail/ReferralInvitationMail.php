<?php
// app/Mail/ReferralInvitationMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReferralInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $referralCode,
        public string $role,
        public ?string $expiresAt,
        public string $createdBy
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invitation to Join PT Migas Finance System',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.referral-invitation',
            with: [
                'referralCode' => $this->referralCode,
                'role' => $this->role,
                'expiresAt' => $this->expiresAt,
                'createdBy' => $this->createdBy,
                'registrationUrl' => url('/register?ref=' . $this->referralCode),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}