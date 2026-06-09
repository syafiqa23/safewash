<?php

namespace App\Services;

use App\Models\LaundryOrder;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTransaction;

class LoyaltyProgramService
{
    public function awardCompletedOrder(LaundryOrder $order): ?LoyaltyTransaction
    {
        if (! $order->customer_id || $order->payment_status !== 'paid' || $order->status !== 'completed') {
            return null;
        }

        $existing = LoyaltyTransaction::where('laundry_order_id', $order->id)
            ->where('type', 'earn')
            ->first();

        if ($existing) {
            return $existing;
        }

        $account = LoyaltyAccount::firstOrCreate(
            ['customer_id' => $order->customer_id],
            ['tier' => 'Ocean', 'points_balance' => 0, 'lifetime_points' => 0]
        );

        $points = max(
            (int) config('safewash.loyalty.minimum_points_per_order', 10),
            (int) floor((float) $order->total_price / 5000) * (int) config('safewash.loyalty.earn_rate_per_5000', 1)
        );

        $transaction = $account->transactions()->create([
            'laundry_order_id' => $order->id,
            'type' => 'earn',
            'points' => $points,
            'description' => 'Poin loyalitas dari order '.$order->tracking_code,
        ]);

        $lifetimePoints = $account->lifetime_points + $points;
        $account->update([
            'points_balance' => $account->points_balance + $points,
            'lifetime_points' => $lifetimePoints,
            'tier' => $this->resolveTier($lifetimePoints),
        ]);

        $order->update([
            'loyalty_points_earned' => $points,
        ]);

        return $transaction;
    }

    private function resolveTier(int $lifetimePoints): string
    {
        $tier = 'Ocean';

        foreach ((array) config('safewash.loyalty.tiers', []) as $label => $minimum) {
            if ($lifetimePoints >= $minimum) {
                $tier = $label;
            }
        }

        return $tier;
    }
}
