<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Teknisi</title>
	<style>
		@page {
            margin: 5mm 6mm 10mm 6mm;
        }

		body {
			font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
			color: #333;
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

		#ringkasan {
			width: 100%;
			border-collapse: collapse;
			margin-bottom: 8px;
		}

		#ringkasan th,
		#ringkasan td {
			font-size: 11px;
			line-height: 1.2em;
			padding: 3px 2px;
			text-align: left;
		}

		#detail {
			width: 100%;
			border-collapse: collapse;
			table-layout: fixed;
		}

		#detail th {
			border: 1px solid #333;
			background-color: #f0f0f0;
			font-size: 8px;
			font-weight: bold;
			padding: 4px 2px;
			text-align: center;
		}

		#detail td {
			border: 1px solid #666;
			font-size: 7.5px;
			line-height: 1.15em;
			padding: 3px 2px;
			word-wrap: break-word;
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

	<table id="detail">
		<thead>
			<tr>
				<th style="width: 4%;">No.</th>
				<th style="width: 10%;">Tanggal</th>
				<th style="width: 12%;">No. Servis</th>
				<th style="width: 12%;">Pelanggan</th>
				<th style="width: 11%;">Model Seri</th>
				<th style="width: 17%;">Tindakan</th>
				<th style="width: 8%;">Modal</th>
				<th style="width: 8%;">Biaya</th>
				<th style="width: 5%;">Diskon</th>
				<th style="width: 7%;">Profit</th>
				<th style="width: 6%;">Bonus</th>
			</tr>
		</thead>
		<tbody>
			@php
				$i = 1;
			@endphp
			@foreach ($services as $item)
				<tr>
					<td style="text-align: center;">{{ $i++ }}</td>
					<td style="text-align: center;">{{ $item->tgl_ambil ? \Carbon\Carbon::parse($item->tgl_ambil)->format('d-m-Y') : ($item->tgl_disetujui ? \Carbon\Carbon::parse($item->tgl_disetujui)->format('d-m-Y') : '-') }}</td>
					<td style="text-align: center;">{{ $item->nomor_servis }}</td>
					<td style="text-align: left;" class="capital">{{ $item->nama_pelanggan }}</td>
					<td style="text-align: left;">{{ $item->modelserie->name ?? '-' }}</td>
					<td class="capital" style="text-align: left;">
						@if ($item->kondisi_servis != 'Sudah jadi')
							{{ $item->kondisi_servis }}
						@else
							{{ $item->tindakan_servis }}
						@endif
					</td>
					<td style="text-align: right;">{{ number_format($item->modal_sparepart) }}</td>
					<td style="text-align: right;">{{ number_format($item->biaya) }}</td>
					<td style="text-align: right;">{{ number_format($item->diskon) }}</td>
					<td style="text-align: right;">{{ number_format($item->profit) }}</td>
					<td style="text-align: right; font-weight: bold;">
						{{ number_format($item->bonus ?? getBonusTeknisiByTransaction($item->id, $teknisi->id)) }}
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>
