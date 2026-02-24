<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('psychologist.patients.show', $patient) }}"
               class="text-gray-400 hover:text-gray-600 text-sm">← {{ $patient->full_name }}</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit patient') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow p-6">

                <form method="POST" action="{{ route('psychologist.patients.update', $patient) }}" class="space-y-5">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="first_name" :value="__('First name')" />
                            <x-text-input id="first_name" name="first_name" type="text"
                                class="mt-1 block w-full"
                                :value="old('first_name', $patient->first_name)" required autofocus />
                            <x-input-error :messages="$errors->get('first_name')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="last_name" :value="__('Last name(s)')" />
                            <x-text-input id="last_name" name="last_name" type="text"
                                class="mt-1 block w-full"
                                :value="old('last_name', $patient->last_name)" required />
                            <x-input-error :messages="$errors->get('last_name')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="email" :value="__('Email address')" />
                        <x-text-input id="email" name="email" type="email"
                            class="mt-1 block w-full"
                            :value="old('email', $patient->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div>
                        <x-input-label for="phone" :value="__('Phone')" />
                        <x-text-input id="phone" name="phone" type="text"
                            class="mt-1 block w-full"
                            :value="old('phone', $patient->phone)" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="date_of_birth" :value="__('Date of birth')" />
                            <x-text-input id="date_of_birth" name="date_of_birth" type="date"
                                class="mt-1 block w-full"
                                :value="old('date_of_birth', $patient->date_of_birth?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('date_of_birth')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="sex" :value="__('Sex')" />
                            <select id="sex" name="sex"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">{{ __('— Select —') }}</option>
                                @foreach([
                                    'male'              => __('Male'),
                                    'female'            => __('Female'),
                                    'other'             => __('Other'),
                                    'prefer_not_to_say' => __('Prefer not to say'),
                                ] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('sex', $patient->sex) === $val ? 'selected' : '' }}>
                                        {{ $lbl }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('sex')" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="notes" :value="__('Clinical notes')" />
                        <textarea id="notes" name="notes" rows="4"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">{{ old('notes', $patient->notes) }}</textarea>
                        <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('psychologist.patients.show', $patient) }}"
                           class="text-sm text-gray-500 hover:text-gray-700">
                            {{ __('Cancel') }}
                        </a>
                        <x-primary-button>
                            {{ __('Save changes') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
