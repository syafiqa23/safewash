@props([
    'title'       => null,
    'icon'        => null,
    'badge'       => null,
    'actions'     => null,
    'emptyIcon'   => 'inbox',
    'emptyTitle'  => 'Belum ada data',
    'emptyDesc'   => null,
    'striped'     => false,
])

<div class="sw-table-wrap">
    @if ($title)
        <div class="sw-table-head">
            <div style="display:flex; align-items:center; gap:8px;">
                @if ($icon)
                    <i data-lucide="{{ $icon }}" style="width:16px;height:16px;color:var(--accent);flex-shrink:0;"></i>
                @endif
                <span class="sw-section-title">{{ $title }}</span>
                @if ($badge)
                    <span class="sw-badge sw-badge-gray" style="font-size:11px;">{{ $badge }}</span>
                @endif
            </div>
            @if (isset($actions))
                <div style="display:flex; align-items:center; gap:8px;">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div style="overflow-x:auto;">
        <table class="sw-table{{ $striped ? ' sw-table-striped' : '' }}">
            {{ $slot }}
        </table>
    </div>
</div>
