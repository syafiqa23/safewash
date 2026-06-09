<?php

namespace App\Services;

use App\Models\Laundry;

class MerchantScoringService
{
    public function recalculate(Laundry $laundry): float
    {
        $orders = $laundry->orders()->with(['claims', 'paymentTransaction', 'deliveryRequest'])->get();

        if ($orders->isEmpty()) {
            $laundry->update([
                'merchant_score' => 0,
                'score_last_calculated_at' => now(),
            ]);

            return 0;
        }

        $totalOrders = max(1, $orders->count());
        $completedRate = $orders->where('status', 'completed')->count() / $totalOrders;
        $paidRate = $orders->where('payment_status', 'paid')->count() / $totalOrders;
        $digitalPaymentRate = $orders->whereIn('payment_method', ['qris', 'ewallet', 'virtual_account'])->count() / $totalOrders;
        $deliveryReadiness = $orders->where('pickup_delivery_opt_in', true)->count() / $totalOrders;
        $claimPenalty = min(1, $orders->flatMap->claims->where('status', '!=', 'resolved')->count() / $totalOrders);

        $score = (
            ($completedRate * 40) +
            ($paidRate * 25) +
            ($digitalPaymentRate * 15) +
            ($deliveryReadiness * 10) +
            ($laundry->supports_white_label ? 10 : 0)
        ) - ($claimPenalty * 20);

        $normalizedScore = max(0, min(100, round($score, 2)));

        $laundry->update([
            'merchant_score' => $normalizedScore,
            'score_last_calculated_at' => now(),
        ]);

        return $normalizedScore;
    }
}
