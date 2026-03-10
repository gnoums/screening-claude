<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.assessments.show', $assessment) }}" class="text-gray-400 hover:text-gray-600">← {{ $assessment->name }}</a>
            <h2 class="font-semibold text-xl text-gray-800">{{ __('Edit screening test') }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow p-6">
                @if($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">
                        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.assessments.update', $assessment) }}" class="space-y-4">
                    @csrf @method('PATCH')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" value="{{ $assessment->slug }}" disabled
                               class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-400">
                        <p class="text-xs text-gray-400 mt-1">{{ __('Slug cannot be changed to maintain data integrity.') }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Name') }} *</label>
                            <input type="text" name="name" value="{{ old('name', $assessment->name) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Abbreviation') }}</label>
                            <input type="text" name="short_name" value="{{ old('short_name', $assessment->short_name) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Description') }}</label>
                        <textarea name="description" rows="2"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">{{ old('description', $assessment->description) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Author') }}</label>
                            <input type="text" name="author" value="{{ old('author', $assessment->author) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Version') }}</label>
                            <input type="text" name="version" value="{{ old('version', $assessment->version) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Estimated minutes') }} *</label>
                            <input type="number" name="estimated_minutes" min="1" max="120"
                                   value="{{ old('estimated_minutes', $assessment->estimated_minutes) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Credits cost') }} *</label>
                            <input type="number" name="credits_cost" min="1"
                                   value="{{ old('credits_cost', $assessment->credits_cost) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:outline-none" required>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               {{ old('is_active', $assessment->is_active) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-primary-600">
                        <label for="is_active" class="text-sm text-gray-700">{{ __('Active') }}</label>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="submit"
                                class="px-6 py-2 bg-primary-600 text-white rounded-lg font-semibold hover:bg-primary-700 transition">
                            {{ __('Save changes') }}
                        </button>
                        <a href="{{ route('admin.assessments.show', $assessment) }}"
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
