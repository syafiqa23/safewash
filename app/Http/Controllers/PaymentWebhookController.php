<?php

namespace App\Http\Controllers;

use App\Mail\PaymentSuccessMail;
use App\Services\LoyaltyProgramService;
use App\Services\MerchantScoringService;
use App\Services\PaymentGatewayService;
use App\Services\WhatsAppGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PaymentWebhookController extends Controller
{
    public function midtrans(
        Request $request,
        PaymentGatewayService $payments,
        MerchantScoringService $merchantScoring,
        LoyaltyProgramService $loyaltyProgram,
        WhatsAppGatewayService $whatsAppGateway,
    ): JsonResponse {
        $payload = $request->all();

        abort_unless($payments->verifyMidtransSignature($payload), 403);

        $transaction = $payments->handleMidtransNotification($payload);

        if ($transaction?->order) {
            $order = $transaction->order->fresh();
            $merchantScoring->recalculate($order->laundry);

            if ($order->payment_status === 'paid') {
                $email = $order->customer_email ?? $order->customer?->email;
                if ($email) {
                    try { Mail::to($email)->send(new PaymentSuccessMail($order)); } catch (\Throwable) {}
                }
            }

            if ($order->status === 'completed' && $order->payment_status === 'paid') {
                $loyaltyProgram->awardCompletedOrder($order);
            }

            $whatsAppGateway->sendOrderUpdate(
                $order,
                'Pembayaran order '.$order->tracking_code.' terverifikasi melalui Midtrans dengan status '.$transaction->webhook_status.'.'
            );
        }

        return response()->json(['ok' => true]);
    }

    public function xendit(
        Request $request,
        PaymentGatewayService $payments,
        MerchantScoringService $merchantScoring,
        LoyaltyProgramService $loyaltyProgram,
        WhatsAppGatewayService $whatsAppGateway,
    ): JsonResponse {
        $token = $request->header('x-callback-token') ?: $request->header('webhook-token');
        abort_unless($payments->verifyXenditWebhook($token), 403);

        $transaction = $payments->handleXenditNotification($request->all());

        if ($transaction?->order) {
            $order = $transaction->order->fresh();
            $merchantScoring->recalculate($order->laundry);

            if ($order->payment_status === 'paid') {
                $email = $order->customer_email ?? $order->customer?->email;
                if ($email) {
                    try { Mail::to($email)->send(new PaymentSuccessMail($order)); } catch (\Throwable) {}
                }
            }

            if ($order->status === 'completed' && $order->payment_status === 'paid') {
                $loyaltyProgram->awardCompletedOrder($order);
            }

            $whatsAppGateway->sendOrderUpdate(
                $order,
                'Pembayaran order '.$order->tracking_code.' terverifikasi melalui Xendit dengan status '.$transaction->webhook_status.'.'
            );
        }

        return response()->json(['ok' => true]);
    }
}
