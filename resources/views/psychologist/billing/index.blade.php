<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Credits & Billing') }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Trial banner --}}
            @if($trialInfo['isActive'])
                <div class="bg-primary-50 border border-primary-200 rounded-xl px-5 py-4">
                    <div class="font-semibold text-primary-800 mb-1">{{ __('Free trial active') }}</div>
                    <p class="text-sm text-primary-700">
                        {{ __('You have') }}
                        <strong>{{ $trialInfo['daysLeft'] }} {{ $trialInfo['daysLeft'] === 1 ? __('day') : __('days') }}</strong>
                        {{ __('and') }}
                        <strong>{{ $creditBalance }}&nbsp;/&nbsp;{{ $trialInfo['creditLimit'] }} {{ __('credits') }}</strong>
                        {{ __('remaining.') }}
                        {{ __('Your trial expires on') }} <strong>{{ $trialInfo['endsAt']->format('m/d/Y') }}</strong>.
                        {{ __('After it expires, you\'ll need to purchase credits to continue.') }}
                    </p>
                </div>
            @elseif($trialInfo['hasExpired'])
                <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4">
                    <div class="font-semibold text-red-800 mb-1">{{ __('Free trial expired') }}</div>
                    <p class="text-sm text-red-700">
                        {{ __('Your free trial of :count assessments has ended.', ['count' => $trialInfo['creditLimit']]) }}
                        {{ __('Purchase a credit package to continue sending assessments.') }}
                    </p>
                </div>
            @endif

            {{-- Current balance --}}
            <div class="bg-gradient-to-r from-primary-600 to-primary-400 rounded-2xl shadow-lg p-6 text-white flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium opacity-80">{{ __('Available balance') }}</div>
                    <div class="text-5xl font-bold mt-1">{{ $creditBalance }}</div>
                    <div class="text-sm opacity-70 mt-1">{{ __('credits') }}</div>
                </div>
                <div class="text-6xl opacity-20">💳</div>
            </div>

            {{-- Global errors --}}
            @if($errors->has('general'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    {{ $errors->first('general') }}
                </div>
            @endif

            {{-- Credit packages --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-800 mb-4">{{ __('Recharge credits') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($packages as $key => $package)
                        <div class="bg-white rounded-xl shadow border-2 {{ $package['popular'] ? 'border-primary-500' : 'border-gray-100' }} p-5 relative">
                            @if($package['popular'])
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                    {{ __('MOST POPULAR') }}
                                </div>
                            @endif
                            <div class="text-2xl font-bold text-gray-800">{{ __($package['label']) }}</div>
                            <div class="text-sm font-medium text-gray-500 mt-0.5">{{ $package['credits'] }} {{ __('credits') }}</div>
                            <div class="text-3xl font-bold text-primary-600 mt-2">
                                ${{ number_format($package['amount_usd'], 2) }}
                                <span class="text-sm font-normal text-gray-500">USD</span>
                            </div>
                            <div class="text-sm text-gray-500 mt-1">{{ __($package['description']) }}</div>
                            <div class="text-xs text-gray-400 mt-1">
                                ${{ number_format($package['amount_usd'] / $package['credits'], 2) }}{{ __('/credit') }}
                            </div>

                            <form method="POST" action="{{ route('psychologist.billing.checkout') }}" class="mt-4">
                                @csrf
                                <input type="hidden" name="package" value="{{ $key }}">
                                <button type="submit"
                                        class="w-full py-2 px-4 {{ $package['popular'] ? 'bg-primary-600 hover:bg-primary-700' : 'bg-gray-800 hover:bg-gray-900' }} text-white font-semibold rounded-lg transition">
                                    {{ __('Buy →') }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-3 text-center">
                    {{ __('Credits never expire.') }} · {{ __('Secure payment processed by Stripe. We do not store card data.') }}
                </p>
            </div>

            {{-- Credit ledger --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">{{ __('Credit history') }}</h3>
                </div>
                @forelse($ledger as $entry)
                    <div class="px-6 py-3 border-b border-gray-50 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-700">
                                @php
                                    $desc = $entry->description;
                                    if (preg_match('/^request_sent\|(\d+)$/', $desc, $m)) {
                                        $desc = __('Request #:number sent', ['number' => $m[1]]);
                                    } elseif (preg_match('/^request_refund\|(\d+)$/', $desc, $m)) {
                                        $desc = __('Refund request #:number', ['number' => $m[1]]);
                                    } elseif (preg_match('/^credit_purchase\|(\d+)\|(\S+)$/', $desc, $m)) {
                                        $desc = __(':count credits purchased — Stripe :id', ['count' => $m[1], 'id' => $m[2]]);
                                    } elseif (!$desc) {
                                        $desc = ucfirst($entry->type);
                                    }
                                @endphp
                                {{ $desc }}
                            </div>
                            <div class="text-xs text-gray-400">{{ $entry->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold {{ $entry->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $entry->amount > 0 ? '+' : '' }}{{ $entry->amount }}
                            </span>
                            <span class="text-xs text-gray-400">{{ __('Balance: :amount', ['amount' => $entry->balance_after]) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400">{{ __('No movements yet.') }}</div>
                @endforelse
            </div>

            {{-- Stripe payment history --}}
            @if($payments->count() > 0)
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-800">{{ __('Payments made') }}</h3>
                    </div>
                    @foreach($payments as $payment)
                        <div class="px-6 py-3 border-b border-gray-50 flex items-center justify-between">
                            <div>
                                <div class="text-sm text-gray-700">{{ __(':count credits', ['count' => $payment->credits_amount]) }}</div>
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
