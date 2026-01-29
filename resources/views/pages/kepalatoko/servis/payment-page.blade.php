<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Servis #{{ $items->nomor_servis }}</title>

    {{-- Google Fonts: Poppins --}}
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

    {{-- SweetAlert2 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    {{-- Midtrans Snap --}}
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ $clientKey }}"></script>

    <style>
        :root {
            --primary-color: #4F46E5; /* Indigo */
            --success-color: #10B981; /* Emerald */
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

        /* Card Container */
        .invoice-card {
            background: white;
            width: 100%;
            max-width: 480px; /* Ukuran pas untuk HP/Tablet */
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            overflow: hidden;
            position: relative;
            margin-bottom: 20px;
        }

        /* Header */
        .card-header {
            background-color: white;
            padding: 25px;
            text-align: center;
            border-bottom: 2px dashed #E5E7EB;
        }

        .shop-name {
            font-size: 18px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .invoice-title {
            font-size: 12px;
            color: var(--text-light);
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* Body Content */
        .card-body {
            padding: 25px;
        }

        .info-group {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .label {
            font-size: 12px;
            color: var(--text-light);
        }

        .value {
            font-size: 14px;
            font-weight: 600;
            text-align: right;
            color: var(--text-dark);
        }

        .divider {
            height: 1px;
            background-color: #E5E7EB;
            margin: 15px 0;
        }

        /* Total Section */
        .total-section {
            background-color: #F9FAFB;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin-top: 10px;
        }

        .total-label {
            font-size: 12px;
            color: var(--text-light);
            margin-bottom: 5px;
        }

        .total-amount {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
        }

        /* QC Table Mini */
        .qc-summary {
            font-size: 11px;
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }
        .qc-summary th { text-align: left; color: var(--text-light); font-weight: 400; padding-bottom: 5px; }
        .qc-summary td { font-weight: 600; }

        /* Actions Footer */
        .card-footer {
            padding: 20px 25px;
            background: white;
        }

        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn:active {
            transform: scale(0.98);
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        }

        .btn-print {
            background-color: var(--text-dark);
            color: white;
            margin-top: 10px;
        }

        /* Paid State */
        .paid-stamp {
            text-align: center;
            color: var(--success-color);
            border: 2px solid var(--success-color);
            padding: 10px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 2px;
            margin-top: 10px;
            background-color: rgba(16, 185, 129, 0.05);
        }

        /* Print CSS */
        @media print {
            body { background: white; padding: 0; }
            .invoice-card { box-shadow: none; max-width: 100%; border-radius: 0; }
            .btn, .no-print { display: none !important; }
            .paid-stamp { border: 2px solid black; color: black; }
        }
    </style>
</head>

<body>

    <div class="invoice-card">
        <div class="card-header">
            {{-- Jika ada logo user, tampilkan disini (opsional) --}}
            {{-- <img src="{{ asset('storage/' . $users->profile_photo_path) }}" height="50"> --}}
            <div class="shop-name">{{ $users->nama_toko }}</div>
            <div class="invoice-title">Official Receipt #{{ $items->nomor_servis }}</div>
        </div>

        <div class="card-body">
            <div class="info-group">
                <span class="label">Pelanggan</span>
                <span class="value">{{ $items->customer->nama }}<br><small style="font-weight:400">{{ $items->customer->nomor_hp }}</small></span>
            </div>

            <div class="divider"></div>

            <div class="info-group">
                <span class="label">Perangkat</span>
                <span class="value">
                    {{ $items->type->name ?? '-' }}
                </span>
            </div>
            <div class="info-group">
                <span class="label">Keluhan/Kerusakan</span>
                <span class="value" style="color: #EF4444;">{{ $items->kerusakan }}</span>
            </div>
            <div class="info-group">
                <span class="label">Tanggal Masuk</span>
                <span class="value">{{ \Carbon\Carbon::parse($items->created_at)->translatedFormat('d F Y') }}</span>
            </div>

            <div class="total-section">
                <div class="total-label">Total Tagihan</div>
                {{-- Gunakan total_biaya sesuai controller --}}
                <div class="total-amount">Rp {{ number_format($items->biaya, 0, ',', '.') }}</div>
            </div>

            {{-- LOGIKA STATUS PEMBAYARAN --}}
            @if($items->status_pembayaran == 'paid')
                <div class="paid-stamp">
                    ✓ LUNAS (PAID)
                </div>
                <div style="text-align: center; margin-top: 5px; font-size: 11px; color: #aaa;">
                    Terimakasih atas kepercayaan Anda
                </div>
            @else
                <div style="text-align: center; margin-top: 15px; font-size: 12px; color: #666;">
                    Silahkan selesaikan pembayaran untuk pengambilan unit.
                </div>
            @endif
        </div>

        <div class="card-footer no-print">
            @if($items->status_pembayaran == 'paid')
                <button onclick="window.print()" class="btn btn-print">
                    <i class="fa fa-print"></i> Cetak Bukti Pembayaran
                </button>
            @else
                <button id="pay-button" class="btn btn-primary">
                    Bayar Sekarang
                </button>
            @endif
        </div>
    </div>

    {{-- SweetAlert2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script type="text/javascript">
        // Hanya jalankan script jika tombol bayar ada (artinya status belum paid)
        var payButton = document.getElementById('pay-button');

        if (payButton) {
            payButton.addEventListener('click', function () {
                // Tampilkan Loading Swal sebelum Snap muncul (opsional, untuk UX)
                /* Swal.fire({
                    title: 'Memproses...',
                    didOpen: () => { Swal.showLoading() },
                    timer: 1000,
                    showConfirmButton: false
                });
                */

                // Snap Midtrans
                window.snap.pay('{{ $snapToken }}', {
                    // 1. KETIKA SUKSES
                    onSuccess: function (result) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran Berhasil!',
                            text: 'Terima kasih, pembayaran Anda telah diverifikasi.',
                            confirmButtonColor: '#4F46E5',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('payment.success', $items->id) }}";
                            }
                        });
                    },
                    // 2. KETIKA PENDING
                    onPending: function (result) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Menunggu Pembayaran',
                            text: 'Silahkan selesaikan pembayaran sesuai instruksi.',
                            footer: '<a href="">Mengapa saya melihat ini?</a>'
                        });
                    },
                    // 3. KETIKA ERROR
                    onError: function (result) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pembayaran Gagal',
                            text: 'Terjadi kesalahan saat memproses pembayaran.',
                        });
                    },
                    // 4. KETIKA DITUTUP (CLOSE)
                    onClose: function () {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Dibatalkan',
                            text: 'Anda menutup halaman pembayaran sebelum selesai.',
                            confirmButtonColor: '#d33',
                        });
                    }
                });
            });
        }
    </script>
</body>
</html>
