<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PsicoScreen — Tamizajes psicológicos para profesionales</title>
    <meta name="description" content="Envía evaluaciones psicológicas validadas a tus pacientes con un enlace único. Obtén resultados automáticos y reportes en PDF en segundos.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="font-sans antialiased bg-white text-gray-900">

{{-- ================================================================
     NAVBAR
     ================================================================ --}}
<header class="sticky top-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <span class="text-lg font-bold text-gray-900">PsicoScreen</span>
        </div>
        <nav class="flex items-center gap-2">
            @auth
                <a href="{{ route('dashboard') }}"
                   class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                    Ir al panel →
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-900 transition">
                    Iniciar sesión
                </a>
                <a href="{{ route('register') }}"
                   class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 transition">
                    Crear cuenta gratis
                </a>
            @endauth
        </nav>
    </div>
</header>

{{-- ================================================================
     HERO
     ================================================================ --}}
<section class="relative overflow-hidden bg-gradient-to-br from-indigo-50 via-white to-blue-50 pt-20 pb-28">
    <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-100 rounded-full opacity-40 -translate-y-1/2 translate-x-1/3"></div>
    <div class="absolute bottom-0 left-0 w-72 h-72 bg-blue-100 rounded-full opacity-40 translate-y-1/3 -translate-x-1/4"></div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 bg-indigo-100 text-indigo-700 text-xs font-semibold px-3 py-1.5 rounded-full mb-6">
            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span>
            Plataforma para psicólogas y psicólogos
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight tracking-tight">
            Tamizajes psicológicos<br>
            <span class="text-indigo-600">simples, rápidos y seguros.</span>
        </h1>

        <p class="mt-6 text-lg sm:text-xl text-gray-500 max-w-2xl mx-auto leading-relaxed">
            Envía evaluaciones validadas (PHQ-9, GAD-7 y más) a tus pacientes con un enlace único.
            Obtén resultados automáticos y descarga el reporte en PDF en segundos.
        </p>

        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}"
               class="w-full sm:w-auto px-8 py-4 bg-indigo-600 text-white text-base font-bold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                Empieza gratis — sin tarjeta
            </a>
            <a href="#como-funciona"
               class="w-full sm:w-auto px-8 py-4 bg-white text-gray-700 text-base font-semibold rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                Ver cómo funciona ↓
            </a>
        </div>

        <p class="mt-4 text-xs text-gray-400">
            Sin contratos, sin mensualidades. Solo pagas por lo que usas.
        </p>
    </div>

    {{-- Preview de la plataforma --}}
    <div class="relative mt-16 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
            <div class="bg-gray-50 border-b border-gray-100 px-4 py-3 flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                <div class="flex-1 text-center">
                    <span class="text-xs text-gray-400 font-mono">psicoscreen.mx/dashboard</span>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                    <div class="bg-indigo-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-indigo-600">42</div>
                        <div class="text-xs text-gray-400 mt-1">Créditos</div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-gray-700">18</div>
                        <div class="text-xs text-gray-400 mt-1">Pacientes</div>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-yellow-600">3</div>
                        <div class="text-xs text-gray-400 mt-1">Pendientes</div>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-green-600">37</div>
                        <div class="text-xs text-gray-400 mt-1">Completadas</div>
                    </div>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center justify-between px-4 py-3 rounded-lg bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-xs font-bold">AG</div>
                            <span class="text-sm font-medium text-gray-700">Ana García</span>
                            <span class="text-xs text-gray-400">PHQ-9</span>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-700">Completado</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-xs font-bold">LP</div>
                            <span class="text-sm font-medium text-gray-700">Luis Pérez</span>
                            <span class="text-xs text-gray-400">GAD-7</span>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-blue-100 text-blue-700">En progreso</span>
                    </div>
                    <div class="flex items-center justify-between px-4 py-3 rounded-lg border border-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 text-xs font-bold">MT</div>
                            <span class="text-sm font-medium text-gray-700">María Torres</span>
                            <span class="text-xs text-gray-400">PHQ-9</span>
                        </div>
                        <span class="text-xs font-semibold px-2 py-1 rounded-full bg-yellow-100 text-yellow-700">Enviado</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
    </div>
</section>

