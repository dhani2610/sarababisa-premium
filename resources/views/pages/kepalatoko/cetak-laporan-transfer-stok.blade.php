<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transfer Stok</title>
    <style>
        @page { margin: 3mm 4mm 10mm 3mm; }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }

        #detail {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        #detail th, #detail td {
            border: 1px solid #000;
            font-size: 10px;
            padding: 4px;
        }

        #detail th {
            background: #f0f0f0;
            text-transform: uppercase;
        }

        h4 { margin: 5px 0; }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="text-center">
        @if ($imagePath)
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" height="70">
        @endif

        <h4 style="margin-top: 5px;">{{ $users->nama_toko }}</h4>
        <p style="margin-top: 3px;">{{ $users->alamat_toko }}</p>
    </div>

    <hr style="border-top: 1px dashed;">

    {{-- JUDUL --}}
    <div class="text-center">
        <h4>LAPORAN TRANSFER STOK</h4>
        <p>
            Periode:
            {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }}
            s/d
            {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}
        </p>
    </div>

    {{-- RINGKASAN --}}
    <h4 style="text-decoration: underline;">Ringkasan</h4>
    <table style="font-size: 12px; margin-bottom: 10px;">
        <tr>
            <td>Total Transfer Stok</td>
            <td>: {{ $transfers->count() }}</td>
        </tr>
    </table>

    {{-- DETAIL --}}
    <h4 style="text-decoration: underline;">Detail Transfer Stok</h4>

    <table id="detail">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Dari Cabang</th>
                <th>Produk Asal</th>
                <th>Ke Cabang</th>
                <th>Produk Tujuan</th>
                <th>Stok</th>
                <th>PIC Transfer</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @php $i = 1; @endphp
            @foreach ($transfers as $t)
                <tr>
                    <td class="text-center">{{ $i++ }}</td>

                    {{-- Tanggal --}}
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($t->tanggal)->format('d-m-Y') }}
                    </td>

                    {{-- Dari Cabang --}}
                    <td class="text-left">
                        {{ optional($t->dariCabang)->nama_cabang ?? '-' }}
                    </td>

                    {{-- Produk Asal --}}
                    <td class="text-left">
                        {{ optional($t->dariProduk)->product_name ?? '-' }}
                    </td>

                    {{-- Ke Cabang --}}
                    <td class="text-left">
                        {{ optional($t->keCabang)->nama_cabang ?? '-' }}
                    </td>

                    {{-- Produk Tujuan --}}
                    <td class="text-left">
                        {{ optional($t->keProduk)->product_name ?? '-' }}
                    </td>

                    {{-- Stok --}}
                    <td class="text-center">{{ $t->stok }}</td>
                    <td class="text-center">{{ $t->pic->name ?? '-' }}</td>

                    {{-- Status --}}
                    <td class="text-center">
                        @if ($t->status == 0)
                            Menunggu
                        @else
                            Disetujui
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
