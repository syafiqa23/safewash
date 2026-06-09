@props([
    'icon'     => 'activity',
    'label'    => '',
    'value'    => '0',
    'trend'    => null,
    'trendDir' => 'up',
    'sub'      => null,
    'color'    => 'accent',
    'href'     => null,
])

@php
    $iconColors = [
        'accent' => 'var(--accent)',
        'green'  => 'var(--success)',
        'amber'  => 'var(--warning)',
        'red'    => 'var(--danger)',
        'info'   => 'var(--info)',
        'purple' => 'var(--accent)',
    ];
    $iconBgs = [
        'accent' => 'var(--accent-light)',
        'green'  => '#dcfce7',
        'amber'  => '#fef3c7',
        'red'    => '#fee2e2',
        'info'   => '#e0f2fe',
        'purple' => 'var(--accent-light)',
    ];
    $ic = $iconColors[$color] ?? $iconColors['accent'];
    $ib = $iconBgs[$color]    ?? $iconBgs['accent'];
@endphp

<div class="sw-kpi{{ $href ? ' sw-kpi-link' : '' }}"
    @if($href) onclick="window.location='{{ $href }}'" style="cursor:pointer;" @endif
>
    <div class="sw-kpi-top">
        <div class="sw-kpi-icon" style="background:{{ $ib }};">
            <i data-lucide="{{ $icon }}" style="color:{{ $ic }};"></i>
        </div>
        @if ($trend)
            <div class="sw-kpi-trend {{ $trendDir }}">
                <i data-lucide="{{ $trendDir === 'up' ? 'trending-up' : ($trendDir === 'down' ? 'trending-down' : 'minus') }}"></i>
                {{ $trend }}
            </div>
        @endif
    </div>
    <div>
        <div class="sw-kpi-label">{{ $label }}</div>
        <div class="sw-kpi-value">{{ $value }}</div>
        @if ($sub)
            <div class="sw-kpi-sub sw-mt-4">{{ $sub }}</div>
        @endif
    </div>
</div>
