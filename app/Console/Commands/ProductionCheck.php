<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ProductionCheck extends Command
{
    protected $signature   = 'safewash:prod-check';
    protected $description = 'Audit kesiapan production SafeWash — cek env, DB, cache, payment, WhatsApp';

    private int $pass    = 0;
    private int $warn    = 0;
    private int $fail    = 0;

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=blue;options=bold>SafeWash Production Readiness Check</fg=blue;options=bold>');
        $this->line('  '.now()->format('d M Y, H:i:s'));
        $this->newLine();

        $this->checkSection('APPLICATION', [
            fn () => $this->check('APP_ENV = production',    app()->environment('production'),  'Set APP_ENV=production di .env'),
            fn () => $this->check('APP_DEBUG = false',       ! config('app.debug'),              'Set APP_DEBUG=false di .env  ← KRITIS'),
            fn () => $this->check('APP_KEY set',             strlen(config('app.key')) > 10,     'Jalankan: php artisan key:generate'),
            fn () => $this->check('APP_URL bukan localhost', ! str_contains(config('app.url'), 'localhost'), 'Set APP_URL ke domain production'),
        ]);

        $this->checkSection('DATABASE', [
            fn () => $this->check('Koneksi DB berhasil',     $this->dbConnects(),               'Periksa DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD'),
            fn () => $this->check('Tabel users ada',         Schema::hasTable('users'),         'Jalankan: php artisan migrate'),
            fn () => $this->check('Tabel laundry_orders ada',Schema::hasTable('laundry_orders'),'Jalankan: php artisan migrate'),
            fn () => $this->check('DB bukan SQLite',         config('database.default') !== 'sqlite', 'Ganti ke MySQL/PostgreSQL untuk production'),
        ]);

        $this->checkSection('CACHE & SESSION', [
            fn () => $this->check('Cache bukan "array"',     config('cache.default') !== 'array',    'Gunakan Redis atau database untuk cache'),
            fn () => $this->warnCheck('Cache adalah Redis',  config('cache.default') === 'redis',    'Disarankan: CACHE_STORE=redis untuk performa terbaik'),
            fn () => $this->warnCheck('Session adalah Redis',config('session.driver') === 'redis',   'Disarankan: SESSION_DRIVER=redis untuk skalabilitas'),
            fn () => $this->check('Queue bukan sync',        config('queue.default') !== 'sync',     'Set QUEUE_CONNECTION=database atau redis'),
        ]);

        $this->checkSection('MAIL', [
            fn () => $this->check('Mail bukan "log"',        config('mail.default') !== 'log',       'Set MAIL_MAILER=smtp/postmark/resend'),
            fn () => $this->check('MAIL_FROM_ADDRESS diset', config('mail.from.address') !== 'hello@example.com', 'Set MAIL_FROM_ADDRESS ke email nyata'),
        ]);

        $this->checkSection('PAYMENT GATEWAY', [
            fn () => $this->check('Provider bukan simulator', config('safewash.payment.provider') !== 'simulator', 'Set SAFEWASH_PAYMENT_PROVIDER=midtrans atau xendit'),
            fn () => $this->warnCheck('Midtrans server key diset', ! empty(config('services.midtrans.server_key')), 'Set MIDTRANS_SERVER_KEY jika menggunakan Midtrans'),
            fn () => $this->warnCheck('Xendit secret key diset',   ! empty(config('services.xendit.secret_key')),  'Set XENDIT_SECRET_KEY jika menggunakan Xendit'),
            fn () => $this->warnCheck('Notification URL diset',    ! empty(config('services.midtrans.notification_url')), 'Set MIDTRANS_NOTIFICATION_URL ke https://yourdomain/webhooks/payments/midtrans'),
        ]);

        $this->checkSection('WHATSAPP', [
            fn () => $this->warnCheck('WhatsApp enabled',          filter_var(config('safewash.whatsapp.enabled'), FILTER_VALIDATE_BOOL), 'Set SAFEWASH_WHATSAPP_ENABLED=true untuk notifikasi live'),
            fn () => $this->warnCheck('Phone Number ID diset',     ! empty(config('services.whatsapp.phone_number_id')), 'Set WHATSAPP_PHONE_NUMBER_ID dari Meta Developer Console'),
            fn () => $this->warnCheck('Access Token diset',        ! empty(config('services.whatsapp.access_token')),    'Set WHATSAPP_ACCESS_TOKEN dari Meta Developer Console'),
            fn () => $this->warnCheck('Verify Token diset',        ! empty(config('services.whatsapp.verify_token')),    'Set WHATSAPP_VERIFY_TOKEN (string acak buatan sendiri)'),
        ]);

        $this->checkSection('SECURITY', [
            fn () => $this->check('BCRYPT_ROUNDS >= 12',     (int) config('hashing.bcrypt.rounds') >= 12, 'Set BCRYPT_ROUNDS=12 di .env'),
            fn () => $this->check('LOG_LEVEL bukan debug',   config('logging.channels.single.level', 'debug') !== 'debug' || ! app()->environment('production'), 'Set LOG_LEVEL=error untuk production'),
        ]);

        $this->checkSection('LARAVEL OPTIMIZATIONS', [
            fn () => $this->warnCheck('Config cached',       file_exists(base_path('bootstrap/cache/config.php')),   'Jalankan: php artisan config:cache'),
            fn () => $this->warnCheck('Routes cached',       file_exists(base_path('bootstrap/cache/routes-v7.php')),'Jalankan: php artisan route:cache'),
            fn () => $this->warnCheck('Views cached',        is_dir(storage_path('framework/views')) && count(glob(storage_path('framework/views/*.php')) ?: []) > 0, 'Jalankan: php artisan view:cache'),
        ]);

        // ── Summary ────────────────────────────────────────────────────────────
        $this->newLine();
        $this->line('  ─────────────────────────────────────');
        $this->line("  <fg=green>✔ Pass: {$this->pass}</>  <fg=yellow>⚠ Warning: {$this->warn}</>  <fg=red>✘ Fail: {$this->fail}</>");
        $this->newLine();

        $total   = $this->pass + $this->warn + $this->fail;
        $score   = $total > 0 ? round(($this->pass / $total) * 100) : 0;
        $color   = $score >= 80 ? 'green' : ($score >= 60 ? 'yellow' : 'red');
        $verdict = $score >= 80 ? '🟢 Siap production' : ($score >= 60 ? '🟡 Perlu perbaikan sebelum launch' : '🔴 TIDAK siap production');

        $this->line("  Skor: <fg={$color};options=bold>{$score}/100</> — {$verdict}");
        $this->newLine();

        if ($this->fail > 0) {
            $this->line('  Jalankan perintah berikut setelah fix semua item:');
            $this->line('  php artisan config:cache && php artisan route:cache && php artisan view:cache');
            $this->newLine();
        }

        return $this->fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function checkSection(string $title, array $checks): void
    {
        $this->line("  <options=bold>{$title}</options=bold>");
        foreach ($checks as $check) {
            $check();
        }
        $this->newLine();
    }

    private function check(string $label, bool $pass, string $fix = ''): void
    {
        if ($pass) {
            $this->line("  <fg=green>  ✔</> {$label}");
            $this->pass++;
        } else {
            $this->line("  <fg=red>  ✘</> {$label}");
            if ($fix) $this->line("       <fg=red>→ Fix: {$fix}</>");
            $this->fail++;
        }
    }

    private function warnCheck(string $label, bool $pass, string $advice = ''): void
    {
        if ($pass) {
            $this->line("  <fg=green>  ✔</> {$label}");
            $this->pass++;
        } else {
            $this->line("  <fg=yellow>  ⚠</> {$label}");
            if ($advice) $this->line("       <fg=yellow>→ {$advice}</>");
            $this->warn++;
        }
    }

    private function dbConnects(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
