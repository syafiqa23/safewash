<?php

namespace App\Console\Commands;

use App\Models\LaundryOrder;
use App\Services\IntegrationSettingsService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestWhatsApp extends Command
{
    protected $signature   = 'safewash:test-whatsapp {phone? : Nomor HP tujuan (format: 628xxx)}';
    protected $description = 'Cek koneksi WhatsApp Cloud API dan kirim pesan test';

    public function handle(IntegrationSettingsService $settings): int
    {
        $this->newLine();
        $this->line('  <fg=blue;options=bold>SafeWash WhatsApp Diagnostic</fg=blue;options=bold>');
        $this->newLine();

        // ── 1. Baca konfigurasi ────────────────────────────────────────────────
        $enabled       = filter_var($settings->get('whatsapp_enabled', config('safewash.whatsapp.enabled', false)), FILTER_VALIDATE_BOOL);
        $phoneNumberId = (string) $settings->get('whatsapp_phone_number_id', config('services.whatsapp.phone_number_id'));
        $accessToken   = (string) $settings->get('whatsapp_access_token', config('services.whatsapp.access_token'));
        $verifyToken   = (string) $settings->get('whatsapp_verify_token', config('services.whatsapp.verify_token'));
        $apiVersion    = (string) $settings->get('whatsapp_api_version', config('services.whatsapp.api_version', 'v23.0'));
        $appUrl        = config('app.url');

        $this->table(['Setting', 'Value', 'Status'], [
            ['SAFEWASH_WHATSAPP_ENABLED',  $enabled ? 'true' : 'false',          $enabled ? '<fg=green>✔ aktif</>' : '<fg=yellow>⚠ simulator</>'],
            ['WHATSAPP_PHONE_NUMBER_ID',   $phoneNumberId ?: '(kosong)',          $phoneNumberId ? '<fg=green>✔ ada</>' : '<fg=red>✘ missing</>'],
            ['WHATSAPP_ACCESS_TOKEN',      $accessToken ? substr($accessToken, 0, 8).'…' : '(kosong)',  $accessToken ? '<fg=green>✔ ada</>' : '<fg=red>✘ missing</>'],
            ['WHATSAPP_VERIFY_TOKEN',      $verifyToken ?: '(kosong)',            $verifyToken ? '<fg=green>✔ ada</>' : '<fg=red>✘ missing</>'],
            ['WHATSAPP_API_VERSION',       $apiVersion,                           '<fg=green>✔</>'],
            ['Webhook URL (configure di Meta)', $appUrl.'/webhooks/whatsapp',     '<fg=blue>ℹ</>'],
        ]);

        $this->newLine();

        // ── 2. Mode simulator ─────────────────────────────────────────────────
        if (! $enabled) {
            $this->warn('  Mode: SIMULATOR — pesan tidak dikirim ke WA nyata, hanya dicatat di notification_logs.');
            $this->line('  Untuk live: set SAFEWASH_WHATSAPP_ENABLED=true + isi semua WHATSAPP_* keys.');
            $this->newLine();
            $this->line('  <fg=yellow>Cara mendapatkan credentials Meta WhatsApp Cloud API:</>');
            $this->line('  1. Buka https://developers.facebook.com/apps');
            $this->line('  2. Buat/pilih app → Add Product → WhatsApp');
            $this->line('  3. WhatsApp > API Setup → salin Phone Number ID & Access Token');
            $this->line('  4. Generate Verify Token sesuka Anda (string acak, contoh: sw-'.bin2hex(random_bytes(6)).')');
            $this->line('  5. WhatsApp > Configuration → Webhook URL: '.$appUrl.'/webhooks/whatsapp');
            $this->line('  6. Subscribe events: messages, message_deliveries');
            $this->newLine();
            return self::SUCCESS;
        }

        // ── 3. Validasi token via Graph API ───────────────────────────────────
        $this->line('  <options=bold>Memvalidasi Access Token ke Meta Graph API…</>');

        try {
            $resp = Http::withToken($accessToken)
                ->timeout(10)
                ->get("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}");

            if ($resp->successful()) {
                $data = $resp->json();
                $this->info('  ✔ Token valid. Phone Number ID: '.($data['id'] ?? $phoneNumberId));
                $this->info('  ✔ Display Name: '.($data['display_phone_number'] ?? '-').' | '.($data['verified_name'] ?? '-'));
            } else {
                $this->error('  ✘ Token tidak valid atau Phone Number ID salah.');
                $this->error('  Response: '.$resp->body());
                return self::FAILURE;
            }
        } catch (\Throwable $e) {
            $this->error('  ✘ Gagal terhubung ke Meta API: '.$e->getMessage());
            return self::FAILURE;
        }

        $this->newLine();

        // ── 4. Kirim pesan test (opsional) ────────────────────────────────────
        $targetPhone = $this->argument('phone');

        if (! $targetPhone) {
            $this->line('  <fg=yellow>Tip: tambah argumen nomor HP untuk kirim pesan test:</fg=yellow>');
            $this->line('  php artisan safewash:test-whatsapp 628123456789');
            $this->newLine();
            $this->info('  ✔ Diagnostic selesai. WhatsApp Cloud API terhubung dengan benar.');
            return self::SUCCESS;
        }

        $this->line("  Mengirim pesan test ke {$targetPhone}…");

        $sendResp = Http::withToken($accessToken)
            ->acceptJson()
            ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to'   => $targetPhone,
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body'        => "✅ SafeWash WhatsApp test berhasil!\n\nWaktu: ".now()->format('d M Y, H:i:s')."\nServer: ".config('app.url'),
                ],
            ]);

        if ($sendResp->successful()) {
            $this->info('  ✔ Pesan test terkirim ke '.$targetPhone);
            $this->info('  Message ID: '.($sendResp->json('messages.0.id') ?? '-'));
        } else {
            $this->error('  ✘ Gagal kirim pesan: '.$sendResp->body());
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('  ✔ WhatsApp Cloud API siap digunakan untuk production.');
        $this->newLine();

        return self::SUCCESS;
    }
}
