<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<style type="text/css">
		html {
			margin: 0;
			padding: 0;
		}
		body {
			font-size: 10px;
			color: #000000;
		}
		
		footer {
			margin-top: 5px;
		}

		.text-center {
			text-align: center;
		}

		.resi {
			margin-top: 5px;
			width: 155px;
    		max-width: 155px;
		}

		td,
		th,
		tr,
		table {
			border-collapse: collapse;
			line-height: 1em;
		}

		td.title {
			width: 60px;
			max-width: 60px;
			word-break: break-all;
		}

		td.value {
			width: 95px;
			max-width: 95px;
			word-break: break-all;
		}
	</style>
    <title>Nota Pengambilan Servis Selesai #{{ $items->nomor_servis }}</title>
</head>
<body>
	<div class="resi">
		<div class="text-center">
			@if ($users->profile_photo_path != null)
				<img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="" height="50">
			@endif
			<p>
				NOTA PENGAMBILAN SERVIS <br>
				<strong>{{ $users->nama_toko }}</strong> <br>
				Telp/WA {{ $users->nomor_hp_toko }}
			</p>
		</div>

		<hr style="border-top: 1px solid; margin: 0px;">

		<table>
			<tbody>
				<tr>
				<td class="title">No. Servis</td>
				<td class="value">: {{ $items->nomor_servis }}</td>
				</tr>
				<tr>
				<td class="title">Pelanggan</td>
				<td class="value">: {{ $items->customer->nama }}</td>
				</tr>
				<tr>
				<td class="title">Nomor HP</td>
				<td class="value">: {{ $items->customer->nomor_hp }}</td>
				</tr>
				<tr>
				<td class="title">Tgl. Masuk</td>
				<td class="value">: {{ \Carbon\Carbon::parse($items->created_at)->locale('id')->translatedFormat('d/m/Y') }} [{{ $items->penerima }}]</td>
				</tr>
				<tr>
				<td class="title">Nama Barang</td>
				<td class="value">: {{ $items->type->name }} {{ $items->brand->name }} {{ $items->modelserie->name }}</td>
				</tr>
				<tr>
				<td class="title">IMEI/SN</td>
				<td class="value">: {{ $items->imei }}</td>
				</tr>
				<tr>
				<td class="title">Kelengkapan</td>
				<td class="value">:
					@if ($items->kelengkapan != null)
						{{ $items->kelengkapan }}
						@else
						Hanya Barang
					@endif
				</td>
				</tr>
				<tr>
				<td class="title">Kerusakan</td>
				<td class="value">: {{ $items->kerusakan }}</td>
				</tr>
				<tr>
				<td class="title">Tindakan</td>
				<td class="value">: {{ $items->tindakan_servis }}</td>
				</tr>
				<tr>
				<td class="title">Biaya Servis</td>
				<td class="value">: Rp. {{ number_format($items->biaya) }}</td>
				</tr>
				@if ($items->diskon != null)
					<tr>
					<td class="title">Diskon</td>
					<td class="value">: Rp. {{ number_format($items->diskon) }}</td>
					</tr>
					<tr>
					<td class="title">Dibayarkan</td>
					<td class="value">: Rp. {{ number_format($items->biaya - $items->diskon) }}</td>
					</tr>
				@endif
				<tr>
				<td class="title">Pembayaran</td>
				<td class="value">: {{ $items->cara_pembayaran }}</td>
				</tr>
				<tr>
				@if ($items->exp_garansi === null)
					<td class="title">Garansi</td>
					<td class="value">: Tidak Ada</td>
				@else
					<td class="title">Masa Garansi</td>
					<td class="value">: {{ \Carbon\Carbon::parse($items->exp_garansi)->locale('id')->translatedFormat('d/m/Y') }}</td>
				@endif
				</tr>
				<tr>
				<td class="title">Teknisi</td>
				@if ($items->user != null)
					<td class="value">: {{ $items->user->name }}</td>
					@else
					<td class="value">: -</td>
				@endif
				</tr>
				<tr>
				<td class="title">Pengecekan Masuk</td>
				<td class="value">: {{ $items->qc_masuk }}</td>
				</tr>
				<tr>
				<td class="title">Pengecekan Keluar</td>
				<td class="value">: {{ $items->qc_keluar }}</td>
				</tr>
				<tr>
				<td class="title">Tgl. Ambil</td>
				<td class="value">: {{ \Carbon\Carbon::parse($items->tgl_ambil)->locale('id')->translatedFormat('d/m/Y') }}</td>
				</tr>
				<tr>
				<td class="title">Pengambil</td>
				<td class="value">: {{ $items->pengambil }}</td>
				</tr>
			</tbody>
		</table>

		<hr style="border-top: 1px solid; margin: 0px;">

		<footer class="text-center">
			<small>Dicetak {{ Auth::user()->name }}, <br> [{{ \Carbon\Carbon::now()->translatedFormat('d/m/Y H:i') }}]</small>
			<p>Rek {{ $users->bank }} {{ $users->rekening }} <br> a.n. {{ $users->pemilik_rekening }}</p>
			<p>Cek status garansi {{ env('APP_URL') }}/tracking</p>
			<p>Terima kasih atas kepercayaan Anda telah melakukan Servis di {{ $users->nama_toko }}</p>
		</footer>
	</div>
</body>
</html>