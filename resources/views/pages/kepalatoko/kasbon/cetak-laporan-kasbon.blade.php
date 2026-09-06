<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Kasbon</title>
	<style>
		@page {
            margin: 3mm 4mm 10mm 3mm;
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
	</style>
</head>
<body>
	<div class="text-center">
		@if (!empty($imagePath) && file_exists($imagePath))
			<img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="" height="70">
		@endif
		<h4 style="margin-top: 5px; margin-bottom: 0">{{ $users->nama_toko ?? 'SARABA BISA' }}</h4>
		<p style="margin-top: 3px; margin-bottom: 5px;">{{ $users->alamat_toko ?? '' }}</p>
	</div>

	<hr style="border-top: 1px dashed; margin-bottom: 0;">

	<div class="text-center">
		<h4 style="margin-bottom: 6px; margin-top: 5px;">
			Laporan Kasbon Karyawan
		</h4>
		<p style="margin-top: 0">
			Periode : {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}
			@if (!empty($selectedWorker))
				<br><span style="font-weight: bold;">Akun / Karyawan: {{ $selectedWorker->name }}</span>
			@endif
		</p>
	</div>

	<table id="detail" style="width: 100%;">
		<thead>
			<tr>
				<th style="width: 25px;">No.</th>
				<th>Tanggal</th>
				<th>Nama Karyawan</th>
				<th>Keterangan / Item</th>
				<th>Status</th>
				<th>Nominal</th>
			</tr>
		</thead>
		<tbody>
			@php
				$i = 1;
			@endphp
			@forelse ($debts as $item)
				<tr>
					<td style="width: 25px;">{{ $i++ }}</td>
					<td class="text-center">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</td>
					<td style="text-align: left;" class="capital">
						{{ $item->worker->name ?? 'Terhapus' }}
					</td>
					<td style="text-align: left;" class="capital">{{ $item->item }}</td>
					<td class="text-center">
						@if ($item->is_approve === 'Setuju')
							Disetujui
						@elseif ($item->is_approve === 'Ditolak')
							Ditolak
						@else
							Menunggu
						@endif
					</td>
					<td style="text-align: right;">Rp. {{ number_format($item->total, 0, ',', '.') }}</td>
				</tr>
			@empty
				<tr>
					<td colspan="6" class="text-center">Tidak ada data kasbon pada periode ini.</td>
				</tr>
			@endforelse
			<tr>
				<th colspan="5" style="text-align: right;">Total Kasbon</th>
				<td style="text-align: right; font-weight: bold;">Rp. {{ number_format($total_kasbon, 0, ',', '.') }}</td>
			</tr>
		</tbody>
	</table>

</body>
</html>
