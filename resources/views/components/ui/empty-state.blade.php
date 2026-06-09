@props([
    'icon'        => 'inbox',
    'title'       => 'Belum ada data',
    'description' => null,
    'actionLabel' => null,
    'actionHref'  => null,
    'actionMethod'=> 'GET',
])

<div class="sw-empty">
    <div class="sw-empty-icon">
        <i data-lucide="{{ $icon }}"></i>
    </div>
    <div class="sw-empty-title">{{ $title }}</div>
    @if ($description)
        <div class="sw-empty-desc">{{ $description }}</div>
    @endif
    @if ($actionLabel && $actionHref)
        @if ($actionMethod === 'POST')
            <form method="POST" action="{{ $actionHref }}">
                @csrf
                <button type="submit" class="sw-btn sw-btn-primary" style="margin-top:4px;">
                    {{ $actionLabel }}
                </button>
            </form>
        @else
            <a href="{{ $actionHref }}" class="sw-btn sw-btn-primary" style="margin-top:4px;">
                {{ $actionLabel }}
            </a>
        @endif
    @endif
    {{ $slot }}
</div>
