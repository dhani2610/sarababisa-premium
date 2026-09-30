<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Teknisi</title>
	<style>
		@page {
            margin: 3mm 4mm 10mm 3mm; /* Atur margin atas, kanan, bawah, dan kiri */
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
    #detail th {
        border: 1px solid #000;
        border-collapse: collapse;
        font-size: 8px;        /* kecilkan font agar muat layout portrait */
        line-height: 0.9em;    /* rapatkan jarak antar baris */
        padding: 2px 2px;      /* kecilkan padding */
        text-align: center;
        word-wrap: break-word; /* pecah teks panjang biar tidak keluar */
        white-space: normal;   /* biar bisa turun ke baris baru */
    }

    #detail {
        width: 100%;
        table-layout: fixed;   /* pastikan tabel menyesuaikan lebar halaman */
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
		@php
            $actualPath = public_path($users->profile_photo_path);

        @endphp

        @if ($users->profile_photo_path != null && file_exists($imagePath))
            <img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="Profile Photo" height="70">
        @else
            <span>No Image Available</span>
        @endif
		<h4 style="margin-top: 5px; margin-bottom: 0">{{ $users->nama_toko }}</h4>
		<p style="margin-top: 3px; margin-bottom: 5px;">{{ $users->alamat_toko }}</p>
	</div>

	<hr style="border-top: 1px dashed; margin-bottom: 0;">

	<div class="text-center">
		<h4 style="margin-bottom: 6px; margin-top: 5px;">
			Laporan Teknisi {{ $teknisi->name }}
		</h4>
		<p style="margin-top: 0">Periode : {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
	</div>

	<h4 style="margin-bottom: 6px; text-decoration: underline;">
		Ringkasan
	</h4>

	<table id="ringkasan">
		<tbody>
			<tr>
				<th>Total Bonus</th>
				<th>: Rp. {{ number_format($total_bonus) }}</th>
				<th class="text-center">Total Biaya Servis</th>
				<th class="text-center">: Rp. {{ number_format($total_biaya) }}</th>
				<th class="text-right">Total Tindakan</th>
				<th class="text-right">: {{ $total_tindakan }} Servis</th>
			</tr>
		</tbody>
	</table>

	<h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
		Detail Servis
	</h4>
            {{-- @dd($services); --}}

	<table id="detail">
		<thead>
			<tr>
				<th style="width: 18px;">No.</th>
				<th style="width: 52px;">Tanggal</th>
				<th style="width: 65px;">No. Servis</th>
				<th style="width: 70px;">Pelanggan</th>
				<th style="width: 70px;">Model Seri</th>
				<th>Tindakan</th>
				<th style="width: 52px;">Modal</th>
				<th style="width: 52px;">Biaya</th>
				<th style="width: 40px;">Diskon</th>
				<th style="width: 52px;">Profit</th>
				<th style="width: 52px;">Bonus</th>
			</tr>
		</thead>
		<tbody>
			@php
				$i = 1;
			@endphp
			@foreach ($services as $item)
				<tr>
					<td style="width: 18px;">{{ $i++ }}</td>
					<td class="text-center" style="width: 52px;">{{ $item->tgl_ambil ? \Carbon\Carbon::parse($item->tgl_ambil)->format('d-m-Y') : ($item->tgl_disetujui ? \Carbon\Carbon::parse($item->tgl_disetujui)->format('d-m-Y') : '-') }}</td>
					<td class="text-center" style="width: 65px;">{{ $item->nomor_servis }}</td>
					<td style="text-align: left; width: 70px;" class="capital">{{ $item->nama_pelanggan }}</td>
					<td style="text-align: left; width: 70px;">{{ $item->modelserie->name ?? '-' }}</td>
					<td class="capital" style="text-align: left;">
						@if ($item->kondisi_servis != 'Sudah jadi')
							{{ $item->kondisi_servis }}
						@else
							{{ $item->tindakan_servis }}
						@endif
					</td>
					<td style="width: 52px; text-align: right;">{{ number_format($item->modal_sparepart) }}</td>
					<td style="width: 52px; text-align: right;">{{ number_format($item->biaya) }}</td>
					<td style="width: 40px; text-align: right;">{{ number_format($item->diskon) }}</td>
					<td style="width: 52px; text-align: right;">{{ number_format($item->profit) }}</td>
					<td style="width: 52px; text-align: right; font-weight: bold;">
						{{ number_format($item->bonus ?? getBonusTeknisiByTransaction($item->id, $teknisi->id)) }}
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>
