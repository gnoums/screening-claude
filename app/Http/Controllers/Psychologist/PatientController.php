<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function index(Request $request): View
    {
        $patients = $request->user()->patients()
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(20);

        return view('psychologist.patients.index', compact('patients'));
    }

    public function create(): View
    {
        return view('psychologist.patients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:80'],
            'last_name'     => ['required', 'string', 'max:80'],
            'email'         => ['nullable', 'email', 'max:150'],
            'phone'         => ['nullable', 'string', 'max:25'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'sex'           => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'notes'         => ['nullable', 'string', 'max:2000'],
        ]);

        $patient = $request->user()->patients()->create($validated);

        return redirect()
            ->route('psychologist.patients.show', $patient)
            ->with('success', 'Paciente creado correctamente.');
    }

    public function show(Request $request, Patient $patient): View
    {
        $this->authorizePatient($request, $patient);

        $patient->load([
            'screeningRequests' => fn ($q) => $q->latest()->limit(10),
            'screeningRequests.items.assessment',
        ]);

        return view('psychologist.patients.show', compact('patient'));
    }

    public function edit(Request $request, Patient $patient): View
    {
        $this->authorizePatient($request, $patient);

        return view('psychologist.patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorizePatient($request, $patient);

        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:80'],
            'last_name'     => ['required', 'string', 'max:80'],
            'email'         => ['nullable', 'email', 'max:150'],
            'phone'         => ['nullable', 'string', 'max:25'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'sex'           => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'notes'         => ['nullable', 'string', 'max:2000'],
        ]);

        $patient->update($validated);

        return redirect()
            ->route('psychologist.patients.show', $patient)
            ->with('success', 'Datos del paciente actualizados.');
    }

    public function destroy(Request $request, Patient $patient): RedirectResponse
    {
        $this->authorizePatient($request, $patient);
        $patient->delete(); // soft delete

        return redirect()
            ->route('psychologist.patients.index')
            ->with('success', 'Paciente eliminado.');
    }

    // ----------------------------------------------------------------

    private function authorizePatient(Request $request, Patient $patient): void
    {
        abort_unless(
            $patient->user_id === $request->user()->id,
            403,
            'No tienes acceso a este paciente.',
        );
    }
}
