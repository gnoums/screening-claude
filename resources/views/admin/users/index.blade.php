<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Usuarias</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Búsqueda --}}
            <form method="GET" class="mb-6 flex gap-3">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Buscar por nombre o email…"
                       class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                    Buscar
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.users.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                        Limpiar
                    </a>
                @endif
            </form>

            <div class="bg-white rounded-xl shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Usuaria</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Rol</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Pacientes</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Solicitudes</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Registrada</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-400">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-semibold px-2 py-1 rounded-full
                                        {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700">{{ $user->patients_count }}</td>
                                <td class="px-6 py-4 text-center text-sm text-gray-700">{{ $user->screening_requests_count }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="text-blue-600 text-sm hover:underline">Ver →</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                                    No se encontraron usuarias.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4">{{ $users->withQueryString()->links() }}</div>
            </div>

        </div>
    </div>
</x-app-layout>
