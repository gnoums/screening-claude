<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.assessments.index') }}" class="text-gray-400 hover:text-gray-600">← {{ __('Screening Tests') }}</a>
            <h2 class="font-semibold text-xl text-gray-800">{{ $assessment->name }}</h2>
            <a href="{{ route('admin.assessments.edit', $assessment) }}"
               class="text-sm text-primary-600 hover:underline">{{ __('Edit info') }}</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                </div>
            @endif

            {{-- QUESTIONS --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">{{ __('Questions') }} ({{ $assessment->questions->count() }})</h3>
                </div>

                @foreach($assessment->questions as $question)
                    <div class="px-6 py-4 border-b border-gray-50">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <span class="text-xs text-gray-400 mr-2">#{{ $question->order }}</span>
                                <span class="text-sm text-gray-800">{{ $question->question_text }}</span>
                                @if($question->question_code)
                                    <span class="ml-2 text-xs font-mono bg-gray-100 px-1 rounded">{{ $question->question_code }}</span>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.assessments.questions.destroy', $question) }}" class="ml-4"
                                  onsubmit="return confirm('{{ __('Delete this question and its options?') }}')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">{{ __('Delete') }}</button>
                            </form>
                        </div>

                        {{-- Question options --}}
                        <div class="ml-5 mt-2 space-y-1">
                            @foreach($question->options as $option)
                                <div class="flex items-center justify-between text-xs text-gray-600 bg-gray-50 rounded px-3 py-1">
                                    <span>{{ $option->option_text }}</span>
                                    <div class="flex items-center gap-3">
                                        <span class="font-semibold text-primary-600">+{{ $option->score_value }} pts</span>
                                        <form method="POST" action="{{ route('admin.assessments.options.destroy', $option) }}"
                                              onsubmit="return confirm('{{ __('Delete option?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600">✕</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Add option --}}
                            <form method="POST" action="{{ route('admin.assessments.options.store', $question) }}"
                                  class="flex gap-2 mt-2">
                                @csrf
                                <input type="text" name="option_text" placeholder="{{ __('Option text') }}" required
                                       class="flex-1 border border-gray-200 rounded px-2 py-1 text-xs">
                                <input type="number" name="score_value" placeholder="Pts" required
                                       class="w-16 border border-gray-200 rounded px-2 py-1 text-xs text-center">
                                <button type="submit"
                                        class="px-3 py-1 bg-gray-700 text-white rounded text-xs hover:bg-gray-800">
                                    + {{ __('Option') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Add question --}}
                <div class="px-6 py-4 bg-gray-50">
                    <form method="POST" action="{{ route('admin.assessments.questions.store', $assessment) }}"
                          class="space-y-2">
                        @csrf
                        <div class="flex gap-2">
                            <input type="text" name="question_text" placeholder="{{ __('Question text (item)') }}" required
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <input type="text" name="question_code" placeholder="{{ __('Code (e.g. PHQ1)') }}"
                                   class="w-28 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <button type="submit"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700">
                                + {{ __('Question') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- INTERPRETATION RULES --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">{{ __('Interpretation Rules') }}</h3>
                </div>

                @foreach($assessment->rules as $rule)
                    <div class="px-6 py-3 border-b border-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if($rule->color_code)
                                <div class="w-4 h-4 rounded-full border-0 flex-shrink-0" style="background:{{ $rule->color_code }}"></div>
                            @endif
                            <div>
                                <span class="text-sm font-semibold text-gray-800">{{ $rule->severity_label }}</span>
                                <span class="text-xs text-gray-400 ml-2">
                                    {{ $rule->min_score }} – {{ $rule->max_score }} pts
                                </span>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $rule->interpretation_text }}</div>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.assessments.rules.destroy', $rule) }}"
                              onsubmit="return confirm('{{ __('Delete this rule?') }}')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">{{ __('Delete') }}</button>
                        </form>
                    </div>
                @endforeach

                {{-- Add rule --}}
                <div class="px-6 py-4 bg-gray-50">
                    <form method="POST" action="{{ route('admin.assessments.rules.store', $assessment) }}"
                          class="grid grid-cols-2 gap-2">
                        @csrf
                        <input type="number" name="min_score" placeholder="{{ __('Min score') }}" required
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="number" name="max_score" placeholder="{{ __('Max score') }}" required
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="text" name="severity_label" placeholder="{{ __('Label (Minimal, Mild…)') }}" required
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <div x-data="{
                                open: false,
                                selected: { label: '— {{ __('Color') }} —', value: '', color: '' },
                                dropTop: 0, dropLeft: 0, dropWidth: 0,
                                options: [
                                    { value: '#4CAF50', color: '#4CAF50', label: '{{ __('Green — Minimal') }}' },
                                    { value: '#8BC34A', color: '#8BC34A', label: '{{ __('Light green — Mild') }}' },
                                    { value: '#FFC107', color: '#FFC107', label: '{{ __('Amber — Moderate') }}' },
                                    { value: '#FF9800', color: '#FF9800', label: '{{ __('Orange — Mod. severe') }}' },
                                    { value: '#F44336', color: '#F44336', label: '{{ __('Red — Severe') }}' }
                                ],
                                toggle() {
                                    const rect = this.$refs.btn.getBoundingClientRect();
                                    this.dropWidth = rect.width;
                                    this.dropLeft  = rect.left;
                                    this.dropTop   = rect.top - (5 * 36 + 8);
                                    this.open = !this.open;
                                }
                             }" class="relative">
                            <input type="hidden" name="color_code" :value="selected.value">
                            <button x-ref="btn" type="button" @click="toggle()"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm flex items-center gap-2 bg-white text-left h-[38px]">
                                <span x-show="selected.color" class="w-4 h-4 rounded-full border-0 flex-shrink-0"
                                      :style="'background:' + selected.color"></span>
                                <span x-text="selected.label" class="flex-1 text-gray-700"></span>
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <template x-teleport="body">
                                <div x-show="open" @click.outside="open = false" x-cloak
                                     :style="'position:fixed; z-index:9999; top:' + dropTop + 'px; left:' + dropLeft + 'px; width:' + dropWidth + 'px;'"
                                     class="bg-white border border-gray-200 rounded-lg shadow-xl py-1">
                                    <template x-for="opt in options" :key="opt.value">
                                        <button type="button" @click="selected = opt; open = false"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-gray-50 text-left">
                                            <span class="w-4 h-4 rounded-full border-0 flex-shrink-0"
                                                  :style="'background:' + opt.color"></span>
                                            <span x-text="opt.label" class="text-gray-700"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <textarea name="interpretation_text" placeholder="{{ __('Clinical interpretation text') }}" required
                                  class="col-span-2 border border-gray-300 rounded-lg px-3 py-2 text-sm" rows="2"></textarea>
                        <button type="submit"
                                class="col-span-2 py-2 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700">
                            + {{ __('Add rule') }}
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
