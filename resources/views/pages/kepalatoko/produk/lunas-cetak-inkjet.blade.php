<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Invoice</title>

<style type="text/css">
    @page {
      size: A4;
      margin: 3mm; /* Atur margin atas, kanan, bawah, dan kiri */
    }
    body {
      margin: 0;
    }
    * {
        font-family: Verdana, Arial, sans-serif;
        color: #000000;
    }
    table{
        font-size: 12px;
    }
    tfoot tr td{
        font-weight: bold;
        font-size: 12px;
    }
    .gray {
        background-color: lightgray
    }
    .authority {
        /*text-align: center;*/
        float: right
    }
    .authority h5 {
        margin-top: -10px;
        margin-left: 35px;
    }

    .informasi-toko {
      text-align: center;
    }

    .w-100 {
			width: 100%;
		}

    .w-50 {
			width: 50%;
		}
		.w-25 {
			width: 25%;
		}
    .text-center {
			text-align: center;
		}
		.text-left {
			text-align: left;
		}
		.text-justify {
			text-align: justify;
		}
</style>

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

  <table class="w-100">
		<tr>
			@if ($users->profile_photo_path != null)
				<td class="text-center" style="width: 30%">
					<img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="" height="70">
				</td>
				<td style="height: 50px; vertical-align: middle; text-align: left; line-height: 1.5em;"><strong>{{ $users->nama_toko }} ({{ $users->deskripsi_toko }})</strong> <br>
					{{ $users->alamat_toko }} - {{ $users->nomor_hp_toko }}
				</td>
			@else
				<td style="text-align: left; line-height: 1.5em;"><strong>{{ $users->nama_toko }} ({{ $users->deskripsi_toko }})</strong> <br>
					{{ $users->alamat_toko }} - {{ $users->nomor_hp_toko }}
				</td>
			@endif
		</tr>
	</table>

	<hr style="border-top: 1px dashed;">

  <table width="100%" style="background: #F7F7F7; padding: 10px;">
    <tr>
        <td>Nama Pelanggan</td>
        <td>: {{ $order->customer->nama }}</td>
        <td>No. Invoice</td>
        <td>: {{ $order->invoice_no }}</td>
    </tr>
    <tr>
        <td>Telp/WA</td>
        <td>: {{ $order->customer->nomor_hp }}</td>
        <td>Tanggal Transaksi</td>
        <td>: {{ \Carbon\Carbon::parse($order->created_at)->locale('id')->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td>Kategori Pelanggan</td>
        <td>: {{ $order->customer->kategori }}</td>
        <td>Metode Pembayaran</td>
        <td>: {{ $order->payment_method }}</td>
    </tr>
    <tr>
        <td>Alamat</td>
        <td>: {{ $order->customer->alamat }}</td>
        <td>Jumlah Pembayaran</td>
        <td>: Rp. {{ number_format($order->pay) }}</td>
    </tr>
    @if ($order->payment_method === 'Tunai & Transfer')
        <tr>
            <td></td>
            <td></td>
            <td>Tunai</td>
            <td>: Rp. {{ number_format($order->tunai) }}</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>Transfer</td>
            <td>: Rp. {{ number_format($order->transfer) }}</td>
        </tr>
    @endif
    @if ($order->due > 0)
        <tr>
            <td></td>
            <td></td>
            <td>Sisa Pembayaran</td>
            <td>: Rp. {{ number_format($order->due) }}</td>
        </tr>
    @endif
    @if ($order->payment_method === 'Kredit')
      <tr>
            <td></td>
            <td></td>
            <td>Waktu Tempo</td>
            <td>: {{ \Carbon\Carbon::parse($order->tempo)->locale('id')->translatedFormat('d F Y') }}</td>
        </tr>
    @endif
  </table>

  <table width="100%" style="margin-top: 15px; border: 1px solid; border-collapse: collapse;">
    <thead style="background-color: lightgrey;">
      <tr class="font">
        <th>No.</th>
        <th>Nama Produk</th>
        <th>Keterangan</th>
        <th>Garansi</th>
        <th>Harga</th>
        <th>Quantity</th>
        <th>Total Harga</th>
        <th>Diskon</th>
        <th>Sub Total</th>
        @if ($toko->is_tax === 1)
            <th>PPN</th>
        @endif
      </tr>
    </thead>
    <tbody>
    @php
      $i = 0;
    @endphp
     @foreach($orderItem as $item)
      <tr class="font">
        <td align="center">
          {{ ++$i }}
        </td>
        <td align="center" style="text-transform: uppercase;">
        @if ($item->product->categories_id == 1)
          {{ $item->product->product_name }} {{ $item->product->kondisi }} {{ $item->product->warna }} {{ $item->product->ram }}/{{ $item->product->capacity->name }} (IMEI {{ $item->product->nomor_seri }})
        @else
          {{ $item->product->product_name }}
        @endif
        </td>
        <td align="center" style="text-transform: uppercase;">
          @if ($item->product->keterangan != null)
              {{ $item->product->keterangan }}
          @else
              -
          @endif
        </td>
        @if ($item->garansi != null && $item->garansi_imei != null)
            <td align="center">Produk {{ $item->garansi }} & IMEI {{ $item->garansi_imei }}</td>
        @elseif ($item->garansi != null)
            <td align="center">Aktif s/d {{ $item->garansi }}</td>
        @else
          <td align="center">-</td>
        @endif
        <td align="center">Rp. {{ number_format($item->price) }}</td>
        <td align="center">{{ $item->quantity }}</td>
        <td align="center">Rp. {{ number_format($item->price * $item->quantity) }}</td>
        @if ($item->sub_total === $item->total)
            <td align="center">-</td>
        @else
            <td align="center">Rp. {{ number_format($item->sub_total - $item->total + $item->ppn) }}</td>
        @endif
          <td align="center">Rp. {{ number_format($item->total - $item->ppn) }}</td>
            @if ($toko->is_tax === 1)
              <td align="center">
              @if ($item->ppn > 0)
                  Rp. {{ number_format($item->ppn) }}
              @else
                  -
            @endif
          </td>
        @endif
      </tr>
      @endforeach
    </tbody>
  </table>
  <table width="100%">
    <tr>
      <th class="text-center" style="vertical-align: top;">Pembeli</th>
      <th class="text-center" style="vertical-align: top;">Penjual</th>
      <td align="right" >
          <h3 style="margin-bottom: 0;">
            Total: Rp. {{ number_format($total) }} <br> <span style="text-transform: capitalize; font-size: 12px; font-weight: normal;">( {{ terbilang($total) }} rupiah )</span>
          </h3>
      </td>
    </tr>
    <tr>
      <td class="text-center capital">{{ $order->customer->nama }}</td>
      @if ($order->user != null)
        <td class="text-center capital">{{ $order->user->name }}</td>
      @else
        <td class="text-center capital">-</td>
      @endif
    </tr>
  </table>
  <table width="100%">
    <tr>
      <td class="text-justify" style="font-style: italic; padding-right: 30px;">
        {!! $terms->description !!}
      </td>
    </tr>
  </table>
  @if ($orderItem->first()->garansi != null)
    <div>
      <p style="text-decoration: underline; font-size: 12px;">Cek status garansi di {{ env('APP_URL') }}/garansi</p>
    </div>
  @endif
</body>
</html>