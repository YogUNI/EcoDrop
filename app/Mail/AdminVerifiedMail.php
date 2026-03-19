<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminVerifiedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $adminName;

    public function __construct($adminName)
    {
        $this->adminName = $adminName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Akun Admin EcoDrop Anda Telah Diverifikasi!',
        );
    }

    public function content(): Content
    {
        return new Content(
            // Ini yang nge-link ke file HTML lu
            view: 'emails.admin-verified', 
        );
    }

    public function attachments(): array
    {
        return [];
    }
}