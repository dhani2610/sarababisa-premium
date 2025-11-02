<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Admin</title>
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
			Laporan Admin {{ $admin->name }}
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
				<th>: Rp. {{ number_format($total_bonus_admin) }}</th>
				<th>Bonus Servis</th>
				<th>: Rp. {{ number_format($total_bonus_servis) }}</th>
				<th>Bonus Penjualan</th>
				<th class="text-right">: Rp. {{ number_format($total_bonus_penjualan) }}</th>
			</tr>
			<tr>
				<th>Total Input</th>
				<th>: {{ $total_tindakan + $total_penjualan }}</th>
				<th>Input Servis</th>
				<th>: {{ $total_tindakan }}</th>
				<th>Input Penjualan</th>
				<th class="text-right">: {{ $total_penjualan }}</th>
			</tr>
		</tbody>
	</table>

	{{-- Servis --}}
	<h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
		Detail Transaksi Servis
	</h4>

	<table id="detail">
		<thead>
			<tr>
				<th>No.</th>
				<th>Teknisi</th>
				<th>Pelanggan</th>
				<th>Model Seri</th>
				<th>Tindakan</th>
				<th>Modal Sparepart</th>
				<th>Biaya Servis</th>
				<th>Diskon</th>
				<th>Profit</th>
				<th>Bonus</th>
			</tr>
		</thead>
		<tbody>
			@php
				$i = 1
			@endphp
			@foreach ($services as $item)
				<tr>
					<td style="width: 10px;">{{ $i++ }}</td>
					@if ($item->user)
						<td style="text-align: left; width: 70px;">
							{{ $item->user->name }}
						</td>
					@elseif ($item->user()->withTrashed()->first())
						<td style="text-align: left; width: 70px;">
							{{ $item->user()->withTrashed()->first()->name }}
						</td>
					@else
						<td style="text-align: center; width: 70px;">
							-
						</td>
					@endif
					<td style="text-align: left; width: 90px;" class="capital">{{ $item->nama_pelanggan }}</td>
					<td style="text-align: left; width: 80px;">{{ $item->modelserie->name }}</td>
					<td class="capital" style="text-align: left;">
						@if ($item->kondisi_servis != 'Sudah jadi')
							{{ $item->kondisi_servis }}
						@else
							{{ $item->tindakan_servis }}
						@endif
					</td>
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->modal_sparepart) }}</td>
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->biaya) }}</td>
					<td style="width: 60px; text-align: right;">Rp. {{ number_format($item->diskon) }}</td>
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->profit) }}</td>
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->profit / 100 * $item->persen_admin) }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>

	{{-- Penjualan --}}
	<h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
		Detail Transaksi Penjualan
	</h4>

	<table id="detail">
		<thead>
			<tr>
				<th>No.</th>
				<th>Sales</th>
				<th>Pelanggan</th>
				<th>Nama Produk</th>
				<th>Jumlah</th>
				<th>Modal</th>
				<th>Harga Jual</th>
				<th>Profit</th>
				<th>Bonus</th>
			</tr>
		</thead>
		<tbody>
			@php
				$i = 1
			@endphp
			@foreach ($orders as $item)
				<tr>
					<td style="width: 10px;">{{ $i++ }}</td>
					@if ($item->user)
						<td style="text-align: left; width: 90px;" class="capital">{{ $item->user->name }}</td>
					@else
						<td style="text-align: center; width: 90px;" class="capital">-</td>
					@endif
					<td style="text-align: left; width: 90px;" class="capital">{{ $item->order->nama_pelanggan }}</td>
					<td style="text-align: left; width: 80px;">{{ $item->product_name }}</td>
					<td style="text-align: center; width: 80px;">{{ $item->quantity }}</td>
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->modal) }}</td>
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->total) }}</td>
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->profit) }}</td>
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->profit / 100 * $item->persen_admin) }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>
