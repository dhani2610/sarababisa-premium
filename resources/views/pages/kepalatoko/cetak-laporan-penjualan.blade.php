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

        /* ✅ Fokus di sini: perkecil font hanya untuk tabel Detail Servis */
    #detail td,
    #detail th {
        border: 1px solid #000;
        border-collapse: collapse;
        font-size: 9px;        /* kecilkan font */
        line-height: 0.9em;    /* rapatkan jarak antar baris */
        padding: 2px 3px;      /* kecilkan padding */
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
				<th>Total Modal</th>
				<th>: Rp. {{ number_format($total_modal) }}</th>
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
				<th>Total Profit</th>
				<th>: Rp. {{ number_format($total_profit) }}</th>
                <th>Total Pengeluaran</th>
                <th>: Rp. {{ number_format($total_pengeluaran) }}</th>
			</tr>
			<tr>
				<th>Total Item Penjualan</th>
				<th>: {{ $total_penjualan }} item</th>
				<th></th>
				<th></th>
			</tr>
		</tbody>
	</table>

    <h4 style="margin-top: 15px; margin-bottom: 6px; text-decoration: underline;">
        Pembayaran Lain
    </h4>
    <table id="ringkasan">
        <tbody>
            @foreach(array_chunk($dataOtherMetodePembayaran, 3) as $row)
                <tr>
                    @foreach($row as $data)
                        <th>{{ $data['metode'] }}</th>
                        <th>: Rp. {{ number_format($data['total']) }}</th>
                    @endforeach
                </tr>
            @endforeach
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
				<th>Modal</th>
				<th>Harga Jual</th>
				<th>Diskon</th>
				<th>Profit</th>
				<th>Pembayaran</th>
			</tr>
		</thead>
		<tbody>
			@php
				$i = 1
			@endphp
			@foreach ($orders as $item)
				<tr>
					<td style="width: 10px;">{{ $i++ }}</td>
					<td class="text-center" style="width: 60px;">
						@if ($item->order != null)
							{{ $item->order->invoice_no }}
						@else
							-
						@endif
					</td>
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
					<td style="text-align: left; width: 90px;" class="capital">
						@if ($item->order != null)
							{{ $item->order->nama_pelanggan }}
						@else
							-
						@endif
					</td>
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
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->modal) }}</td>
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->total) }}</td>
					<td style="width: 60px; text-align: right;">Rp. {{ number_format($item->sub_total - $item->total) }}</td>
					<td style="width: 70px; text-align: right;">Rp. {{ number_format($item->profit) }}</td>
					<td style="width: 70px; text-align: left;">
                        <div><strong>Status:</strong><br> {{ $item->order->tipe_status_pembayaran == 1 ? 'Lunas' : 'Belum Lunas' }} <br>

                        @if ($item->order->payment_method == 'Tunai')
                            Tunai : Rp. {{ number_format($item->order->tunai) }}
                        @elseif ($item->order->payment_method == 'Transfer')
                            Transfer : Rp. {{ number_format($item->order->transfer) }}
                        @elseif ($item->order->payment_method == 'Tunai & Transfer')
                            Tunai : Rp. {{ number_format($item->order->tunai) }} <br> <hr>
                            Transfer : Rp. {{ number_format($item->order->transfer) }}
                        @else
                            {{ $item->order->payment_method }} : Rp. {{ number_format($item->order->transfer) }}
                        @endif
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>

     <hr>
    <h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
        Pengeluaran
    </h4>

    <table id="detail">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tgl Pengeluaran</th>
                <th>Nama</th>
                <th>Item Pengeluaran</th>
                <th>Status</th>
                <th>Biaya</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengeluaran_data as $item)
                <tr>
                    <td style="width: 10px;">{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y') }}</td>
                    <td>
                        @if ($item->user)
                            {{ $item->user->name }}
                        @else
                            Akun sudah dihapus
                        @endif
                    </td>
                    <td>{{ $item->name }}</td>
                    <td>
                        @if ($item->is_approve === null)
                            Belum Disetujui
                        @elseif ($item->is_approve === 'Setuju')
                            Sudah Disetujui
                        @else
                            Ditolak
                        @endif
                    </td>
                    <td>Rp. {{ number_format($item->price) }}</td>
                </tr>
            @endforeach
            <tr>
                <th colspan="5">Total Biaya</th>
                <td style="text-align: right;">Rp. {{ number_format($total_pengeluaran) }}</td>
            </tr>
        </tbody>
    </table>
    <hr>
</body>
</html>
