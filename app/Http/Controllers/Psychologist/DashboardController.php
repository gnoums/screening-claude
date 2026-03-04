<?php

namespace App\Http\Controllers\Psychologist;

use App\Http\Controllers\Controller;
use App\Services\TrialService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'credit_balance'        => $user->creditBalance(),
            'patients_count'        => $user->patients()->count(),
            'sent_this_month'       => $user->screeningRequests()
                ->whereMonth('sent_at', now()->month)
                ->whereYear('sent_at', now()->year)
                ->count(),
            'pending_requests'      => $user->screeningRequests()
                ->whereIn('status', ['sent', 'partially_completed'])
                ->count(),
            'completed_requests'    => $user->screeningRequests()
                ->where('status', 'completed')
                ->count(),
            'recent_requests'       => $user->screeningRequests()
                ->with('patient')
                ->latest('sent_at')
                ->limit(5)
                ->get(),
        ];

        $trialInfo = [
            'hasStarted'  => $user->hasStartedTrial(),
            'isActive'    => $user->isOnActiveTrial(),
            'hasExpired'  => $user->trialHasExpired(),
            'daysLeft'    => $user->trialDaysLeft(),
            'endsAt'      => $user->trialEndsAt(),
            'creditLimit' => TrialService::TRIAL_CREDITS,
        ];

        return view('psychologist.dashboard', compact('stats', 'trialInfo'));
    }
}
