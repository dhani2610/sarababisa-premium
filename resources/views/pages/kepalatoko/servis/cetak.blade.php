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
<body style="font-size: 12px;">
  		<table width="100%">
  			<tr>
  				<td align="center"><h4><b>TANDA TERIMA SERVIS</b></h4></td>
  			</tr>
  		</table>
  		<table width="100%">
  			<tr>
  				<td width="18"></td>
  				<td><h5><b>{{ $users->nama_toko }}</b></h5></td>
  				<td width="90">No. Servis</td>
  				<td width="8">:</td>
  				<td width="6"></td>
  				<td width="106px">{{ $items->nomor_servis }}</td>
  				<td width="18"></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td>{{ $users->deskripsi_toko }}</td>
  				<td>Tanggal</td>
  				<td>:</td>
  				<td></td>
  				<td>{{ \Carbon\Carbon::parse($items->created_at)->translatedFormat('d M Y') }}</td>
  				<td></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td>{{ $users->alamat_toko }}</td>
  				<td>Nama</td>
  				<td>:</td>
  				<td></td>
  				<td style="white-space: nowrap;">
  				{{ $items->customer->nama }}	  				</td>
  				<td></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td>Telp/WA +{{ $users->nomor_hp_toko }}</td>
  				<td>Telepon</td>
  				<td>:</td>
  				<td></td>
  				<td style="white-space: nowrap;">+{{ $items->customer->nomor_hp }}</td>
  				<td></td>
  			</tr>
  		</table>
  		<hr style="margin: 10px 18px 4px 18px; border-top: 1px solid;">
  		<b style="margin-left: 18px;">DATA BARANG SERVIS</b>
  		<hr style="margin: 2px 18px 8px 18px; border-top: 1px solid;">
  		<table width="100%">
  			<tr>
  				<td width="18"></td>
  				<td width="100">Nama Barang</td>
  				<td width="8">:</td>
  				<td width="18"></td>
  				<td>{{ $items->type->name }} {{ $items->brand->name }} {{ $items->modelserie->name ?? '-' }}</td>
  				<td width="480" align="right">Estimasi Biaya (Rp) : {{ number_format($items->estimasi_biaya) }} ......................</td>
  				<td width="18"></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td>Warna/Kapasitas</td>
  				<td>:</td>
  				<td></td>
  				<td>{{ $items->warna }} / {{ $items->capacity->name }}</td>
  				<td align="right">..............................</td>
  				<td></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td>Nomor Serial</td>
  				<td>:</td>
  				<td></td>
  				<td>{{ $items->imei }}</td>
  				<td></td>
  				<td></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td>Kelengkapan</td>
  				<td>:</td>
  				<td></td>
  				<td>{{ $items->kelengkapan }}</td>
  				<td align="right">Keterangan Lain-Lain : ..............................</td>
  				<td></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td>Kerusakan</td>
  				<td>:</td>
  				<td></td>
  				<td>{{ $items->kerusakan }}</td>
  				<td align="right">..............................</td>
  				<td></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td>DP/Uang Muka</td>
  				<td>:</td>
  				<td></td>
  				<td>Rp. {{ $items->uang_muka }}</td>
  				<td align="right">..............................</td>
  				<td></td>
  			</tr>
  		</table>
  		<hr style="margin: 8px 18px 16px 18px; border-top: 1px solid;">
  		<table width="100%">
  			<tr>
  				<td width="18"></td>
  				<td align="center"><div id="no"></div></td>
  				<td align="center"><div id="track"></div></td>
  				<td width="18"></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td align="center">Scan Pada Saat Pengambilan Barang Servis</td>
  				<td align="center">Scan Untuk Cek Status (Tracking) Servis</td>
  				<td></td>
  			</tr>
  		</table>
  		<hr style="margin: 10px 18px 12px 18px; border-top: 1px solid;">
  		<table width="100%">
  			<tr>
  				<td width="18"></td>
  				<td><small><i>*Tanda Terima Servis ini silahkan dibawa pada saat pengambilan Barang Servis</i></small></td>
  				<td width="100" align="center"><p>Penerima</p></td>
  				<td width="20"></td>
  				<td width="100" align="center"><p>Pemilik</p></td>
  				<td width="18"></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td><small><i>*Download Aplikasi Scanner Android untuk Tracking di https://dataservisonline.com/scanner.apk</i></small></td>
  				<td></td>
  				<td></td>
  				<td></td>
  				<td></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td><small><i>*Nota ini dicetak  [10/01/2023 11:09 ]</i></small></td>
  				<td align="center" style="white-space: nowrap;">
  					<p>{{ $items->user->name }}</p></td>
  				<td></td>
  				<td align="center">
  					<p>..........</p>
  				</td>
  				<td></td>
  			</tr>
  		</table>
  		<table width="100%" style="margin-top: 10px;">
  			<tr>
  				<td width="18"></td>
  				<td><small><i>Syarat & Ketentuan :</i></small></td>
  				<td width="18"></td>
  			</tr>
  			<tr>
  				<td></td>
  				<td><small><i>{!! $users->syarat_ketentuan_toko !!}</i></small></td>
  				<td></td>
  			</tr>
  		</table>
</body>
</html>
