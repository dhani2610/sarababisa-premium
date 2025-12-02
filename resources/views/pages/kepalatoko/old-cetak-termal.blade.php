<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>Tanda Terima Servis #{{ $items->nomor_servis }}</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<style>
		body {
			font-family: 'DejaVu Sans Mono';
		}
	</style>
</head>
<body style="font-size: 10px;">
		<script>window.print();</script>
  		<table width="75%">
  			<tr>
  				<td align="center">TANDA TERIMA SERVIS</td>
  			</tr>
  			<tr>
  				<td align="center" style="font-size: 15px;"><b>{{ $users->nama_toko }}</b></td>
  			</tr>
  			<tr>
  				<td align="center" style="padding-bottom: 4px;">Telp/WA +{{ $users->nomor_hp_toko }}</td>
  			</tr>
  		</table>
  		<hr style="border-top: 1px solid; margin: 0px;">
  		<table width="75%">
  			<tr>
  				<td width="54">No. Servis</td>
  				<td width="4">:</td>
  				<td>{{ $items->nomor_servis }}</td>
  			</tr>
			<tr>
  				<td>Tanggal</td>
  				<td>:</td>
  				<td>{{ \Carbon\Carbon::parse($items->created_at)->format('d/m/Y') }}</td>
  			</tr>
  			<tr>
  				<td>Kerusakan</td>
  				<td>:</td>
  				<td>{{ $items->kerusakan }}</td>
  			</tr>
			<tr>
  				<td>Estimasi Biaya</td>
  				<td>:</td>
  				<td>Rp. {{ number_format($items->estimasi_biaya) }}</td>
  			</tr>
			<tr>
  				<td>Estimasi Pengerjaan</td>
  				<td>:</td>
  				<td>{{ $items->estimasi_pengerjaan }}</td>
  			</tr>
			<tr>
  				<td>Nama Barang</td>
  				<td>:</td>
  				<td>{{ $items->type->name }} {{ $items->brand->name }} {{ $items->modelserie->name ?? '-' }}</td>
  			</tr>
			<tr>
  				<td>IMEI/SN</td>
  				<td>:</td>
  				<td>{{ $items->imei }}</td>
  			</tr>
			<tr>
  				<td>Warna/Kapasitas</td>
  				<td>:</td>
  				<td>{{ $items->warna }} {{ $items->capacity->name }}</td>
  			</tr>
			<tr>
  				<td>Pelanggan</td>
  				<td>:</td>
  				<td>{{ $items->customer->nama }}</td>
  			</tr>
			<tr>
  				<td>Nomor HP</td>
  				<td>:</td>
  				<td>{{ $items->customer->nomor_hp }}</td>
  			</tr>
  		</table>
  		<table width="96%">
  			<tr>
  				<td align="center" style="padding-top: 4px;"><div id="qrcodethermal"></div></td>
  			</tr>
  			<tr>
  				<td align="center" style="font-size: 11px;">Scan QR Code ini untuk Cek Online</td>
  			</tr>
  		</table>
  		<hr style="border-top: 1px solid; margin: 0px;">
  		<table width="75%" style="font-size: 11px;">
  			<tr>
  				<td style="padding-bottom: 2px;">
            Dicetak Admin [10/01/2023 23:16]          </td>
  			</tr>
  			<tr>
  				<td align="center">Silahkan bawa Nota Tanda Terima Servis</td>
  			</tr>
  			<tr>
  				<td align="center">ini pada saat pengambilan barang.</td>
  			</tr>
  			<tr>
  				<td align="center">Terima kasih.</td>
  			</tr>
  		</table>
		<table width="75%" class="mt-5">
  			<tr>
  				<td align="center" style="font-size: 15px;"><b>Pengecekan Masuk/Keluar Servis</b></td>
  			</tr>
  		</table>
		<table class="mt-3">
			<thead>
				<tr>
				<th scope="col" width="54">Item</th>
				<th scope="col" width="54">Masuk</th>
				<th scope="col" width="54">Keluar</th>
				</tr>
			</thead>
			<tbody>
				<tr>
				<td>Face ID/Finger</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Kamera Depan</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Kamera Belakang</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Speaker Atas</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Speaker Bawah</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Housing</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>LCD</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Jaringan</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Baterai</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Mikrofon</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Audio</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Wifi</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Flash LED</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
				<tr>
				<td>Semua Tombol</td>
				<td width="25"></td>
				<td width="25"></td>
				</tr>
			</tbody>
		</table>
		<table class="table table-borderless table-sm text-center mt-3">
			<thead>
				<tr>
				<th scope="col">Kasir/Teknisi</th>
				<th scope="col">Pelanggan</th>
				</tr>
			</thead>
			<tbody>
				<tr>
				<th scope="row"></th>
				<td></td>
				</tr>
				<tr>
				<th scope="row"></th>
				<td></td>
				</tr>
				<tr>
				<th scope="row">..........</th>
				<td>..........</td>
				</tr>
			</tbody>
		</table>
</body>
</html>
