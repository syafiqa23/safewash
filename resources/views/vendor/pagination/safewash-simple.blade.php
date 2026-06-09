@if ($paginator->hasPages())
<nav aria-label="Pagination" style="display:flex;align-items:center;gap:8px;padding:4px 0;">

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

</nav>
@endif
