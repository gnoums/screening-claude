<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Visualización y filtrado del log de auditoría.
 */
class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = AuditLog::with(['user'])
            ->when($request->event, fn ($q) => $q->where('event', $request->event))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->when($request->ip, fn ($q) => $q->where('ip_address', $request->ip))
            ->when($request->date_from, fn ($q) => $q->where('occurred_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($q) => $q->where('occurred_at', '<=', $request->date_to . ' 23:59:59'))
            ->orderByDesc('occurred_at')
            ->paginate(50)
            ->withQueryString();

        $eventTypes = AuditLog::distinct()->pluck('event')->sort()->values();

        return view('admin.audit-logs.index', compact('logs', 'eventTypes'));
    }
}
