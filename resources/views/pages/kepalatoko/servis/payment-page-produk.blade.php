<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Sesuaikan title dengan nomor invoice/transaksi --}}
    <title>Pembayaran Produk #{{ $items->nomor_invoice ?? $items->id }}</title>

    {{-- Google Fonts: Poppins --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {{-- Midtrans Snap --}}
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ $clientKey }}"></script>

    <style>
        /* ... STYLE CSS SEBELUMNYA TETAP SAMA ... */
        :root {
            --primary-color: #4F46E5;
            --success-color: #10B981;
            --bg-color: #F3F4F6;
            --text-dark: #1F2937;
            --text-light: #6B7280;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 20px;
            color: var(--text-dark);
            display: flex;
            justify-content: center;
            min-height: 100vh;
        }

        /* ... Tambahan Style untuk List Produk ... */
        .product-list {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .product-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px dashed #eee;
            padding-bottom: 10px;
        }
        .product-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            display: block;
        }
        .product-meta {
            font-size: 11px;
            color: var(--text-light);
        }
        .product-price {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
        }

         /* Paste sisa CSS Anda disini (invoice-card, header, dll) */
         .invoice-card { background: white; width: 100%; max-width: 480px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 20px; }
         .card-header { background-color: white; padding: 25px; text-align: center; border-bottom: 2px dashed #E5E7EB; }
         .shop-name { font-size: 18px; font-weight: 700; margin-bottom: 5px; text-transform: uppercase; }
         .invoice-title { font-size: 12px; color: var(--text-light); letter-spacing: 1px; text-transform: uppercase; }
         .card-body { padding: 25px; }
         .info-group { margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
         .label { font-size: 12px; color: var(--text-light); }
         .value { font-size: 14px; font-weight: 600; text-align: right; }
         .divider { height: 1px; background-color: #E5E7EB; margin: 15px 0; }
         .total-section { background-color: #F9FAFB; padding: 20px; border-radius: 12px; text-align: center; margin-top: 10px; }
         .total-label { font-size: 12px; color: var(--text-light); margin-bottom: 5px; }
         .total-amount { font-size: 24px; font-weight: 700; color: var(--primary-color); }
         .card-footer { padding: 20px 25px; background: white; }
         .btn { display: block; width: 100%; padding: 15px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; }
         .btn-primary { background-color: var(--primary-color); color: white; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3); }
         .btn-print { background-color: var(--text-dark); color: white; margin-top: 10px; }
         .paid-stamp { text-align: center; color: var(--success-color); border: 2px solid var(--success-color); padding: 10px; border-radius: 10px; font-weight: 700; font-size: 18px; margin-top: 10px; background-color: rgba(16, 185, 129, 0.05); }
         @media print { body { background: white; padding: 0; } .invoice-card { box-shadow: none; max-width: 100%; border-radius: 0; } .btn, .no-print { display: none !important; } }
    </style>
</head>

<body>

    <div class="invoice-card">
        <div class="card-header">
            {{-- Nama Toko --}}
            <div class="shop-name">{{ $users->nama_toko }}</div>
            {{-- Gunakan Nomor Invoice atau ID Order --}}
            <div class="invoice-title">Order #{{ $items->nomor_invoice ?? $items->id }}</div>
        </div>

        <div class="card-body">
            <div class="info-group">
                <span class="label">Pelanggan</span>
                <span class="value">{{ $items->customer->nama }}<br><small style="font-weight:400">{{ $items->customer->nomor_hp }}</small></span>
            </div>

            <div class="info-group">
                <span class="label">Tanggal Order</span>
                <span class="value">{{ \Carbon\Carbon::parse($items->created_at)->translatedFormat('d F Y') }}</span>
            </div>

            <div class="divider"></div>

            <div style="font-size: 12px; font-weight: 700; color: var(--text-light); margin-bottom: 10px;">
                Rincian Pembelian
            </div>

            <div class="product-list">
                @foreach($orderDetails as $detail)
                <div class="product-item">
                    <div>
                        <span class="product-name">{{ $detail->product->product_name }}</span>
                        <span class="product-meta">
                            {{ $detail->quantity }} x Rp {{ number_format($detail->price, 0, ',', '.') }}
                            @if($detail->product->nomor_seri)
                                <br>IMEI: {{ $detail->product->nomor_seri }}
                            @endif
                        </span>
                    </div>
                    <div class="product-price">
                        Rp {{ number_format($detail->total, 0, ',', '.') }}
                    </div>
                </div>
                @endforeach
            </div>

            <div class="total-section">
                <div class="total-label">Total Tagihan</div>
                {{-- Mengambil sum dari orderDetails agar sinkron --}}
                <div class="total-amount">Rp {{ number_format($orderDetails->sum('total'), 0, ',', '.') }}</div>
            </div>

            @if (!empty($metodePembayaran->is_payment_gateway) && $metodePembayaran->is_payment_gateway == 1)
                @if($items->status_pembayaran == 'paid')
                    <div class="paid-stamp">
                        ✓ LUNAS (PAID)
                    </div>
                    <div style="text-align: center; margin-top: 5px; font-size: 11px; color: #aaa;">
                        Terimakasih telah berbelanja
                    </div>
                @else
                    <div style="text-align: center; margin-top: 15px; font-size: 12px; color: #666;">
                        Silahkan selesaikan pembayaran.
                    </div>
                @endif
            @else
                @if (!empty($metodePembayaran->is_payment_gateway))
                {{-- Jika Manual Transfer / Cash --}}
                <div style="text-align: center; margin-top: 5px; font-size: 11px; color: #aaa;">
                    <a target="_blank" href="{{ asset('storage/metode-pembayaran/' . $metodePembayaran->foto) }}"><img src="{{ asset('storage/metode-pembayaran/' . $metodePembayaran->foto) }}"  class=" w-auto object-contain rounded" /> </>
                </div>
                @endif
            @endif
        </div>

        <div class="card-footer no-print">
            @if ($metodePembayaran->is_payment_gateway == 1)
                @if($items->status_pembayaran == 'paid')
                    <button onclick="window.print()" class="btn btn-print">
                        <i class="fa fa-print"></i> Cetak Struk
                    </button>
                @else
                    <button id="pay-button" class="btn btn-primary">
                        Bayar Sekarang
                    </button>
                @endif
            @endif
        </div>
    </div>

    {{-- SweetAlert2 JS & Script Payment --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        var payButton = document.getElementById('pay-button');

        if (payButton) {
            payButton.addEventListener('click', function () {
                window.snap.pay('{{ $snapToken }}', {
                    onSuccess: function (result) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil!',
                            text: 'Terima kasih, pesanan Anda sedang diproses.',
                            confirmButtonColor: '#4F46E5',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('payment-produk.success', $items->id) }}";
                            }
                        });
                    },
                    onPending: function (result) {
                        Swal.fire({ icon: 'info', title: 'Menunggu Pembayaran', text: 'Silahkan selesaikan pembayaran.' });
                    },
                    onError: function (result) {
                        Swal.fire({ icon: 'error', title: 'Pembayaran Gagal', text: 'Terjadi kesalahan saat memproses pembayaran.' });
                    },
                    onClose: function () {
                        Swal.fire({ icon: 'warning', title: 'Dibatalkan', text: 'Anda menutup halaman pembayaran.', confirmButtonColor: '#d33' });
                    }
                });
            });
        }
    </script>
</body>
</html>
