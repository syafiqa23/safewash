<?php

namespace App\Http\Controllers;

use App\Models\Claim;
use App\Models\LaundryOrder;
use App\Models\NotificationLog;
use App\Models\TrackingUpdate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user         = Auth::user();
        $filter       = $request->input('filter', 'all');   // all | unread
        $notifications = $this->buildNotifications($user, 50);

        if ($filter === 'unread') {
            $notifications = $notifications->where('read', false)->values();
        }

        $unreadCount = $this->buildNotifications($user, 50)->where('read', false)->count();

        return view('notifications.index', compact('notifications', 'filter', 'unreadCount'));
    }

    /** Returns latest 8 notifications as JSON for the bell dropdown. */
    public function bell(): JsonResponse
    {
        $user  = Auth::user();
        $items = $this->buildNotifications($user, 8)->values();

        return response()->json([
            'notifications' => $items,
            'unread_count'  => $this->buildNotifications($user, 50)->where('read', false)->count(),
        ]);
    }

    private function buildNotifications($user, int $limit): Collection
    {
        $items = collect();

        if ($user->isAdmin()) {
            // Admin: recent claims + recent orders
            Claim::with('order.laundry')->latest('submitted_at')->take($limit)->get()
                ->each(function (Claim $c) use (&$items): void {
                    $items->push([
                        'id'    => 'claim-'.$c->id,
                        'icon'  => 'shield-alert',
                        'color' => '#dc2626',
                        'bg'    => '#fee2e2',
                        'title' => 'Klaim baru: '.($c->item_name ?? '-'),
                        'body'  => ($c->order?->tracking_code ?? '-').' · '.($c->order?->laundry?->name ?? '-'),
                        'time'  => $c->submitted_at ?? $c->created_at,
                        'read'  => in_array($c->status, ['resolved', 'compensated', 'closed', 'rejected']),
                        'url'   => route('admin.claims.index'),
                    ]);
                });

            LaundryOrder::with('laundry')->latest()->take($limit)->get()
                ->each(function (LaundryOrder $o) use (&$items): void {
                    $items->push([
                        'id'    => 'order-'.$o->id,
                        'icon'  => 'package',
                        'color' => '#2563eb',
                        'bg'    => '#dbeafe',
                        'title' => 'Order baru: '.$o->tracking_code,
                        'body'  => ($o->laundry?->name ?? '-').' · '.($o->customer_name ?? '-'),
                        'time'  => $o->created_at,
                        'read'  => $o->status === 'completed',
                        'url'   => route('orders.show', ['order' => $o->id]),
                    ]);
                });

        } elseif ($user->isMerchant()) {
            // Merchant: recent tracking updates for their orders
            $laundryIds = $user->laundries()->pluck('id');

            TrackingUpdate::with('order')
                ->whereHas('order', fn ($q) => $q->whereIn('laundry_id', $laundryIds))
                ->latest()->take($limit)->get()
                ->each(function (TrackingUpdate $t) use (&$items): void {
                    $orderId = $t->order?->id ?? $t->laundry_order_id ?? null;
                    $items->push([
                        'id'    => 'tu-'.$t->id,
                        'icon'  => 'activity',
                        'color' => '#059669',
                        'bg'    => '#d1fae5',
                        'title' => $t->title ?? 'Update order',
                        'body'  => ($t->order?->tracking_code ?? '-').' · '.($t->description ?? ''),
                        'time'  => $t->created_at,
                        'read'  => $t->order?->status === 'completed',
                        'url'   => $orderId
                            ? route('orders.show', ['order' => $orderId])
                            : route('orders.index'),
                    ]);
                });

            Claim::with('order')->whereHas('order', fn ($q) => $q->whereIn('laundry_id', $laundryIds))
                ->latest('submitted_at')->take(10)->get()
                ->each(function (Claim $c) use (&$items): void {
                    $items->push([
                        'id'    => 'claim-'.$c->id,
                        'icon'  => 'shield-alert',
                        'color' => '#dc2626',
                        'bg'    => '#fee2e2',
                        'title' => 'Klaim: '.($c->item_name ?? '-'),
                        'body'  => ($c->order?->tracking_code ?? '-').' · '.($c->claimant_name ?? '-'),
                        'time'  => $c->submitted_at ?? $c->created_at,
                        'read'  => in_array($c->status, ['resolved', 'compensated', 'closed', 'rejected']),
                        'url'   => route('claims.index'),
                    ]);
                });

        } else {
            // Customer: their order tracking updates + notification logs
            $orderIds = LaundryOrder::where('customer_id', $user->id)->pluck('id');

            TrackingUpdate::with('order')
                ->whereIn('laundry_order_id', $orderIds)
                ->where('is_visible_to_customer', true)
                ->latest()->take($limit)->get()
                ->each(function (TrackingUpdate $t) use (&$items): void {
                    $trackingCode = $t->order?->tracking_code;
                    $items->push([
                        'id'    => 'tu-'.$t->id,
                        'icon'  => $this->statusIcon($t->status ?? ''),
                        'color' => '#2563eb',
                        'bg'    => '#dbeafe',
                        'title' => $t->title ?? 'Update pesanan',
                        'body'  => ($trackingCode ?? '-').' · '.($t->description ?? ''),
                        'time'  => $t->created_at,
                        'read'  => $t->order?->status === 'completed',
                        'url'   => $trackingCode
                            ? route('customer.tracking', ['code' => $trackingCode])
                            : route('orders.index'),
                    ]);
                });

            NotificationLog::whereIn('laundry_order_id', $orderIds)
                ->latest('sent_at')->take(20)->get()
                ->each(function (NotificationLog $n) use (&$items): void {
                    $items->push([
                        'id'    => 'log-'.$n->id,
                        'icon'  => 'bell',
                        'color' => '#7c3aed',
                        'bg'    => '#ede9fe',
                        'title' => 'Notifikasi WhatsApp',
                        'body'  => \Illuminate\Support\Str::limit($n->message ?? '', 60),
                        'time'  => $n->sent_at ?? $n->created_at,
                        'read'  => $n->status === 'sent',
                        'url'   => route('orders.index'),
                    ]);
                });
        }

        return $items->sortByDesc(fn ($n) => $n['time'])->take($limit)->values();
    }

    private function statusIcon(string $status): string
    {
        return match ($status) {
            'received'       => 'clipboard-check',
            'pickup'         => 'package',
            'washing'        => 'droplets',
            'drying'         => 'wind',
            'ironing'        => 'zap',
            'quality_check'  => 'shield-check',
            'ready_delivery' => 'truck',
            'completed'      => 'check-circle',
            default          => 'bell',
        };
    }
}
