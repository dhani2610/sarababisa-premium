<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Transaksi Servis</title>
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
			font-size: 10px;
			line-height: 1em;
			padding: 4px;
			text-align: center;
			border: solid;
			table-layout: fixed;
			word-wrap: break-word;
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
			Laporan Transaksi Servis
		</h4>
		<p style="margin-top: 0">Periode : {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
	</div>

	<h4 style="margin-bottom: 6px; text-decoration: underline;">
		Ringkasan
	</h4>

	<table id="ringkasan">
		<tbody>
			<tr>
				<th>Total Modal Sparepart</th>
				<th>: Rp. {{ number_format($total_modal) }}</th>
				<th>Total Pembayaran Tunai</th>
				<th>: Rp. {{ number_format($total_tunai) }}</th>
			</tr>
			<tr>
				<th>Total Biaya Servis</th>
				<th>: Rp. {{ number_format($total_biaya) }}</th>
				<th>Total Pembayaran Transfer</th>
				<th>: Rp. {{ number_format($total_transfer) }}</th>
			</tr>
			<tr>
				<th>Total Diskon</th>
				<th>: Rp. {{ number_format($total_diskon) }}</th>
				<th>Total Pembayaran Tempo</th>
				<th>: Rp. {{ number_format($total_kredit) }}</th>
			</tr>
			<tr>
				<th>Total Profit</th>
				<th>: Rp. {{ number_format($total_profit) }}</th>
				<th></th>
				<th></th>
			</tr>
			<tr>
				<th>Total Item Servis</th>
				<th>: {{ $total_servis }} Item</th>
				<th></th>
				<th></th>
			</tr>
		</tbody>
	</table>

	<h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
		Detail Transaksi
	</h4>

	<table id="detail">
		<thead>
			<tr>
				<th>No.</th>
				<th>No. Servis</th>
				<th>Pelanggan</th>
				<th>Model Seri</th>
				<th>Tindakan</th>
				<th>Teknisi</th>
				<th>Modal Sparepart</th>
				<th>Biaya Servis</th>
				<th>Diskon</th>
				<th>Profit</th>
			</tr>
		</thead>
		<tbody>
			@php
				$i = 1
			@endphp
			@foreach ($services as $item)
				<tr>
					<td style="width: 10px;">{{ $i++ }}</td>
					<td class="text-center" style="width: 60px;">{{ $item->nomor_servis }}</td>
					<td style="text-align: left; width: 70px;" class="capital">{{ $item->nama_pelanggan }}</td>
					<td style="text-align: left; width: 70px;">
						@if ($item->modelserie)
							{{ $item->modelserie->name ?? '-' }}
						@else
							-
						@endif
					</td>
					<td class="capital" style="text-align: left; width: 80px;">
						@if ($item->kondisi_servis != 'Sudah jadi')
							{{ $item->kondisi_servis }}
						@else
							{{ $item->tindakan_servis }}
						@endif
					</td>
					@if ($item->user)
						<td style="text-align: left; width: 70px;">
							{{ $item->user->name }}
						</td>
					@elseif ($item->user()->withTrashed()->first())
						<td style="text-align: left; width: 70px;">
							{{ $item->user()->withTrashed()->first()->name }}
						</td>
					@else
						<td style="text-align: left; width: 70px;">
							-
						</td>
					@endif
					<td style="width: 60px; text-align: right;">Rp. {{ number_format($item->modal_sparepart) }}</td>
					<td style="width: 60px; text-align: right;">Rp. {{ number_format($item->biaya) }}</td>
					<td style="width: 50px; text-align: right;">Rp. {{ number_format($item->diskon) }}</td>
					<td style="width: 60px; text-align: right;">Rp. {{ number_format($item->profit) }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>

	@if ($expenses->count() != null)
		<h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
			Pengeluaran
		</h4>

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
							<td style="text-align: left;" class="capital">{{ $item->user->name }}</td>
						@else
							<td style="text-align: left;" class="capital">Akun sudah dihapus</td>
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
	@endif

	@if ($incidents->count() != null)
		<h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
			Insiden
		</h4>

		<table id="detail">
			<thead>
				<tr>
					<th>No.</th>
					<th>Tanggal</th>
					<th>Teknisi</th>
					<th>Nama Insiden</th>
					<th>Biaya</th>
				</tr>
			</thead>
			<tbody>
				@php
					$i = 1
				@endphp
				@foreach ($incidents as $item)
					<tr>
						<td style="width: 10px;">{{ $i++ }}</td>
						<td class="text-center">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</td>
						<td style="text-align: left;" class="capital">{{ $item->worker->name }}</td>
						<td style="text-align: left;" class="capital">{{ $item->name }}</td>
						<td style="text-align: right;">Rp. {{ number_format($item->biaya_toko) }}</td>
					</tr>
				@endforeach
				<tr>
					<th colspan="4">Total Biaya</th>
					<td style="text-align: right;">Rp. {{ number_format($total_insiden) }}</td>
				</tr>
			</tbody>
		</table>
	@endif

	{{-- <h4 style="margin-bottom: 6px;">
		<span style="background:color: blue; padding: 6px 12px; border-radius: 50%; color: #fff;">Statistik</span>
	</h4>

	<table id="analisis">
		<thead>
			<th>Merek Terbanyak :</th>
		</thead>
		<tbody>
			<tr>
				@php
					$i = 1
				@endphp
				@foreach($topbrands as $count)
					<td>{{ $i++ }}. {{ $count->brand_name }}</td>
				@endforeach
			</tr>
		</tbody>
	</table>

	<table id="analisis">
		<thead>
			<th>Model Serie Terbanyak :</th>
		</thead>
		<tbody>
			<tr>
				@php
					$i = 1
				@endphp
				@foreach($topmodelseries as $count)
					<td>{{ $i++ }}. {{ $count->model_name }}</td>
				@endforeach
			</tr>
		</tbody>
	</table>

	<table id="analisis">
		<thead>
			<th>Tindakan Terbanyak :</th>
		</thead>
		<tbody>
			<tr>
				@php
					$i = 1
				@endphp
				@foreach($topactions as $count)
					<td>{{ $i++ }}. {{ $count->action_name }}</td>
				@endforeach
			</tr>
		</tbody>
	</table> --}}
</body>
</html>
