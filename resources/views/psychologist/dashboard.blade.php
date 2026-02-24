<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['credit_balance'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ __('Available credits') }}</div>
                </div>
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold text-gray-700">{{ $stats['patients_count'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ __('Registered patients') }}</div>
                </div>
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending_requests'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ __('Pending completion') }}</div>
                </div>
                <div class="bg-white rounded-xl shadow p-5 text-center">
                    <div class="text-3xl font-bold text-green-600">{{ $stats['completed_requests'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">{{ __('Completed screenings') }}</div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="flex gap-3 mb-8">
                <a href="{{ route('psychologist.screenings.create') }}"
                   class="px-5 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                    {{ __('+ New Screening') }}
                </a>
                <a href="{{ route('psychologist.patients.create') }}"
                   class="px-5 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition">
                    {{ __('+ New Patient') }}
                </a>
            </div>

            {{-- Recent Requests --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">{{ __('Recent requests') }}</h3>
                </div>
                @forelse($stats['recent_requests'] as $req)
                <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between hover:bg-gray-50 transition">
                    <div>
                        <div class="font-medium text-gray-800">{{ $req->patient->full_name }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $req->sent_at?->diffForHumans() ?? __('Draft') }}
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @php
                            $badges = [
                                'draft'                => ['bg-gray-100 text-gray-600',    __('Draft')],
                                'sent'                 => ['bg-yellow-100 text-yellow-700', __('Sent')],
                                'partially_completed'  => ['bg-blue-100 text-blue-700',    __('In Progress')],
                                'completed'            => ['bg-green-100 text-green-700',  __('Completed')],
                                'expired'              => ['bg-red-100 text-red-600',      __('Expired')],
                            ];
                            [$cls, $label] = $badges[$req->status] ?? ['bg-gray-100 text-gray-600', $req->status];
                        @endphp
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $cls }}">
                            {{ $label }}
                        </span>
                        <a href="{{ route('psychologist.screenings.show', $req) }}"
                           class="text-blue-600 text-sm hover:underline">{{ __('View →') }}</a>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-400">
                    {{ __("You haven't sent any screenings yet.") }}
                </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
