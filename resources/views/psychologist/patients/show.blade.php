<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('psychologist.patients.index') }}"
                   class="text-gray-400 hover:text-gray-600 text-sm">{{ __('← Patients') }}</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $patient->full_name }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('psychologist.screenings.create', ['patient_id' => $patient->id]) }}"
                   class="px-4 py-2 bg-primary-600 text-white rounded-lg font-semibold text-sm hover:bg-primary-700 transition">
                    {{ __('+ New Screening') }}
                </a>
                <a href="{{ route('psychologist.patients.edit', $patient) }}"
                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-50 transition">
                    {{ __('Edit') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Patient data --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-700 mb-4">{{ __('Personal data') }}</h3>
                <dl class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Email</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $patient->email ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Phone') }}</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $patient->phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Date of birth') }}</dt>
                        <dd class="text-gray-800 mt-0.5">
                            {{ $patient->date_of_birth?->format('d/m/Y') ?? '—' }}
                            @if($patient->age)
                                <span class="text-gray-400">({{ $patient->age }} {{ __('years old') }})</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Sex') }}</dt>
                        <dd class="text-gray-800 mt-0.5">
                            @php
                                $sexLabels = [
                                    'male'              => __('Male'),
                                    'female'            => __('Female'),
                                    'other'             => __('Other'),
                                    'prefer_not_to_say' => __('Prefer not to say'),
                                ];
                            @endphp
                            {{ $sexLabels[$patient->sex] ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">{{ __('Registered') }}</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $patient->created_at->format('d/m/Y') }}</dd>
                    </div>
                </dl>

                @if($patient->notes)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-gray-500 text-sm mb-1">{{ __('Clinical notes') }}</dt>
                        <dd class="text-gray-800 text-sm whitespace-pre-wrap">{{ $patient->notes }}</dd>
                    </div>
                @endif
            </div>

            {{-- Screenings --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-700">{{ __('Screenings') }}</h3>
                </div>

                @forelse ($patient->screeningRequests as $req)
                    @php
                        $badges = [
                            'draft'               => ['bg-gray-100 text-gray-600',    __('Draft')],
                            'sent'                => ['bg-yellow-100 text-yellow-700', __('Sent')],
                            'partially_completed' => ['bg-primary-100 text-primary-700',    __('In Progress')],
                            'completed'           => ['bg-green-100 text-green-700',  __('Completed')],
                            'expired'             => ['bg-red-100 text-red-600',      __('Expired')],
                            'cancelled'           => ['bg-gray-100 text-gray-500',    __('Cancelled')],
                        ];
                        [$cls, $label] = $badges[$req->status] ?? ['bg-gray-100 text-gray-600', $req->status];
                    @endphp
                    <div class="px-6 py-4 border-b border-gray-50 flex items-center justify-between hover:bg-gray-50 transition">
                        <div>
                            <div class="font-medium text-gray-800 text-sm">
                                {{ $req->items->pluck('assessment.name')->filter()->join(', ') ?: __('No screening tests') }}
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
                               class="text-primary-600 text-sm hover:underline">
                                {{ __('View →') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-gray-400 text-sm">
                        {{ __('This patient has no screenings yet.') }}
                    </div>
                @endforelse
            </div>

            {{-- Danger zone --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-red-600 mb-2">{{ __('Danger zone') }}</h3>
                <p class="text-sm text-gray-500 mb-4">
                    {{ __('Deleting the patient will hide their data. Completed screenings will be preserved.') }}
                </p>
                <form method="POST" action="{{ route('psychologist.patients.destroy', $patient) }}"
                      onsubmit="return confirm('{{ __('Are you sure you want to delete :name?', ['name' => addslashes($patient->full_name)]) }}')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm font-medium hover:bg-red-100 transition">
                        {{ __('Delete patient') }}
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
