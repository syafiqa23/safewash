<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Admin SafeWash</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1f3347; font-size: 12px; }
        h1 { margin-bottom: 4px; }
        .muted { color: #6a7f95; }
        .summary { margin: 18px 0; }
        .summary td { padding: 6px 10px; border: 1px solid #d6e6f3; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d6e6f3; padding: 8px; text-align: left; vertical-align: top; }
        th { background: #e9f5ff; }
    </style>
</head>
<body>
    <h1>Laporan Admin SafeWash</h1>
    <div class="muted">Generated: {{ $summary['generated_at']->format('d M Y H:i') }}</div>
    <div class="muted">Filter keyword: {{ $filters['q'] ?? '-' }} | Status: {{ $filters['status'] ?? 'all' }}</div>

    <table class="summary">
        <tr>
            <td>Total Merchant</td>
            <td>{{ $summary['total'] }}</td>
            <td>Aktif</td>
            <td>{{ $summary['active'] }}</td>
            <td>Nonaktif</td>
            <td>{{ $summary['inactive'] }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Merchant</th>
                <th>Owner</th>
                <th>Status</th>
                <th>Plan</th>
                <th>Order</th>
                <th>Omzet Paid</th>
                <th>Pemasukan Admin</th>
                <th>Pendapatan Merchant</th>
                <th>Klaim Terbuka</th>
                <th>Score</th>
                <th>Digital Pay</th>
                <th>Delivery</th>
                <th>White Label</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row['laundry']->name }}</td>
                    <td>{{ $row['owner']?->name }}<br>{{ $row['owner']?->email }}</td>
                    <td>{{ $row['laundry']->is_active ? 'active' : 'inactive' }}</td>
                    <td>{{ $row['laundry']->subscription_plan }}</td>
                    <td>{{ $row['orders_count'] }}</td>
                    <td>Rp{{ number_format($row['paid_revenue'], 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($row['admin_revenue'], 0, ',', '.') }}</td>
                    <td>Rp{{ number_format($row['merchant_revenue'], 0, ',', '.') }}</td>
                    <td>{{ $row['open_claims'] }}</td>
                    <td>{{ number_format($row['merchant_score'], 1) }}</td>
                    <td>{{ $row['digital_payment_orders'] }}</td>
                    <td>{{ $row['delivery_orders'] }}</td>
                    <td>{{ $row['white_label_ready'] ? 'yes' : 'no' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
