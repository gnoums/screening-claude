<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.assessments.index') }}" class="text-gray-400 hover:text-gray-600">← Pruebas</a>
            <h2 class="font-semibold text-xl text-gray-800">{{ $assessment->name }}</h2>
            <a href="{{ route('admin.assessments.edit', $assessment) }}"
               class="text-sm text-blue-600 hover:underline">Editar info</a>
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

            {{-- ---- PREGUNTAS ---- --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-semibold text-gray-800">Preguntas ({{ $assessment->questions->count() }})</h3>
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
                                  onsubmit="return confirm('¿Eliminar esta pregunta y sus opciones?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-red-500 hover:text-red-700">Eliminar</button>
                            </form>
                        </div>

                        {{-- Opciones de la pregunta --}}
                        <div class="ml-5 mt-2 space-y-1">
                            @foreach($question->options as $option)
                                <div class="flex items-center justify-between text-xs text-gray-600 bg-gray-50 rounded px-3 py-1">
                                    <span>{{ $option->option_text }}</span>
                                    <div class="flex items-center gap-3">
                                        <span class="font-semibold text-blue-600">+{{ $option->score_value }} pts</span>
                                        <form method="POST" action="{{ route('admin.assessments.options.destroy', $option) }}"
                                              onsubmit="return confirm('¿Eliminar opción?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-600">✕</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach

                            {{-- Añadir opción --}}
                            <form method="POST" action="{{ route('admin.assessments.options.store', $question) }}"
                                  class="flex gap-2 mt-2">
                                @csrf
                                <input type="text" name="option_text" placeholder="Texto de la opción" required
                                       class="flex-1 border border-gray-200 rounded px-2 py-1 text-xs">
                                <input type="number" name="score_value" placeholder="Pts" required
                                       class="w-16 border border-gray-200 rounded px-2 py-1 text-xs text-center">
                                <button type="submit"
                                        class="px-3 py-1 bg-gray-700 text-white rounded text-xs hover:bg-gray-800">
                                    + Opción
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach

                {{-- Añadir pregunta --}}
                <div class="px-6 py-4 bg-gray-50">
                    <form method="POST" action="{{ route('admin.assessments.questions.store', $assessment) }}"
                          class="space-y-2">
                        @csrf
                        <div class="flex gap-2">
                            <input type="text" name="question_text" placeholder="Texto de la pregunta (ítem)" required
                                   class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <input type="text" name="question_code" placeholder="Código (ej: PHQ1)"
                                   class="w-28 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                                + Pregunta
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- ---- REGLAS DE INTERPRETACIÓN ---- --}}
            <div class="bg-white rounded-xl shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Reglas de Interpretación</h3>
                </div>

                @foreach($assessment->rules as $rule)
                    <div class="px-6 py-3 border-b border-gray-50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            @if($rule->color_code)
                                <div class="w-4 h-4 rounded-full" style="background:{{ $rule->color_code }}"></div>
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
                              onsubmit="return confirm('¿Eliminar esta regla?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Eliminar</button>
                        </form>
                    </div>
                @endforeach

                {{-- Añadir regla --}}
                <div class="px-6 py-4 bg-gray-50">
                    <form method="POST" action="{{ route('admin.assessments.rules.store', $assessment) }}"
                          class="grid grid-cols-2 gap-2">
                        @csrf
                        <input type="number" name="min_score" placeholder="Puntaje mínimo" required
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="number" name="max_score" placeholder="Puntaje máximo" required
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="text" name="severity_label" placeholder="Etiqueta (Mínima, Leve…)" required
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <input type="text" name="color_code" placeholder="Color hex (#4CAF50)"
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <textarea name="interpretation_text" placeholder="Texto de interpretación clínica" required
                                  class="col-span-2 border border-gray-300 rounded-lg px-3 py-2 text-sm" rows="2"></textarea>
                        <button type="submit"
                                class="col-span-2 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                            + Añadir regla
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
