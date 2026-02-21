<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Audit Logs</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filtros --}}
            <form method="GET" class="bg-white rounded-xl shadow p-4 grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Evento</label>
                    <select name="event" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="">Todos</option>
                        @foreach($eventTypes as $type)
                            <option value="{{ $type }}" {{ request('event') === $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">IP</label>
                    <input type="text" name="ip" value="{{ request('ip') }}"
                           placeholder="192.168.1.x"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Desde</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="col-span-2 md:col-span-4 flex gap-2">
                    <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                        Filtrar
                    </button>
                    <a href="{{ route('admin.audit-logs.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                        Limpiar
                    </a>
                </div>
            </form>

            {{-- Tabla --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Evento</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">IP</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Entidad</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Metadatos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded text-gray-700">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 font-mono text-xs">
                                    {{ $log->ip_address ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">
                                    {{ $log->user?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs">
                                    @if($log->auditable_type)
                                        {{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                                    {{ $log->occurred_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3 text-xs">
                                    @if($log->metadata)
                                        <details>
                                            <summary class="cursor-pointer text-blue-600 hover:underline">Ver</summary>
                                            <pre class="mt-1 text-xs bg-gray-100 rounded p-2 overflow-auto max-w-xs">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </details>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                    No hay eventos que coincidan con los filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $logs->links() }}</div>
            </div>

        </div>
    </div>
</x-app-layout>
