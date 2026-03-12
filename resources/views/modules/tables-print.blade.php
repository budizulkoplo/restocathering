<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cetak QR Meja {{ $table->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 24px; }
        .sheet { width: 420px; margin: 0 auto; border: 1px solid #ccc; padding: 24px; text-align: center; }
        .qr { width: 240px; height: 240px; margin: 20px auto; display: block; }
        .muted { color: #666; font-size: 12px; word-break: break-all; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <div class="sheet">
        <h2>Meja {{ $table->name }}</h2>
        <p>Kode: {{ $table->code }}</p>
        <img class="qr" src="https://quickchart.io/qr?size=240&text={{ urlencode($orderUrl) }}" alt="QR Meja">
        <p>Scan untuk melakukan pemesanan dari meja ini.</p>
        <p class="muted">{{ $orderUrl }}</p>
        <button class="print-btn" onclick="window.print()">Cetak</button>
    </div>
</body>
</html>
