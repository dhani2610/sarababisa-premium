<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Nota Pengambilan Servis #{{ $items->nomor_servis }}</title>
    <style>
        @page { size: A4; margin: 3mm; }
        body { margin: 0; font-family: sans-serif; }

        /* Utility Classes */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .text-justify { text-align: justify; }
        .capital { text-transform: uppercase; }
        .pt-5 { padding-top: 20px; }
        .v-top { vertical-align: top; }
        .v-mid { vertical-align: middle; }

        /* Widths */
        .w-100 { width: 100%; }
        .w-75 { width: 75%; }
        .w-50 { width: 50%; }
        .w-25 { width: 25%; }

        /* Table Styling */
        table { border-collapse: collapse; font-size: 12px; line-height: 1em; width: 100%; }
        td, th { padding: 4px; }

        /* Borders Helpers */
        .b-top { border-top: 1px solid #000; }
        .b-bottom { border-bottom: 1px solid #000; }
        .b-left { border-left: 1px solid #000; }
        .b-right { border-right: 1px solid #000; }

        /* Row Separator */
        .row-line { border-bottom: 1px solid #ddd; }

        /* List Styling inside tables */
        ul { margin: 0; padding: 0; margin-left: 10px; margin-top: 3px; }
        li { margin-bottom: 6px; }
    </style>
</head>

<body>
    <table>
        <tr>
            @php
                $phones = old('phones', json_decode($users->phones ?? '[]', true));
            @endphp
            @if ($users->profile_photo_path != null)
                <td class="text-center" style="width: 30%">
                    <img src="data:image/png;base64,{{ Storage::disk('public')->exists($users->profile_photo_path) ? base64_encode(file_get_contents($imagePath)) : '' }}" alt="" height="70">
                </td>
                <td class="v-mid text-left" style="height: 50px; line-height: 1.5em;">
                    <strong>{{ $users->nama_toko }} ({{ $users->deskripsi_toko }})</strong> <br>
                    {{ $users->alamat_toko }} - {{ $users->nomor_hp_toko }}
                    @foreach ($phones as $phone) | {{ $phone['title'] }} : {{ $phone['nomor'] }} @endforeach
                </td>
            @else
                <td class="text-left" style="line-height: 1.5em;">
                    <strong>{{ $users->nama_toko }} ({{ $users->deskripsi_toko }})</strong> <br>
                    {{ $users->alamat_toko }} - {{ $users->nomor_hp_toko }}
                    @foreach ($phones as $phone) | {{ $phone['title'] }} : {{ $phone['nomor'] }} @endforeach
                </td>
            @endif
        </tr>
    </table>

    <hr style="border-top: 1px dashed;">

    <h4 class="text-center" style="margin: 6px 0;">NOTA PENGAMBILAN SERVIS</h4>

    <table>
        <tr>
            <td class="text-left"><strong>No. Servis</strong> : {{ $items->nomor_servis }}</td>
            <td class="text-right"><strong>Tanggal</strong> : {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }}</td>
            <td class="text-right"><strong>Dicetak oleh</strong> : Admin</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr class="b-top b-right">
                <th colspan="2" class="text-left b-left row-line">Data Pelanggan</th>
                <th colspan="4" class="text-left b-left row-line">Data Barang</th>
            </tr>
        </thead>
        <tbody>
            <tr class="b-right">
                <td class="b-left row-line">Nama</td>
                <td class="capital row-line">: {{ $items->customer->nama }}</td>
                <td class="b-left row-line">Jenis Barang</td>
                <td class="capital row-line">: {{ $items->type->name }}</td>
                <td class="row-line">IMEI/SN</td>
                <td class="row-line">: {{ $items->imei }}</td>
            </tr>
            <tr class="b-right">
                <td class="b-left row-line">Nomor HP</td>
                <td class="row-line">: {{ $items->customer->nomor_hp }}</td>
                <td class="b-left row-line">Merek</td>
                <td class="capital row-line">: {{ $items->brand->name }}</td>
                <td class="row-line">Kelengkapan</td>
                <td class="capital row-line">: {{ $items->kelengkapan ?? 'Hanya Unit' }}</td>
            </tr>
            <tr class="b-bottom b-right">
                <td class="b-left">Alamat</td>
                <td class="capital">: {{ $items->customer->alamat }}</td>
                <td class="b-left">Model Seri</td>
                <td class="capital">: {{ $items->modelserie->name ?? '-' }}</td>
                <td>Warna/Kapasitas</td>
                <td class="capital">: {{ $items->warna }} / {{ $items->capacity->name }}</td>
            </tr>
        </tbody>
    </table>

    <table style="padding-top: 0px;">
        <thead>
            <tr class="b-top b-right">
                <th colspan="2" class="text-left b-left row-line">Tindakan</th>
                <th colspan="2" class="text-left b-left row-line">Pengecekan (Tombol, Kamera, dll)</th>
                <th colspan="2" class="text-left b-left row-line">Pembayaran</th>
            </tr>
        </thead>
        <tbody>
            <tr class="b-right">
                <td class="b-left row-line">Kerusakan</td>
                <td class="capital row-line">: {{ $items->kerusakan }}</td>

                <td rowspan="2" class="b-left row-line">Link QC</td>
                <td rowspan="2" class="row-line">: <a href="{{ route('kepalatoko-cetak-qc', $items->id) }}">{{ route('kepalatoko-cetak-qc', $items->id) }}</a></td>

                <td class="b-left row-line">
                    Total Biaya Servis
                    @php
                        $totalWithPpn = $items->biaya - $items->diskon;
                        $ppnValue = 0;
                        if (!empty($items->ppn)) {
                            $ppnValue = ($totalWithPpn * $items->ppn) / 100;
                            $totalWithPpn += $ppnValue;
                        }
                    @endphp
                    @if (!empty($items->ppn))
                        <br><br> PPN ({{ $items->ppn }}%) <br><br> Total
                    @endif
                </td>
                <td class="row-line">
                    : Rp. {{ number_format($items->biaya - $items->diskon) }}
                    @if (!empty($items->ppn))
                        <br><br> : Rp. {{ number_format($ppnValue) }}
                        <br><br> : Rp. {{ number_format($totalWithPpn) }}
                    @endif
                </td>
            </tr>

            <tr class="b-right">
                <td class="b-left row-line">Kondisi Servis</td>
                <td class="capital row-line">: {{ $items->kondisi_servis }}</td>

                @if ($items->kondisi_servis === 'Dibatalkan')
                    <td class="b-left row-line">Metode Pembayaran</td>
                    <td class="row-line">: - </td>
                @else
                    <td class="b-left row-line">
                        {{ $items->cara_pembayaran === 'Kredit' ? 'Tempo' : 'Metode Pembayaran' }}
                    </td>
                    <td class="row-line">:
                        @if ($items->cara_pembayaran === 'Tunai & Transfer')
                            Tunai Rp. {{ number_format($items->tunai) }} & Transfer Rp. {{ number_format($items->transfer) }}
                        @elseif ($items->cara_pembayaran === 'Tunai')
                            Tunai Rp. {{ number_format($items->tunai) }}
                        @elseif ($items->cara_pembayaran === 'Transfer')
                            Transfer Rp. {{ number_format($items->transfer) }}
                        @elseif ($items->cara_pembayaran === 'Kredit')
                            Rp. {{ number_format($items->due) }} ({{ \Carbon\Carbon::parse($items->tempo)->locale('id')->translatedFormat('d F Y') }})
                        @endif
                    </td>
                @endif
            </tr>

            @php
                // Variabel Penampung Akhir
                $finalTindakan = [];
                $finalGaransi = [];
                $finalBiaya = [];

                // Cek apakah ada data TeknisiServis
                $useTeknisi = (isset($teknisiServis) && count($teknisiServis) > 0);

                if ($useTeknisi) {
                    // LOOPING DARI TEKNISISERVIS
                    foreach ($teknisiServis as $tek) {
                        // 1. Tindakan
                        $t = json_decode($tek->tindakan_servis, true);
                        if (is_string($t)) $t = json_decode($t, true);
                        if (is_array($t)) $finalTindakan = array_merge($finalTindakan, $t);

                        // 2. Garansi
                        $g = json_decode($tek->garansi, true);
                        if (is_string($g)) $g = json_decode($g, true);
                        if (is_array($g)) $finalGaransi = array_merge($finalGaransi, $g);

                        // 3. Biaya
                        $b = json_decode($tek->biaya_j, true);
                        if (is_string($b)) $b = json_decode($b, true);
                        if (is_array($b)) $finalBiaya = array_merge($finalBiaya, $b);
                    }
                } else {
                    // FALLBACK KE ITEMS (JIKA TEKNISI KOSONG)
                    $t = json_decode($items->tindakan_servis, true);
                    if (is_string($t)) $t = json_decode($t, true);
                    $finalTindakan = is_array($t) ? $t : [];

                    $g = json_decode($items->exp_garansi_j, true);
                    if (is_string($g)) $g = json_decode($g, true);
                    $finalGaransi = is_array($g) ? $g : [];

                    $b = json_decode($items->biaya_j, true);
                    if (is_string($b)) $b = json_decode($b, true);
                    $finalBiaya = is_array($b) ? $b : [];
                }

                // --- HELPER FUNCTION UNTUK RENDER HTML ---

                // 1. Render Tindakan
                $tindakanList = function() use ($finalTindakan, $finalGaransi, $items, $useTeknisi) {
                    if (!empty($finalTindakan)) {
                        foreach ($finalTindakan as $key => $tindakan) {
                            $garansiDate = $finalGaransi[$key] ?? null;

                            $garansiText = 'Garansi tidak ada';
                            if ($garansiDate) {
                                try {
                                    $garansiText = 'Garansi ' . \Carbon\Carbon::parse($garansiDate)->translatedFormat('d F Y');
                                } catch (\Exception $e) {
                                    $garansiText = 'Garansi -';
                                }
                            }
                            echo '<ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">'. $tindakan .' (<strong>'. $garansiText .'</strong>)</li>
                                  </ul>';
                        }
                    } else {
                        if (!$useTeknisi) echo ': ' . $items->tindakan_servis;
                    }
                };

                // 2. Render Rincian Biaya (DENGAN LINK DETAIL DIBAWAHNYA)
                $rincianBiaya = function() use ($finalBiaya, $finalTindakan, $items) {
                    if (!empty($finalBiaya)) {
                        echo '<td class="b-left row-line">Rincian Biaya Servis</td><td class="row-line">';

                        // Loop rincian biaya
                        foreach ($finalBiaya as $key => $biaya) {
                            $tindakanName = $finalTindakan[$key] ?? '-';
                            echo '<ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">'. $tindakanName .' = Rp. '. number_format((float)$biaya) .'</li>
                                  </ul>';
                        }

                        // Tambahan Link Detail Rincian
                        $linkUrl = route('servis-detail', $items->id);
                        echo '<span style="margin-left: 10px; font-size: 10px;">Link Detail: <a href="' . $linkUrl . '">' . $linkUrl . '</a></span>';

                        echo '</td>';
                    }
                };
            @endphp

            @if (($items->uang_muka != null && $items->uang_muka != 0) && ($items->diskon != null && $items->diskon != 0))
                <tr class="b-right">
                    <td class="b-left row-line">Tindakan Servis</td>
                    <td class="capital row-line">{{ $tindakanList() }}</td>
                    <td class="b-left row-line"></td>
                    <td class="row-line"></td>
                    <td class="b-left row-line">Diskon</td>
                    <td class="row-line">: Rp. {{ number_format($items->diskon) }}</td>
                </tr>
                <tr class="b-right">
                    {{ $rincianBiaya() }}
                    <td class="b-left row-line"></td>
                    <td class="row-line"></td>
                    <td class="b-left row-line">Uang Muka</td>
                    <td class="row-line">: Rp. {{ number_format($items->uang_muka) }}</td>
                </tr>
                <tr class="b-bottom b-right">
                    <td class="b-left row-line"></td><td class="row-line"></td>
                    <td class="b-left row-line"></td><td class="row-line"></td>
                    <td class="b-left row-line">Sisa Pembayaran</td>
                    @php
                        $totalDasar = $items->biaya - $items->diskon;
                        if ($items->kondisi_servis != 'Dibatalkan') $totalDasar -= $items->uang_muka;
                        if (!empty($items->ppn) && $items->ppn != 0) $totalDasar += ($items->biaya * $items->ppn / 100);
                        $totalAkhir = max(0, $totalDasar);
                    @endphp
                    <td class="row-line">: Rp. {{ number_format($totalAkhir) }}</td>
                </tr>

            @elseif (($items->uang_muka != null && $items->uang_muka != 0) && ($items->diskon == null || $items->diskon == 0))
                <tr class="b-right">
                    <td class="b-left row-line">Tindakan Servis</td>
                    <td class="capital row-line">{{ $tindakanList() }}</td>
                    {{ $rincianBiaya() }}
                    <td class="b-left row-line">Uang Muka</td>
                    <td class="row-line">: Rp. {{ number_format($items->uang_muka) }}</td>
                </tr>
                <tr class="b-bottom b-right">
                    <td class="b-left row-line"></td><td class="row-line"></td>
                    <td class="b-left row-line"></td><td class="row-line"></td>
                    <td class="b-left row-line">Sisa Pembayaran</td>
                    @php
                        $subtotal = $items->biaya - $items->uang_muka;
                        if (!empty($items->ppn) && $items->ppn != 0) $subtotal += ($subtotal * $items->ppn / 100);
                    @endphp
                    <td class="row-line">: Rp. {{ number_format($subtotal) }}</td>
                </tr>

            @elseif (($items->diskon != null && $items->diskon != 0) && ($items->uang_muka == null || $items->uang_muka == 0))
                <tr class="b-right">
                    <td class="b-left row-line">Tindakan Servis</td>
                    <td class="capital row-line">{{ $tindakanList() }}</td>
                    {{ $rincianBiaya() }}
                    <td class="b-left row-line">Diskon</td>
                    <td class="row-line">: Rp. {{ number_format($items->diskon) }}</td>
                </tr>
                <tr class="b-bottom b-right">
                    <td class="b-left row-line"></td><td class="row-line"></td>
                    <td class="b-left row-line"></td><td class="row-line"></td>
                    <td class="b-left row-line">Sisa Pembayaran</td>
                    @php
                        $subtotal = $items->biaya - $items->diskon;
                        if (!empty($items->ppn) && $items->ppn != 0) $subtotal += ($subtotal * $items->ppn / 100);
                    @endphp
                    <td class="row-line">: Rp. {{ number_format($subtotal) }}</td>
                </tr>

            @elseif (($items->diskon == null || $items->diskon == 0) && ($items->uang_muka == null || $items->uang_muka == 0))
                <tr class="b-right b-bottom">
                    <td class="b-left row-line">Tindakan Servis</td>
                    <td class="capital row-line">{{ $tindakanList() }}</td>
                    @if (!empty($finalBiaya))
                        <td class="b-left row-line">Rincian Biaya Servis</td>
                        <td class="row-line" colspan="3">
                            @foreach ($finalBiaya as $key => $biaya)
                                <ul style="margin: 0; padding: 0; margin-left: 10px; margin-top: 3px;">
                                    <li style="margin-bottom: 6px">
                                        {{ $finalTindakan[$key] ?? '-' }} = Rp. {{ number_format((float)$biaya) }}
                                    </li>
                                </ul>
                            @endforeach
                            <span style="margin-left: 10px; font-size: 10px;">Link Detail: <a href="{{ route('servis-detail', $items->id) }}">{{ route('servis-detail', $items->id) }}</a></span>
                        </td>
                    @endif
                </tr>
            @endif
        </tbody>
    </table>

    @php
        if (!function_exists('penyebut')) {
            function penyebut($nilai) {
                $nilai = abs($nilai);
                $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
                $temp = "";
                if ($nilai < 12) { $temp = " ". $huruf[$nilai]; }
                else if ($nilai < 20) { $temp = penyebut($nilai - 10). " Belas"; }
                else if ($nilai < 100) { $temp = penyebut($nilai/10)." Puluh". penyebut($nilai % 10); }
                else if ($nilai < 200) { $temp = " Seratus" . penyebut($nilai - 100); }
                else if ($nilai < 1000) { $temp = penyebut($nilai/100) . " Ratus" . penyebut($nilai % 100); }
                else if ($nilai < 2000) { $temp = " Seribu" . penyebut($nilai - 1000); }
                else if ($nilai < 1000000) { $temp = penyebut($nilai/1000) . " Ribu" . penyebut($nilai % 1000); }
                else if ($nilai < 1000000000) { $temp = penyebut($nilai/1000000) . " Juta" . penyebut($nilai % 1000000); }
                return $temp;
            }
        }
        if (!function_exists('terbilang')) {
            function terbilang($nilai) {
                if($nilai<0) { $hasil = "Minus ". trim(penyebut($nilai)); }
                else { $hasil = trim(penyebut($nilai)); }
                return $hasil;
            }
        }
    @endphp

    <table>
        <tr>
            <th class="text-right w-75"></th>
            <th class="text-right w-75" colspan="2">
                <i><span style="font-size: 10px; font-weight: normal;"><b>Terbilang : {{ terbilang($totalWithPpn) }}</b></span></i>
            </th>
        </tr>
        @if ($items->catatan != null)
            <tr><td colspan="4"><strong>Catatan</strong> : {{ $items->catatan }}</td></tr>
        @endif
        <tr>
            <th class="text-left w-75">Syarat & Ketentuan</th>
            <th colspan="3" class="w-25 text-right"></th>
        </tr>
        <tr>
            @php
                $banks = old('banks', json_decode($users->banks ?? '[]', true));
            @endphp
            <td rowspan="2" class="text-justify v-top" style="font-style: italic; padding-right: 30px;">
                {!! $terms->description !!}
                <br><strong>No. Rekening : {{ $users->rekening }} {{ $users->bank }} An. {{ $users->pemilik_rekening }} </strong>
                @foreach ($banks as $bank)
                    <br><strong>No. Rekening : {{ $bank['rekening'] }}, {{ $bank['bank'] }} An. {{ $bank['pemilik'] }} </strong>
                @endforeach
            </td>
            <th class="text-center v-top">Pengambil</th>
            <th class="text-center v-top">Penyerah</th>
            <th class="text-center v-top">Teknisi</th>
        </tr>
        <tr>
            <td class="pt-5 text-center capital">{{ $items->pengambil }}</td>
            <td class="pt-5 text-center capital">{{ $items->penyerah ?? '-' }}</td>
            <td class="pt-5 text-center capital">{{ $items->user->name ?? '-' }}</td>
        </tr>
    </table>

</body>
</html>
