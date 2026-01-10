<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<style>
		body {
			font-size: 22px;
		}
		hr {
			border: 1px solid #000;
		}
	</style>
    <title>Slip Gaji</title>
</head>
<body>

	@php
		function penyebut($nilai) {
		$nilai = abs($nilai);
		$huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
		$temp = "";
		if ($nilai < 12) {
			$temp = " ". $huruf[$nilai];
		} else if ($nilai <20) {
			$temp = penyebut($nilai - 10). " belas";
		} else if ($nilai < 100) {
			$temp = penyebut($nilai/10)." puluh". penyebut($nilai % 10);
		} else if ($nilai < 200) {
			$temp = " seratus" . penyebut($nilai - 100);
		} else if ($nilai < 1000) {
			$temp = penyebut($nilai/100) . " ratus" . penyebut($nilai % 100);
		} else if ($nilai < 2000) {
			$temp = " seribu" . penyebut($nilai - 1000);
		} else if ($nilai < 1000000) {
			$temp = penyebut($nilai/1000) . " ribu" . penyebut($nilai % 1000);
		} else if ($nilai < 1000000000) {
			$temp = penyebut($nilai/1000000) . " juta" . penyebut($nilai % 1000000);
		}
		return $temp;
	}

	function terbilang($nilai) {
		if($nilai<0) {
			$hasil = "minus ". trim(penyebut($nilai));
		} else {
			$hasil = trim(penyebut($nilai));
		}
		return $hasil;
	}
	@endphp

    <table class="table table-borderless">
        <colgroup>
            <col style="width: 100%">
        </colgroup>
        <tr>
            <td class="text-center">
                <h3>SLIP GAJI</h3>
            </td>
        </tr>
    </table>

    <hr>

	<table class="table table-sm table-borderless">
		<tbody>
			<tr>
			<th class="w-50">{{ $users->nama_toko }}</th>
			<td class="w-50 text-right">Periode : {{ \Carbon\Carbon::parse($periode)->locale('id')->translatedFormat('F Y') }}</td>
			</tr>
			<tr>
			<th>{{ $users->deskripsi_toko }}</th>
			<td class="text-right">Karyawan : {{ $items->name }}</td>
			</tr>
			<tr>
			<td>{{ $users->alamat_toko }}</td>
			<td class="text-right">Jabatan : {{ $items->jabatan }}</td>
			</tr>
			<tr>
			<td></td>
			<td class="text-right">Status : {{ $items->status }}</td>
			</tr>
			<tr>
			<td></td>
			<td class="text-right">Bulan Kerja : {{ \Carbon\Carbon::parse($items->bulankerja)->locale('id')->translatedFormat('d F Y') }}</td>
			</tr>
			<tr>
			<td></td>
			@php
				$dt     = \Carbon\Carbon::parse($tanggal)->locale('id');
			@endphp
			<td class="text-right">Masa Kerja :
				{{ \Carbon\Carbon::parse($items->bulankerja)->diffForHumans
				($dt, ['parts' => 2, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) }}
			</td>
			</tr>
		</tbody>
	</table>

	<hr>

	<table class="table table-sm table-borderless">
		<thead>
			<tr>
			<th scope="col">PENERIMAAN</th>
			<th scope="col"></th>
			</tr>
		</thead>
		<tbody>
			<tr>
			<td>Gaji Pokok</td>
			<td class="text-right">Rp. {{ number_format($shift->nominal_gaji ?? 0) }}</td>
			</tr>
			<tr>
			<td>Tunjangan Kehadiran</td>
			<td class="text-right">Rp. {{ number_format($items->absen) }}</td>
			</tr>
			<tr>
			<td>Tunjangan BPJS</td>
			<td class="text-right">Rp. {{ number_format($items->bpjs) }}</td>
			</tr>
			{{-- @foreach ($salaries as $item) --}}
			<tr>
			<td>Bonus</td>
			<td class="text-right">Rp. {{ number_format($bonus) }}</td>
			</tr>
			{{-- @endforeach --}}
			{{-- @foreach ($salaries as $item)
			<tr>
			<td>{{ $item->name }}</td>
			<td class="text-right">Rp. {{ number_format($item->bonus) }}</td>
			</tr>
			@endforeach --}}
			<tr>
				<td>Lembur</td>
				<td class="text-right">Rp. {{ number_format($overtime) }}</td>
			</tr>
		</tbody>
	</table>

	<table class="table table-sm table-borderless">
		<thead>
			<tr>
			<th scope="col">Total Penghasilan Bruto</th>
            {{-- @dd($shift->nominal_gaji, (int)$items->absen, (int)$items->bpjs, $bonus, $overtime,$shift->nominal_gaji ?? 0 + $items->absen + $items->bpjs + $bonus + $overtime) --}}
			<th scope="col" class="text-right text-primary">
                Rp. {{ number_format(
                    ($shift->nominal_gaji ?? 0) +
                    (int)$items->absen +
                    (int)$items->bpjs +
                    $bonus +
                    $overtime
                ) }}
            </th>
		</thead>
	</table>

	<table class="table table-sm table-borderless">
		<thead>
			<tr>
			<th scope="col">PENGURANGAN</th>
			<th scope="col"></th>
			</tr>
		</thead>
		<tbody>
			@foreach ($debts as $item)
			<tr>
			<td>{{ $item->item }}</td>
			<td class="text-right">Rp. {{ number_format($item->total) }}</td>
			</tr>
			@endforeach
			@foreach ($incidents as $item)
			<tr>
			<td>{{ $item->name }}</td>
			<td class="text-right">Rp. {{ number_format($item->biaya_teknisi) }}</td>
			</tr>
			@endforeach
		</tbody>
	</table>

	@foreach ($totalPotonganServis as $pt)
		<table class="table table-sm table-borderless">
			<thead>
				<tr>
				<th scope="col">Refund Invoice {{  $pt->ServiceTransaction->nomor_servis  }}</th>
				<th scope="col" class="text-right text-danger">Rp. {{ number_format($pt->nominal) }}</th>
				</tr>
			</thead>
		</table>
	@endforeach

	<table class="table table-sm table-borderless">
		<thead>
			<tr>
			<th scope="col">Izin/Sakit/Alfa/Cuti</th>
			<th scope="col" class="text-right text-danger">Rp. {{ number_format($izin) }}</th>
			</tr>
		</thead>
	</table>
	<table class="table table-sm table-borderless">
		<thead>
			<tr>
			<th scope="col">Terlambat</th>
			<th scope="col" class="text-right text-danger">Rp. {{ number_format($potongan_telat) }}</th>
			</tr>
		</thead>
	</table>
	<table class="table table-sm table-borderless">
		<thead>
			<tr>
			<th scope="col">Total Pengurangan</th>
			<th scope="col" class="text-right text-danger">Rp. {{ number_format($totalkasbon + $totalinsiden +  $totalPotonganServis->sum('nominal') + $izin + $potongan_telat) }}</th>
			</tr>
		</thead>
	</table>

	<table class="table table-sm table-borderless">
		<thead>
			<tr>
			<th scope="col">TOTAL DITERIMA KARYAWAN</th>
			<th scope="col" class="text-right text-success">Rp. {{ number_format(($shift->nominal_gaji ?? 0) + (int)$items->absen + (int)$items->bpjs + $bonus - $totalkasbon - $totalinsiden - $totalPotonganServis->sum('nominal') - $izin + $overtime - $potongan_telat) }}</th>
			</tr>
		</thead>
	</table>

	<div class="text-center">
		@php
			$angka = $shift->nominal_gaji ?? 0 + $items->absen + $items->bpjs + $bonus - $totalkasbon - $totalinsiden;
		@endphp
		<span class="badge badge-light px-4 py-3">
			<p class="text-capitalize mb-0">
				Terbilang: {{ terbilang($angka) }} rupiah
			</p>
		</span>
	</div>

	<hr>

	<div class="text-center">
		{{ $users->kota }}, {{ \Carbon\Carbon::parse($tanggal)->locale('id')->translatedFormat('d F Y') }}
	</div>

	<table class="table table-sm table-borderless text-center mt-2">
		<thead>
			<tr>
			<th scope="col">Penerima</th>
			<th scope="col">{{ $users->nama_toko }}</th>
			</tr>
		</thead>
		<tbody>
			<tr>
			<td>{{ $items->name }}</td>
			<td>{{ $users->owner }}</td>
			</tr>
		</tbody>
	</table>
</body>
</html>
