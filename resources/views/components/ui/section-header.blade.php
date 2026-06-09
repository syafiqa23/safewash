@props([
    'title'       => '',
    'description' => null,
    'icon'        => null,
    'badge'       => null,
])

<div class="sw-section-hdr">
    <div>
        <div class="sw-flex sw-items-center sw-gap-8">
            @if ($icon)
                <i data-lucide="{{ $icon }}" style="width:16px;height:16px;color:var(--accent);flex-shrink:0;"></i>
            @endif
            <span class="sw-section-title">{{ $title }}</span>
            @if ($badge)
                <span class="sw-badge sw-badge-gray" style="font-size:11px;">{{ $badge }}</span>
            @endif
        </div>
        @if ($description)
            <div class="sw-section-desc">{{ $description }}</div>
        @endif
    </div>
    @if ($slot->isNotEmpty())
        <div class="sw-flex sw-items-center sw-gap-8" style="flex-shrink:0;">
            {{ $slot }}
        </div>
    @endif
</div>
