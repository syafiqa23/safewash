<x-layouts.app :title="'SafeWash — Platform Laundry Digital'">

{{-- ── 1. Hero ──────────────────────────────────────────────────────────── --}}
<section>
    <div class="hp-container">
        <div class="hp-hero">

            <div>
                <div class="hp-eyebrow">
                    <i data-lucide="shield-check" style="width:12px;height:12px;"></i>
                    Platform Laundry Digital #1 Indonesia
                </div>
                <h1 class="hp-h1">
                    Laundry lebih modern,<br>
                    <span>customer lebih percaya.</span>
                </h1>
                <p class="hp-lead">
                    SafeWash membantu merchant laundry mengelola order, pembayaran, QR tracking real-time,
                    pickup-delivery, dan loyalitas pelanggan — dalam satu platform terintegrasi.
                </p>
                <div class="hp-cta">
                    <a href="{{ route('register') }}" class="hp-btn hp-btn-primary">
                        <i data-lucide="store" style="width:15px;height:15px;"></i>
                        Daftar Merchant Gratis
                    </a>
                    <a href="#how-it-works" class="hp-btn hp-btn-ghost">
                        <i data-lucide="play-circle" style="width:15px;height:15px;"></i>
                        Cara Kerja
                    </a>
                </div>
                <div class="hp-metrics">
                    <div>
                        <div class="hp-metric-val">{{ number_format($metrics['orders']) }}+</div>
                        <div class="hp-metric-lbl">Order diproses</div>
                    </div>
                    <div>
                        <div class="hp-metric-val">{{ number_format($metrics['laundries']) }}</div>
                        <div class="hp-metric-lbl">Merchant aktif</div>
                    </div>
                    <div>
                        <div class="hp-metric-val">{{ number_format($metrics['digitalPayments']) }}</div>
                        <div class="hp-metric-lbl">Pembayaran digital</div>
                    </div>
                </div>
            </div>

            {{-- Mock dashboard preview --}}
            <div class="hp-mockup">
                <div class="hp-mock-bar">
                    <div class="hp-mock-dot" style="background:var(--danger);"></div>
                    <div class="hp-mock-dot" style="background:var(--warning);"></div>
                    <div class="hp-mock-dot" style="background:var(--success);"></div>
                    <span style="font-size:11px; color:var(--muted); margin-left:8px; font-weight:600;">Dashboard SafeWash</span>
                </div>
                <div class="hp-mock-body">
                    <div class="hp-mock-kpis">
                        <div class="hp-mock-kpi">
                            <div class="hp-mock-kpi-val">{{ number_format($metrics['orders']) }}</div>
                            <div class="hp-mock-kpi-lbl">Total Order</div>
                        </div>
                        <div class="hp-mock-kpi">
                            <div class="hp-mock-kpi-val">Rp{{ number_format($metrics['platformRevenue'] / 1000, 0) }}K</div>
                            <div class="hp-mock-kpi-lbl">Platform Revenue</div>
                        </div>
                        <div class="hp-mock-kpi">
                            <div class="hp-mock-kpi-val">{{ $metrics['deliveryOrders'] }}</div>
                            <div class="hp-mock-kpi-lbl">Delivery Aktif</div>
                        </div>
                    </div>
                    @foreach ($latestOrders as $order)
                        <div class="hp-mock-row">
                            <div>
                                <div class="hp-mock-code">{{ $order->tracking_code }}</div>
                                <div style="font-size:11px; color:var(--muted); margin-top:1px;">{{ $order->customer_name }}</div>
                            </div>
                            <x-ui.status-badge :status="$order->status" size="sm" />
                            <div style="font-size:12px; font-weight:700; color:var(--ink);">
                                Rp{{ number_format($order->total_price, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ── 2. Problem Section ───────────────────────────────────────────────── --}}
<section class="hp-section" style="background:var(--surface-2); border-top:1px solid var(--line); border-bottom:1px solid var(--line);">
    <div class="hp-container">
        <div class="hp-eyebrow" style="margin-bottom:14px;">
            <i data-lucide="alert-triangle" style="width:12px;height:12px;"></i>
            Masalah Nyata
        </div>
        <h2 class="hp-section-title">Mengapa laundry konvensional tidak cukup?</h2>
        <p class="hp-section-sub">Customer kehilangan kepercayaan karena tidak ada transparansi proses.</p>
        <div class="hp-grid-3">
            @foreach ([
                ['icon' => 'x-circle',       'title' => 'Tidak Ada Tracking',      'desc' => 'Customer tidak tahu posisi pakaian mereka. Hanya bisa menunggu tanpa kepastian.'],
                ['icon' => 'alert-octagon',  'title' => 'Klaim Susah Ditangani',   'desc' => 'Pakaian hilang atau rusak? Tidak ada sistem klaim yang transparan dan terstruktur.'],
                ['icon' => 'smartphone-nfc', 'title' => 'Pembayaran Manual',       'desc' => 'Cash-only, tidak ada struk digital, tidak ada riwayat pembayaran yang tercatat.'],
            ] as $prob)
                <div class="hp-feat" style="border:1px solid var(--danger); border-color:color-mix(in srgb,var(--danger) 20%,transparent);">
                    <div class="hp-feat-icon" style="background:color-mix(in srgb,var(--danger) 10%,transparent);">
                        <i data-lucide="{{ $prob['icon'] }}" style="color:var(--danger);"></i>
                    </div>
                    <div class="hp-feat-title">{{ $prob['title'] }}</div>
                    <div class="hp-feat-desc">{{ $prob['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── 3. Solution Section ──────────────────────────────────────────────── --}}
<section class="hp-section">
    <div class="hp-container">
        <div class="hp-eyebrow" style="margin-bottom:14px;">
            <i data-lucide="check-circle" style="width:12px;height:12px;"></i>
            Solusi SafeWash
        </div>
        <h2 class="hp-section-title">Satu platform untuk semua masalah laundry</h2>
        <p class="hp-section-sub">SafeWash adalah infrastruktur digital untuk bisnis laundry — dari order masuk hingga pembayaran dan loyalty.</p>
        <div class="hp-model" style="margin-top:32px;">
            <div class="hp-model-item">
                <div class="hp-model-val" style="color:var(--accent);">Real-time</div>
                <div class="hp-model-lbl">QR Tracking setiap tahap proses</div>
            </div>
            <div class="hp-model-sep"></div>
            <div class="hp-model-item">
                <div class="hp-model-val" style="color:var(--success);">Terstruktur</div>
                <div class="hp-model-lbl">Sistem klaim dengan workflow jelas</div>
            </div>
            <div class="hp-model-sep"></div>
            <div class="hp-model-item">
                <div class="hp-model-val" style="color:var(--warning);">Fleksibel</div>
                <div class="hp-model-lbl">QRIS, e-wallet, VA, dan tunai</div>
            </div>
        </div>
    </div>
</section>

{{-- ── 4. Features ──────────────────────────────────────────────────────── --}}
<section class="hp-section" style="background:var(--surface-2); border-top:1px solid var(--line); border-bottom:1px solid var(--line);">
    <div class="hp-container">
        <div class="hp-eyebrow" style="margin-bottom:14px;">
            <i data-lucide="zap" style="width:12px;height:12px;"></i>
            Fitur Platform
        </div>
        <h2 class="hp-section-title">Semua yang dibutuhkan merchant laundry</h2>
        <p class="hp-section-sub">Dari order masuk, pembayaran, QR tracking, hingga notifikasi WhatsApp otomatis — satu platform.</p>
        <div class="hp-grid-3">
            @foreach ([
                ['icon'=>'qr-code',         'title'=>'QR Tracking Publik',     'desc'=>'Customer scan QR untuk melihat status laundry real-time tanpa perlu login sama sekali.'],
                ['icon'=>'layout-dashboard', 'title'=>'Dashboard Terpadu',     'desc'=>'KPI order, revenue, merchant score, dan loyalty dalam satu tampilan bersih dan informatif.'],
                ['icon'=>'credit-card',      'title'=>'Pembayaran Fleksibel',  'desc'=>'Midtrans & Xendit terintegrasi — QRIS, e-wallet, virtual account, dan tunai dicatat otomatis.'],
                ['icon'=>'message-circle',   'title'=>'Notifikasi WhatsApp',   'desc'=>'Notifikasi otomatis setiap perubahan status order langsung ke nomor WhatsApp customer.'],
                ['icon'=>'shield-check',     'title'=>'Klaim & Proteksi',      'desc'=>'Sistem klaim terstruktur. Admin dapat investigasi, setujui, tolak, dan atur kompensasi.'],
                ['icon'=>'gift',             'title'=>'Program Loyalty',       'desc'=>'4 tier: Ocean → Sky → Cloud → Aurora. Poin otomatis setiap order lunas dan selesai.'],
                ['icon'=>'truck',            'title'=>'Pickup-Delivery',       'desc'=>'Layanan antar-jemput terintegrasi. Jadwal, tracking kurir, dan status real-time.'],
                ['icon'=>'gauge',            'title'=>'Merchant Score',        'desc'=>'Skor 0–100 otomatis berdasarkan completion rate, payment rate, dan adopsi digital.'],
                ['icon'=>'star',             'title'=>'White-Label Ready',     'desc'=>'Merchant bisa pakai domain dan brand sendiri — powered by SafeWash di balik layar.'],
            ] as $feat)
                <div class="hp-feat">
                    <div class="hp-feat-icon">
                        <i data-lucide="{{ $feat['icon'] }}"></i>
                    </div>
                    <div class="hp-feat-title">{{ $feat['title'] }}</div>
                    <div class="hp-feat-desc">{{ $feat['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── 5. How It Works ──────────────────────────────────────────────────── --}}
<section id="how-it-works" class="hp-section">
    <div class="hp-container">
        <div class="hp-eyebrow" style="margin-bottom:14px;">
            <i data-lucide="workflow" style="width:12px;height:12px;"></i>
            Cara Kerja
        </div>
        <h2 class="hp-section-title">Alur kerja SafeWash</h2>
        <p class="hp-section-sub">Tiga aktor, satu ekosistem terintegrasi.</p>

        <div class="hp-grid-3" style="margin-top:32px; gap:24px;">
            {{-- Customer flow --}}
            <div style="background:var(--surface-2); border:1px solid var(--line); border-radius:16px; padding:24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                    <div class="hp-feat-icon" style="width:40px; height:40px; border-radius:10px; flex-shrink:0;">
                        <i data-lucide="user" style="width:18px;height:18px;"></i>
                    </div>
                    <div style="font-size:15px; font-weight:700; color:var(--ink);">Customer</div>
                </div>
                @foreach (['Bawa pakaian ke outlet atau request pickup','Terima notifikasi WhatsApp setiap update','Scan QR untuk tracking real-time','Bayar via QRIS / e-wallet / VA / tunai','Kumpulkan poin loyalty & naik tier'] as $step)
                    <div style="display:flex; align-items:flex-start; gap:10px; margin-bottom:10px;">
                        <i data-lucide="arrow-right" style="width:13px;height:13px;color:var(--accent);flex-shrink:0;margin-top:2px;"></i>
                        <span style="font-size:13px; color:var(--ink-2);">{{ $step }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Merchant flow --}}
            <div style="background:var(--surface-2); border:1px solid var(--line); border-radius:16px; padding:24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                    <div class="hp-feat-icon" style="width:40px; height:40px; border-radius:10px; flex-shrink:0;">
                        <i data-lucide="store" style="width:18px;height:18px;"></i>
                    </div>
                    <div style="font-size:15px; font-weight:700; color:var(--ink);">Merchant</div>
                </div>
                @foreach (['Buat order baru dari dashboard','Update status proses laundry','Kelola pickup-delivery kurir','Konfirmasi pembayaran digital/tunai','Pantau pendapatan & merchant score'] as $step)
                    <div style="display:flex; align-items:flex-start; gap:10px; margin-bottom:10px;">
                        <i data-lucide="arrow-right" style="width:13px;height:13px;color:var(--accent);flex-shrink:0;margin-top:2px;"></i>
                        <span style="font-size:13px; color:var(--ink-2);">{{ $step }}</span>
                    </div>
                @endforeach
            </div>

            {{-- Platform flow --}}
            <div style="background:var(--surface-2); border:1px solid var(--line); border-radius:16px; padding:24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
                    <div class="hp-feat-icon" style="width:40px; height:40px; border-radius:10px; flex-shrink:0;">
                        <i data-lucide="layers" style="width:18px;height:18px;"></i>
                    </div>
                    <div style="font-size:15px; font-weight:700; color:var(--ink);">Platform SafeWash</div>
                </div>
                @foreach (['Onboard merchant baru gratis','Ambil 15% komisi + 3% service fee','Tangani eskalasi klaim customer','Kelola ekosistem loyalty multi-tier','Monitor seluruh transaksi platform'] as $step)
                    <div style="display:flex; align-items:flex-start; gap:10px; margin-bottom:10px;">
                        <i data-lucide="arrow-right" style="width:13px;height:13px;color:var(--accent);flex-shrink:0;margin-top:2px;"></i>
                        <span style="font-size:13px; color:var(--ink-2);">{{ $step }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ── 6. Merchant Benefits ──────────────────────────────────────────────── --}}
<section class="hp-section" style="background:var(--surface-2); border-top:1px solid var(--line); border-bottom:1px solid var(--line);">
    <div class="hp-container">
        <div class="hp-eyebrow" style="margin-bottom:14px;">
            <i data-lucide="trending-up" style="width:12px;height:12px;"></i>
            Keuntungan Merchant
        </div>
        <h2 class="hp-section-title">Kenapa merchant memilih SafeWash?</h2>
        <div class="hp-grid-3">
            @foreach ([
                ['icon'=>'rocket',        'title'=>'Onboarding Gratis',          'desc'=>'Tidak ada biaya pendaftaran. Bayar komisi hanya saat order berhasil dan lunas.'],
                ['icon'=>'smartphone',    'title'=>'Pembayaran Digital Instan',   'desc'=>'Terima QRIS, e-wallet, dan virtual account tanpa setup rumit. Langsung aktif.'],
                ['icon'=>'shield',        'title'=>'Perlindungan Reputasi',      'desc'=>'Sistem klaim yang transparan membantu merchant menjaga kepercayaan customer.'],
                ['icon'=>'bar-chart-2',   'title'=>'Analytics Real-time',        'desc'=>'Dashboard merchant score, completion rate, dan revenue dalam satu layar.'],
                ['icon'=>'bell',          'title'=>'Notifikasi Otomatis',        'desc'=>'WhatsApp otomatis ke customer. Tidak perlu lagi kirim pesan manual satu per satu.'],
                ['icon'=>'award',         'title'=>'White-Label Option',         'desc'=>'Merchant besar bisa pakai brand sendiri — powered by SafeWash di belakang layar.'],
            ] as $benefit)
                <div class="hp-feat">
                    <div class="hp-feat-icon"><i data-lucide="{{ $benefit['icon'] }}"></i></div>
                    <div class="hp-feat-title">{{ $benefit['title'] }}</div>
                    <div class="hp-feat-desc">{{ $benefit['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── 7. Pricing / Revenue Model ───────────────────────────────────────── --}}
<section style="padding:64px 0;background:linear-gradient(180deg,#f8faff 0%,#fff 100%);">
    <div class="hp-container">
        <div style="max-width:680px;margin:0 auto;text-align:center;">
            <div class="hp-eyebrow" style="margin-bottom:14px;justify-content:center;">
                <i data-lucide="percent" style="width:12px;height:12px;"></i>
                Model Bisnis
            </div>
            <h2 class="hp-section-title" style="text-align:center;margin-bottom:12px;">Transparan. Berbasis Performa.</h2>
            <p style="font-size:15px;color:var(--muted);line-height:1.7;text-align:center;margin:0 auto 40px;max-width:480px;">
                Tidak ada biaya tetap, tidak ada kontrak, tidak ada risiko. SafeWash hanya untung ketika merchant Anda untung — model bisnis yang benar-benar berpihak kepada mitra.
            </p>
        </div>
        <div class="hp-model" style="margin-top:0;max-width:860px;margin-left:auto;margin-right:auto;">
            <div class="hp-model-item" style="text-align:center;">
                <div class="hp-model-val" style="text-align:center;">Rp0</div>
                <div class="hp-model-lbl" style="text-align:center;">Biaya pendaftaran merchant</div>
            </div>
            <div class="hp-model-sep"></div>
            <div class="hp-model-item" style="text-align:center;">
                <div class="hp-model-val" style="text-align:center;">15%</div>
                <div class="hp-model-lbl" style="text-align:center;">Komisi per order selesai & lunas</div>
            </div>
            <div class="hp-model-sep"></div>
            <div class="hp-model-item" style="text-align:center;">
                <div class="hp-model-val" style="text-align:center;">3%</div>
                <div class="hp-model-lbl" style="text-align:center;">Service fee per transaksi berhasil</div>
            </div>
            <div class="hp-model-sep"></div>
            <div class="hp-model-item" style="text-align:center;">
                <div class="hp-model-val" style="text-align:center;">{{ number_format($metrics['whiteLabelMerchants']) }}</div>
                <div class="hp-model-lbl" style="text-align:center;">Merchant aktifkan white-label</div>
            </div>
        </div>
        <p style="text-align:center;font-size:12px;color:var(--muted);margin-top:24px;">
            * Komisi dihitung dari total order yang telah <strong>selesai dan lunas</strong>. Tidak ada biaya tersembunyi.
        </p>
    </div>
</section>

{{-- ── 8. Testimonials ──────────────────────────────────────────────────── --}}
<section class="hp-section" style="background:var(--surface-2); border-top:1px solid var(--line); border-bottom:1px solid var(--line);">
    <div class="hp-container">
        <div class="hp-eyebrow" style="margin-bottom:14px;">
            <i data-lucide="message-square" style="width:12px;height:12px;"></i>
            Testimoni
        </div>
        <h2 class="hp-section-title">Apa kata mereka?</h2>
        <div class="hp-grid-3" style="margin-top:32px;">
            @foreach ([
                ['name'=>'Budi Santoso',    'role'=>'Pemilik Laundry Express Bandung', 'msg'=>'Sejak pakai SafeWash, customer tidak pernah lagi tanya "kapan siap?" — mereka bisa cek sendiri lewat QR. Komplain turun drastis.'],
                ['name'=>'Siti Rahayu',     'role'=>'Pelanggan Setia',                 'msg'=>'Poin loyalty saya sudah sampai tier Sky. Senang banget bisa tracking pakaian real-time. Tidak perlu telepon laundry lagi.'],
                ['name'=>'Ahmad Fauzi',     'role'=>'Merchant Surabaya, 3 Outlet',     'msg'=>'Dashboard merchant score membantu saya tahu di mana harus improve. Revenue naik 23% dalam 2 bulan pertama.'],
            ] as $testi)
                <div style="background:var(--surface); border:1px solid var(--line); border-radius:16px; padding:24px;">
                    <div style="font-size:22px; color:var(--accent); margin-bottom:12px; line-height:1;">"</div>
                    <p style="font-size:13.5px; color:var(--ink-2); line-height:1.7; margin-bottom:16px;">{{ $testi['msg'] }}</p>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div style="width:36px; height:36px; border-radius:50%; background:var(--accent-light); display:flex; align-items:center; justify-content:center; font-weight:700; color:var(--accent); font-size:14px; flex-shrink:0;">
                            {{ strtoupper(mb_substr($testi['name'], 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight:700; font-size:13px; color:var(--ink);">{{ $testi['name'] }}</div>
                            <div style="font-size:11.5px; color:var(--muted);">{{ $testi['role'] }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── 9. Tracking widget ───────────────────────────────────────────────── --}}
<section id="tracking" style="padding:72px 0;">
    <div class="hp-container">
        <div class="hp-track-card">
            <h2 class="hp-track-title">Cek Status Order</h2>
            <p class="hp-track-sub">Masukkan kode tracking untuk melihat status laundry Anda secara real-time.</p>
            <form
                class="hp-track-form"
                method="GET"
                onsubmit="event.preventDefault(); var v = this.querySelector('input').value.trim().toUpperCase(); if(v) window.location = '{{ url('/track') }}/' + v;"
            >
                <input type="text" class="hp-track-input" placeholder="SW-XXXXXXXX"
                    maxlength="20" oninput="this.value=this.value.toUpperCase()" required>
                <button type="submit" class="hp-track-btn">
                    <i data-lucide="search" style="width:14px;height:14px;display:inline-block;vertical-align:middle;margin-right:4px;"></i>
                    Cari
                </button>
            </form>

            @if ($latestOrders->isNotEmpty())
                <div class="hp-order-list">
                    <p style="font-size:11.5px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px;">Order terbaru di platform</p>
                    @foreach ($latestOrders as $order)
                        <a href="{{ route('tracking.show', $order->qr_token) }}" class="hp-order-row" style="text-decoration:none;">
                            <div>
                                <div class="hp-order-code">{{ $order->tracking_code }}</div>
                                <div class="hp-order-meta">{{ $order->laundry->name }} · {{ $order->customer_name }}</div>
                            </div>
                            <x-ui.status-badge :status="$order->status" size="sm" />
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>

{{-- ── 10. CTA ──────────────────────────────────────────────────────────── --}}
<section class="hp-cta-bar">
    <div class="hp-container">
        <h2 class="hp-cta-title">Siap modernisasi laundry Anda?</h2>
        <p class="hp-cta-sub">Bergabung gratis. Bayar komisi hanya saat order berhasil. Tidak ada lock-in.</p>
        <div style="display:flex; align-items:center; justify-content:center; gap:12px; flex-wrap:wrap;">
            <a href="{{ route('register') }}" class="hp-btn hp-btn-primary" style="font-size:15px; padding:13px 28px;">
                <i data-lucide="store" style="width:16px;height:16px;"></i>
                Daftar Merchant Sekarang
            </a>
            <a href="{{ route('login') }}" class="hp-btn hp-btn-ghost" style="font-size:15px; padding:13px 24px;">
                <i data-lucide="log-in" style="width:15px;height:15px;"></i>
                Login ke Dashboard
            </a>
        </div>
    </div>
</section>

{{-- ── Footer ───────────────────────────────────────────────────────────── --}}
<footer style="border-top:1px solid var(--line); padding:40px 0 24px;">
    <div class="hp-container">
        <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:32px; margin-bottom:32px;">
            <div>
                <div style="font-size:18px; font-weight:900; color:var(--ink); margin-bottom:8px; letter-spacing:-.02em;">
                    Safe<span style="color:var(--accent);">Wash</span>
                </div>
                <p style="font-size:13px; color:var(--muted); line-height:1.6; max-width:240px;">
                    Platform laundry digital untuk merchant modern. Lebih profesional, lebih transparan, lebih dipercaya.
                </p>
            </div>
            <div>
                <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:12px;">Platform</div>
                @foreach (['Fitur', 'Cara Kerja', 'Harga', 'Integrasi'] as $link)
                    <div style="margin-bottom:8px;">
                        <a href="#" style="font-size:13px; color:var(--ink-2); text-decoration:none;">{{ $link }}</a>
                    </div>
                @endforeach
            </div>
            <div>
                <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:12px;">Merchant</div>
                @foreach (['Daftar Gratis', 'Dashboard', 'Tracking QR', 'Loyalty Program'] as $link)
                    <div style="margin-bottom:8px;">
                        <a href="{{ $loop->first ? route('register') : '#' }}" style="font-size:13px; color:var(--ink-2); text-decoration:none;">{{ $link }}</a>
                    </div>
                @endforeach
            </div>
            <div>
                <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin-bottom:12px;">Perusahaan</div>
                @foreach (['Tentang Kami', 'Blog', 'Karir', 'Hubungi Kami'] as $link)
                    <div style="margin-bottom:8px;">
                        <a href="#" style="font-size:13px; color:var(--ink-2); text-decoration:none;">{{ $link }}</a>
                    </div>
                @endforeach
            </div>
        </div>
        <div style="border-top:1px solid var(--line); padding-top:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
            <div style="font-size:12px; color:var(--muted);">
                &copy; {{ date('Y') }} SafeWash. Platform Laundry Digital Indonesia.
            </div>
            <div style="display:flex; gap:16px;">
                @foreach (['Privasi', 'Syarat & Ketentuan', 'Kebijakan Klaim'] as $link)
                    <a href="#" style="font-size:12px; color:var(--muted); text-decoration:none;">{{ $link }}</a>
                @endforeach
            </div>
        </div>
    </div>
</footer>

</x-layouts.app>
