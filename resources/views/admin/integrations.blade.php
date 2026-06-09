<x-layouts.sidebar :title="'Pengaturan Integrasi'" :heading="'Pengaturan Integrasi'">

<form method="POST" action="{{ route('admin.integrations.update') }}">
    @csrf

    {{-- ─── General Settings ──────────────────────────────────────────────────── --}}
    <div class="sw-card sw-mb-20">
        <x-ui.section-header title="Pengaturan Umum" icon="settings" badge="Live-ready" />

        <div class="sw-form-row sw-mt-4">
            <div class="sw-form-group">
                <label class="sw-label">
                    <i data-lucide="globe" style="width:12px;height:12px;"></i>
                    Public App URL
                </label>
                <input type="url" name="public_app_url" class="sw-input" value="{{ $form['public_app_url'] }}" required>
            </div>
            <div class="sw-form-group">
                <label class="sw-label">
                    <i data-lucide="credit-card" style="width:12px;height:12px;"></i>
                    Payment Provider Aktif
                </label>
                <select name="payment_provider" class="sw-input">
                    <option value="midtrans"  {{ $form['payment_provider'] === 'midtrans'  ? 'selected' : '' }}>Midtrans</option>
                    <option value="xendit"    {{ $form['payment_provider'] === 'xendit'    ? 'selected' : '' }}>Xendit</option>
                    <option value="simulator" {{ $form['payment_provider'] === 'simulator' ? 'selected' : '' }}>Simulator (Safe Default)</option>
                </select>
            </div>
        </div>

        <div class="sw-grid sw-grid-3 sw-gap-14 sw-mt-14">
            <div class="sw-form-group">
                <label class="sw-label">Payment Mode</label>
                <select name="payment_mode" class="sw-input">
                    <option value="sandbox"    {{ $form['payment_mode'] === 'sandbox'    ? 'selected' : '' }}>Sandbox</option>
                    <option value="production" {{ $form['payment_mode'] === 'production' ? 'selected' : '' }}>Production</option>
                </select>
            </div>
            <div class="sw-form-group">
                <label class="sw-label">Xendit API Version</label>
                <input type="text" name="xendit_api_version" class="sw-input" value="{{ $form['xendit_api_version'] }}">
            </div>
            <div class="sw-form-group">
                <label class="sw-label">WhatsApp API Version</label>
                <input type="text" name="whatsapp_api_version" class="sw-input" value="{{ $form['whatsapp_api_version'] }}">
            </div>
        </div>
    </div>

    {{-- ─── Midtrans + Xendit ──────────────────────────────────────────────────── --}}
    <div class="sw-body-grid sw-mb-20">
        <div class="sw-card">
            <x-ui.section-header title="Midtrans Settings" icon="credit-card" badge="Default live choice" />
            <div class="sw-form sw-mt-4">
                <div class="sw-form-group">
                    <label class="sw-label">Server Key</label>
                    <input type="text" name="midtrans_server_key" class="sw-input"
                        value="{{ old('midtrans_server_key') }}"
                        placeholder="{{ $form['midtrans_server_key'] ?: 'Isi server key Midtrans' }}">
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Client Key</label>
                    <input type="text" name="midtrans_client_key" class="sw-input"
                        value="{{ old('midtrans_client_key') }}"
                        placeholder="{{ $form['midtrans_client_key'] ?: 'Isi client key Midtrans' }}">
                </div>
                <label class="sw-check-label">
                    <input type="checkbox" name="midtrans_is_production" value="1" {{ $form['midtrans_is_production'] ? 'checked' : '' }}>
                    <span>Gunakan endpoint production Midtrans</span>
                </label>
            </div>
        </div>

        <div class="sw-card">
            <x-ui.section-header title="Xendit Settings" icon="zap" badge="Alternative provider" />
            <div class="sw-form sw-mt-4">
                <div class="sw-form-group">
                    <label class="sw-label">Secret Key</label>
                    <input type="text" name="xendit_secret_key" class="sw-input"
                        value="{{ old('xendit_secret_key') }}"
                        placeholder="{{ $form['xendit_secret_key'] ?: 'Isi secret key Xendit' }}">
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Webhook Token</label>
                    <input type="text" name="xendit_webhook_token" class="sw-input"
                        value="{{ old('xendit_webhook_token') }}"
                        placeholder="{{ $form['xendit_webhook_token'] ?: 'Isi webhook token Xendit' }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ─── WhatsApp Cloud API ─────────────────────────────────────────────────── --}}
    <div class="sw-card sw-mb-20">
        <x-ui.section-header title="WhatsApp Cloud API" icon="message-circle" badge="Official Channel" />

        <div class="sw-form sw-mt-4">
            <div class="sw-form-row">
                <div class="sw-form-group">
                    <label class="sw-label">Gateway Label</label>
                    <input type="text" name="whatsapp_gateway_name" class="sw-input" value="{{ $form['whatsapp_gateway_name'] }}">
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Phone Number ID</label>
                    <input type="text" name="whatsapp_phone_number_id" class="sw-input"
                        value="{{ old('whatsapp_phone_number_id') }}"
                        placeholder="{{ $form['whatsapp_phone_number_id'] ?: 'Isi phone number ID Meta' }}">
                </div>
            </div>
            <div class="sw-form-row">
                <div class="sw-form-group">
                    <label class="sw-label">Access Token</label>
                    <input type="text" name="whatsapp_access_token" class="sw-input"
                        value="{{ old('whatsapp_access_token') }}"
                        placeholder="{{ $form['whatsapp_access_token'] ?: 'Isi permanent access token' }}">
                </div>
                <div class="sw-form-group">
                    <label class="sw-label">Verify Token</label>
                    <input type="text" name="whatsapp_verify_token" class="sw-input"
                        value="{{ old('whatsapp_verify_token') }}"
                        placeholder="{{ $form['whatsapp_verify_token'] ?: 'Isi verify token custom' }}">
                </div>
            </div>
            <label class="sw-check-label">
                <input type="checkbox" name="whatsapp_enabled" value="1" {{ $form['whatsapp_enabled'] ? 'checked' : '' }}>
                <span>Aktifkan WhatsApp Cloud API live (default: disabled / simulated log)</span>
            </label>
        </div>
    </div>

    {{-- ─── Save button ────────────────────────────────────────────────────────── --}}
    <div class="sw-save-bar">
        <button type="submit" class="sw-btn sw-btn-primary" style="min-width:200px;">
            <i data-lucide="save" style="width:15px;height:15px;"></i>
            Simpan Pengaturan Integrasi
        </button>
    </div>
