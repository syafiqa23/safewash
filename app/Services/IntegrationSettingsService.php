<?php

namespace App\Services;

use App\Models\IntegrationSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class IntegrationSettingsService
{
    private const CACHE_KEY = 'safewash.integration_settings.map';

    public function all(): Collection
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function () {
            return IntegrationSetting::query()
                ->get()
                ->mapWithKeys(function (IntegrationSetting $setting): array {
                    return [$setting->key => $setting->is_secret && $setting->value !== null
                        ? Crypt::decryptString($setting->value)
                        : $setting->value];
                });
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()->get($key, $default);
    }

    public function putMany(array $settings, int $updatedBy, array $secretKeys = []): void
    {
        foreach ($settings as $key => $value) {
            $isSecret = in_array($key, $secretKeys, true);

            IntegrationSetting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value === null || $value === '' ? null : ($isSecret ? Crypt::encryptString((string) $value) : (string) $value),
                    'is_secret' => $isSecret,
                    'updated_by' => $updatedBy,
                ]
            );
        }

        Cache::forget(self::CACHE_KEY);
    }

    public function masked(string $key): ?string
    {
        $value = $this->get($key);

        if (! is_string($value) || $value === '') {
            return null;
        }

        if (strlen($value) <= 8) {
            return str_repeat('*', strlen($value));
        }

        return substr($value, 0, 4).str_repeat('*', max(4, strlen($value) - 8)).substr($value, -4);
    }

    public function paymentProvider(): string
    {
        return (string) $this->get('payment_provider', config('safewash.payment.provider', 'midtrans'));
    }

    public function appUrl(): string
    {
        return (string) $this->get('public_app_url', config('app.url'));
    }
}
