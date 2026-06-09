<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminReportExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn (array $row) => [
            'merchant' => $row['laundry']->name,
            'owner' => $row['owner']?->name,
            'email' => $row['owner']?->email,
            'status' => $row['laundry']->is_active ? 'active' : 'inactive',
            'plan' => $row['laundry']->subscription_plan,
            'orders_count' => $row['orders_count'],
            'paid_revenue' => $row['paid_revenue'],
            'admin_revenue' => $row['admin_revenue'],
            'merchant_revenue' => $row['merchant_revenue'],
            'open_claims' => $row['open_claims'],
            'merchant_score' => $row['merchant_score'],
            'digital_payment_orders' => $row['digital_payment_orders'],
            'delivery_orders' => $row['delivery_orders'],
            'loyalty_points_issued' => $row['loyalty_points_issued'],
            'white_label_ready' => $row['white_label_ready'] ? 'yes' : 'no',
            'commission_rate' => $row['laundry']->commission_rate,
            'service_fee_rate' => $row['laundry']->service_fee_rate,
        ]);
    }

    public function headings(): array
    {
        return [
            'Merchant',
            'Owner',
            'Email',
            'Status',
            'Plan',
            'Orders Count',
            'Paid Revenue',
            'Admin Revenue',
            'Merchant Revenue',
            'Open Claims',
            'Merchant Score',
            'Digital Payment Orders',
            'Delivery Orders',
            'Loyalty Points Issued',
            'White Label Ready',
            'Commission Rate',
            'Service Fee Rate',
        ];
    }
}
