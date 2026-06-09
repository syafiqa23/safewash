<x-layouts.sidebar :title="'Claim Center'" :heading="'Claim Center'">

{{-- ─── Summary KPIs ─────────────────────────────────────────────────────── --}}
<div class="sw-kpi-grid sw-mb-20">
    <x-ui.kpi-card icon="alert-triangle"  label="Total Klaim"    value="{{ $summary['total'] }}"         color="accent" />
    <x-ui.kpi-card icon="clock"           label="Menunggu"       value="{{ $summary['submitted'] }}"     color="amber" />
    <x-ui.kpi-card icon="search"          label="Investigasi"    value="{{ $summary['investigating'] }}" color="purple" />
    <x-ui.kpi-card icon="check-circle"    label="Disetujui"      value="{{ $summary['approved'] }}"      color="green" />
</div>

{{-- ─── Filter ────────────────────────────────────────────────────────────── --}}
<div class="sw-card sw-mb-20">
    <form method="GET" action="{{ route('admin.claims.index') }}" style="display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap;">
        <div class="sw-form-group" style="flex:2; min-width:200px;">
            <label class="sw-label">
                <i data-lucide="search" style="width:12px;height:12px;"></i>
                Cari Klaim
            </label>
            <input type="text" name="q" class="sw-input" value="{{ $filters['q'] ?? '' }}" placeholder="Kode order, nama, item...">
        </div>
        <div class="sw-form-group" style="flex:1; min-width:140px;">
            <label class="sw-label">Status</label>
            <select name="status" class="sw-input">
                @foreach (['all' => 'Semua Status', 'submitted' => 'Submitted', 'investigating' => 'Investigasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'resolved' => 'Selesai'] as $val => $lbl)
                    <option value="{{ $val }}" {{ ($filters['status'] ?? 'all') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>
        <div style="display:flex; gap:8px; padding-bottom:1px;">
            <button type="submit" class="sw-btn sw-btn-primary">
                <i data-lucide="filter" style="width:14px;height:14px;"></i>
                Filter
            </button>
            <a href="{{ route('admin.claims.index') }}" class="sw-btn sw-btn-ghost">
                <i data-lucide="rotate-ccw" style="width:14px;height:14px;"></i>
                Reset
            </a>
        </div>
    </form>
</div>

{{-- ─── Claims List ───────────────────────────────────────────────────────── --}}
@forelse ($claims as $claim)
    <div class="sw-card sw-mb-16">
        {{-- Header --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:16px; flex-wrap:wrap;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div class="sw-icon-box">
                    <i data-lucide="alert-triangle" style="width:20px;height:20px;color:var(--warning);"></i>
                </div>
                <div>
                    <div style="font-size:15px; font-weight:700; color:var(--ink);">{{ $claim->item_name }}</div>
                    <div class="sw-muted" style="font-size:12px;">
                        {{ $claim->claimant_name }} · {{ $claim->claimant_contact }}
                        @if ($claim->order)
                            · <a href="{{ route('orders.show', $claim->order) }}" class="sw-font-mono" style="color:var(--accent); font-weight:700;">{{ $claim->order->tracking_code }}</a>
                        @endif
                    </div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <x-ui.status-badge :status="$claim->status" />
                <span class="sw-badge sw-badge-gray" style="font-size:11px;">
                    {{ $claim->submitted_at?->format('d M Y') ?? $claim->created_at->format('d M Y') }}
                </span>
            </div>
        </div>

        {{-- Description --}}
        <div class="sw-detail-box sw-mb-16">
            <div class="sw-detail-lbl">Deskripsi Klaim</div>
            <div style="font-size:13.5px; color:var(--ink-2); line-height:1.6;">{{ $claim->description }}</div>
        </div>

        {{-- Compensation info --}}
        @if ($claim->compensation_amount > 0 || $claim->resolution_notes)
            <div class="sw-grid sw-grid-2 sw-gap-12 sw-mb-16">
                <div class="sw-detail-box">
                    <div class="sw-detail-lbl">Kompensasi</div>
                    <div class="sw-detail-val" style="color:var(--success);">Rp{{ number_format($claim->compensation_amount, 0, ',', '.') }}</div>
                </div>
                @if ($claim->resolution_notes)
                    <div class="sw-detail-box">
                        <div class="sw-detail-lbl">Catatan Resolusi</div>
                        <div style="font-size:13px; color:var(--ink-2);">{{ Str::limit($claim->resolution_notes, 120) }}</div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Update form --}}
        <details>
            <summary class="sw-section-lbl" style="cursor:pointer; font-size:13px; font-weight:600; color:var(--accent); padding:8px 0; list-style:none; display:flex; align-items:center; gap:6px;">
                <i data-lucide="edit-3" style="width:14px;height:14px;"></i>
                Perbarui Status Klaim
                <i data-lucide="chevron-down" style="width:13px;height:13px; margin-left:auto;"></i>
            </summary>

            <form method="POST" action="{{ route('admin.claims.update', $claim) }}" class="sw-form" style="padding-top:16px; border-top:1px solid var(--line); margin-top:8px;">
                @csrf
                @method('PATCH')
                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Status Baru</label>
                        <select name="status" class="sw-input">
                            @foreach (['submitted' => 'Submitted', 'investigating' => 'Sedang Investigasi', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'resolved' => 'Selesai'] as $val => $lbl)
                                <option value="{{ $val }}" {{ $claim->status === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Jumlah Kompensasi (Rp)</label>
                        <input type="number" step="1000" name="compensation_amount" class="sw-input" value="{{ $claim->compensation_amount }}">
                    </div>
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Catatan Resolusi / Keputusan</label>
                    <textarea name="resolution_notes" class="sw-input" rows="3">{{ $claim->resolution_notes }}</textarea>
                </div>
                <button type="submit" class="sw-btn sw-btn-primary">
                    <i data-lucide="save" style="width:14px;height:14px;"></i>
                    Simpan Keputusan
                </button>
            </form>
        </details>
    </div>
@empty
    <x-ui.empty-state
        icon="check-circle"
        title="Tidak ada klaim"
        description="Tidak ada klaim yang cocok dengan filter saat ini."
        actionLabel="Reset Filter"
        actionHref="{{ route('admin.claims.index') }}"
    />
@endforelse

@if ($claims->hasPages())
    <div class="sw-flex sw-justify-center" style="padding:8px 0;">
        {{ $claims->links() }}
    </div>
@endif

</x-layouts.sidebar>
