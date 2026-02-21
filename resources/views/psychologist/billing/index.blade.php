<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Créditos y Facturación</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Saldo actual --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-400 rounded-2xl shadow-lg p-6 text-white flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium opacity-80">Saldo disponible</div>
                    <div class="text-5xl font-bold mt-1">{{ $creditBalance }}</div>
                    <div class="text-sm opacity-70 mt-1">créditos</div>
                </div>
                <div class="text-6xl opacity-20">💳</div>
            </div>

            {{-- Errores globales --}}
            @if($errors->has('general'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ $errors->first('general') }}
                </div>
            @endif

            {{-- Paquetes de créditos --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recargar créditos</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($packages as $key => $package)
                        <div class="bg-white rounded-xl shadow border-2 {{ $package['popular'] ? 'border-blue-500' : 'border-gray-100' }} p-5 relative">
                            @if($package['popular'])
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                    MÁS POPULAR
                                </div>
                            @endif
                            <div class="text-2xl font-bold text-gray-800">{{ $package['label'] }}</div>
                            <div class="text-3xl font-bold text-blue-600 mt-2">
                                ${{ number_format($package['amount_mxn']) }}
                                <span class="text-sm font-normal text-gray-500">MXN</span>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">{{ $package['description'] }}</div>
                            <div class="text-xs text-gray-400 mt-1">
                                ${{ number_format($package['amount_mxn'] / $package['credits'], 1) }}/crédito
                            </div>

                            <form method="POST" action="{{ route('psychologist.billing.checkout') }}" class="mt-4">
                                @csrf
                                <input type="hidden" name="package" value="{{ $key }}">
                                <button type="submit"
                                        class="w-full py-2 px-4 {{ $package['popular'] ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-800 hover:bg-gray-900' }} text-white font-semibold rounded-lg transition">
                                    Comprar →
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-3 text-center">
                    Pago seguro procesado por Stripe. No almacenamos datos de tarjeta.
                </p>
            </div>

            {{-- Historial de movimientos de créditos --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Historial de créditos</h3>
                </div>
                @forelse($ledger as $entry)
                    <div class="px-6 py-3 border-b border-gray-50 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-700">{{ $entry->description ?: ucfirst($entry->type) }}</div>
                            <div class="text-xs text-gray-400">{{ $entry->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold {{ $entry->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $entry->amount > 0 ? '+' : '' }}{{ $entry->amount }}
                            </span>
                            <span class="text-xs text-gray-400">Saldo: {{ $entry->balance_after }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400">Sin movimientos aún.</div>
                @endforelse
            </div>

            {{-- Historial de pagos Stripe --}}
            @if($payments->count() > 0)
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">Pagos realizados</h3>
                    </div>
                    @foreach($payments as $payment)
                        <div class="px-6 py-3 border-b border-gray-50 flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-700">{{ $payment->credits_amount }} créditos</div>
                                <div class="text-xs text-gray-400">{{ $payment->paid_at?->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="text-sm font-semibold text-gray-700">
                                {{ $payment->amountFormatted() }}
                            </div>
                        </div>
                    @endforeach
                    <div class="px-6 py-3">{{ $payments->links() }}</div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
