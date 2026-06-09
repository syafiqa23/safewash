<?php

namespace App\Services;

use App\Models\LaundryOrder;
use App\Models\NotificationLog;
use Illuminate\Support\Facades\Http;

class WhatsAppGatewayService
{
    public function __construct(private readonly IntegrationSettingsService $settings)
    {
    }

    public function sendOrderUpdate(LaundryOrder $order, string $message): NotificationLog
    {
        $gateway = (string) $this->settings->get('whatsapp_gateway_name', config('safewash.whatsapp.gateway_name'));
        $enabled = filter_var($this->settings->get('whatsapp_enabled', config('safewash.whatsapp.enabled', false)), FILTER_VALIDATE_BOOL);
        $phoneNumberId = (string) $this->settings->get('whatsapp_phone_number_id', config('services.whatsapp.phone_number_id'));
        $accessToken = (string) $this->settings->get('whatsapp_access_token', config('services.whatsapp.access_token'));
        $apiVersion = (string) $this->settings->get('whatsapp_api_version', config('services.whatsapp.api_version', 'v23.0'));

        if (! $enabled || $phoneNumberId === '' || $accessToken === '') {
            return $this->logOnly($order, '['.$gateway.'] '.$message, 'simulated', [
                'mode' => 'simulated',
            ]);
        }

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post("https://graph.facebook.com/{$apiVersion}/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $this->normalizePhoneNumber($order->customer_phone),
                'type' => 'text',
                'text' => [
                    'preview_url' => false,
                    'body' => $message,
                ],
            ]);

        return $this->logOnly(
            $order,
            '['.$gateway.'] '.$message,
            $response->successful() ? 'sent' : 'failed',
            $response->json() ?: ['body' => $response->body()]
        );
    }

    public function verifyWebhook(string $mode, string $verifyToken, string $challenge): ?string
    {
        if ($mode !== 'subscribe') {
            return null;
        }

        return hash_equals((string) $this->settings->get('whatsapp_verify_token', config('services.whatsapp.verify_token')), $verifyToken)
            ? $challenge
            : null;
    }

    public function handleWebhookPayload(array $payload): void
    {
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $value = $change['value'] ?? [];

                foreach ($value['statuses'] ?? [] as $status) {
                    $messageId = $status['id'] ?? null;
                    $recipient = $status['recipient_id'] ?? null;
                    $deliveryStatus = $status['status'] ?? 'unknown';

                    NotificationLog::query()
                        ->when($recipient, fn ($query) => $query->where('recipient', 'like', '%'.$recipient.'%'))
                        ->latest()
                        ->first()?->update([
                            'status' => $deliveryStatus,
                        ]);
                }

                foreach ($value['messages'] ?? [] as $incomingMessage) {
                    $from = $incomingMessage['from'] ?? 'unknown';
                    $text = $incomingMessage['text']['body'] ?? 'Pesan masuk WhatsApp';

                    NotificationLog::create([
                        'laundry_order_id' => null,
                        'channel' => 'whatsapp-inbound',
                        'recipient' => $from,
                        'message' => $text,
                        'status' => 'received',
                        'sent_at' => now(),
                    ]);
                }
            }
        }
    }

    private function normalizePhoneNumber(?string $phone): string
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($phone, '0')) {
            return '62'.substr($phone, 1);
        }

        return $phone;
    }

    private function logOnly(LaundryOrder $order, string $message, string $status, array $payload = []): NotificationLog
    {
        return NotificationLog::create([
            'laundry_order_id' => $order->id,
            'channel' => 'whatsapp-gateway',
            'recipient' => $order->customer_phone,
            'message' => $message,
            'status' => $status,
            'sent_at' => now(),
            'gateway_payload' => $payload,
        ]);
    }
}
