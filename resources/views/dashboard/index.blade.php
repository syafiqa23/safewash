<x-layouts.sidebar :title="'Dashboard SafeWash'" :heading="'Dashboard'">

{{-- ─── KPI Row 1: Always visible ────────────────────────────────────────── --}}
<div class="sw-kpi-grid">
    <x-ui.kpi-card
        icon="{{ $user->isAdmin() ? 'layers' : ($user->isMerchant() ? 'shopping-bag' : 'package') }}"
        label="{{ $user->isAdmin() ? 'Total Order Platform' : ($user->isMerchant() ? 'Total Order' : 'Total Order Saya') }}"
        value="{{ number_format($stats['orders']) }}"
        color="accent"
    />
    <x-ui.kpi-card
        icon="{{ $user->isCustomer() ? 'loader' : 'activity' }}"
        label="{{ $user->isCustomer() ? 'Order Aktif' : 'Operasional Aktif' }}"
        value="{{ number_format($stats['active']) }}"
        color="green"
    />
    <x-ui.kpi-card
        icon="banknote"
        label="{{ $user->isAdmin() ? 'Omzet Platform' : ($user->isMerchant() ? 'Omzet Merchant' : 'Nilai Order') }}"
        value="Rp{{ number_format($stats['revenue'], 0, ',', '.') }}"
        color="purple"
    />
    @if ($user->isCustomer())
        <x-ui.kpi-card
            icon="star"
            label="Poin Loyalty"
            value="{{ number_format($loyaltyAccount?->points_balance ?? 0) }}"
            sub="{{ $loyaltyAccount?->tier ?? 'Belum Aktif' }}"
            color="amber"
        />
    @else
        <x-ui.kpi-card
            icon="trending-up"
            label="Komisi Admin 15%"
            value="Rp{{ number_format($stats['commission'], 0, ',', '.') }}"
            color="amber"
        />
    @endif
</div>

{{-- ─── KPI Row 2: Admin + Merchant only ─────────────────────────────────── --}}
@if (! $user->isCustomer())
<div class="sw-kpi-grid sw-mt-12">
    <x-ui.kpi-card
        icon="percent"
        label="Service Fee 3%"
        value="Rp{{ number_format($stats['service_fee'], 0, ',', '.') }}"
        color="accent"
    />
    <x-ui.kpi-card
        icon="wallet"
        label="{{ $user->isAdmin() ? 'Estimasi Pendapatan Merchant' : 'Pendapatan Bersih Merchant' }}"
        value="Rp{{ number_format($stats['net_merchant'], 0, ',', '.') }}"
        color="green"
    />
    <x-ui.kpi-card
        icon="smartphone"
        label="Pembayaran Digital"
        value="{{ number_format($stats['digital_payments']) }}"
        color="purple"
    />
    @if ($user->isMerchant())
        <x-ui.kpi-card
            icon="gauge"
            label="Skor Performa"
            value="{{ number_format($stats['merchant_score'], 1) }}"
            sub="dari 100 poin"
            color="amber"
        />
    @else
        <x-ui.kpi-card
            icon="award"
            label="Merchant White-Label"
            value="{{ number_format($stats['white_label_ready']) }}"
            color="amber"
        />
    @endif
</div>
@endif

