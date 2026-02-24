<svg viewBox="0 0 220 44" xmlns="http://www.w3.org/2000/svg" {{ $attributes->merge(['class' => 'block h-9 w-auto']) }}>
    {{-- Manta ray body --}}
    <path style="fill:#5a7123" d="M44,22 C37,10 22,5 6,13 L0,22 L6,31 C22,39 37,34 44,22Z"/>
    {{-- Upper wing --}}
    <path style="fill:#45581c" d="M6,13 C11,4 22,0 31,5 L35,14 Z"/>
    {{-- Lower wing --}}
    <path style="fill:#45581c" d="M6,31 C11,40 22,44 31,39 L35,30 Z"/>
    {{-- Tail --}}
    <path style="fill:#5a7123" d="M44,22 L52,18 L56,22 L52,26 Z"/>
    {{-- Belly highlight --}}
    <ellipse style="fill:#c9b89a;opacity:0.65" cx="29" cy="22" rx="9" ry="6"/>
    {{-- App name --}}
    <text x="62" y="16" style="font-family:Figtree,system-ui,sans-serif;font-weight:700;font-size:13px;fill:#374151">My Progress</text>
    <text x="62" y="33" style="font-family:Figtree,system-ui,sans-serif;font-weight:700;font-size:13px;fill:#374151">Matters</text>
</svg>
