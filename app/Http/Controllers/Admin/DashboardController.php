<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ScreeningRequest;
use App\Models\StripePayment;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'total_users'       => User::where('role', 'psychologist')->count(),
            'active_users'      => User::where('role', 'psychologist')
                ->whereHas('screeningRequests', fn ($q) => $q->whereMonth('sent_at', now()->month))
                ->count(),
            'requests_today'    => ScreeningRequest::whereDate('sent_at', today())->count(),
            'requests_month'    => ScreeningRequest::whereMonth('sent_at', now()->month)
                ->whereYear('sent_at', now()->year)->count(),
            'revenue_month'     => StripePayment::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)->sum('amount_paid_cents') / 100,
            'recent_payments'   => StripePayment::with('user')
                ->where('status', 'paid')
                ->latest('paid_at')
                ->limit(5)
                ->get(),
            'recent_audit_logs' => AuditLog::latest('occurred_at')->limit(10)->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
