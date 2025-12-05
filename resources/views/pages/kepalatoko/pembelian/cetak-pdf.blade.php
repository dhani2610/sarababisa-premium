<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Laporan Pembelian</title>
    <style>
        @page { margin: 3mm 4mm 10mm 3mm; }
        body { font-family: sans-serif; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* Style Header */
        h4, p { margin: 2px 0; }

        /* Style Tabel Ringkasan */
        #ringkasan { width: 100%; margin-top: 10px; margin-bottom: 10px; }
        #ringkasan th { text-align: left; font-size: 12px; padding: 2px; }

        /* Style Tabel Detail (Sesuai request user: font kecil) */
        #detail { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        #detail th, #detail td {
            border: 1px solid #000;
            font-size: 9px; /* Font diperkecil */
            padding: 3px;
            text-align: center;
            word-wrap: break-word;
        }
        #detail th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <div class="text-center">
        @if ($users->profile_photo_path != null && file_exists($imagePath))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="" height="60">
        @endif
        <h4>{{ $users->nama_toko }}</h4>
        <p style="font-size: 12px;">{{ $users->alamat_toko }}</p>
    </div>

    <hr style="border-top: 1px dashed black; margin: 5px 0;">

    <div class="text-center">
        <h4 style="text-decoration: underline;">LAPORAN PEMBELIAN BARANG</h4>
        <p style="font-size: 11px;">Periode: {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
    </div>

    <table id="ringkasan">
        <tr>
            <th width="20%">Total Transaksi</th>
            <th>: {{ $purchases->count() }} Transaksi</th>
            <th width="20%">Total Nominal Pembelian</th>
            <th>: Rp. {{ number_format($total_pembelian) }}</th>
        </tr>
        <tr>
            <th>Total Item Dibeli</th>
            <th>: {{ $total_item }} Pcs/Unit</th>
            <th></th>
            <th></th>
        </tr>
    </table>

    <table id="detail">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="15%">No. Referensi</th>
                <th width="15%">Supplier</th>
                <th width="20%">Nama Produk</th>
                <th width="15%">Keterangan</th>
                <th width="8%">Qty</th>
                <th width="15%">Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($purchases as $item)
            <tr>
                <td>{{ $i++ }}</td>
                <td>{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                <td>{{ $item->reference_number }}</td>
                <td style="text-align: left;">{{ $item->suppliers_name }}</td>
                <td style="text-align: left;">
                    @if($item->product)
                        {{ $item->product->product_name }}
                    @else
                        <span style="color:red;">Produk Dihapus</span>
                    @endif
                </td>
                <td>{{ $item->keterangan ?? '-' }}</td>
                <td>{{ $item->quantity }}</td>
                <td style="text-align: right;">Rp. {{ number_format($item->total_price) }}</td>
            </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="6" style="text-align: right; padding-right: 5px;">GRAND TOTAL</td>
                <td>{{ $total_item }}</td>
                <td style="text-align: right;">Rp. {{ number_format($total_pembelian) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
