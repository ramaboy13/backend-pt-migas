<?php
// app/Mail/EmailVerificationMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $verificationUrl,
        public string $verificationCode
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Email - PT Migas Finance',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification',
            with: [
                'name' => $this->name,
                'verificationUrl' => $this->verificationUrl,
                'verificationCode' => $this->verificationCode,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}