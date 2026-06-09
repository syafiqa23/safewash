<x-layouts.sidebar title="Settlement Merchant" heading="Settlement Merchant">

@if (session('success'))
    <div class="sw-alert-success sw-mb-16">{{ session('success') }}</div>
@endif

<div class="sw-kpi-grid sw-mb-20">
    <x-ui.kpi-card icon="file-text"  label="Total Settlement"  value="{{ number_format($summary['total']) }}"      color="accent" />
    <x-ui.kpi-card icon="clock"      label="Pending"           value="{{ number_format($summary['pending']) }}"    color="amber" />
    <x-ui.kpi-card icon="loader"     label="Diproses"          value="{{ number_format($summary['processing']) }}" color="purple" />
    <x-ui.kpi-card icon="check-circle" label="Sudah Dibayar"  value="{{ number_format($summary['paid']) }}"       color="green" />
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">

    {{-- Daftar Settlement --}}
    <div>
        {{-- Filter --}}
        <div class="sw-card sw-mb-16">
            <form method="GET" action="{{ route('admin.settlement.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                <div class="sw-form-group" style="flex:1;min-width:160px;">
                    <label class="sw-label">Merchant</label>
                    <select name="laundry" class="sw-input">
                        <option value="">Semua Merchant</option>
                        @foreach ($laundries as $l)
                            <option value="{{ $l->id }}" {{ request('laundry') == $l->id ? 'selected' : '' }}>{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sw-form-group" style="flex:1;min-width:130px;">
                    <label class="sw-label">Status</label>
                    <select name="status" class="sw-input">
                        <option value="">Semua</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Diproses</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Dibayar</option>
                    </select>
                </div>
                <div style="display:flex;gap:6px;padding-bottom:1px;">
                    <button type="submit" class="sw-btn sw-btn-primary"><i data-lucide="filter" style="width:14px;height:14px;"></i> Filter</button>
                    <a href="{{ route('admin.settlement.index') }}" class="sw-btn sw-btn-ghost">Reset</a>
                </div>
            </form>
        </div>

        <div class="sw-card">
            <x-ui.section-header title="Riwayat Settlement" icon="banknote" badge="{{ $settlements->total() }} settlement" />

            @if ($settlements->isEmpty())
                <x-ui.empty-state icon="banknote" title="Belum ada settlement" description="Buat settlement baru untuk merchant." />
            @else
                <div class="sw-table-wrap">
                    <table class="sw-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Merchant</th>
                                <th>Periode</th>
                                <th class="sw-text-right">Order</th>
                                <th class="sw-text-right">Gross</th>
                                <th class="sw-text-right">Net Merchant</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($settlements as $s)
                                <tr>
                                    <td class="sw-muted sw-text-xs">{{ $s->id }}</td>
                                    <td>
                                        <div class="sw-fw-600 sw-text-sm">{{ $s->laundry->name }}</div>
                                    </td>
                                    <td class="sw-text-sm">
                                        {{ $s->period_start->format('d M') }} – {{ $s->period_end->format('d M Y') }}
                                    </td>
                                    <td class="sw-text-right sw-fw-600">{{ number_format($s->order_count) }}</td>
                                    <td class="sw-text-right sw-text-sm">Rp{{ number_format($s->gross_amount,0,',','.') }}</td>
                                    <td class="sw-text-right sw-fw-700 sw-text-sm" style="color:var(--success);">
                                        Rp{{ number_format($s->net_amount,0,',','.') }}
                                    </td>
                                    <td><x-ui.status-badge :status="$s->status" size="sm" /></td>
                                    <td>
                                        @if ($s->status !== 'paid')
                                            <form method="POST" action="{{ route('admin.settlement.mark-paid', $s) }}" style="display:inline;">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="sw-btn sw-btn-primary sw-btn-sm" onclick="return confirm('Tandai sudah dibayar?')">
                                                    Bayar
                                                </button>
                                            </form>
                                        @else
                                            <span class="sw-text-xs sw-muted">{{ $s->settled_at?->format('d M Y') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($settlements->hasPages())
                    <div class="sw-flex sw-justify-center" style="padding:16px 0 4px;">
                        {{ $settlements->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Buat Settlement Baru --}}
    <div style="position:sticky;top:80px;">
        <div class="sw-card" style="border:2px solid var(--accent);">
            <x-ui.section-header title="Buat Settlement Baru" icon="plus-circle" />
            <form method="POST" action="{{ route('admin.settlement.store') }}" class="sw-form">
                @csrf
                <div class="sw-form-group">
                    <label class="sw-label">Pilih Merchant *</label>
                    <select name="laundry_id" class="sw-input" required>
                        <option value="">— Pilih Merchant —</option>
                        @foreach ($laundries as $l)
                            <option value="{{ $l->id }}">{{ $l->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Dari Tanggal *</label>
                    <input type="date" name="period_start" class="sw-input" required value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Sampai Tanggal *</label>
                    <input type="date" name="period_end" class="sw-input" required value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Catatan</label>
                    <textarea name="notes" class="sw-input" rows="2" placeholder="Keterangan settlement..."></textarea>
                </div>
                <button type="submit" class="sw-btn sw-btn-primary sw-w-full" style="justify-content:center;">
                    <i data-lucide="calculator" style="width:14px;height:14px;"></i>
                    Generate Settlement
                </button>
            </form>
        </div>

        <div class="sw-card sw-mt-4" style="background:var(--surface-2);">
            <div style="font-size:12px;font-weight:600;color:var(--ink-1);margin-bottom:6px;">Total Sudah Dibayar</div>
            <div style="font-size:22px;font-weight:800;color:var(--success);">
                Rp{{ number_format($summary['total_net'],0,',','.') }}
            </div>
        </div>
    </div>

</div>

</x-layouts.sidebar>
