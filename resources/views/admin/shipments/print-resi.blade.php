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
<<<<<<< HEAD
            padding-bottom: 2px;
=======
            padding-bottom: 5px;
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
<<<<<<< HEAD
        .logo {
            max-width: 100%;
            height: auto;
            max-height: 80px;
            width: auto;
            margin: 0 auto;
            display: block;
            object-fit: contain;
        }
=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
<<<<<<< HEAD
        <img src="{{ asset('img/LOHO.png') }}" alt="GPL Express" class="logo">
=======
        <div class="company-name">GPL EXPRES</div>
        <div style="font-size: 8px;">Jasa Pengiriman Paket</div>
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
    </div>

    <div class="resi-number">{{ $shipment->resi_number }}</div>

<<<<<<< HEAD
    @if(($shipment->source_type ?? ($shipment->expedition_id ? 'ekspedisi_lain' : 'perorangan')) === 'ekspedisi_lain' && $shipment->external_resi_number)
    <div class="info-section">
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Resi {{ $shipment->expedition->code ?? $shipment->expedition->name ?? 'Ekspedisi' }}:</span>
            <span class="info-value" style="font-weight: bold;">{{ $shipment->external_resi_number }}</span>
        </div>
    </div>
    @endif

=======
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
    <div class="qr-code">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($shipment->resi_number) }}" alt="QR Code">
    </div>

    <div class="divider"></div>

    <div class="info-section">
        <div class="info-label">PENGIRIM</div>
        <div class="info-value">{{ $shipment->sender_name }}</div>
<<<<<<< HEAD
        <div class="info-value">{{ $shipment->sender_phone ?? ($shipment->external_resi_number ?? '-') }}</div>
=======
        <div class="info-value">{{ $shipment->sender_phone }}</div>
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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
<<<<<<< HEAD
            <span class="info-label">Nominal COD:</span>
            <span class="info-value">Rp {{ number_format($shipment->cod_amount, 0, ',', '.') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Ongkir:</span>
            <span class="info-value">Rp {{ number_format($shipment->cod_shipping_cost ?? 0, 0, ',', '.') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between;">
            <span class="info-label">Admin COD:</span>
            <span class="info-value">Rp {{ number_format($shipment->cod_admin_fee ?? 0, 0, ',', '.') }}</span>
        </div>
        <div style="display: flex; justify-content: space-between; border-top: 1px solid #ccc; padding-top: 3px; margin-top: 3px;">
            <span class="info-label" style="font-weight: bold;">Total:</span>
            <span class="info-value" style="font-weight: bold;">Rp {{ number_format($shipment->total_cod_collectible, 0, ',', '.') }}</span>
=======
            <span class="info-label">Nilai COD:</span>
            <span class="info-value" style="font-weight: bold;">Rp {{ number_format($shipment->cod_amount, 0, ',', '.') }}</span>
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
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

<<<<<<< HEAD
    <div class="info-section" style="border-bottom: none;">
        <div class="info-value" style="font-size: 8px; text-align: center; font-weight: bold;">
            Bila ada kendala silahkan hubungi CS: 0817779942
        </div>
    </div>

    <div class="footer">
        Terima kasih telah menggunakan layanan GPL Express
=======
    <div class="footer">
        Terima kasih telah menggunakan layanan GPL Expres
>>>>>>> 8415c2504e0943d7af6fcb75f06c3f500ecde573
    </div>

    <script>
        window.onload = function() {
            // Auto print when page loads (optional)
            // window.print();
        };
    </script>
</body>
</html>




