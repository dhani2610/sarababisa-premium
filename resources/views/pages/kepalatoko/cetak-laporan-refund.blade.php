<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Refund</title>
    <style>
        @page { margin: 3mm 4mm 10mm 3mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        #detail td, #detail th {
            border: 1px solid #000;
            border-collapse: collapse;
            font-size: 10px;
            padding: 3px;
            text-align: center;
        }
        #detail { width: 100%; border-collapse: collapse; }

        h4 { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="text-center">
        @if ($users->profile_photo_path)
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="" height="70">
        @endif
        <h4 style="margin-top: 5px;">{{ $users->nama_toko }}</h4>
        <p style="margin-top: 3px;">{{ $users->alamat_toko }}</p>
    </div>

    <hr style="border-top: 1px dashed;">

    <div class="text-center">
        <h4>LAPORAN REFUND</h4>
        <p>Periode: {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
    </div>

    <h4 style="text-decoration: underline;">Ringkasan</h4>
    <table style="font-size: 12px; margin-bottom: 10px;">
        <tr>
            <td>Total Data</td><td>: {{ $totalData }}</td>
        </tr>
        <tr>
            <td>Total Refund</td><td>: Rp {{ number_format($totalRefund, 0, ',', '.') }}</td>
        </tr>
    </table>

    <h4 style="text-decoration: underline;">Detail Refund</h4>

    <table id="detail">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nomor Servis</th>
                <th>Customer</th>
                <th>Teknisi</th>
                <th>Nominal Potongan Teknisi</th>
                <th>Nominal Potongan Servis</th>
                <th>Bulan/Tahun</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach ($refunds as $r)
                <tr>
                    <td>{{ $i++ }}</td>
                    <td>{{ \Carbon\Carbon::parse($r->created_at)->format('d-m-Y') }}</td>
                    <td>#{{ $r->ServiceTransaction->nomor_servis ?? '-' }}</td>
                    <td>{{ $r->ServiceTransaction->user->name ?? '-' }}</td>
                    <td>{{ $r->teknisi->name ?? '-' }}</td>
                    <td>Rp {{ number_format($r->nominal, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($r->nominal_servis, 0, ',', '.') }}</td>
                    <td>{{ $r->period ? $r->period->format('F Y') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
