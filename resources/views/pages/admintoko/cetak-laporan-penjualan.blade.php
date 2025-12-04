<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Penjualan</title>
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
			Laporan Penjualan
		</h4>
		<p style="margin-top: 0">Periode : {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} s/d {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
	</div>

	<h4 style="margin-bottom: 6px; text-decoration: underline;">
		Ringkasan
	</h4>

	<table id="ringkasan">
		<tbody>
			<tr>
                <th>Total Item Penjualan</th>
				<th>: {{ $total_penjualan }} item</th>
				<th>Total Pembayaran Tunai</th>
				<th>: Rp. {{ number_format($total_tunai) }}</th>
			</tr>
			<tr>
				<th>Total Omzet</th>
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
                @if ($toko->is_modal_produk === 1)
				<th>Total Modal</th>
				<th>: Rp. {{ number_format($total_modal) }}</th>
				@endif
				@if ($toko->is_profit_produk === 1)
				<th>Total Profit</th>
				<th>: Rp. {{ number_format($total_profit) }}</th>
				@endif

			</tr>
			{{-- <tr>
				<th>Total Item Penjualan</th>
				<th>: {{ $total_penjualan }} item</th>
				<th></th>
				<th></th>
			</tr> --}}
		</tbody>
	</table>

	<h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
		Detail Penjualan
	</h4>

	<table id="detail">
		<thead>
			<tr>
				<th>No.</th>
				<th>Nota</th>
				<th>Sales</th>
				<th>Pelanggan</th>
				<th>Nama Produk</th>
				<th>Jumlah</th>
				@if ($toko->is_modal_produk === 1)
				<th>Modal</th>
				@endif
				<th>Harga Jual</th>
				<th>Diskon</th>
				@if ($toko->is_bonus_produk === 1)
				<th>Profit</th>
				@endif
			</tr>
		</thead>
		<tbody>
			@php
				$i = 1
			@endphp
			@foreach ($orders as $item)
				<tr>
					<td style="width: 10px;">{{ $i++ }}</td>
					<td class="text-center" style="width: 60px;">{{ $item->order->invoice_no }}</td>
					@if ($item->user)
						<td style="text-align: left; width: 90px;" class="capital">
							{{ $item->user->name }}
						</td>
					@elseif ($item->user()->withTrashed()->first())
						<td style="text-align: left; width: 90px;" class="capital">
							{{ $item->user()->withTrashed()->first()->name }}
						</td>
					@else
						<td style="text-align: center; width: 90px;" class="capital">
							-
						</td>
					@endif
					<td style="text-align: left; width: 90px;" class="capital">{{ $item->order->nama_pelanggan }}</td>
					<td style="text-align: left; width: 90px;">
						@if ($item->product->categories_id == 1)
							{{ $item->product->product_name }} {{ $item->product->kondisi }} {{ $item->product->warna }} {{ $item->product->ram }}/@if($item->product->capacity != null)
                                                {{ $item->product->capacity->name }}
                                            @else
                                                -
                                            @endif {{ $item->product->keterangan }} (IMEI {{ $item->product->nomor_seri }})
						@else
							{{ $item->product->product_name }} {{ $item->product->keterangan }}
						@endif
					</td>
					<td style="text-align: center; width: 40px;">{{ $item->quantity }}</td>
					@if ($toko->is_modal_produk === 1)
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->modal) }}</td>
					@endif
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->total) }}</td>
					<td style="width: 60px; text-align: right;">Rp. {{ number_format($item->sub_total - $item->total) }}</td>
					@if ($toko->is_ === 1)
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->profit) }}</td>
					@endif
				</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>
