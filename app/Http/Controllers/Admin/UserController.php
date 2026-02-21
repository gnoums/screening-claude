<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CreditsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestión de usuarios (psicólogas) desde el panel admin.
 */
class UserController extends Controller
{
    public function __construct(
        private readonly CreditsService $creditsService,
    ) {}

    public function index(Request $request): View
    {
        $users = User::where('role', 'psychologist')
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->withCount(['patients', 'screeningRequests'])
            ->latest()
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['profile', 'stripePayments' => fn ($q) => $q->latest()->limit(10)]);

        $stats = [
            'credit_balance'  => $user->creditBalance(),
            'patients_count'  => $user->patients()->count(),
            'requests_count'  => $user->screeningRequests()->count(),
            'completed_count' => $user->screeningRequests()->where('status', 'completed')->count(),
        ];

        $ledger = $user->creditsLedger()->latest()->paginate(15);

        return view('admin.users.show', compact('user', 'stats', 'ledger'));
    }

    /**
     * Ajuste manual de créditos (acreditar o descontar).
     */
    public function adjustCredits(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'amount'      => ['required', 'integer', 'not_in:0', 'min:-9999', 'max:9999'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $amount = (int) $validated['amount'];

        if ($amount > 0) {
            $this->creditsService->addCredits(
                user:        $user,
                amount:      $amount,
                type:        'adjustment',
                description: '[Admin] ' . $validated['description'],
            );
        } else {
            // Descuento manual — usamos addCredits con valor negativo
            $this->creditsService->addCredits(
                user:        $user,
                amount:      $amount, // negativo
                type:        'adjustment',
                description: '[Admin] ' . $validated['description'],
            );
        }

        return back()->with('success', "Créditos ajustados: {$amount} para {$user->name}.");
    }

    /**
     * Cambiar rol de usuario (admin ↔ psychologist).
     */
    public function updateRole(Request $request, User $user): RedirectResponse
    {
        // No permitir que el admin se quite el rol a sí mismo
        abort_if($user->id === $request->user()->id, 422, 'No puedes cambiar tu propio rol.');

        $validated = $request->validate([
            'role' => ['required', 'in:admin,psychologist'],
        ]);

        $user->update(['role' => $validated['role']]);

        return back()->with('success', "Rol actualizado a '{$validated['role']}' para {$user->name}.");
    }
}