{{-- ================================================================
     BENEFICIOS
     ================================================================ --}}
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl font-bold text-gray-900">Todo lo que necesitas, nada que sobre</h2>
            <p class="mt-3 text-gray-500 max-w-xl mx-auto">Diseñado específicamente para el flujo de trabajo de psicólogas clínicas.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="group p-6 rounded-2xl border border-gray-100 hover:border-indigo-200 hover:shadow-md transition">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition">
                    <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Enlace único por paciente</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Cada evaluación genera un token seguro con expiración. El paciente solo necesita el enlace, sin crear cuenta.
                </p>
            </div>

            <div class="group p-6 rounded-2xl border border-gray-100 hover:border-indigo-200 hover:shadow-md transition">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition">
                    <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Resultados automáticos</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    El sistema calcula el puntaje e interpreta el resultado según las reglas clínicas de cada prueba. Sin cálculos manuales.
                </p>
            </div>

            <div class="group p-6 rounded-2xl border border-gray-100 hover:border-indigo-200 hover:shadow-md transition">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition">
                    <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Reporte PDF listo para imprimir</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Descarga un reporte profesional con los resultados, puntaje, interpretación y datos del paciente. Ideal para expediente clínico.
                </p>
            </div>

            <div class="group p-6 rounded-2xl border border-gray-100 hover:border-indigo-200 hover:shadow-md transition">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition">
                    <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Envío por email automático</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    El sistema envía el enlace al correo del paciente en cuanto creas la evaluación. También puedes reenviar con un clic.
                </p>
            </div>

            <div class="group p-6 rounded-2xl border border-gray-100 hover:border-indigo-200 hover:shadow-md transition">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition">
                    <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Datos seguros y privados</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    Cada psicóloga solo ve sus propios pacientes. Los tokens expiran automáticamente y el acceso está protegido por contraseña.
                </p>
            </div>

            <div class="group p-6 rounded-2xl border border-gray-100 hover:border-indigo-200 hover:shadow-md transition">
                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-indigo-600 transition">
                    <svg class="w-6 h-6 text-indigo-600 group-hover:text-white transition" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">100% responsive para pacientes</h3>
                <p class="text-gray-500 text-sm leading-relaxed">
                    El formulario funciona perfecto en celular. El paciente responde desde donde esté, sin instalar nada ni crear una cuenta.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     CÓMO FUNCIONA
     ================================================================ --}}
<section id="como-funciona" class="py-20 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl font-bold text-gray-900">Tres pasos y listo</h2>
            <p class="mt-3 text-gray-500">De cero a resultados en menos de 5 minutos.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
            <div class="text-center">
                <div class="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-2xl font-extrabold mx-auto mb-5 shadow-lg shadow-indigo-200">
                    1
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Registra al paciente</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Agrega nombre, correo y los datos básicos del paciente. Solo te lleva 30 segundos.
                </p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-2xl font-extrabold mx-auto mb-5 shadow-lg shadow-indigo-200">
                    2
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Crea y envía la evaluación</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Elige la prueba (PHQ-9, GAD-7 u otras), crea la solicitud y el sistema envía el enlace al paciente por email automáticamente.
                </p>
            </div>

            <div class="text-center">
                <div class="w-16 h-16 bg-indigo-600 text-white rounded-2xl flex items-center justify-center text-2xl font-extrabold mx-auto mb-5 shadow-lg shadow-indigo-200">
                    3
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Revisa y descarga el reporte</h3>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Cuando el paciente termina, entra al panel, revisa el puntaje interpretado automáticamente y descarga el PDF.
                </p>
            </div>
        </div>
    </div>
</section>

{{-- ================================================================
     PRECIOS
     ================================================================ --}}
