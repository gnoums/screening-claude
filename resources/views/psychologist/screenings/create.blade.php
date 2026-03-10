<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('psychologist.screenings.index') }}"
               class="text-gray-400 hover:text-gray-600 text-sm">{{ __('← Screenings') }}</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('New Screening') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow p-6">

                @if ($errors->has('general'))
                    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                        {{ $errors->first('general') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('psychologist.screenings.store') }}" class="space-y-6">
                    @csrf

                    {{-- Patient --}}
                    <div>
                        <x-input-label for="patient_id" :value="__('Patient')" />
                        @if($patients->isEmpty())
                            <p class="mt-1 text-sm text-gray-500">
                                {{ __('No patients registered.') }}
                                <a href="{{ route('psychologist.patients.create') }}" class="text-primary-600 hover:underline">
                                    {{ __('Create a patient') }}
                                </a>
                            </p>
                        @else
                            <select id="patient_id" name="patient_id" required
                                class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">
                                <option value="">{{ __('— Select patient —') }}</option>
                                @foreach($patients as $patient)
                                    <option value="{{ $patient->id }}"
                                        {{ old('patient_id', request('patient_id')) == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->full_name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        <x-input-error :messages="$errors->get('patient_id')" class="mt-1" />
                    </div>

                    {{-- Screening Tests --}}
                    <div>
                        <x-input-label :value="__('Screening Tests to apply')" />
                        <p class="text-xs text-gray-400 mb-2">{{ __('Select one or more screening tests') }}</p>
                        @if($assessments->isEmpty())
                            <p class="text-sm text-gray-500">{{ __('No screening tests available.') }}</p>
                        @else
                            <div class="space-y-2">
                                @foreach($assessments as $assessment)
                                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                        <input type="checkbox"
                                               name="assessment_ids[]"
                                               value="{{ $assessment->id }}"
                                               {{ in_array($assessment->id, old('assessment_ids', [])) ? 'checked' : '' }}
                                               class="mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <div>
                                            <div class="text-sm font-medium text-gray-800">{{ $assessment->name }}</div>
                                            @if($assessment->description)
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $assessment->description }}</div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                        <x-input-error :messages="$errors->get('assessment_ids')" class="mt-1" />
                    </div>

                    {{-- Recipient email --}}
                    <div>
                        <x-input-label for="recipient_email" :value="__('Patient email')" />
                        <x-text-input id="recipient_email" name="recipient_email" type="email"
                            class="mt-1 block w-full"
                            :value="old('recipient_email')"
                            placeholder="patient@example.com"
                            required />
                        <p class="text-xs text-gray-400 mt-1">{{ __('The screening link will be sent to this email.') }}</p>
                        <x-input-error :messages="$errors->get('recipient_email')" class="mt-1" />
                    </div>

                    {{-- Optional message --}}
                    <div>
                        <x-input-label for="message_to_patient" :value="__('Message for patient (optional)')" />
                        <textarea id="message_to_patient" name="message_to_patient" rows="3"
                            placeholder="Hi, here is the link to complete your screening test…"
                            class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm">{{ old('message_to_patient') }}</textarea>
                        <x-input-error :messages="$errors->get('message_to_patient')" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('psychologist.screenings.index') }}"
                           class="text-sm text-gray-500 hover:text-gray-700">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button>
                            {{ __('Send Screening') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
