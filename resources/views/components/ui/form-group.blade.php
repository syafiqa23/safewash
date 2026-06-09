@props([
    'label'    => null,
    'for'      => null,
    'hint'     => null,
    'error'    => null,
    'required' => false,
    'icon'     => null,
])

<div class="sw-form-group">
    @if ($label)
        <label class="sw-label" @if($for) for="{{ $for }}" @endif>
            @if ($icon)
                <i data-lucide="{{ $icon }}" style="width:12px;height:12px;"></i>
            @endif
            {{ $label }}
            @if ($required)
                <span style="color:var(--danger); margin-left:2px;">*</span>
            @endif
        </label>
    @endif

    @if ($icon && !$label)
        <div class="sw-input-icon">
            <i data-lucide="{{ $icon }}"></i>
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif

    @if ($hint && !$error)
        <div class="sw-hint">{{ $hint }}</div>
    @endif
    @if ($error)
        <div class="sw-error-msg">{{ $error }}</div>
    @endif
</div>
