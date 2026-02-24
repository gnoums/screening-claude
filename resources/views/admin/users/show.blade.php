<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="text-gray-400 hover:text-gray-600">← Usuarios</a>
            <h2 class="font-semibold text-xl text-gray-800">{{ $user->name }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Info básica + stats --}}
            <div class="bg-white rounded-xl shadow p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <div class="text-xs text-gray-500 uppercase font-semibold">Email</div>
                    <div class="text-sm text-gray-800 mt-1">{{ $user->email }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase font-semibold">Rol</div>
                    <div class="mt-1">
                        <span class="text-xs font-semibold px-2 py-1 rounded-full
                            {{ $user->role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-primary-100 text-primary-700' }}">
                            {{ $user->role }}
                        </span>
                    </div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 uppercase font-semibold">Créditos</div>
                    <div class="text-2xl font-bold text-primary-600 mt-1">{{ $stats['credit_balance'] }}</div>
                </div>
                <div class="text-center">
                    <div class="text-xs text-gray-500 uppercase font-semibold">Evaluaciones</div>
                    <div class="text-2xl font-bold text-gray-700 mt-1">{{ $stats['requests_count'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Ajuste de créditos --}}
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Ajuste manual de créditos</h3>
                    <form method="POST" action="{{ route('admin.users.credits', $user) }}">
                        @csrf
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">
                                    Cantidad (positivo = agregar, negativo = descontar)
                                </label>
                                <input type="number" name="amount"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                       placeholder="Ej: 10 o -5" required>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Motivo</label>
                                <input type="text" name="description"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                       placeholder="Ej: Bonificación por error del sistema" required>
                            </div>
                            <button type="submit"
                                    class="w-full py-2 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700 transition">
                                Aplicar ajuste
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Cambiar rol --}}
                <div class="bg-white rounded-xl shadow p-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Cambiar rol</h3>
                    <form method="POST" action="{{ route('admin.users.role', $user) }}">
                        @csrf
                        @method('PATCH')
                        <div class="space-y-3">
                            <select name="role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                                <option value="psychologist" {{ $user->role === 'psychologist' ? 'selected' : '' }}>
                                    Psicóloga
                                </option>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                                    Administradora
                                </option>
                            </select>
                            <button type="submit"
                                    class="w-full py-2 bg-gray-800 text-white rounded-lg text-sm font-semibold hover:bg-gray-900 transition">
                                Actualizar rol
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Historial de créditos --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-800">
                    Historial de créditos
                </div>
                @forelse($ledger as $entry)
                    <div class="px-6 py-3 border-b border-gray-50 flex justify-between items-center">
                        <div>
                            <div class="text-sm text-gray-700">{{ $entry->description ?: ucfirst($entry->type) }}</div>
                            <div class="text-xs text-gray-400">{{ $entry->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold {{ $entry->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $entry->amount > 0 ? '+' : '' }}{{ $entry->amount }}
                            </span>
                            <span class="text-xs text-gray-400">→ {{ $entry->balance_after }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-6 text-center text-gray-400 text-sm">Sin movimientos.</div>
                @endforelse
                <div class="px-6 py-3">{{ $ledger->links() }}</div>
            </div>

            {{-- Pagos Stripe --}}
            @if($user->stripePayments->count() > 0)
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 font-semibold text-gray-800">Pagos Stripe</div>
                    @foreach($user->stripePayments as $payment)
                        <div class="px-6 py-3 border-b border-gray-50 flex justify-between items-center">
                            <div>
                                <div class="text-sm text-gray-700">{{ $payment->credits_amount }} créditos</div>
                                <div class="text-xs font-mono text-gray-400">{{ $payment->stripe_session_id }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold">{{ $payment->amountFormatted() }}</div>
                                <span class="text-xs px-2 py-0.5 rounded-full
                                    {{ $payment->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $payment->status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
