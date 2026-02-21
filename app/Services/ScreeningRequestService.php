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
     * @return array{request: ScreeningRequest, accessUrl: string}
     */
    public function createAndSend(
        User    $psychologist,
        Patient $patient,
        array   $assessmentIds,
        string  $recipientEmail,
        ?string $messageToPatient = null,
    ): array {
        $emailUrl = null;

        $request = DB::transaction(function () use (
            $psychologist,
            $patient,
            $assessmentIds,
            $recipientEmail,
            $messageToPatient,
            &$emailUrl,
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
            $emailUrl = $this->tokenService->generateUrl($plainToken);

            // Descontar créditos
            $this->creditsService->chargeForRequest($request);

            // Marcar como enviada — el email se despacha fuera de la transacción
            $request->update(['status' => 'sent', 'sent_at' => now()]);

            return $request->load('items.assessment');
        });

        // Despachar fuera de la transacción para que un fallo de SMTP
        // no haga rollback de los créditos y la solicitud ya creada.
        SendScreeningEmailJob::dispatch($request->id, $emailUrl, $recipientEmail);

        return ['request' => $request, 'accessUrl' => $emailUrl];
    }
}
