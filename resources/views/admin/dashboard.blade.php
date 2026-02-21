<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold bg-red-100 text-red-700 px-2 py-1 rounded">ADMIN</span>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel de Administración</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Stats --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold text-gray-700">{{ $stats['total_users'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Psicólogas registradas</div>
                </div>
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['active_users'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Activas este mes</div>
                </div>
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $stats['requests_month'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Evaluaciones este mes</div>
                </div>
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold text-emerald-600">
                        ${{ number_format($stats['revenue_month'], 0) }}
                    </div>
                    <div class="text-sm text-gray-500 mt-1">Ingresos MXN este mes</div>
                </div>
            </div>

            {{-- Quick links --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    👥 Usuarios
                </a>
                <a href="{{ route('admin.assessments.index') }}"
                   class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    📋 Pruebas
                </a>
                <a href="{{ route('admin.audit-logs.index') }}"
                   class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50 transition">
                    🔍 Audit Logs
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Pagos recientes --}}
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-800">
                        Pagos recientes
                    </div>
                    @forelse($stats['recent_payments'] as $payment)
                        <div class="px-6 py-3 border-b border-gray-50 flex justify-between items-center">
                            <div>
                                <div class="text-sm font-medium text-gray-800">{{ $payment->user->name }}</div>
                                <div class="text-xs text-gray-400">{{ $payment->paid_at?->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-green-600">{{ $payment->amountFormatted() }}</div>
                                <div class="text-xs text-gray-400">{{ $payment->credits_amount }} créditos</div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-center text-gray-400 text-sm">Sin pagos aún.</div>
                    @endforelse
                </div>

                {{-- Audit log reciente --}}
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                        <span class="font-semibold text-gray-800">Actividad reciente</span>
                        <a href="{{ route('admin.audit-logs.index') }}" class="text-xs text-blue-600 hover:underline">Ver todo</a>
                    </div>
                    @forelse($stats['recent_audit_logs'] as $log)
                        <div class="px-6 py-2 border-b border-gray-50 flex justify-between items-center">
                            <div>
                                <span class="text-xs font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-600">
                                    {{ $log->event }}
                                </span>
                                @if($log->ip_address)
                                    <span class="text-xs text-gray-400 ml-2">{{ $log->ip_address }}</span>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400">{{ $log->occurred_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-center text-gray-400 text-sm">Sin eventos aún.</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
