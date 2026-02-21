<?php

namespace App\Mail;

use App\Models\ScreeningRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email de invitación al paciente para completar su tamizaje.
 */
class ScreeningInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly ScreeningRequest $request,
        public readonly string           $accessUrl,
    ) {}

    public function envelope(): Envelope
    {
        $psychologistName = $this->request->user->name;

        return new Envelope(
            subject: "Evaluación psicológica de {$psychologistName} – Ingresa aquí",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.screening-invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
