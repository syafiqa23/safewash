<x-layouts.sidebar title="Marketplace Merchant" heading="Marketplace Merchant">

{{-- Hero ───────────────────────────────────────────────────────────────────── --}}
<div style="background:linear-gradient(135deg,#0f1f3d 0%,#1e3a5f 60%,#1d4ed8 100%);border-radius:16px;padding:28px 28px;margin-bottom:20px;position:relative;overflow:hidden;">
    <div style="position:absolute;top:-20px;right:-20px;width:160px;height:160px;background:rgba(255,255,255,.04);border-radius:50%;"></div>
    <div style="position:absolute;bottom:-30px;right:60px;width:100px;height:100px;background:rgba(255,255,255,.03);border-radius:50%;"></div>
    <div style="position:relative;z-index:1;">
        <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.12);border-radius:20px;padding:4px 12px;font-size:11px;font-weight:600;color:rgba(255,255,255,.85);letter-spacing:.08em;text-transform:uppercase;margin-bottom:10px;">
            <i data-lucide="store" style="width:10px;height:10px;"></i> Marketplace
        </div>
        <div style="font-size:24px;font-weight:800;color:#fff;line-height:1.2;margin-bottom:6px;">Temukan Laundry Terpercaya</div>
        <div style="color:rgba(255,255,255,.7);font-size:13px;">QR Tracking · Pickup Delivery · Digital Payment · Proteksi Klaim</div>
        <div style="display:flex;gap:16px;margin-top:16px;flex-wrap:wrap;">
            @foreach([['icon'=>'map-pin','val'=>$cities->count(),'lbl'=>'Kota'],['icon'=>'store','val'=>$laundries->total(),'lbl'=>'Merchant'],['icon'=>'shield-check','val'=>'100%','lbl'=>'Terverifikasi']] as $stat)
            <div style="display:flex;align-items:center;gap:6px;">
                <div style="width:30px;height:30px;background:rgba(255,255,255,.12);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="{{ $stat['icon'] }}" style="width:13px;height:13px;color:#fff;"></i>
                </div>
                <div>
                    <div style="font-size:16px;font-weight:800;color:#fff;line-height:1;">{{ $stat['val'] }}</div>
                    <div style="font-size:10px;color:rgba(255,255,255,.6);">{{ $stat['lbl'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Filter ─────────────────────────────────────────────────────────────────── --}}
<div class="sw-card sw-mb-20">
    <form method="GET" action="{{ route('marketplace.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="sw-form-group" style="flex:2;min-width:180px;">
            <label class="sw-label">Cari Merchant</label>
            <div style="position:relative;">
                <i data-lucide="search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);width:14px;height:14px;color:var(--ink-3);pointer-events:none;"></i>
                <input type="text" name="q" class="sw-input" style="padding-left:32px;" placeholder="Nama laundry atau kota..." value="{{ request('q') }}">
            </div>
        </div>
        <div class="sw-form-group" style="flex:1;min-width:130px;">
            <label class="sw-label">Kota</label>
            <select name="city" class="sw-input">
                <option value="">Semua Kota</option>
                @foreach ($cities as $city)
                    <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div class="sw-form-group" style="flex:1;min-width:130px;">
            <label class="sw-label">Urutkan</label>
            <select name="sort" class="sw-input">
                <option value="rating" {{ request('sort','rating') === 'rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                <option value="orders" {{ request('sort') === 'orders' ? 'selected' : '' }}>Terbanyak Order</option>
                <option value="name"   {{ request('sort') === 'name'   ? 'selected' : '' }}>Nama A–Z</option>
            </select>
        </div>
        <div style="display:flex;gap:12px;align-items:center;padding-bottom:1px;">
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;color:var(--ink-2);white-space:nowrap;">
                <input type="checkbox" name="pickup" value="1" {{ request('pickup') ? 'checked' : '' }} style="accent-color:var(--accent);width:15px;height:15px;">
                <i data-lucide="package" style="width:12px;height:12px;color:#1d4ed8;"></i> Pickup
            </label>
            <label style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;color:var(--ink-2);white-space:nowrap;">
                <input type="checkbox" name="delivery" value="1" {{ request('delivery') ? 'checked' : '' }} style="accent-color:var(--accent);width:15px;height:15px;">
                <i data-lucide="truck" style="width:12px;height:12px;color:#059669;"></i> Delivery
            </label>
        </div>
        <div style="display:flex;gap:6px;padding-bottom:1px;">
            <button type="submit" class="sw-btn sw-btn-primary">
                <i data-lucide="filter" style="width:14px;height:14px;"></i> Filter
            </button>
            <a href="{{ route('marketplace.index') }}" class="sw-btn sw-btn-ghost">Reset</a>
        </div>
    </form>
</div>

{{-- Results count --}}
@if (!$laundries->isEmpty())
<div style="font-size:13px;color:var(--ink-3);margin-bottom:16px;">
    Menampilkan <strong style="color:var(--ink-1);">{{ $laundries->total() }}</strong> merchant
    @if(request('city')) di <strong style="color:var(--accent);">{{ request('city') }}</strong>@endif
</div>
@endif

{{-- Grid Merchant ───────────────────────────────────────────────────────────── --}}
@if ($laundries->isEmpty())
    <x-ui.empty-state icon="store" title="Belum ada merchant ditemukan" description="Coba ubah kata kunci atau hapus filter." />
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(288px,1fr));gap:20px;margin-bottom:24px;">
        @foreach ($laundries as $laundry)
        <a href="{{ route('marketplace.show', $laundry) }}" class="mkt-card" style="text-decoration:none;display:block;">
            <div style="background:var(--surface);border:1px solid var(--surface-3);border-radius:16px;overflow:hidden;transition:transform .2s,box-shadow .2s;height:100%;display:flex;flex-direction:column;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 32px rgba(37,99,235,.14)'" onmouseout="this.style.transform='';this.style.boxShadow=''">

                {{-- ── Photo Banner ─────────────────────────────────────────── --}}
                <div style="position:relative;height:160px;overflow:hidden;flex-shrink:0;">
                    @if ($laundry->photo_url)
                        <img
                            src="{{ $laundry->photo_url }}"
                            alt="{{ $laundry->name }}"
                            style="width:100%;height:100%;object-fit:cover;transition:transform .35s ease;"
                            onmouseover="this.style.transform='scale(1.06)'"
                            onmouseout="this.style.transform='scale(1)'"
                            loading="lazy"
                        >
                    @else
                        <div style="width:100%;height:100%;background:linear-gradient(135deg,#dbeafe 0%,#eff6ff 100%);display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="washing-machine" style="width:48px;height:48px;color:#93c5fd;"></i>
                        </div>
                    @endif

                    {{-- Gradient overlay ──────────────────────────────────── --}}
                    <div style="position:absolute;inset:0;background:linear-gradient(to bottom, rgba(0,0,0,0) 40%, rgba(10,20,50,.6) 100%);pointer-events:none;"></div>

                    {{-- Rating badge (overlay on image) ─────────────────── --}}
                    <div style="position:absolute;top:10px;right:10px;display:flex;align-items:center;gap:3px;background:rgba(0,0,0,.55);backdrop-filter:blur(6px);border-radius:20px;padding:4px 10px;">
                        @php $r = (float)$laundry->rating; @endphp
                        @for($i=1; $i<=5; $i++)
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="{{ $i <= round($r) ? '#fbbf24' : 'rgba(255,255,255,.3)' }}" xmlns="http://www.w3.org/2000/svg"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                        <span style="font-size:12px;font-weight:700;color:#fff;margin-left:2px;">{{ number_format($r, 1) }}</span>
                    </div>

                    {{-- City badge (bottom-left overlay) ───────────────── --}}
                    @if($laundry->city)
                    <div style="position:absolute;bottom:10px;left:10px;display:flex;align-items:center;gap:4px;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);border-radius:12px;padding:3px 8px;">
                        <i data-lucide="map-pin" style="width:10px;height:10px;color:#fbbf24;"></i>
                        <span style="font-size:11px;font-weight:600;color:#fff;">{{ $laundry->city }}</span>
                    </div>
                    @endif
                </div>

                {{-- ── Card Body ────────────────────────────────────────────── --}}
                <div style="padding:16px;flex:1;display:flex;flex-direction:column;gap:8px;">

                    {{-- Name ──────────────────────────────────────────────── --}}
                    <div style="font-size:15px;font-weight:700;color:var(--ink-1);line-height:1.3;">{{ $laundry->name }}</div>

                    {{-- Operating hours ────────────────────────────────── --}}
                    @if ($laundry->operating_hours)
                    <div style="display:flex;align-items:center;gap:5px;color:var(--ink-3);font-size:12px;">
                        <i data-lucide="clock" style="width:11px;height:11px;flex-shrink:0;"></i>
                        {{ $laundry->operating_hours }}
                    </div>
                    @endif

                    {{-- Description snippet ────────────────────────────── --}}
                    @if ($laundry->description)
                    <p style="font-size:12px;color:var(--ink-3);line-height:1.5;margin:0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">{{ $laundry->description }}</p>
                    @endif

                    {{-- Badges ─────────────────────────────────────────── --}}
                    <div style="display:flex;gap:5px;flex-wrap:wrap;margin-top:2px;">
                        @if ($laundry->pickup_available)
                            <span style="background:#dbeafe;color:#1d4ed8;font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:20px;display:inline-flex;align-items:center;gap:3px;">
                                <i data-lucide="package" style="width:9px;height:9px;"></i> Pickup
                            </span>
                        @endif
                        @if ($laundry->delivery_available)
                            <span style="background:#d1fae5;color:#065f46;font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:20px;display:inline-flex;align-items:center;gap:3px;">
                                <i data-lucide="truck" style="width:9px;height:9px;"></i> Delivery
                            </span>
                        @endif
                        @if ($laundry->premium_protection_enabled)
                            <span style="background:#ede9fe;color:#5b21b6;font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:20px;display:inline-flex;align-items:center;gap:3px;">
                                <i data-lucide="shield-check" style="width:9px;height:9px;"></i> Proteksi
                            </span>
                        @endif
                    </div>

                    {{-- Footer ─────────────────────────────────────────── --}}
                    <div style="display:flex;justify-content:space-between;align-items:center;padding-top:10px;border-top:1px solid var(--surface-3);margin-top:auto;">
                        <div style="font-size:11.5px;color:var(--ink-3);">
                            <i data-lucide="message-circle" style="width:10px;height:10px;vertical-align:middle;"></i>
                            {{ number_format($laundry->review_count) }} ulasan
                        </div>
                        <div style="font-size:12px;font-weight:700;color:var(--accent);display:flex;align-items:center;gap:4px;">
                            Lihat Detail <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
                        </div>
                    </div>
                </div>

            </div>
        </a>
        @endforeach
    </div>

    @if ($laundries->hasPages())
        <div style="display:flex;justify-content:center;padding:8px 0 4px;">
            {{ $laundries->links() }}
        </div>
    @endif
@endif

</x-layouts.sidebar>
