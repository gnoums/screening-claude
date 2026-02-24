<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Payment successful') }}</h2>
    </x-slot>

    <div class="py-16 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-lg p-10 max-w-md text-center">
            <div class="text-6xl mb-4">🎉</div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">{{ __('Payment processed!') }}</h1>
            <p class="text-gray-600 mb-6">
                {{ __('Your credits will be credited in seconds. If they don\'t appear immediately, wait a moment and refresh the page.') }}
            </p>
            <div class="bg-blue-50 rounded-xl p-4 mb-6">
                <div class="text-sm text-blue-600">{{ __('Current balance') }}</div>
                <div class="text-3xl font-bold text-blue-700 mt-1">{{ $creditBalance }} {{ __('credits') }}</div>
            </div>
            <div class="flex gap-3 justify-center">
                <a href="{{ route('psychologist.billing.index') }}"
                   class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    {{ __('View billing') }}
                </a>
                <a href="{{ route('psychologist.screenings.create') }}"
                   class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                    {{ __('Send screening →') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
