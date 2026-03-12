<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 24px; }
        .receipt { width: 420px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 6px 0; border-bottom: 1px dashed #999; text-align: left; }
        .text-right { text-align: right; }
        @media print { .print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
    <div class="receipt">
        <h3>Nota Reservasi Catering</h3>
        <p>
            No: {{ $order->order_number }}<br>
            Tanggal Pesan: {{ $order->order_date?->format('d-m-Y') }}<br>
            Tanggal Acara: {{ $order->event_date?->format('d-m-Y') }}<br>
            Customer: {{ $order->customer_name }}
        </p>
        <table>
            <thead>
                <tr><th>Menu</th><th class="text-right">Qty</th><th class="text-right">Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->menu_name }}</td>
                        <td class="text-right">{{ format_qty($item->qty) }}</td>
                        <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="text-right">Subtotal: Rp {{ number_format($order->subtotal, 0, ',', '.') }}</p>
        <p class="text-right">Diskon: Rp {{ number_format($order->discount, 0, ',', '.') }}</p>
        <p class="text-right">DP: Rp {{ number_format($order->down_payment, 0, ',', '.') }}</p>
        <p class="text-right"><strong>Total: Rp {{ number_format($order->total, 0, ',', '.') }}</strong></p>
        <p class="text-right">Tunai: Rp {{ number_format($order->cash_received, 0, ',', '.') }}</p>
        <p class="text-right">Kembalian: Rp {{ number_format($order->change_amount, 0, ',', '.') }}</p>
        <p class="text-right">Sisa: Rp {{ number_format($order->balance_due, 0, ',', '.') }}</p>
        <button class="print-btn" onclick="window.print()">Cetak</button>
    </div>
    <script>
        window.addEventListener('load', () => {
            window.setTimeout(() => window.print(), 150);
        });

        window.addEventListener('afterprint', () => {
            window.close();
        });
    </script>
</body>
</html>
