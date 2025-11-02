<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Produk Sparepart</title>
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
			Laporan Produk Sparepart
		</h4>
	</div>

	<h4 style="margin-bottom: 6px; text-decoration: underline;">
		Ringkasan
	</h4>

	<table id="ringkasan">
		<tbody>
			@if ($pilihan === "tersedia")
				<tr>
					<th style="width: 100px;">Total Item</th>
					<th>: {{ $jumlah_item_tersedia }}</th>
				</tr>
				<tr>
					<th style="width: 100px;">Total Stok</th>
					<th>: {{ $jumlah_stok_tersedia }}</th>
				</tr>
				<tr>
					<th style="width: 100px;">Total Modal</th>
					<th>: Rp. {{ number_format($modal_stok_tersedia) }}</th>
				</tr>
			@else
				<tr>
					<th style="width: 100px;">Total Item</th>
					<th>: {{ $jumlah_item_habis }}</th>
				</tr>
			@endif
		</tbody>
	</table>

	<h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
		Detail Produk
	</h4>

	<table id="detail">
		<thead>
			<tr>
				<th>No.</th>
				<th>Nama Produk</th>
				<th>Kode</th>
				<th>Stok</th>
				<th>Modal</th>
				<th>Harga Jual</th>
			</tr>
		</thead>
		<tbody>
			@php
				$i = 1
			@endphp
			@foreach ($products as $item)
				<tr>
					<td style="width: 10px;">{{ $i++ }}</td>
					{{-- <td style="text-align: left; width: 90px;">
						@if ($item->product->categories_id == 1)
							{{ $item->product->product_name }} {{ $item->product->kondisi }} {{ $item->product->warna }} {{ $item->product->ram }}/@if($item->product->capacity != null)
                                                {{ $item->product->capacity->name }}
                                            @else
                                                -
                                            @endif {{ $item->product->keterangan }} (IMEI {{ $item->product->nomor_seri }})
						@else
							{{ $item->product->product_name }} {{ $item->product->keterangan }}
						@endif
					</td> --}}
					<td style="text-align: left;">
						{{ $item->product_name }}
					</td>
					<td style="text-align: center; width: 90px;">{{ $item->product_code }}</td>
					<td style="text-align: center; width: 40px;">{{ $item->stok }}</td>
					<td style="width: 90px; text-align: right;">Rp. {{ number_format($item->harga_modal) }}</td>
					<td style="width: 90px; text-align: right;">Rp. {{ number_format($item->harga_jual) }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>
