<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Screenings') }}
            </h2>
            <a href="{{ route('psychologist.screenings.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold text-sm hover:bg-blue-700 transition">
                {{ __('+ New Screening') }}
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

            <div class="bg-white rounded-xl shadow overflow-hidden">
                @forelse ($requests as $req)
                    @php
                        $badges = [
                            'draft'               => ['bg-gray-100 text-gray-600',    __('Draft')],
                            'sent'                => ['bg-yellow-100 text-yellow-700', __('Sent')],
                            'partially_completed' => ['bg-blue-100 text-blue-700',    __('In Progress')],
                            'completed'           => ['bg-green-100 text-green-700',  __('Completed')],
                            'expired'             => ['bg-red-100 text-red-600',      __('Expired')],
                            'cancelled'           => ['bg-gray-100 text-gray-500',    __('Cancelled')],
                        ];
                        [$cls, $label] = $badges[$req->status] ?? ['bg-gray-100 text-gray-600', $req->status];
                    @endphp
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between hover:bg-gray-50 transition">
                        <div>
                            <div class="font-medium text-gray-800">
                                {{ $req->patient->full_name }}
                            </div>
                            <div class="text-sm text-gray-500 mt-0.5">
                                {{ $req->items->pluck('assessment.name')->filter()->join(', ') ?: '—' }}
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ $req->sent_at?->format('d/m/Y H:i') ?? __('Not sent') }}
                                &middot; {{ $req->recipient_email }}
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $cls }}">
                                {{ $label }}
                            </span>
                            <a href="{{ route('psychologist.screenings.show', $req) }}"
                               class="text-blue-600 text-sm hover:underline">
                                {{ __('View →') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-gray-400">
                        {{ __("You haven't sent any screenings yet.") }}
                        <a href="{{ route('psychologist.screenings.create') }}" class="text-blue-600 hover:underline ml-1">
                            {{ __('Create the first one') }}
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $requests->links() }}
            </div>

        </div>
    </div>
</x-app-layout>
