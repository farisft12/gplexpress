<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Resi - {{ $shipment->resi_number }}</title>
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            padding: 10px;
            max-width: 80mm;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .resi-number {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }
        .qr-code {
            text-align: center;
            margin: 5px 0;
        }
        .qr-code img {
            width: 60px;
            height: 60px;
        }
        .info-section {
            margin: 5px 0;
            padding: 3px 0;
            border-bottom: 1px dashed #ccc;
        }
        .info-label {
            font-weight: bold;
            font-size: 9px;
        }
        .info-value {
            font-size: 9px;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 8px;
            color: #666;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #F4C430; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">
            Print Resi
        </button>
        <a href="{{ route('admin.shipments.index') }}" style="display: inline-block; margin-left: 10px; padding: 10px 20px; background: #gray; color: white; text-decoration: none; border-radius: 5px;">
            Kembali
        </a>
    </div>

    <div class="header">
        <div class="company-name">GPL EXPRES</div>
        <div style="font-size: 8px;">Jasa Pengiriman Paket</div>
    </div>

    <div class="resi-number">{{ $shipment->resi_number }}</div>

    <div class="qr-code">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($shipment->resi_number) }}" alt="QR Code">
    </div>

    <div class="divider"></div>

    <div class="info-section">
        <div class="info-label">PENGIRIM</div>
        <div class="info-value">{{ $shipment->sender_name }}</div>
        <div class="info-value">{{ $shipment->sender_phone }}</div>
        <div class="info-value" style="font-size: 8px;">{{ Str::limit($shipment->sender_address, 40) }}</div>
    </div>

    <div class="info-section">
        <div class="info-label">PENERIMA</div>
        <div class="info-value">{{ $shipment->receiver_name }}</div>
        <div class="info-value">{{ $shipment->receiver_phone }}</div>
        <div class="info-value" style="font-size: 8px;">{{ Str::limit($shipment->receiver_address, 40) }}</div>
    </div>

    <div class="divider"></div>

    <div class="info-section">
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Asal:</span>
            <span class="info-value">{{ $shipment->originBranch->name ?? 'N/A' }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Tujuan:</span>
            <span class="info-value">{{ $shipment->destinationBranch->name ?? 'N/A' }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Jenis:</span>
            <span class="info-value">{{ $shipment->package_type }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Berat:</span>
            <span class="info-value">{{ $shipment->weight }} kg</span>
        </div>
    </div>

    @if($shipment->type === 'cod')
    <div class="info-section">
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Tipe:</span>
            <span class="info-value" style="font-weight: bold;">COD</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Nilai COD:</span>
            <span class="info-value" style="font-weight: bold;">Rp {{ number_format($shipment->cod_amount, 0, ',', '.') }}</span>
        </div>
    </div>
    @else
    <div class="info-section">
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Tipe:</span>
            <span class="info-value">Non-COD</span>
        </div>
        @if($shipment->shipping_cost)
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Ongkir:</span>
            <span class="info-value">Rp {{ number_format($shipment->shipping_cost, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>
    @endif

    <div class="divider"></div>

    <div class="info-section">
        <div class="info-value" style="font-size: 8px; text-align: center;">
            Tanggal: {{ $shipment->created_at->format('d/m/Y H:i') }}
        </div>
    </div>

    <div class="footer">
        Terima kasih telah menggunakan layanan GPL Expres
    </div>

    <script>
        window.onload = function() {
            // Auto print when page loads (optional)
            // window.print();
        };
    </script>
</body>
</html>




