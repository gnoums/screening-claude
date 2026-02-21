<?php

namespace App\Services;

use App\Jobs\SendScreeningEmailJob;
use App\Models\Assessment;
use App\Models\Patient;
use App\Models\ScreeningRequest;
use App\Models\ScreeningRequestItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Orquesta la creación y envío de solicitudes de tamizaje.
 */
class ScreeningRequestService
{
    public function __construct(
        private readonly TokenService   $tokenService,
        private readonly CreditsService $creditsService,
    ) {}

    /**
     * Crea y envía una solicitud de tamizaje completa.
     *
     * @param  array<int>  $assessmentIds  IDs de pruebas a incluir
     */
    public function createAndSend(
        User    $psychologist,
        Patient $patient,
        array   $assessmentIds,
        string  $recipientEmail,
        ?string $messageToPatient = null,
    ): ScreeningRequest {
        return DB::transaction(function () use (
            $psychologist,
            $patient,
            $assessmentIds,
            $recipientEmail,
            $messageToPatient,
        ) {
            $assessments    = Assessment::active()->whereIn('id', $assessmentIds)->get();
            $totalCredits   = $assessments->sum('credits_cost');

            // Validar créditos antes de crear
            if (! $psychologist->hasCredits($totalCredits)) {
                throw new \DomainException(
                    "Créditos insuficientes. Necesitas {$totalCredits} y tienes {$psychologist->creditBalance()}.",
                );
            }

            // Crear solicitud
            $request = ScreeningRequest::create([
                'user_id'            => $psychologist->id,
                'patient_id'         => $patient->id,
                'status'             => 'draft',
                'recipient_email'    => $recipientEmail,
                'message_to_patient' => $messageToPatient,
                'credits_charged'    => $totalCredits,
            ]);

            // Crear ítems en el orden recibido
            foreach ($assessments as $index => $assessment) {
                ScreeningRequestItem::create([
                    'screening_request_id' => $request->id,
                    'assessment_id'        => $assessment->id,
                    'order'                => $index,
                    'status'               => 'pending',
                ]);
            }

            // Generar token seguro
            ['plain' => $plainToken] = $this->tokenService->generate($request);
            $url = $this->tokenService->generateUrl($plainToken);

            // Descontar créditos
            $this->creditsService->chargeForRequest($request);

            // Actualizar estado y despachar email a la cola
            $request->update(['status' => 'sent', 'sent_at' => now()]);
            SendScreeningEmailJob::dispatch($request->id, $url, $recipientEmail);

            return $request->load('items.assessment');
        });
    }
}
