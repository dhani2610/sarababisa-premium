<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Laporan Teknisi</title>
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
			Laporan Teknisi {{ $teknisi->name }}
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
				<th>: Rp. {{ number_format($total_bonus) }}</th>
				<th class="text-center">Total Biaya Servis</th>
				<th class="text-center">: Rp. {{ number_format($total_biaya) }}</th>
				<th class="text-right">Total Tindakan</th>
				<th class="text-right">: {{ $total_tindakan }} Servis</th>
			</tr>
		</tbody>
	</table>

	<h4 style="margin-top: 8px; margin-bottom: 6px; text-decoration: underline;">
		Detail Servis
	</h4>

	<table id="detail">
		<thead>
			<tr>
				<th>No.</th>
				<th>No. Servis</th>
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
					<td class="text-center" style="width: 60px;">{{ $item->nomor_servis }}</td>
					<td style="text-align: left; width: 90px;" class="capital">{{ $item->nama_pelanggan }}</td>
					<td style="text-align: left; width: 80px;">{{ $item->modelserie->name ?? '-' ?? '-' }}</td>
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
					<td style="width: 70px; text-align: right;">
                        {{-- @dd($item->id,getTypeTeknisiMultiTransaksi($item->id,$item->users_id)); --}}

						{{-- @if ($item->tipe == 'Interface')
						Rp. {{ number_format($item->bonus_interface) }}
						@else
						Rp. {{ number_format($item->profit / 100 * $item->persen_teknisi) }}
						@endif --}}

                        @if (getTypeTeknisiMultiTransaksi($item->id,$item->users_id) == null)
                        @php
                            if ($item->tipe == 'Interface') {
                                $bonus = $item->bonus_interface;
                            }else{
                                $bonus = $item->profit/100;
                                $bonus *= $item->persen_teknisi;
                            }
                        @endphp
                        Rp. {{ number_format($bonus) }}
                        @else
                            @if (getTypeTeknisiMultiTransaksi($item->id,$item->users_id)->tipe == 'Hardware')
                                @php
                                    $bonus = bonusTeknisiMultiHardwareByTransactionId($item->id,$item->users_id);
                                @endphp
                                {{-- @dd(bonusTeknisiMultiHardwareByTransactionId($item->id,$item->users_id)->tipe,$bonus) --}}

                                Rp. {{ number_format($bonus) }}
                            @else
                                @php
                                    $bonus = bonusTeknisiMultiInterfaceByTransactionId($item->id,$item->users_id);
                                @endphp
                                Rp. {{ number_format($bonus) }}
                            @endif
                        @endif
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</body>
</html>
