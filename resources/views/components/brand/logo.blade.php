@props([
    'size'         => 'md',
    'withWordmark' => true,
    'stacked'      => false,
    'class'        => '',
    'variant'      => 'light', // 'light' = dark text on light bg | 'dark' = white text on dark bg
])

@php
    $sizes = [
        'xs' => ['icon' => 20, 'text' => '13px', 'gap' => '6px',  'sub' => '9px'],
        'sm' => ['icon' => 28, 'text' => '17px', 'gap' => '8px',  'sub' => '10px'],
        'md' => ['icon' => 34, 'text' => '21px', 'gap' => '10px', 'sub' => '11px'],
        'lg' => ['icon' => 46, 'text' => '28px', 'gap' => '12px', 'sub' => '13px'],
        'xl' => ['icon' => 64, 'text' => '40px', 'gap' => '16px', 'sub' => '16px'],
    ];

    $s      = $sizes[$size] ?? $sizes['md'];
    $iconPx = $s['icon'];
    $textPx = $s['text'];
    $subPx  = $s['sub'];
    $gap    = $s['gap'];
    $dir    = $stacked ? 'column' : 'row';
    $align  = $stacked ? 'center' : 'center';

    $uid = 'sw' . substr(md5(uniqid('', true)), 0, 8);
@endphp

<span
    {{ $attributes }}
    style="display:inline-flex; flex-direction:{{ $dir }}; align-items:{{ $align }}; gap:{{ $gap }}; line-height:1; user-select:none;"
    aria-label="SafeWash"
>

    {{-- ── Logomark ────────────────────────────────────────────────────────── --}}
    <svg
        width="{{ $iconPx }}"
        height="{{ round($iconPx * 1.15) }}"
        viewBox="0 0 32 37"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
        focusable="false"
    >
        <defs>
            <linearGradient id="drop-{{ $uid }}" x1="4" y1="0" x2="28" y2="37" gradientUnits="userSpaceOnUse">
                <stop offset="0%"   stop-color="#60C8FF"/>
                <stop offset="55%"  stop-color="#2563EB"/>
                <stop offset="100%" stop-color="#1340B0"/>
            </linearGradient>
            <linearGradient id="shd-{{ $uid }}" x1="12" y1="10" x2="20" y2="31" gradientUnits="userSpaceOnUse">
                <stop offset="0%"   stop-color="#ffffff" stop-opacity="0.35"/>
                <stop offset="100%" stop-color="#ffffff" stop-opacity="0.06"/>
            </linearGradient>
            <filter id="glow-{{ $uid }}" x="-20%" y="-20%" width="140%" height="140%">
                <feGaussianBlur stdDeviation="1.2" result="blur"/>
                <feMerge><feMergeNode in="blur"/><feMergeNode in="SourceGraphic"/></feMerge>
            </filter>
        </defs>

        {{-- Teardrop body --}}
        <path
            d="M16 1.5 C13.2 5.2, 5.5 12, 5.5 21.2 C5.5 29.1 10.2 35.5 16 35.5 C21.8 35.5 26.5 29.1 26.5 21.2 C26.5 12 18.8 5.2 16 1.5 Z"
            fill="url(#drop-{{ $uid }})"
            filter="url(#glow-{{ $uid }})"
        />

        {{-- Inner shine overlay --}}
        <path
            d="M16 5 C14.5 7.5 10 13 10 21 C10 21.5 10 22 10.1 22.5 C11 17 13.5 12 16 8.5 C18.5 12 21 17 21.9 22.5 C22 22 22 21.5 22 21 C22 13 17.5 7.5 16 5 Z"
            fill="white"
            opacity="0.13"
        />

        {{-- Shield fill --}}
        <path
            d="M16 10.5 L11.2 13.2 L11.2 20.8 C11.2 25.5 13.3 29 16 30.5 C18.7 29 20.8 25.5 20.8 20.8 L20.8 13.2 Z"
            fill="url(#shd-{{ $uid }})"
            stroke="rgba(255,255,255,0.55)"
            stroke-width="1.1"
            stroke-linejoin="round"
        />

        {{-- Checkmark --}}
        <path
            d="M13.2 21.2 L15.4 23.8 L20.2 17.5"
            stroke="white"
            stroke-width="2.1"
            stroke-linecap="round"
            stroke-linejoin="round"
        />

        {{-- Sparkle (top right, 4-point star) --}}
        <path
            d="M25.8 3.5 L26.3 5.1 L27.9 5.5 L26.3 5.9 L25.8 7.5 L25.3 5.9 L23.7 5.5 L25.3 5.1 Z"
            fill="white"
            opacity="0.95"
        />

        {{-- Accent dots --}}
        <circle cx="28.5" cy="11.5" r="0.9"  fill="white" opacity="0.72"/>
        <circle cx="7.5"  cy="5"    r="0.65" fill="white" opacity="0.58"/>
    </svg>

    {{-- ── Wordmark ─────────────────────────────────────────────────────────── --}}
    @if ($withWordmark)
        @php
            $safeColor = $variant === 'dark' ? '#ffffff' : '#0F1F3D';
            $washColor = $variant === 'dark' ? '#93c5fd' : '#2563EB';
            $subColor  = $variant === 'dark' ? 'rgba(255,255,255,0.55)' : '#64748b';
        @endphp
        <span style="display:inline-flex; flex-direction:column; gap:0; line-height:1;">
            <span style="font-size:{{ $textPx }}; font-weight:800; letter-spacing:-0.03em; line-height:1;">
                <span style="color:{{ $safeColor }};">Safe</span><span style="color:{{ $washColor }};">Wash</span>
            </span>
            @if (in_array($size, ['md', 'lg', 'xl']))
                <span style="font-size:{{ $subPx }}; font-weight:500; letter-spacing:0.12em; color:{{ $subColor }}; text-transform:uppercase; line-height:1.4;">Laundry Platform</span>
            @endif
        </span>
    @endif

</span>