{{-- ─── Body: 2-col grid ───────────────────────────────────────────────── --}}
<div class="sw-body-grid sw-mt-24">

    {{-- Left panel ─────────────────────────────────────────────────────────── --}}
    @if ($user->isMerchant())
        {{-- Merchant: new order form --}}
        <div class="sw-card">
            <x-ui.section-header title="Buat Order Baru" icon="plus-circle" badge="merchant">
                <span class="sw-badge sw-badge-green sw-text-xs">
                    <i data-lucide="check-circle" style="width:10px;height:10px;"></i>
                    Live
                </span>
            </x-ui.section-header>

            <form method="POST" action="{{ route('orders.store') }}" class="sw-form sw-mt-16">
                @csrf

                <div class="sw-form-group">
                    <label class="sw-label">Outlet Laundry</label>
                    <select name="laundry_id" class="sw-input">
                        @foreach ($laundries as $laundry)
                            <option value="{{ $laundry->id }}">{{ $laundry->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Nama Customer</label>
                        <input type="text" name="customer_name" class="sw-input" required>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">No. HP Customer</label>
                        <input type="text" name="customer_phone" class="sw-input" required>
                    </div>
                </div>

                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Email Customer</label>
                        <input type="email" name="customer_email" class="sw-input">
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Jenis Layanan</label>
                        <input type="text" name="service_type" class="sw-input" placeholder="cuci setrika express" required>
                    </div>
                </div>

                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Berat (kg)</label>
                        <input type="number" step="0.1" name="weight_kg" class="sw-input" required>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Total Harga</label>
                        <input type="number" step="1000" name="total_price" class="sw-input" required>
                    </div>
                </div>

                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Status Pembayaran</label>
                        <select name="payment_status" class="sw-input">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Metode Pembayaran</label>
                        <select name="payment_method" class="sw-input">
                            <option value="cash">Cash</option>
                            <option value="qris">QRIS</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="virtual_account">Virtual Account</option>
                        </select>
                    </div>
                </div>

                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Estimasi Selesai</label>
                        <input type="datetime-local" name="promised_at" class="sw-input">
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Biaya Pickup-Delivery</label>
                        <input type="number" step="1000" name="pickup_delivery_fee" class="sw-input" value="0">
                    </div>
                </div>

                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Jadwal Pickup</label>
                        <input type="datetime-local" name="pickup_scheduled_at" class="sw-input">
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Jadwal Delivery</label>
                        <input type="datetime-local" name="delivery_scheduled_at" class="sw-input">
                    </div>
                </div>

                <div class="sw-form-group">
                    <label class="sw-label">Tipe Pickup-Delivery</label>
                    <select name="pickup_delivery_type" class="sw-input">
                        <option value="none">Outlet Only</option>
                        <option value="pickup">Pickup</option>
                        <option value="delivery">Delivery</option>
                        <option value="round_trip">Pickup + Delivery</option>
                    </select>
                </div>

                <div class="sw-form-row">
                    <div class="sw-form-group">
                        <label class="sw-label">Alamat Pickup</label>
                        <textarea name="pickup_address" class="sw-input" rows="2" placeholder="Alamat pickup customer"></textarea>
                    </div>
                    <div class="sw-form-group">
                        <label class="sw-label">Alamat Delivery</label>
                        <textarea name="delivery_address" class="sw-input" rows="2" placeholder="Alamat pengantaran customer"></textarea>
                    </div>
                </div>

                <div class="sw-form-group">
                    <label class="sw-label">Daftar Item</label>
                    <textarea name="items" class="sw-input" rows="2" placeholder="Kemeja | 3&#10;Celana | 2"></textarea>
                </div>

                <div class="sw-form-group">
                    <label class="sw-label">Catatan</label>
                    <input type="text" name="notes" class="sw-input">
                </div>

                <div class="sw-flex sw-flex-col sw-gap-8 sw-mb-16">
                    <label class="sw-check-label">
                        <input type="checkbox" name="is_premium_protected" value="1">
                        <span>Aktifkan proteksi premium</span>
                    </label>
                    <label class="sw-check-label">
                        <input type="checkbox" name="pickup_delivery_opt_in" value="1">
                        <span>Aktifkan pickup-delivery</span>
                    </label>
                </div>

                <button type="submit" class="sw-btn sw-btn-primary sw-w-full">
                    <i data-lucide="save" style="width:15px;height:15px;"></i>
                    Simpan Order
                </button>
            </form>
        </div>

    @elseif ($user->isAdmin())
        {{-- Admin: platform summary --}}
        <div class="sw-card">
            <x-ui.section-header title="Ringkasan Platform" icon="layout-dashboard" badge="{{ $stats['laundries'] }} merchant" />

            <div class="sw-timeline sw-mt-16">
                <div class="sw-timeline-item">
                    <div class="sw-timeline-dot"><i data-lucide="percent"></i></div>
                    <div class="sw-timeline-body">
                        <div class="sw-timeline-title">Sumber Pendapatan</div>
                        <div class="sw-timeline-desc">15% komisi + 3% service fee per order selesai & berbayar</div>
                    </div>
                </div>
                <div class="sw-timeline-item">
                    <div class="sw-timeline-dot"><i data-lucide="users"></i></div>
                    <div class="sw-timeline-body">
                        <div class="sw-timeline-title">Merchant Terdaftar</div>
                        <div class="sw-timeline-desc">{{ $stats['laundries'] }} outlet laundry aktif di platform</div>
                    </div>
                </div>
                <div class="sw-timeline-item">
                    <div class="sw-timeline-dot"><i data-lucide="alert-triangle"></i></div>
                    <div class="sw-timeline-body">
                        <div class="sw-timeline-title">Klaim Aktif</div>
                        <div class="sw-timeline-desc">{{ $stats['claims'] }} kasus perlu penanganan admin</div>
                    </div>
                </div>
                <div class="sw-timeline-item">
                    <div class="sw-timeline-dot"><i data-lucide="zap"></i></div>
                    <div class="sw-timeline-body">
                        <div class="sw-timeline-title">Ekspansi Produk</div>
                        <div class="sw-timeline-desc">Pembayaran digital, WA gateway, loyalty, white-label</div>
                    </div>
                </div>
                <div class="sw-timeline-item">
                    <div class="sw-timeline-dot"><i data-lucide="truck"></i></div>
                    <div class="sw-timeline-body">
                        <div class="sw-timeline-title">Pickup-Delivery</div>
                        <div class="sw-timeline-desc">{{ $stats['delivery_orders'] }} order dengan layanan antar-jemput</div>
                    </div>
                </div>
            </div>

            <div class="sw-flex sw-gap-8 sw-mt-20 sw-flex-wrap">
                <a href="{{ route('admin.merchants.index') }}" class="sw-btn sw-btn-primary sw-flex-1" style="min-width:120px;">
                    <i data-lucide="store" style="width:14px;height:14px;"></i>
                    Kelola Merchant
                </a>
                <a href="{{ route('orders.index') }}" class="sw-btn sw-btn-ghost sw-flex-1" style="min-width:120px;">
                    <i data-lucide="list" style="width:14px;height:14px;"></i>
                    Semua Order
                </a>
            </div>
        </div>

    @else
        {{-- Customer: loyalty + quick actions --}}
        <div class="sw-card">
            <x-ui.section-header
                title="Loyalty & Akun"
                icon="star"
                badge="{{ $loyaltyAccount?->tier ?? 'Belum Aktif' }}"
            />

            <div class="sw-timeline sw-mt-16">
                <div class="sw-timeline-item">
                    <div class="sw-timeline-dot"><i data-lucide="star"></i></div>
                    <div class="sw-timeline-body">
                        <div class="sw-timeline-title">Poin Aktif</div>
                        <div class="sw-timeline-desc">{{ number_format($loyaltyAccount?->points_balance ?? 0) }} pts tersedia</div>
                    </div>
                </div>
                <div class="sw-timeline-item">
                    <div class="sw-timeline-dot"><i data-lucide="trophy"></i></div>
                    <div class="sw-timeline-body">
                        <div class="sw-timeline-title">Lifetime Points</div>
                        <div class="sw-timeline-desc">{{ number_format($loyaltyAccount?->lifetime_points ?? 0) }} pts dikumpulkan</div>
                    </div>
                </div>
                <div class="sw-timeline-item">
                    <div class="sw-timeline-dot"><i data-lucide="truck"></i></div>
                    <div class="sw-timeline-body">
                        <div class="sw-timeline-title">Order Pickup-Delivery</div>
                        <div class="sw-timeline-desc">{{ $stats['delivery_orders'] }} order dengan antar-jemput</div>
                    </div>
                </div>
            </div>

            <div class="sw-flex sw-gap-8 sw-mt-20 sw-flex-wrap">
                <a href="{{ route('orders.index') }}" class="sw-btn sw-btn-primary sw-flex-1" style="min-width:120px;">
                    <i data-lucide="package" style="width:14px;height:14px;"></i>
                    Order Saya
                </a>
            </div>
        </div>
    @endif

    {{-- Right panel: claims / loyalty history ──────────────────────────────── --}}
    <div class="sw-card">
        <x-ui.section-header
            title="{{ $user->isCustomer() ? 'Riwayat Loyalty' : 'Klaim Aktif' }}"
            icon="{{ $user->isCustomer() ? 'gift' : 'alert-circle' }}"
            badge="{{ $user->isCustomer() ? ($loyaltyAccount?->tier ?? 'Customer') : $stats['claims'].' kasus' }}"
        />

        <div class="sw-mt-12">
            @if ($user->isCustomer())
                @forelse ($loyaltyHistory as $history)
                    <div class="sw-list-row">
                        <div class="sw-flex sw-items-center sw-gap-10">
                            <div class="sw-timeline-dot">
                                <i data-lucide="{{ $history->type === 'earn' ? 'plus-circle' : 'minus-circle' }}"></i>
                            </div>
                            <div>
                                <div class="sw-fw-600 sw-text-sm" style="color:var(--ink);">
                                    {{ strtoupper($history->type) }} {{ number_format($history->points) }} pts
                                </div>
                                <div class="sw-muted sw-text-xs">{{ $history->description }}</div>
                            </div>
                        </div>
                        <span class="sw-badge sw-badge-gray sw-text-xs" style="white-space:nowrap;">
                            {{ $history->created_at->format('d M Y') }}
                        </span>
                    </div>
                @empty
                    <x-ui.empty-state icon="gift" title="Belum ada transaksi loyalty" description="Poin akan muncul setelah order selesai dan lunas." />
                @endforelse
            @else
                @forelse ($claims as $claim)
                    <div class="sw-list-row">
                        <div class="sw-flex sw-items-center sw-gap-10">
                            <div class="sw-timeline-dot">
                                <i data-lucide="alert-triangle"></i>
                            </div>
                            <div>
                                <div class="sw-fw-600 sw-text-sm" style="color:var(--ink);">{{ $claim->item_name }}</div>
                                <div class="sw-muted sw-text-xs">
                                    {{ $claim->order?->tracking_code }} · {{ Str::limit($claim->description, 40) }}
                                </div>
                            </div>
                        </div>
                        <x-ui.status-badge :status="$claim->status" size="sm" />
                    </div>
                @empty
                    <x-ui.empty-state icon="check-circle" title="Tidak ada klaim aktif" description="Semua klaim customer sudah tertangani." />
                @endforelse
            @endif
        </div>
    </div>

</div>

{{-- ─── Recent Orders Table ────────────────────────────────────────────────── --}}
<div class="sw-card sw-mt-24">
    <x-ui.section-header
        title="{{ $user->isAdmin() ? 'Order Terbaru Seluruh Platform' : 'Order Terbaru' }}"
        icon="list-ordered"
    >
        <a href="{{ route('orders.index') }}" class="sw-btn sw-btn-ghost" style="font-size:13px;">
            <i data-lucide="arrow-right" style="width:14px;height:14px;"></i>
            Lihat Semua
        </a>
    </x-ui.section-header>

    @if ($orders->isEmpty())
        <div class="sw-mt-12">
            <x-ui.empty-state
                icon="inbox"
                title="Belum ada order"
                description="Order akan muncul di sini setelah dibuat."
                actionLabel="{{ $user->isMerchant() ? 'Buat Order Pertama' : null }}"
                actionHref="{{ $user->isMerchant() ? route('orders.index') : null }}"
            />
        </div>
    @else
        <div class="sw-table-wrap sw-mt-12">
            <table class="sw-table">
                <thead>
                    <tr>
                        <th>Tracking</th>
                        <th>{{ $user->isCustomer() ? 'Merchant' : 'Customer' }}</th>
                        <th>Layanan</th>
                        <th>Status</th>
                        <th>Bayar</th>
                        <th class="sw-text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr onclick="window.location='{{ route('orders.show', $order) }}'" class="sw-cursor-pointer">
                            <td>
                                <a href="{{ route('orders.show', $order) }}" onclick="event.stopPropagation()" class="sw-font-mono sw-fw-700" style="letter-spacing:.5px; color:var(--accent);">
                                    {{ $order->tracking_code }}
                                </a>
                            </td>
                            <td>{{ $user->isCustomer() ? $order->laundry->name : $order->customer_name }}</td>
                            <td>{{ Str::limit($order->service_type, 20) }}</td>
                            <td><x-ui.status-badge :status="$order->status" /></td>
                            <td><x-ui.status-badge :status="$order->payment_status ?? 'pending'" size="sm" /></td>
                            <td class="sw-text-right sw-fw-600">Rp{{ number_format($order->total_price, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

</x-layouts.sidebar>