</form>

{{-- ─── Webhook URLs + Checklist ───────────────────────────────────────────── --}}
<div class="sw-body-grid">
    <div class="sw-card">
        <x-ui.section-header title="Webhook URLs" icon="link" badge="Copy to provider" />
        <div style="display:flex; flex-direction:column; gap:0; margin-top:4px;">
            @foreach ([
                ['label' => 'Midtrans',          'value' => $webhooks['midtrans'],          'icon' => 'credit-card'],
                ['label' => 'Xendit',            'value' => $webhooks['xendit'],            'icon' => 'zap'],
                ['label' => 'WhatsApp Verify',   'value' => $webhooks['whatsapp_verify'],   'icon' => 'check-circle'],
                ['label' => 'WhatsApp Receive',  'value' => $webhooks['whatsapp_receive'],  'icon' => 'message-circle'],
            ] as $wh)
                <div class="sw-list-row" style="flex-direction:column; align-items:flex-start; gap:4px; padding:12px 0;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <i data-lucide="{{ $wh['icon'] }}" style="width:12px;height:12px;color:var(--accent);"></i>
                        <span class="sw-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em;">{{ $wh['label'] }}</span>
                    </div>
                    <code class="sw-code">{{ $wh['value'] }}</code>
                </div>
            @endforeach
        </div>
    </div>

    <div class="sw-card">
        <x-ui.section-header title="Deployment Checklist" icon="clipboard-check" badge="Laragon / VPS" />
        <div style="display:flex; flex-direction:column; gap:0; margin-top:4px;">
            @foreach ([
                ['label' => 'Domain HTTPS aktif',     'value' => 'Wajib untuk webhook live',           'ok' => true ],
                ['label' => 'APP_URL / public_app_url','value' => 'Harus sesuai domain final',          'ok' => true ],
                ['label' => 'Queue worker',            'value' => 'Direkomendasikan aktif di server',   'ok' => true ],
                ['label' => 'Provider payment',        'value' => 'Midtrans atau Xendit',               'ok' => true ],
                ['label' => 'Provider WhatsApp',       'value' => 'Meta WhatsApp Cloud API',            'ok' => true ],
            ] as $check)
                <div class="sw-list-row">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <i data-lucide="{{ $check['ok'] ? 'check-circle' : 'circle' }}" style="width:14px;height:14px;color:{{ $check['ok'] ? 'var(--success)' : 'var(--subtle)' }};flex-shrink:0;"></i>
                        <span style="font-size:13px; color:var(--ink-2);">{{ $check['label'] }}</span>
                    </div>
                    <span style="font-size:12px; font-weight:600; color:var(--muted);">{{ $check['value'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

</x-layouts.sidebar>