<section id="precios" class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900">Sin mensualidades. Solo paga lo que usas.</h2>
            <p class="mt-3 text-gray-500 max-w-xl mx-auto">
                Cada evaluación enviada consume 1 crédito. Compra el paquete que mejor se adapte a tu ritmo de trabajo.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Paquete básico --}}
            <div class="rounded-2xl border border-gray-200 p-7 flex flex-col">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Básico</div>
                <div class="flex items-end gap-1 mb-1">
                    <span class="text-4xl font-extrabold text-gray-900">$199</span>
                    <span class="text-gray-400 mb-1.5 text-sm">MXN</span>
                </div>
                <div class="text-indigo-600 font-semibold text-sm">10 créditos</div>
                <div class="text-xs text-gray-400 mt-0.5 mb-6">$19.90 por evaluación</div>
                <ul class="space-y-2.5 mb-8 flex-1 text-sm text-gray-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        10 evaluaciones
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Reporte PDF incluido
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Envío automático por email
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Sin fecha de expiración
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                   class="w-full py-3 text-center bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-700 transition text-sm">
                    Empezar
                </a>
            </div>

            {{-- Paquete popular --}}
            <div class="rounded-2xl border-2 border-indigo-500 p-7 flex flex-col relative shadow-xl shadow-indigo-100">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs font-bold px-4 py-1.5 rounded-full whitespace-nowrap">
                    MÁS POPULAR
                </div>
                <div class="text-xs font-semibold text-indigo-500 uppercase tracking-widest mb-2">Profesional</div>
                <div class="flex items-end gap-1 mb-1">
                    <span class="text-4xl font-extrabold text-gray-900">$499</span>
                    <span class="text-gray-400 mb-1.5 text-sm">MXN</span>
                </div>
                <div class="text-indigo-600 font-semibold text-sm">30 créditos</div>
                <div class="text-xs text-gray-400 mt-0.5 mb-6">$16.63 por evaluación — ahorra 16%</div>
                <ul class="space-y-2.5 mb-8 flex-1 text-sm text-gray-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        30 evaluaciones
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Reporte PDF incluido
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Envío automático por email
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Sin fecha de expiración
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Múltiples pruebas por solicitud
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                   class="w-full py-3 text-center bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition text-sm">
                    Empezar
                </a>
            </div>

            {{-- Paquete clínica --}}
            <div class="rounded-2xl border border-gray-200 p-7 flex flex-col">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-2">Clínica</div>
                <div class="flex items-end gap-1 mb-1">
                    <span class="text-4xl font-extrabold text-gray-900">$1,499</span>
                    <span class="text-gray-400 mb-1.5 text-sm">MXN</span>
                </div>
                <div class="text-indigo-600 font-semibold text-sm">100 créditos</div>
                <div class="text-xs text-gray-400 mt-0.5 mb-6">$14.99 por evaluación — mejor precio</div>
                <ul class="space-y-2.5 mb-8 flex-1 text-sm text-gray-600">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        100 evaluaciones
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Reporte PDF incluido
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Envío automático por email
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Sin fecha de expiración
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Múltiples pruebas por solicitud
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Ideal para equipos y clínicas
                    </li>
                </ul>
                <a href="{{ route('register') }}"
                   class="w-full py-3 text-center bg-gray-900 text-white font-semibold rounded-xl hover:bg-gray-700 transition text-sm">
                    Empezar
                </a>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            Pago seguro procesado por Stripe. Aceptamos tarjetas de crédito y débito.
        </p>
    </div>
</section>

{{-- ================================================================
     CTA FINAL
     ================================================================ --}}
<section class="py-20 bg-indigo-600">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl font-extrabold text-white">
            Empieza a usar PsicoScreen hoy
        </h2>
        <p class="mt-4 text-indigo-200 text-lg">
            Crea tu cuenta en 30 segundos. Sin tarjeta de crédito.
        </p>
        <a href="{{ route('register') }}"
           class="inline-block mt-8 px-10 py-4 bg-white text-indigo-700 text-base font-bold rounded-xl hover:bg-indigo-50 transition shadow-lg">
            Crear cuenta gratis →
        </a>
    </div>
</section>

{{-- ================================================================
     FOOTER
     ================================================================ --}}
<footer class="bg-gray-900 text-gray-400 py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <div class="w-6 h-6 bg-indigo-500 rounded-md flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <span class="text-white font-semibold text-sm">PsicoScreen</span>
        </div>
        <p class="text-xs text-center">
            &copy; {{ date('Y') }} PsicoScreen. Todos los derechos reservados.
        </p>
        <div class="flex gap-4 text-xs">
            <a href="{{ route('login') }}" class="hover:text-white transition">Iniciar sesión</a>
            <a href="{{ route('register') }}" class="hover:text-white transition">Registrarse</a>
        </div>
    </div>
</footer>

</body>
</html>
