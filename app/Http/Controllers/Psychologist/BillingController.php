<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use App\Services\BillingService;
use App\Services\TrialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestiona la compra de créditos vía Stripe Checkout.
 */
class BillingController extends Controller
{
    public function __construct(
        private readonly BillingService $billing,
    ) {}

    /**
     * Panel de facturación: saldo actual, paquetes disponibles e historial.
     */
    public function index(Request $request): View
    {
        $user     = $request->user();
        $packages = config('stripe.credit_packages');

        $payments = $user->stripePayments()
            ->where('status', 'paid')
            ->latest('paid_at')
            ->paginate(10);

        $pendingPayments = $user->stripePayments()
            ->where('status', 'pending')
            ->latest()
            ->limit(3)
            ->get();

        $trialInfo = [
            'hasStarted'  => $user->hasStartedTrial(),
            'isActive'    => $user->isOnActiveTrial(),
            'hasExpired'  => $user->trialHasExpired(),
            'daysLeft'    => $user->trialDaysLeft(),
            'endsAt'      => $user->trialEndsAt(),
            'creditLimit' => TrialService::TRIAL_CREDITS,
        ];

        return view('psychologist.billing.index', [
            'user'            => $user,
            'creditBalance'   => $user->creditBalance(),
            'packages'        => $packages,
            'payments'        => $payments,
            'pendingPayments' => $pendingPayments,
            'ledger'          => $user->creditsLedger()->latest()->limit(20)->get(),
            'trialInfo'       => $trialInfo,
        ]);
    }

    /**
     * Inicia el Checkout de Stripe y redirige al hosted page.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'package' => ['required', 'string', 'in:' . implode(',', array_keys(config('stripe.credit_packages')))],
        ]);

        try {
            $session = $this->billing->createCheckoutSession(
                $request->user(),
                $validated['package'],
            );
        } catch (\Exception $e) {
            return back()->withErrors(['general' => __('Could not start payment: :error', ['error' => $e->getMessage()])]);
        }

        return redirect($session->url);
    }

    /**
     * Página de confirmación tras pago exitoso.
     * Los créditos ya fueron acreditados vía webhook; aquí solo mostramos feedback.
     */
    public function success(Request $request): View
    {
        $sessionId = $request->query('session_id');

        return view('psychologist.billing.success', [
            'creditBalance' => $request->user()->creditBalance(),
            'sessionId'     => $sessionId,
        ]);
    }
}
