<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Pengeluaran</title>
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
			Laporan Pengeluaran {{  $tipe  }}
		</h4>
		<p style="margin-top: 0">
			Periode : {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}
			@if (!empty($selectedUser))
				<br><span style="font-weight: bold;">Akun / Karyawan: {{ $selectedUser->name }}</span>
			@endif
		</p>
	</div>

	<table id="detail">
		<thead>
			<tr>
				<th>No.</th>
				<th>Tanggal</th>
				<th>Nama</th>
				<th>Item Pengeluaran</th>
				<th>Biaya</th>
			</tr>
		</thead>
		<tbody>
			@php
				$i = 1
			@endphp
			@foreach ($expenses as $item)
				<tr>
					<td style="width: 10px;">{{ $i++ }}</td>
					<td class="text-center">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</td>
					@if ($item->user)
						<td style="text-align: left;" class="capital">
							{{ $item->user->name }}
						</td>
					@elseif ($item->user()->withTrashed()->first())
						<td style="text-align: left;" class="capital">
							{{ $item->user()->withTrashed()->first()->name }}
						</td>
					@else
						<td style="text-align: center;" class="capital">
							-
						</td>
					@endif
					<td style="text-align: left;" class="capital">{{ $item->name }}</td>
					<td style="text-align: right;">Rp. {{ number_format($item->price) }}</td>
				</tr>
			@endforeach
			<tr>
				<th colspan="4">Total Biaya</th>
				<td style="text-align: right;">Rp. {{ number_format($total_pengeluaran) }}</td>
			</tr>
		</tbody>
	</table>

</body>
</html>