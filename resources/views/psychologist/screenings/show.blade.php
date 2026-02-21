<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('psychologist.screenings.index') }}"
                   class="text-gray-400 hover:text-gray-600 text-sm">← Evaluaciones</a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $screening->patient->full_name }}
                </h2>
            </div>
            <div class="flex items-center gap-2">
                @if($screening->isCompleted())
                    <a href="{{ route('psychologist.screenings.pdf', $screening) }}"
                       class="px-4 py-2 bg-green-600 text-white rounded-lg font-semibold text-sm hover:bg-green-700 transition">
                        Descargar PDF
                    </a>
                @elseif(!in_array($screening->status, ['completed', 'cancelled']))
                    <form method="POST" action="{{ route('psychologist.screenings.resend', $screening) }}">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-50 transition">
                            Reenviar enlace
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Resumen de la solicitud --}}
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="font-semibold text-gray-700 mb-4">Detalles de la evaluación</h3>
                @php
                    $badges = [
                        'draft'               => ['bg-gray-100 text-gray-600', 'Borrador'],
                        'sent'                => ['bg-yellow-100 text-yellow-700', 'Enviado'],
                        'partially_completed' => ['bg-blue-100 text-blue-700', 'En progreso'],
                        'completed'           => ['bg-green-100 text-green-700', 'Completado'],
                        'expired'             => ['bg-red-100 text-red-600', 'Expirado'],
                        'cancelled'           => ['bg-gray-100 text-gray-500', 'Cancelado'],
                    ];
                    [$cls, $label] = $badges[$screening->status] ?? ['bg-gray-100 text-gray-600', $screening->status];
                @endphp
                <dl class="grid grid-cols-2 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Estado</dt>
                        <dd class="mt-0.5">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $cls }}">{{ $label }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Paciente</dt>
                        <dd class="text-gray-800 mt-0.5">
                            <a href="{{ route('psychologist.patients.show', $screening->patient) }}"
                               class="text-blue-600 hover:underline">
                                {{ $screening->patient->full_name }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Email destinatario</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $screening->recipient_email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Enviado</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $screening->sent_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Completado</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $screening->completed_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Créditos usados</dt>
                        <dd class="text-gray-800 mt-0.5">{{ $screening->credits_charged }}</dd>
                    </div>
                </dl>

                @if($screening->message_to_patient)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <dt class="text-gray-500 text-sm mb-1">Mensaje enviado</dt>
                        <dd class="text-gray-700 text-sm italic">{{ $screening->message_to_patient }}</dd>
                    </div>
                @endif
            </div>

            {{-- Resultados por prueba --}}
            @foreach($screening->items as $item)
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-700">{{ $item->assessment->name ?? 'Prueba' }}</h3>
                        @php
                            $itemBadges = [
                                'pending'   => ['bg-gray-100 text-gray-500', 'Pendiente'],
                                'completed' => ['bg-green-100 text-green-700', 'Completado'],
                            ];
                            [$ic, $il] = $itemBadges[$item->status] ?? ['bg-gray-100 text-gray-500', $item->status];
                        @endphp
                        <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $ic }}">{{ $il }}</span>
                    </div>

                    @if($item->response)
                        <div class="px-6 py-5">
                            {{-- Puntaje e interpretación --}}
                            <div class="flex items-center gap-6 mb-4">
                                <div class="text-center">
                                    <div class="text-3xl font-bold text-blue-600">
                                        {{ $item->response->total_score }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">Puntaje total</div>
                                </div>
                                @if($item->response->interpretation)
                                    <div class="flex-1 px-4 py-3 bg-blue-50 rounded-lg">
                                        <div class="text-sm font-semibold text-blue-800">
                                            {{ $item->response->interpretation }}
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Respuestas detalladas --}}
                            @if($item->response->answers->isNotEmpty())
                                <details class="mt-2">
                                    <summary class="text-sm text-gray-500 cursor-pointer hover:text-gray-700">
                                        Ver respuestas detalladas
                                    </summary>
                                    <div class="mt-3 space-y-2">
                                        @foreach($item->response->answers as $answer)
                                            <div class="flex items-start justify-between text-sm py-2 border-b border-gray-50">
                                                <span class="text-gray-700 flex-1 pr-4">
                                                    {{ $answer->question->text ?? '—' }}
                                                </span>
                                                <div class="text-right shrink-0">
                                                    <span class="text-gray-600">{{ $answer->option_text_snapshot ?? $answer->option->text ?? '—' }}</span>
                                                    <span class="ml-2 text-xs text-gray-400">({{ $answer->score_value_snapshot }} pts)</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </div>
                    @else
                        <div class="px-6 py-8 text-center text-gray-400 text-sm">
                            El paciente aún no ha completado esta prueba.
                        </div>
                    @endif
                </div>
            @endforeach

        </div>
    </div>
</x-app-layout>
