# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# First-time setup
composer install && php artisan key:generate && php artisan migrate --seed && npm install && npm run build

# Dev server (runs server + queue + logs + vite concurrently)
composer dev

# Run all tests
composer test

# Run a single test file
php artisan test tests/Feature/AdminPagesTest.php

# Run a specific test method
php artisan test --filter=test_method_name

# Code formatting
./vendor/bin/pint

# Reset database with fresh seed
php artisan migrate:fresh --seed

# Clear config after .env changes
php artisan config:clear && php artisan cache:clear
```

## Architecture Overview

This is a **Laravel 12** multi-role SaaS for laundry businesses. Three user roles (`admin`, `merchant`, `customer`) are stored on the `users.role` column and enforced by `EnsureRole` middleware (used as `role:admin,merchant` in routes).

### Role-based routing

- `/dashboard` — role-aware dashboard (dispatches to different views per role)
- `/admin/*` — admin-only: platform analytics, merchant management, integration settings, reports
- `/orders/*` — all authenticated users; write routes restricted to `admin,merchant`
- `/track/{code}` — public QR tracking page (no auth)
- `/webhooks/payments/*` and `/webhooks/whatsapp` — unauthenticated webhook endpoints

### Service layer

Business logic lives in `app/Services/`:

| Service | Responsibility |
|---|---|
| `PaymentGatewayService` | Routes payment creation/settlement to the correct provider based on `SAFEWASH_PAYMENT_PROVIDER` env |
| `MidtransPaymentService` | Midtrans Snap integration |
| `XenditPaymentService` | Xendit Payment Request integration |
| `IntegrationSettingsService` | Reads live keys from `integration_settings` table first, falling back to `.env` — allows admin to update keys at runtime without redeployment |
| `WhatsAppGatewayService` | Meta WhatsApp Cloud API; silently falls back to simulated log when disabled |
| `LoyaltyProgramService` | Awards points and upgrades tiers (Ocean → Sky → Cloud → Aurora) on completed, paid orders |
| `MerchantScoringService` | Calculates 0–100 merchant score from completion rate, payment rate, digital adoption, delivery readiness, and claim penalty |

### Simulator vs live mode

The app ships with a safe default (`SAFEWASH_PAYMENT_PROVIDER=simulator`, `SAFEWASH_WHATSAPP_ENABLED=false`). Switching to live requires only env changes; no code paths differ. `PaymentGatewayService` uses a `match()` on the provider string; `WhatsAppGatewayService` checks `enabled` before making HTTP calls.

### Key models and relationships

- `Laundry` — merchant's shop; belongs to a `User` (merchant role)
- `LaundryOrder` — central entity; auto-generates `tracking_code` (`SW-XXXXXXXX`) and `qr_token` (UUID) on create
- `PaymentTransaction` — one per order; updated by webhooks or manual settlement
- `DeliveryRequest` — optional pickup/delivery leg on an order
- `Claim` — customer claim on a completed order
- `LoyaltyAccount` / `LoyaltyTransaction` — per-customer loyalty state
- `NotificationLog` — every WhatsApp message attempt (sent, simulated, failed, received inbound)
- `IntegrationSetting` — key-value store for live payment and WhatsApp credentials managed via admin UI

### App-specific config

`config/safewash.php` holds payment providers, loyalty tier thresholds, delivery partner names, and gateway fee rate. All values are overridable via env.

### Tests

Tests use SQLite in-memory (`DB_DATABASE=:memory:`). Feature tests cover admin pages, merchant management, integration settings, webhook handling, and order growth features. No mocking of the database — tests hit the real in-memory SQLite instance.

### Frontend

Blade templates with Vite. Views are organized under `resources/views/` by role: `admin/`, `dashboard/`, `orders/`, `tracking/`, `auth/`, `layouts/`, `components/`.

## Business rules to know

- Admin takes 15% commission + 3% service fee from each completed, paid order
- Merchant score formula: `(completedRate×40) + (paidRate×25) + (digitalPaymentRate×15) + (deliveryReadiness×10) + (supportsWhiteLabel?10:0) − (claimPenalty×20)`, clamped to 0–100
- Loyalty points: `max(10, floor(total_price / 5000))` per completed paid order
- `IntegrationSettingsService` always checks the DB row first so admin UI changes take effect immediately without cache clearing
