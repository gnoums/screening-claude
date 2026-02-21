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

            {{-- Enlace para compartir (aparece al crear/reenviar y al generar manualmente) --}}
            @if(!in_array($screening->status, ['completed', 'cancelled']))
            <div class="bg-white rounded-xl shadow p-6" id="share-panel">
                <h3 class="font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    Compartir enlace de evaluación
                </h3>

                @if(session('access_url'))
                <p class="text-xs text-gray-500 mb-2">Enlace generado. Cópialo y envíalo al paciente por el canal que prefieras.</p>
                @else
                <p class="text-xs text-gray-500 mb-2">Genera un nuevo enlace de acceso para compartirlo por WhatsApp u otra plataforma.</p>
                @endif

                {{-- Input con el URL (oculto hasta generar) --}}
                <div id="link-container" class="{{ session('access_url') ? '' : 'hidden' }} flex flex-col sm:flex-row gap-2 mb-3">
                    <input id="access-url-input" type="text" readonly
                           value="{{ session('access_url', '') }}"
                           class="flex-1 text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 text-gray-700 select-all focus:outline-none focus:ring-2 focus:ring-blue-300"/>
                    <button onclick="copyLink()"
                            class="flex items-center justify-center gap-1.5 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition shrink-0">
                        <svg id="copy-icon" xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span id="copy-label">Copiar</span>
                    </button>
                    <a id="whatsapp-btn" href="#" target="_blank" rel="noopener"
                       class="flex items-center justify-center gap-1.5 px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-semibold hover:bg-green-600 transition shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        WhatsApp
                    </a>
                    <p id="expires-label" class="text-xs text-gray-400 self-center sm:hidden"></p>
                </div>
                <p id="expires-label-sm" class="text-xs text-gray-400 mb-3 {{ session('access_url') ? '' : 'hidden' }}"></p>

                {{-- Botón para generar nuevo enlace --}}
                <button id="get-link-btn" onclick="generateLink()"
                        class="flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span id="get-link-label">{{ session('access_url') ? 'Generar nuevo enlace' : 'Generar enlace para compartir' }}</span>
                </button>
                <p class="text-xs text-amber-600 mt-2 {{ session('access_url') ? '' : 'hidden' }}" id="new-link-warning">
                    Generar un nuevo enlace invalida el anterior.
                </p>
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

@if(!in_array($screening->status, ['completed', 'cancelled']))
<script>
    const getLinkUrl  = "{{ route('psychologist.screenings.get-link', $screening) }}";
    const csrfToken   = "{{ csrf_token() }}";
    const patientName = "{{ addslashes($screening->patient->full_name) }}";

    @if(session('access_url'))
    // Si viene de crear/reenviar, inicializar WhatsApp con el URL de sesión
    document.addEventListener('DOMContentLoaded', function () {
        setLinkUI("{{ session('access_url') }}", null);
    });
    @endif

    function setLinkUI(url, expiresAt) {
        const input    = document.getElementById('access-url-input');
        const waBtn    = document.getElementById('whatsapp-btn');
        const container = document.getElementById('link-container');
        const warning  = document.getElementById('new-link-warning');
        const getLinkLabel = document.getElementById('get-link-label');

        input.value = url;
        container.classList.remove('hidden');
        warning.classList.remove('hidden');
        getLinkLabel.textContent = 'Generar nuevo enlace';

        const waMessage = `Hola ${patientName}, te comparto el enlace para tu evaluación psicológica: ${url}`;
        waBtn.href = 'https://wa.me/?text=' + encodeURIComponent(waMessage);

        if (expiresAt) {
            const expLabel = document.getElementById('expires-label-sm');
            expLabel.textContent = 'Expira el ' + expiresAt;
            expLabel.classList.remove('hidden');
        }
    }

    async function generateLink() {
        const btn   = document.getElementById('get-link-btn');
        const label = document.getElementById('get-link-label');
        const orig  = label.textContent;
        btn.disabled = true;
        label.textContent = 'Generando...';

        try {
            const resp = await fetch(getLinkUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            if (!resp.ok) {
                const err = await resp.json().catch(() => ({}));
                alert(err.message ?? 'Error al generar el enlace.');
                return;
            }

            const data = await resp.json();
            setLinkUI(data.url, data.expires_at);
        } catch (e) {
            alert('Error de conexión. Intenta de nuevo.');
        } finally {
            btn.disabled = false;
            label.textContent = document.getElementById('get-link-label').textContent === 'Generando...'
                ? orig : document.getElementById('get-link-label').textContent;
        }
    }

    async function copyLink() {
        const input = document.getElementById('access-url-input');
        const label = document.getElementById('copy-label');

        try {
            await navigator.clipboard.writeText(input.value);
            label.textContent = '¡Copiado!';
            setTimeout(() => label.textContent = 'Copiar', 2000);
        } catch {
            input.select();
            document.execCommand('copy');
            label.textContent = '¡Copiado!';
            setTimeout(() => label.textContent = 'Copiar', 2000);
        }
    }
</script>
@endif
</x-app-layout>
