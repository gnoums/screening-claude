<?php

namespace App\Jobs;

use App\Mail\ScreeningInvitationMail;
use App\Models\ScreeningRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Job de cola para envío de email de invitación.
 * Usa database queue driver → compatible con cPanel + cron.
 */
class SendScreeningEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // segundos entre reintentos

    public function __construct(
        private readonly int    $screeningRequestId,
        private readonly string $accessUrl,
        private readonly string $recipientEmail,
    ) {}

    public function handle(): void
    {
        $request = ScreeningRequest::with(['patient', 'user', 'items.assessment'])
            ->findOrFail($this->screeningRequestId);

        Mail::to($this->recipientEmail)
            ->send(new ScreeningInvitationMail($request, $this->accessUrl));
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendScreeningEmailJob failed', [
            'screening_request_id' => $this->screeningRequestId,
            'recipient'            => $this->recipientEmail,
            'error'                => $exception->getMessage(),
        ]);
    }
}
