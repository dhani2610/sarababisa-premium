<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan History Garansi</title>
    <style>
        @page {
            margin: 3mm 4mm 10mm 3mm;
            /* Atur margin atas, kanan, bawah, dan kiri */
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .capital {
            text-transform: uppercase;
        }

        #ringkasan td,
        th,
        tr,
        table {
            border-collapse: collapse;
            font-size: 12px;
            line-height: 1em;
            padding: 4px 0 4px 0;
            text-align: left;
        }

        #detail td,
        #detail th,
        #detail tr,
        #detail table {
            border-collapse: collapse;
            font-size: 12px;
            line-height: 1em;
            padding: 4px;
            text-align: center;
            border: solid;
        }

        #analisis td,
        th,
        tr,
        table {
            border-collapse: collapse;
            font-size: 14px;
            line-height: 1em;
            width: 100%;
            padding: 4px 0 4px 0;
            text-align: left;
        }

        #data {
            border-bottom: 1px solid #ddd;
        }

        /* ✅ Fokus di sini: perkecil font hanya untuk tabel Detail Servis */
        #detail td,
        #detail th {
            border: 1px solid #000;
            border-collapse: collapse;
            font-size: 9px;
            /* kecilkan font */
            line-height: 0.9em;
            /* rapatkan jarak antar baris */
            padding: 2px 3px;
            /* kecilkan padding */
            text-align: center;
            word-wrap: break-word;
            /* pecah teks panjang biar tidak keluar */
            white-space: normal;
            /* biar bisa turun ke baris baru */
        }

        #detail {
            width: 100%;
            table-layout: fixed;
            /* pastikan tabel menyesuaikan lebar halaman */
        }

        #analisis td,
        th,
        tr,
        table {
            border-collapse: collapse;
            font-size: 14px;
            line-height: 1em;
            width: 100%;
            padding: 4px 0 4px 0;
            text-align: left;
        }

        #data {
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body>
	<div class="text-center">
		@if ($users->profile_photo_path != null)
			<img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="" height="70">
		@endif
		<h4 style="margin-top: 5px; margin-bottom: 0">{{ $users->nama_toko }}</h4>
		<p style="margin-top: 3px; margin-bottom: 5px;">{{ $users->alamat_toko }}</p>
	</div>

	<hr style="border-top: 1px dashed; margin-bottom: 0;">

	<div class="text-center">
		<h4 style="margin-bottom: 6px; margin-top: 5px;">
			LAPORAN HISTORY GARANSI
		</h4>
		<p style="margin-top: 0">Periode : {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
	</div>

    <br>

    <h4 style="margin-bottom: 6px; text-decoration: underline;">
        Ringkasan
    </h4>

   
    <table id="ringkasan">
        <tbody>
            <tr>
                <th>Total Data</th>
                <th>:{{ $totalData }}</th>
                <th>Total Selesai</th>
                <th>:{{ $totalSelesai }}</th>
            </tr>
            <tr>
                <th>Total Proses</th>
                <th>:{{ $totalProses }}</th>
                <th>Total Batal</th>
                <th>:{{ $totalBatal }}</th>
            </tr>
        </tbody>
    </table>

    <h4 style="margin-top: 15px; margin-bottom: 6px; text-decoration: underline;">
        Detail History
    </h4>
    <br>
    <table id="detail">
        <thead>
            <tr>
                <th>No</th>
                <th>No. Servis</th>
                <th>Nama Pelanggan</th>
                <th>Nama Teknisi</th>
                <th>Penerima</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($data as $item)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $item->service->nomor_servis ?? '-' }}</td>
                    <td class="text-left">{{ $item->service->nama_pelanggan ?? '-' }}</td>
                    <td>{{ $item->teknisi->name ?? '-' }}</td>
                    <td>{{ $item->penerima->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                    <td>{{ $item->status }}</td>
                    <td class="text-left">{{ $item->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>


</body>

</html>
