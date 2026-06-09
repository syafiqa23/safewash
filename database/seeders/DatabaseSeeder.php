<?php

namespace Database\Seeders;

use App\Models\Claim;
use App\Models\DeliveryRequest;
use App\Models\Laundry;
use App\Models\LaundryItem;
use App\Models\LaundryOrder;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;
use App\Models\NotificationLog;
use App\Models\PaymentTransaction;
use App\Models\TrackingUpdate;
use App\Models\User;
use App\Services\MerchantScoringService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Users ──────────────────────────────────────────────────────────────
        $admin = User::factory()->create([
            'name'     => 'SafeWash Admin',
            'email'    => 'admin@safewash.test',
            'phone'    => '081200000001',
            'city'     => 'Jakarta',
            'role'     => 'admin',
            'password' => 'password',
        ]);

        $merchant = User::factory()->create([
            'name'     => 'Mitra Laundry Cempaka',
            'email'    => 'merchant@safewash.test',
            'phone'    => '081234567890',
            'city'     => 'Jakarta',
            'role'     => 'merchant',
            'password' => 'password',
        ]);

        $customer = User::factory()->create([
            'name'     => 'Nadia Customer',
            'email'    => 'customer@safewash.test',
            'phone'    => '081298765432',
            'city'     => 'Jakarta',
            'role'     => 'customer',
            'password' => 'password',
        ]);

        // ── Merchant 1 — Cempaka Jakarta ──────────────────────────────────────
        $laundry = Laundry::create([
            'user_id'                    => $merchant->id,
            'name'                       => 'SafeWash Express Cempaka',
            'slug'                       => 'safewash-express-cempaka',
            'phone'                      => '021-555-0199',
            'address'                    => 'Jl. Cempaka No. 12, Jakarta Pusat',
            'city'                       => 'Jakarta',
            'description'                => 'Laundry premium dengan pickup-delivery, proteksi barang, dan tracking QR real-time. Berpengalaman melayani hunian, kos, dan bisnis.',
            'subscription_plan'          => 'professional',
            'commission_rate'            => 15,
            'service_fee_rate'           => 3,
            'is_active'                  => true,
            'premium_protection_enabled' => true,
            'supports_white_label'       => true,
            'network_name'               => 'Cempaka Laundry Group',
            'brand_name'                 => 'Cempaka Clean',
            'brand_primary_color'        => '#2563eb',
            'brand_secondary_color'      => '#1e3a5f',
            'custom_domain'              => 'cempaka.safewash.test',
            'photo_url'                  => '/images/merchants/safewashexpresscempaka.png',
            'rating'                     => 4.8,
            'review_count'               => 127,
            'operating_hours'            => 'Senin–Sabtu, 07.00–21.00',
            'pickup_available'           => true,
            'delivery_available'         => true,
        ]);

        // ── Merchant 2 — BlueBubble Bandung ───────────────────────────────────
        $merchantTwo = User::factory()->create([
            'name'     => 'BlueBubble Laundry',
            'email'    => 'merchant2@safewash.test',
            'phone'    => '081355500222',
            'city'     => 'Bandung',
            'role'     => 'merchant',
            'password' => 'password',
        ]);

        $laundryTwo = Laundry::create([
            'user_id'                    => $merchantTwo->id,
            'name'                       => 'BlueBubble Express',
            'slug'                       => 'bluebubble-express',
            'phone'                      => '022-555-0200',
            'address'                    => 'Jl. Melati No. 21, Bandung',
            'city'                       => 'Bandung',
            'description'                => 'Jaringan laundry modern Bandung dengan teknologi QR tracking dan layanan antar-jemput seluruh Bandung Raya.',
            'subscription_plan'          => 'growth-network',
            'commission_rate'            => 15,
            'service_fee_rate'           => 3,
            'is_active'                  => true,
            'premium_protection_enabled' => true,
            'supports_white_label'       => true,
            'network_name'               => 'BlueBubble Network',
            'brand_name'                 => 'BlueBubble',
            'brand_primary_color'        => '#5eb6f5',
            'brand_secondary_color'      => '#102f45',
            'custom_domain'              => 'bluebubble.safewash.test',
            'photo_url'                  => '/images/merchants/bluebubbleexpress.png',
            'rating'                     => 4.6,
            'review_count'               => 89,
            'operating_hours'            => 'Setiap hari, 08.00–22.00',
            'pickup_available'           => true,
            'delivery_available'         => true,
        ]);

        // ── Merchant 3 — FreshClean Surabaya ─────────────────────────────────
        $merchantThree = User::factory()->create([
            'name'     => 'FreshClean Surabaya',
            'email'    => 'merchant3@safewash.test',
            'phone'    => '031555-0333',
            'city'     => 'Surabaya',
            'role'     => 'merchant',
            'password' => 'password',
        ]);

        $laundryThree = Laundry::create([
            'user_id'                    => $merchantThree->id,
            'name'                       => 'FreshClean Surabaya',
            'slug'                       => 'freshclean-surabaya',
            'phone'                      => '031-555-0333',
            'address'                    => 'Jl. Pemuda 45, Surabaya',
            'city'                       => 'Surabaya',
            'description'                => 'Laundry terpercaya di Surabaya. Spesialis cuci karpet, bed cover, dan pakaian hotel.',
            'subscription_plan'          => 'basic',
            'commission_rate'            => 15,
            'service_fee_rate'           => 3,
            'is_active'                  => true,
            'premium_protection_enabled' => true,
            'supports_white_label'       => false,
            'photo_url'                  => '/images/merchants/freshcleansurabaya.png',
            'rating'                     => 4.4,
            'review_count'               => 54,
            'operating_hours'            => 'Senin–Jumat, 08.00–20.00',
            'pickup_available'           => true,
            'delivery_available'         => false,
        ]);

        // ── Merchant 4 — BersihKilat Yogyakarta ──────────────────────────────
        $merchantFour = User::factory()->create([
            'name'     => 'BersihKilat Jogja',
            'email'    => 'merchant4@safewash.test',
            'phone'    => '0274-555-0444',
            'city'     => 'Yogyakarta',
            'role'     => 'merchant',
            'password' => 'password',
        ]);

        $laundryFour = Laundry::create([
            'user_id'                    => $merchantFour->id,
            'name'                       => 'BersihKilat Jogja',
            'slug'                       => 'bersihkilat-jogja',
            'phone'                      => '0274-555-0444',
            'address'                    => 'Jl. Malioboro 88, Yogyakarta',
            'city'                       => 'Yogyakarta',
            'description'                => 'Laundry kilat 24 jam di pusat kota Yogyakarta. Cocok untuk wisatawan dan mahasiswa.',
            'subscription_plan'          => 'basic',
            'commission_rate'            => 15,
            'service_fee_rate'           => 3,
            'is_active'                  => true,
            'premium_protection_enabled' => false,
            'supports_white_label'       => false,
            'photo_url'                  => '/images/merchants/bersihkilatjogja.png',
            'rating'                     => 4.2,
            'review_count'               => 31,
            'operating_hours'            => '24 Jam',
            'pickup_available'           => false,
            'delivery_available'         => true,
        ]);

        // ── Order 1 — Aktif (masuk status ready_delivery) ─────────────────────
        $order = LaundryOrder::create([
            'laundry_id'             => $laundry->id,
            'customer_id'            => $customer->id,
            'customer_name'          => $customer->name,
            'customer_email'         => $customer->email,
            'customer_phone'         => $customer->phone,
            'service_type'           => 'Cuci + Setrika Express',
            'weight_kg'              => 4.5,
            'total_price'            => 42000,
            'status'                 => 'ready_delivery',
            'payment_status'         => 'paid',
            'payment_method'         => 'qris',
            'tracking_code'          => 'SW-DEMO001',
            'qr_token'               => (string) Str::uuid(),
            'payment_paid_at'        => now()->subHours(6),
            'pickup_delivery_opt_in' => true,
            'pickup_delivery_fee'    => 12000,
            'pickup_address'         => 'Apartemen Harmoni Tower A, Jakarta',
            'delivery_address'       => 'Apartemen Harmoni Tower A, Jakarta',
            'is_premium_protected'   => true,
            'promised_at'            => now()->addHours(3),
            'notes'                  => 'Mohon pisahkan pakaian putih dan seragam kerja.',
        ]);

        LaundryItem::insert([
            ['laundry_order_id' => $order->id, 'item_name' => 'Kemeja kerja', 'quantity' => 5, 'condition_notes' => '2 item memiliki noda ringan.', 'is_priority' => true, 'created_at' => now(), 'updated_at' => now()],
            ['laundry_order_id' => $order->id, 'item_name' => 'Celana bahan', 'quantity' => 2, 'condition_notes' => 'Dalam kondisi baik.', 'is_priority' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        TrackingUpdate::insert([
            ['laundry_order_id' => $order->id, 'status' => 'received',      'title' => 'Order diterima',         'description' => 'Order SW-DEMO001 diterima dan diverifikasi petugas SafeWash.', 'is_visible_to_customer' => true, 'sent_to_customer' => true, 'created_by' => $merchant->id, 'created_at' => now()->subHours(8), 'updated_at' => now()->subHours(8)],
            ['laundry_order_id' => $order->id, 'status' => 'washing',       'title' => 'Proses pencucian',       'description' => 'Batch pencucian dimulai pukul '.now()->subHours(5)->format('H:i').'.', 'is_visible_to_customer' => true, 'sent_to_customer' => true, 'created_by' => $merchant->id, 'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)],
            ['laundry_order_id' => $order->id, 'status' => 'drying',        'title' => 'Proses pengeringan',     'description' => 'Pakaian masuk mesin pengering.', 'is_visible_to_customer' => true, 'sent_to_customer' => true, 'created_by' => $merchant->id, 'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)],
            ['laundry_order_id' => $order->id, 'status' => 'ironing',       'title' => 'Proses penyetrikaan',    'description' => 'Setrika dilakukan oleh petugas berpengalaman.', 'is_visible_to_customer' => true, 'sent_to_customer' => true, 'created_by' => $merchant->id, 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)],
            ['laundry_order_id' => $order->id, 'status' => 'quality_check', 'title' => 'Quality check selesai', 'description' => 'Semua item telah diperiksa dan dikemas rapih.', 'is_visible_to_customer' => true, 'sent_to_customer' => true, 'created_by' => $merchant->id, 'created_at' => now()->subHour(), 'updated_at' => now()->subHour()],
            ['laundry_order_id' => $order->id, 'status' => 'ready_delivery','title' => 'Siap diantar',          'description' => 'Pakaian siap diantar. Kurir akan tiba dalam ±1 jam.', 'is_visible_to_customer' => true, 'sent_to_customer' => true, 'created_by' => $merchant->id, 'created_at' => now()->subMinutes(20), 'updated_at' => now()->subMinutes(20)],
        ]);

        NotificationLog::insert([
            ['laundry_order_id' => $order->id, 'channel' => 'whatsapp-gateway', 'recipient' => $customer->phone, 'message' => 'Order SW-DEMO001 Anda siap diantar!', 'status' => 'sent', 'sent_at' => now()->subMinutes(20), 'created_at' => now()->subMinutes(20), 'updated_at' => now()->subMinutes(20)],
            ['laundry_order_id' => $order->id, 'channel' => 'whatsapp-gateway', 'recipient' => $customer->phone, 'message' => 'Order SW-DEMO001 sedang diproses.', 'status' => 'sent', 'sent_at' => now()->subHours(5), 'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)],
        ]);

        Claim::create([
            'laundry_order_id'  => $order->id,
            'customer_id'       => $customer->id,
            'claimant_name'     => $customer->name,
            'claimant_contact'  => $customer->phone,
            'claim_type'        => 'hilang',
            'item_name'         => 'Kemeja kerja putih',
            'description'       => 'Satu kemeja belum ditemukan saat pengecekan akhir. Kemeja berkerah putih bermerk Polo.',
            'loss_amount'       => 250000,
            'status'            => 'investigating',
            'compensation_amount' => 0,
            'resolution_notes'  => 'Sedang ditelusuri melalui log tracking batch dan foto packing.',
            'submitted_at'      => now()->subMinutes(40),
        ]);

        // ── Order 2 — Sedang diproses ─────────────────────────────────────────
        $secondOrder = LaundryOrder::create([
            'laundry_id'             => $laundry->id,
            'customer_name'          => 'Rifki Santoso',
            'customer_email'         => 'rifki@example.com',
            'customer_phone'         => '081377711122',
            'service_type'           => 'Cuci Lipat Reguler',
            'weight_kg'              => 7.0,
            'total_price'            => 56000,
            'status'                 => 'washing',
            'payment_status'         => 'pending',
            'payment_method'         => 'virtual_account',
            'tracking_code'          => 'SW-DEMO002',
            'qr_token'               => (string) Str::uuid(),
            'is_premium_protected'   => false,
            'pickup_delivery_opt_in' => true,
            'pickup_delivery_fee'    => 15000,
            'pickup_address'         => 'Jl. Kenanga 10, Bekasi',
            'delivery_address'       => 'Jl. Kenanga 10, Bekasi',
            'promised_at'            => now()->addDays(2),
            'notes'                  => 'Harap gunakan deterjen non-parfum.',
        ]);

        TrackingUpdate::insert([
            ['laundry_order_id' => $secondOrder->id, 'status' => 'received', 'title' => 'Order diterima', 'description' => 'Order SW-DEMO002 masuk ke sistem.', 'is_visible_to_customer' => true, 'sent_to_customer' => true, 'created_by' => $merchant->id, 'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)],
            ['laundry_order_id' => $secondOrder->id, 'status' => 'washing',  'title' => 'Sedang dicuci',  'description' => 'Proses pencucian dengan deterjen non-parfum sesuai permintaan.',  'is_visible_to_customer' => true, 'sent_to_customer' => true, 'created_by' => $merchant->id, 'created_at' => now()->subHour(), 'updated_at' => now()->subHour()],
        ]);

        // ── Order 3 — Selesai (BlueBubble) ────────────────────────────────────
        $networkOrder = LaundryOrder::create([
            'laundry_id'             => $laundryTwo->id,
            'customer_id'            => $customer->id,
            'customer_name'          => $customer->name,
            'customer_email'         => $customer->email,
            'customer_phone'         => $customer->phone,
            'service_type'           => 'Laundry Hotel Kit',
            'weight_kg'              => 9.5,
            'total_price'            => 98000,
            'status'                 => 'completed',
            'payment_status'         => 'paid',
            'payment_method'         => 'ewallet',
            'tracking_code'          => 'SW-DEMO003',
            'qr_token'               => (string) Str::uuid(),
            'payment_paid_at'        => now()->subDays(3),
            'pickup_delivery_opt_in' => true,
            'pickup_delivery_fee'    => 18000,
            'pickup_address'         => 'Hotel Sentosa, Bandung',
            'delivery_address'       => 'Hotel Sentosa, Bandung',
            'is_premium_protected'   => true,
            'promised_at'            => now()->subDays(2),
            'picked_up_at'           => now()->subDays(2),
            'delivered_at'           => now()->subDays(1),
            'loyalty_points_earned'  => 19,
            'notes'                  => 'Order hotel white-label BlueBubble.',
        ]);

        // ── Historis orders (untuk grafik) ─────────────────────────────────────
        foreach (range(1, 5) as $m) {
            LaundryOrder::create([
                'laundry_id'            => $laundry->id,
                'customer_name'         => 'Pelanggan Historis '.$m,
                'customer_email'        => 'hist'.$m.'@example.com',
                'customer_phone'        => '08120000'.$m.'99',
                'service_type'          => 'Paket Bulanan Premium',
                'weight_kg'             => 5 + $m,
                'total_price'           => 65000 + ($m * 12000),
                'status'                => 'completed',
                'payment_status'        => 'paid',
                'payment_method'        => $m % 2 === 0 ? 'qris' : 'cash',
                'tracking_code'         => 'SW-HIST0'.$m,
                'qr_token'              => (string) Str::uuid(),
                'is_premium_protected'  => $m % 2 === 0,
                'pickup_delivery_opt_in'=> $m % 2 === 0,
                'pickup_delivery_fee'   => $m % 2 === 0 ? 12000 : 0,
                'payment_paid_at'       => now()->subMonths($m)->addDay(),
                'loyalty_points_earned' => 10 + $m,
                'promised_at'           => now()->subMonths($m)->addDays(2),
                'picked_up_at'          => now()->subMonths($m)->addDays(3),
                'created_at'            => now()->subMonths($m)->addDays(1),
                'updated_at'            => now()->subMonths($m)->addDays(3),
            ]);
        }

        // ── Payment Transactions ──────────────────────────────────────────────
        PaymentTransaction::insert([
            ['laundry_order_id' => $order->id,        'gateway_name' => 'SafeWash Simulator', 'payment_method' => 'qris',            'reference' => 'PAY-DEMO001', 'gross_amount' => 42000, 'gateway_fee' => 630,  'net_amount' => 41370, 'status' => 'paid',    'paid_at' => now()->subHours(6), 'gateway_payload' => '{}', 'created_at' => now()->subHours(6), 'updated_at' => now()->subHours(6)],
            ['laundry_order_id' => $secondOrder->id,  'gateway_name' => 'SafeWash Simulator', 'payment_method' => 'virtual_account', 'reference' => 'PAY-DEMO002', 'gross_amount' => 56000, 'gateway_fee' => 840,  'net_amount' => 55160, 'status' => 'pending', 'paid_at' => null,              'gateway_payload' => '{}', 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)],
            ['laundry_order_id' => $networkOrder->id, 'gateway_name' => 'SafeWash Simulator', 'payment_method' => 'ewallet',         'reference' => 'PAY-DEMO003', 'gross_amount' => 98000, 'gateway_fee' => 1470, 'net_amount' => 96530, 'status' => 'paid',    'paid_at' => now()->subDays(3), 'gateway_payload' => '{}', 'created_at' => now()->subDays(3),  'updated_at' => now()->subDays(3)],
        ]);

        // ── Delivery Requests ─────────────────────────────────────────────────
        DeliveryRequest::insert([
            ['laundry_order_id' => $order->id,       'service_type' => 'round_trip', 'partner_name' => 'SafeWash Courier', 'status' => 'out_for_delivery', 'fee' => 12000, 'pickup_scheduled_at' => now()->subHours(9), 'delivery_scheduled_at' => now()->addHour(), 'picked_up_at' => now()->subHours(8), 'delivered_at' => null, 'pickup_address' => 'Apartemen Harmoni Tower A', 'delivery_address' => 'Apartemen Harmoni Tower A', 'courier_notes' => 'Kurir siap antar sore ini.', 'created_at' => now()->subHours(9), 'updated_at' => now()->subHour()],
            ['laundry_order_id' => $secondOrder->id, 'service_type' => 'round_trip', 'partner_name' => 'SafeWash Courier', 'status' => 'scheduled',       'fee' => 15000, 'pickup_scheduled_at' => now()->addHour(),  'delivery_scheduled_at' => now()->addDays(2), 'picked_up_at' => null,                  'delivered_at' => null, 'pickup_address' => 'Jl. Kenanga 10 Bekasi',    'delivery_address' => 'Jl. Kenanga 10 Bekasi',    'courier_notes' => 'Menunggu pickup.',       'created_at' => now()->subHour(),  'updated_at' => now()->subHour()],
            ['laundry_order_id' => $networkOrder->id,'service_type' => 'round_trip', 'partner_name' => 'BlueBubble Rider','status' => 'delivered',        'fee' => 18000, 'pickup_scheduled_at' => now()->subDays(3),  'delivery_scheduled_at' => now()->subDays(1), 'picked_up_at' => now()->subDays(3),      'delivered_at' => now()->subDays(1), 'pickup_address' => 'Hotel Sentosa Bandung',     'delivery_address' => 'Hotel Sentosa Bandung',     'courier_notes' => 'Selesai diantar.',       'created_at' => now()->subDays(3), 'updated_at' => now()->subDays(1)],
        ]);

        // ── Loyalty ───────────────────────────────────────────────────────────
        $loyaltyAccount = LoyaltyAccount::create([
            'customer_id'    => $customer->id,
            'tier'           => 'Sky',
            'points_balance' => 84,
            'lifetime_points'=> 284,
        ]);

        LoyaltyTransaction::insert([
            ['loyalty_account_id' => $loyaltyAccount->id, 'laundry_order_id' => $networkOrder->id, 'type' => 'earn',       'points' => 19, 'description' => 'Poin dari order SW-DEMO003',        'created_at' => now()->subDays(1), 'updated_at' => now()->subDays(1)],
            ['loyalty_account_id' => $loyaltyAccount->id, 'laundry_order_id' => $order->id,        'type' => 'adjustment', 'points' => 65, 'description' => 'Bonus onboarding loyal customer',    'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)],
        ]);

        // ── Merchant Scoring ──────────────────────────────────────────────────
        $scoringService = app(MerchantScoringService::class);
        $scoringService->recalculate($laundry);
        $scoringService->recalculate($laundryTwo);
        $scoringService->recalculate($laundryThree);
        $scoringService->recalculate($laundryFour);
    }
}
