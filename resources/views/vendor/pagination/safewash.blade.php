@if ($paginator->hasPages())
<nav aria-label="Pagination" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:4px 0;">

    <div style="font-size:12.5px;color:var(--muted);">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ number_format($paginator->total()) }} data
    </div>

    <div style="display:flex;align-items:center;gap:4px;">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span style="display:inline-flex;align-items:center;padding:5px 12px;border-radius:var(--r-md);font-size:12.5px;font-weight:600;color:var(--subtle);background:var(--surface-2);border:1px solid var(--line);cursor:default;user-select:none;">
                ‹ Prev
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
               style="display:inline-flex;align-items:center;padding:5px 12px;border-radius:var(--r-md);font-size:12.5px;font-weight:600;color:var(--ink);background:var(--surface);border:1px solid var(--line);text-decoration:none;transition:background var(--transition-fast);"
               onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='var(--surface)'">
                ‹ Prev
            </a>
        @endif

        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="padding:5px 4px;font-size:13px;color:var(--subtle);">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page" style="display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 6px;border-radius:var(--r-md);font-size:12.5px;font-weight:700;background:var(--accent);color:#fff;border:1px solid var(--accent);">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                           style="display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 6px;border-radius:var(--r-md);font-size:12.5px;font-weight:600;color:var(--ink);background:var(--surface);border:1px solid var(--line);text-decoration:none;transition:background var(--transition-fast);"
                           onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='var(--surface)'">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
               style="display:inline-flex;align-items:center;padding:5px 12px;border-radius:var(--r-md);font-size:12.5px;font-weight:600;color:var(--ink);background:var(--surface);border:1px solid var(--line);text-decoration:none;transition:background var(--transition-fast);"
               onmouseover="this.style.background='var(--surface-2)'" onmouseout="this.style.background='var(--surface)'">
                Next ›
            </a>
        @else
            <span style="display:inline-flex;align-items:center;padding:5px 12px;border-radius:var(--r-md);font-size:12.5px;font-weight:600;color:var(--subtle);background:var(--surface-2);border:1px solid var(--line);cursor:default;user-select:none;">
                Next ›
            </span>
        @endif

    </div>
</nav>
@endif
