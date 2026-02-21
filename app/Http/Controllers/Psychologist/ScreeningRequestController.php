<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Patient;
use App\Models\ScreeningRequest;
use App\Services\ScreeningRequestService;
use App\Services\TokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScreeningRequestController extends Controller
{
    public function __construct(
        private readonly ScreeningRequestService $service,
        private readonly TokenService            $tokenService,
    ) {}

    public function index(Request $request): View
    {
        $requests = $request->user()->screeningRequests()
            ->with(['patient', 'items.assessment'])
            ->latest()
            ->paginate(20);

        return view('psychologist.screenings.index', compact('requests'));
    }

    public function create(Request $request): View
    {
        $patients    = $request->user()->patients()->orderBy('last_name')->get();
        $assessments = Assessment::active()->orderBy('name')->get();

        return view('psychologist.screenings.create', compact('patients', 'assessments'));
    }

    /**
     * Crea y envía la solicitud de tamizaje.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id'         => ['required', 'integer', 'exists:patients,id'],
            'assessment_ids'     => ['required', 'array', 'min:1'],
            'assessment_ids.*'   => ['integer', 'exists:assessments,id'],
            'recipient_email'    => ['required', 'email', 'max:150'],
            'message_to_patient' => ['nullable', 'string', 'max:1000'],
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);

        // Solo la psicóloga dueña puede enviar a sus pacientes
        abort_unless($patient->user_id === $request->user()->id, 403);

        try {
            $screeningRequest = $this->service->createAndSend(
                psychologist:      $request->user(),
                patient:           $patient,
                assessmentIds:     $validated['assessment_ids'],
                recipientEmail:    $validated['recipient_email'],
                messageToPatient:  $validated['message_to_patient'] ?? null,
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['general' => $e->getMessage()])->withInput();
        }

        return redirect()
            ->route('psychologist.screenings.show', $screeningRequest)
            ->with('success', 'Solicitud enviada correctamente.');
    }

    public function show(Request $request, ScreeningRequest $screening): View
    {
        $this->authorizeRequest($request, $screening);

        $screening->load([
            'patient',
            'items.assessment',
            'items.response.answers.question',
            'items.response.answers.option',
            'token',
        ]);

        return view('psychologist.screenings.show', compact('screening'));
    }

    /**
     * Re-envía el email de la solicitud (regenera token).
     */
    public function resend(Request $request, ScreeningRequest $screening): RedirectResponse
    {
        $this->authorizeRequest($request, $screening);

        abort_if(in_array($screening->status, ['completed', 'cancelled']), 422, 'No se puede re-enviar.');

        ['plain' => $plainToken] = $this->tokenService->generate($screening);
        $url = $this->tokenService->generateUrl($plainToken);

        \App\Jobs\SendScreeningEmailJob::dispatch($screening->id, $url, $screening->recipient_email);

        return back()->with('success', 'Enlace de acceso re-enviado al paciente.');
    }

    // ----------------------------------------------------------------

    private function authorizeRequest(Request $request, ScreeningRequest $screening): void
    {
        abort_unless(
            $screening->user_id === $request->user()->id,
            403,
        );
    }
}
