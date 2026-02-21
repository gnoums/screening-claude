<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pacientes
            </h2>
            <a href="{{ route('psychologist.patients.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition">
                + Nuevo paciente
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Búsqueda --}}
            <form method="GET" action="{{ route('psychologist.patients.index') }}" class="mb-6">
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Buscar por nombre o email…"
                           class="flex-1 border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit"
                            class="px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Buscar
                    </button>
                    @if(request('search'))
                        <a href="{{ route('psychologist.patients.index') }}"
                           class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>

            {{-- Tabla --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                @forelse ($patients as $patient)
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between hover:bg-gray-50 transition">
                        <div>
                            <div class="font-medium text-gray-800">{{ $patient->full_name }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $patient->email ?? 'Sin email' }}
                                @if($patient->age)
                                    &middot; {{ $patient->age }} años
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-gray-400">
                                {{ $patient->screeningRequests()->count() }} evaluaciones
                            </span>
                            <a href="{{ route('psychologist.patients.show', $patient) }}"
                               class="text-blue-600 text-sm hover:underline">
                                Ver →
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-gray-400">
                        @if(request('search'))
                            No se encontraron pacientes con ese criterio.
                        @else
                            Aún no has registrado pacientes.
                            <a href="{{ route('psychologist.patients.create') }}" class="text-blue-600 hover:underline ml-1">
                                Crear el primero
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- Paginación --}}
            <div class="mt-4">
                {{ $patients->withQueryString()->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
