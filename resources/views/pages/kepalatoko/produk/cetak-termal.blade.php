<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

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
        /* margin-left: 3px; */
      }

      td,
      th,
      tr,
      table {
        border-collapse: collapse;
        line-height: 1em;
      }

      td.title {
        width: 90px;
        max-width: 90px;
        word-break: break-all;
      }

      td.value {
        width: 95px;
        max-width: 95px;
        word-break: break-all;
      }
    </style>
      <title>Nota Penjualan Handphone #{{ $order->invoice_no }}</title>
  </head>
  <body>

    <div class="resi">
      <div class="text-center">
        @if ($users->profile_photo_path != null)
          <img src="data:image/png;base64,{{ base64_encode(file_get_contents($imagePath)) }}" alt="" height="50">
        @endif
        <p>
          NOTA PENJUALAN <br>
          <strong>{{ $users->nama_toko }}</strong> <br>
          Telp/WA {{ $users->nomor_hp_toko }}
        </p>
      </div>

      <table>
        <tbody>
          <tr>
          <td class="title">No. Invoice</td>
          <td class="value">: {{ $order->invoice_no }}</td>
          </tr>
          <tr>
          <td class="title">Tgl Transaksi</td>
          <td class="value">: {{ \Carbon\Carbon::parse($order->created_at)->locale('id')->translatedFormat('d F Y') }}</td>
          </tr>
          <tr>
          <td class="title">Pelanggan</td>
          <td class="value">: {{ $order->customer->nama }}</td>
          </tr>
          <tr>
          <td class="title">No. HP</td>
          <td class="value">: {{ $order->customer->nomor_hp }}</td>
          </tr>
        </tbody>
      </table>

      <hr style="border-top: 1px dotted;">

      @foreach ($orderItem as $item)
      <table>
        <tbody>
              <tr>
                @if ($item->garansi_imei != null)
                <td colspan="3">{{ $item->product_name }} {{ $item->product->nomor_seri }} {{ $item->product->keterangan }} (Garansi item s/d {{ $item->garansi }}) (Garansi IMEI s/d {{ $item->garansi_imei }})</td>
                @elseif ($item->garansi != null)
                  <td colspan="3">{{ $item->product_name }} {{ $item->product->nomor_seri }} {{ $item->product->keterangan }} (Garansi s/d {{ $item->garansi }})</td>
                @else
                  <td colspan="3">{{ $item->product_name }} {{ $item->product->nomor_seri }} {{ $item->product->keterangan }}</td>
                @endif
              </tr>
              <tr>
                @if ($order->customer->kategori == 'Toko')
                <td> {{ number_format($item->product->harga_jual_toko) }}</td>
                @else
                <td> {{ number_format($item->product->harga_jual) }}</td>
                @endif
                <td>X {{ $item->quantity }}</td>
                {{-- <td>= {{ number_format($item->total - $item->ppn) }}</td> --}}
                <td>= {{ number_format($item->sub_total - $item->ppn) }}</td>
              </tr>
          @endforeach
        </tbody>
      </table>

      <hr style="border-top: 1px dotted;">

      <table>
        <tbody>
          @if ($subtotal === $total)
              <tr>
                <td class="title">Total</td>
                <td class="value">: Rp. {{ number_format($total) }}</td>
              </tr>
          @else
              <tr>
              <td class="title">Diskon</td>
              @if ($item->product_discount_amount <= 0)
                  <td align="value">0</td>
              @else
                  <td align="value">: Rp. {{ number_format($item->product_discount_amount) }}</td>
              @endif
              </tr>
              <tr>
              <td class="title">Sub Total</td>
              <td class="value">: Rp. {{ number_format($totalWithoutTax) }}</td>
              </tr>
              <tr>
                <td class="title">Pajak</td>
                <td class="value">: Rp. {{ number_format($totalTax) }}</td>
              </tr>
              <tr>
                <td class="title">Total</td>
                <td class="value">: Rp. {{ number_format($total) }}</td>
              </tr>
          @endif
          <tr>
              <td class="title">M.Pembayaran</td>
              <td class="value">: {{ $order->payment_method  }}</td>
          </tr>
          @if ($order->payment_method == 'Tunai')
          <tr>
              <td class="title">Tunai</td>
              <td class="value">: Rp. {{ number_format($order->tunai ?? $order->pay) }}</td>
          </tr>

          @elseif ($order->payment_method == 'Transfer')
          <tr>
              <td class="title">Transfer</td>
              <td class="value">: Rp. {{ number_format($order->transfer ?? $order->pay) }}</td>
          </tr>

          @elseif ($order->payment_method == 'Tunai & Transfer')
          <tr>
              <td class="title">Tunai</td>
              <td class="value">: Rp. {{ number_format($order->tunai ?? 0) }}</td>
          </tr>
          <tr>
              <td class="title">Transfer</td>
              <td class="value">: Rp. {{ number_format($order->transfer ?? 0) }}</td>
          </tr>
          @endif


          <tr>
            <td class="title">Total Pembayaran</td>
            <td class="value">: Rp. {{ number_format($order->pay) }}</td>
          </tr>
          @if ($order->due > 0)
            <tr>
              <td class="title">Sisa Pembayaran</td>
              <td class="value">: Rp. {{ number_format($order->due) }}</td>
            </tr>
          @endif
        </tbody>
      </table>

      <hr style="border-top: 1px solid;">

      <div class="text-center">
        <small>Dicetak {{ Auth::user()->name }}, <br> [{{ \Carbon\Carbon::now()->translatedFormat('d/m/Y H:i') }}]</small>
        <p style="margin-top: 4px; margin-bottom: 4px;">Rek {{ $users->bank }} {{ $users->rekening }} <br> a.n. {{ $users->pemilik_rekening }}</p>
        @if ($orderItem->first()->garansi != null)
          <p style="
              margin-top: 4px;
              margin-bottom: 4px;
              max-width: 100%;
              word-wrap: break-word;
              overflow-wrap: break-word;
              white-space: normal;
          ">
              Cek status garansi {{ env('APP_URL') }}/garansi
          </p>
        @endif
        <p style="margin-top: 4px; margin-bottom: 4px;">Terima kasih atas kepercayaan Anda telah berbelanja di <br> {{ $users->nama_toko }}</p>
        <p style="margin-top: 4px;">Barang yang sudah dibeli tidak bisa dikembalikan.</p>
      </div>
    </div>
  </body>
</html>
