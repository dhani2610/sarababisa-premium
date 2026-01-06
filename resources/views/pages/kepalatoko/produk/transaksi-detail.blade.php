@section('title')
    Detail Transaksi Produk
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Billing Information -->
        <div class="mb-6">
            <div class="text-slate-800 font-semibold mb-4">Data Pelanggan</div>
            <form>
                <div class="space-y-4">
                    <!-- 1st row -->
                    <div class="md:flex space-y-4 md:space-y-0 md:space-x-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-1" for="card-name">Nama</label>
                            <input id="card-name" class="form-input w-full" type="text" value="{{ $order->customer->nama }}" disabled/>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-1" for="card-surname">Nomor HP</label>
                            <input id="card-surname" class="form-input w-full" type="text" value="{{ $order->customer->nomor_hp }}" disabled/>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="max-w-sm mx-auto lg:max-w-none">
            <div class="space-y-6">

                <!-- Order Details -->
                <div>
                    <div class="text-slate-800 font-semibold mb-2">Detail Transaksi</div>
                    <!-- Cart items -->
                    <ul>
                        <!-- Cart item -->
                        @foreach ($orderItem as $item)
                            <li class="flex items-center py-3 border-b border-slate-200">
                                <div class="grow">
                                    <h4 class="text-sm font-medium text-slate-800 leading-tight">
                                        @if ($item->product->categories_id == 1)
                                            {{ $item->product->product_name }} {{ $item->product->kondisi }} {{ $item->product->warna }} {{ $item->product->ram }}/{{ $item->product->capacity->name }} {{ $item->product->keterangan }} (IMEI {{ $item->product->nomor_seri }})
                                        @else
                                            {{ $item->product->product_name }} {{ $item->product->keterangan }}
                                        @endif
                                         (Rp. {{ number_format($item->price) }} x {{ $item->quantity }}
                                        @if ($item->quantity == 1)
                                            pc
                                        @else
                                            pcs
                                        @endif)
                                    </h4>
                                </div>
                                <div class="text-sm font-medium text-slate-800 ml-6">
                                    @if ($item->ppn > 0)
                                        <span class="text-xs text-blue-500">(+PPN Rp. {{ number_format($item->ppn) }})</span>
                                    @endif
                                     Rp. {{ number_format($item->price * $item->quantity + $item->ppn) }}
                                </div>
                                <div class="ml-4">
                                    <a href="{{ route('qc-produk',$item->product->id) }}" target="_blank" class="btn-xs bg-blue-100 text-blue-600 hover:bg-blue-200 rounded border border-blue-200 ">
                                        Lihat QC
                                    </a>
                                    <input type="hidden" name="products_id[]" value="{{ $item->product->id }}">

                                    <input type="hidden" name="qc_data[]" class="qc-data-json" value="">

                                    <button type="button" class="btn-xs bg-blue-100 text-blue-600 hover:bg-blue-200 rounded border border-blue-200 open-qc-modal">
                                        Edit QC
                                    </button>
                                </div>
                            </li>
                        @endforeach
                        <li class="flex items-center justify-between py-3 border-b border-slate-200">
                            <div class="text-sm">Total</div>
                            <div class="text-sm font-medium text-emerald-600 ml-2">
                                 Rp. {{ number_format($total) }}
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Payment Details -->
                <div>
                    <div class="text-slate-800 font-semibold mb-4">Detail Pembayaran</div>
                    <div class="text-sm rounded border-3 border-indigo-300 p-3 space-y-3">
                        @if ($order->due == 0)
                            <div class="flex items-center justify-between space-x-2">
                                <!-- CC details -->
                                <div class="font-semibold">{{ $order->payment_method }}</div>
                                <!-- Expiry -->
                                <div class="text-blue-700 font-semibold ml-2">Rp. {{ number_format($order->pay) }}</div>
                            </div>
                        @else
                            <div class="flex items-center justify-between space-x-2">
                                <!-- CC details -->
                                <div class="font-semibold">{{ $order->payment_method }}</div>
                                <!-- Expiry -->
                                <div class="text-blue-700 font-semibold ml-2">Rp. {{ number_format($order->pay) }}</div>
                            </div>
                            <div class="flex items-center justify-between space-x-2">
                                <!-- CC details -->
                                <div class="font-semibold">Sisa Pembayaran</div>
                                <!-- Expiry -->
                                <div class="font-semibold text-rose-700 ml-2">Rp. {{ number_format($order->due) }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 space-y-2">
                    <!-- Bayar -->
                    @if ($order->due > 0)
                        <div x-data="{ modalOpen: false }">
                            <button
                                type="button"
                                @click.prevent="modalOpen = true"
                                aria-controls="basic-modal"
                                id="{{ $order->id }}"
                                onclick="orderDue(this.id)"
                                class="btn w-full bg-emerald-700 hover:bg-emerald-800 text-white"
                            >
                                Bayar Sekarang
                            </button>
                            <!-- Modal backdrop -->
                            <div
                                class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                                x-show="modalOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-out duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                aria-hidden="true"
                                x-cloak
                            ></div>
                            <!-- Modal dialog -->
                            <div
                                id="tambah-modal"
                                class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                role="dialog"
                                aria-modal="true"
                                x-show="modalOpen"
                                x-transition:enter="transition ease-in-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in-out duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-4"
                                x-cloak
                            >
                                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                                    <!-- Modal content -->
                                    <div class="text-center my-3">
                                        <h6>Sisa Pembayaran</h6>
                                        <p>Rp. {{ number_format($order->due) }}</p>
                                    </div>
                                    <div class="px-5 py-4">
                                        <div>
                                            <form action="{{ route('produk.updateDue') }}" method="post">
                                                @csrf
                                                <input type="hidden" name="id" id="id">
                                                <input type="hidden" name="pay" id="pay">
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium mb-1" for="due">Bayar Sekarang</label>
                                                    <div class="relative">
                                                        <input class="form-input w-full pl-10 px-2 py-1" type="number" name="due" id="due"/>
                                                        <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                            <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Modal footer -->
                                                <button class="w-full btn bg-emerald-700 hover:bg-emerald-800 text-white">Perbarui Pembayaran</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <!-- Start Printer -->
                    <div x-data="{ modalOpen: false }">
                        <button
                            @click.prevent="modalOpen = true"
                            aria-controls="basic-modal"
                            class="btn w-full bg-indigo-500 hover:bg-indigo-600 text-white"
                        >
                        Cetak Nota
                        </button>
                            <!-- Modal backdrop -->
                            <div
                                class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                                x-show="modalOpen"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0"
                                x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-out duration-100"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                aria-hidden="true"
                                x-cloak
                            ></div>
                            <!-- Modal dialog -->
                            <div
                                id="basic-modal"
                                class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                role="dialog"
                                aria-modal="true"
                                x-show="modalOpen"
                                x-transition:enter="transition ease-in-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-4"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in-out duration-200"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 translate-y-4"
                                x-cloak
                            >
                                <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                                    <!-- Modal header -->
                                    <div class="px-5 py-3 border-b border-slate-200">
                                        <div class="flex justify-between items-center">
                                            <div class="font-semibold text-slate-800">Pilih Jenis Printer</div>
                                            <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                                <div class="sr-only">Close</div>
                                                <svg class="w-4 h-4 fill-current">
                                                    <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Modal content -->
                                    <div class="px-5 pt-4 pb-1">
                                        <div class="text-sm">
                                            <div class="space-y-2">
                                                <p>Silahkan pilih printer untuk cetak Nota Tanda Terima Servis.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Modal footer -->
                                    <div class="px-5 py-4">
                                        <div class="flex flex-wrap justify-end space-x-2">
                                            <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                            <a href="{{ route('cetak-termal', $order->id) }}"  target="_blank">
                                                <button class="btn-sm bg-orange-500 hover:bg-orange-600 text-white">
                                                    <span class="mr-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                        <rect x="7" y="13" width="10" height="8" rx="2" />
                                                        </svg>
                                                    </span>
                                                    Printer Termal
                                                </button>
                                            </a>
                                            <a href="{{ route('lunas-cetak-inkjet', $order->id) }}"  target="_blank">
                                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">
                                                    <span class="mr-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                        <rect x="7" y="13" width="10" height="8" rx="2" />
                                                        </svg>
                                                    </span>
                                                    Printer Inkjet
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                    @php
                        if ($order->customer != null) {
                            $nomor = $order->customer->nomor_hp;
                            $nomorwa = preg_replace('/^08/', 628, $nomor);
                            $fonteeToken = getStoreSettingByCabang()->fonnte ?? null;
                        }
                    @endphp
                    @php
                        $waProductList = "";

                        foreach($orderItem as $item) {
                            // 1. Ambil Link QC
                            $linkQc = route('qc-produk', $item->product->id);

                            // 2. Susun Format Teks (Sesuai Fitur Existing)
                            if ($item->product->categories_id == 1) {
                                // Jika HP: Tampilkan Nama + IMEI
                                $waProductList .= $item->product->product_name .
                                                ' IMEI ' . $item->product->nomor_seri .
                                                ' (Rp ' . number_format($item->price, 0, ',', '.') . ' x ' . $item->quantity . ' pcs)%0A';
                            } else {
                                // Jika Produk Lain: Tampilkan Nama Saja
                                $waProductList .= $item->product->product_name .
                                                ' (Rp ' . number_format($item->price, 0, ',', '.') . ' x ' . $item->quantity . ' pcs)%0A';
                            }

                            // 3. Tambahkan Link QC di baris bawahnya
                            $waProductList .= "   📄 Cek QC: " . $linkQc . "%0A";
                        }
                    @endphp
                    @php
                        $banks = json_decode($toko->banks ?? '[]', true);

                        $infoBayar = 'Informasi Pembayaran :%0A';
                        $infoBayar .= 'Bank : '. $toko->bank . '%0A';
                        $infoBayar .= 'Norek : '. $toko->rekening . '%0A';
                        $infoBayar .= 'a.n : '. $toko->pemilik_rekening . '%0A';

                        if (!empty($banks)) {
                            foreach ($banks as $bank) {
                                $infoBayar .= 'Bank : '. $bank['bank'] . '%0A';
                                $infoBayar .= 'Norek : '. $bank['rekening'] . '%0A';
                                $infoBayar .= 'a.n : '. $bank['pemilik'] . '%0A';
                            }
                        }

                        $infoBayar .= '%0A%0A';

                        $infoBayarLink = str_replace(' ', '%20', $infoBayar);
                    @endphp
                    {{-- <a href="https://wa.me/{{ $nomorwa }}/?text=*Notifikasi%20Penjualan*%0A{{ $toko->nama_toko }}%0A%0ANo.%20Nota%20:%20{{ $order->invoice_no }}%0ANama%20pelanggan%20:%20*{{ $order->nama_pelanggan }}*%0AProduk%20:%0A{{ $produkDetails }}%0APembayaran%20:%20{{ $order->payment_method }}%0A%0ALink%20garansi%20:%20{{ $toko->link_toko }}/garansi%0A%0ATerimakasih"  target="_blank"> --}}
                    <a href="javascript:void(0)"
                        @click="
                            @if($fonteeToken ?? false)
                                kirimFontee(
                                    '{{ $fonteeToken }}',
                                    '{{ $nomorwa }}',
                                    '*Notifikasi Pembelian*%0A{{ $toko->nama_toko }}%0A%0A' +
                                    'No. Nota : {{ $order->invoice_no }}%0A' +
                                    'Nama pelanggan : *{{ $order->nama_pelanggan }}*%0A' +
                                    'Produk : {{ $waProductList }}%0A' +
                                    'Pembayaran : {{ $order->payment_method }}%0A%0A' +
                                    'Link garansi : {{ env('APP_URL') }}/garansi%0A' +
                                    'Link nota : {{ route('lunas-cetak-inkjet', $order->id) }}%0A%0A' +
                                    '{{ $infoBayar }}' +
                                    'Terimakasih'
                                )
                            @else
                                window.open(
                                    'https://wa.me/{{ $nomorwa }}/?text=' +
                                    '*Notifikasi%20Pembelian*%0A{{ $toko->nama_toko }}%0A%0A' +
                                    'No.%20Nota%20:%20{{ $order->invoice_no }}%0A' +
                                    'Nama%20pelanggan%20:%20*{{ $order->nama_pelanggan }}*%0A' +
                                    'Produk%20:%20{{ $waProductList }}%0A' +
                                    'Pembayaran%20:%20{{ $order->payment_method }}%0A%0A' +
                                    'Link%20garansi%20:%20{{ env('APP_URL') }}/garansi%0A' +
                                    'Link%20nota%20:%20{{ route('lunas-cetak-inkjet', $order->id) }}%0A%0A' +
                                    '{{ $infoBayar }}' +
                                    'Terimakasih',
                                    '_blank'
                                );
                            @endif
                        ">

                        <button class="btn w-full bg-emerald-500 hover:bg-emerald-600 text-white mt-3">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                    <path d="M9 10a0.5 .5 0 0 0 1 0v-1a0.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a0.5 .5 0 0 0 0 -1h-1a0.5 .5 0 0 0 0 1" />
                                </svg>
                            </span>
                            Kirim Nota
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>
<div id="qcModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start flex-col">
                    <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                        <h3 class="text-xl leading-6 font-bold text-gray-900 mb-4 border-b pb-2">
                            QC Data: <span id="qc_product_name" class="text-indigo-600">Loading...</span>
                        </h3>

                        <div class="overflow-x-auto border rounded-sm h-[60vh] overflow-y-auto">
                            <table class="w-full text-xs text-left border-collapse">
                                <thead class="bg-slate-100 uppercase text-slate-500 font-semibold sticky top-0 z-10 shadow-sm">
                                    <tr>
                                        <th class="border border-slate-300 p-2 w-10 text-center">No</th>
                                        <th class="border border-slate-300 p-2 w-1/3">Item Pengecekan</th>
                                        <th class="border border-slate-300 p-2 bg-blue-50 text-center text-blue-800">QC Masuk</th>
                                        <th class="border border-slate-300 p-2 bg-orange-50 text-center text-orange-800">QC Keluar</th>
                                        <th class="border border-slate-300 p-2 w-10 text-center">#</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_qc" class="text-slate-700 bg-white">
                                    </tbody>
                            </table>
                        </div>

                        <button type="button" onclick="addCustomRow()" class="mt-3 text-xs flex items-center text-indigo-600 font-bold hover:text-indigo-800 border border-indigo-200 p-2 rounded bg-indigo-50">
                            + Tambah Baris Custom
                        </button>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t">
                <button type="button" id="btnSaveQC" class="btn w-full bg-indigo-500 hover:bg-indigo-600 text-white w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Simpan Perubahan
                </button>
                <button type="button" onclick="closeQcModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

    <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
    <script type="text/javascript">
        function orderDue(id) {
            $.ajax({
                type: 'GET',
                url: '/order/due/'+id,
                dataType: 'json',
                success:function(data){
                    // console.log(data)
                    $('#due').val(data.due);
                    $('#pay').val(data.pay);
                    $('#id').val(data.id);
                }
            })
        }
    </script>

    {{-- SCRIPT JS UNTUK FONTEE --}}
    <script>
    function kirimFontee(token, phone, message) {
        fetch('https://api.fonnte.com/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': token
            },
            body: JSON.stringify({
                target: phone,
                message: decodeURIComponent(message)
            })
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));

            console.log("🔍 Response status:", response.status);
            console.log("📦 Response data:", data);

            if (response.ok && (data.status === true || data.success)) {
                alert('✅ Pesan berhasil dikirim ke Pelanggan!');
            } else {
                console.error("❌ Gagal mengirim pesan. Detail error:", data);
                alert('⚠️ Gagal mengirim ke Pelanggan. Lihat console log untuk detail.');
            }
        })
        .catch(error => {
            console.error("🚨 Terjadi kesalahan koneksi ke Fonnte:", error);
            alert('❌ Terjadi kesalahan saat mengirim ke Fontee.');
        });
    }
    </script>

<script>
    let currentActiveRow = null;
    let currentProductId = null;

    // --- BUKA MODAL ---
    $(document).on('click', '.open-qc-modal', function() {
        currentActiveRow = $(this).closest('li'); // Sesuaikan dengan element list Anda
        currentProductId = currentActiveRow.find('input[name="products_id[]"]').val();

        let productName = currentActiveRow.find('h4').text().trim().substring(0, 50); // Ambil nama pendek
        $('#qc_product_name').text(productName);

        // LOAD DATA VIA AJAX
        $.ajax({
            url: "{{ route('get-qc-data') }}/" + currentProductId,
            type: "GET",
            beforeSend: function() {
                $('#tbody_qc').html('<tr><td colspan="5" class="text-center p-10"><span class="loading">Mengambil data QC...</span></td></tr>');
                $('#qcModal').removeClass('hidden');
            },
            success: function(response) {
                // Response.data sudah dalam format bersih dari Controller
                // [{item: 'LCD', val_masuk: 'OK', val_keluar: 'Pecah', is_custom: false}, ...]
                renderQcTable(response.data);
            },
            error: function() {
                alert("Gagal koneksi ke database QC.");
                closeQcModal();
            }
        });
    });

    // --- RENDER TABLE ---
    function renderQcTable(dataList) {
        const tbody = document.getElementById('tbody_qc');
        tbody.innerHTML = '';

        if (!dataList || dataList.length === 0) return;

        dataList.forEach((row, index) => {
            // Panggil fungsi create row dengan Value yang sudah ada
            appendRowToModal(index + 1, row.item, row.val_masuk, row.val_keluar, row.is_custom);
        });
    }

    function appendRowToModal(num, itemName, valMasuk, valKeluar, isCustom) {
        const tbody = document.getElementById('tbody_qc');
        const tr = document.createElement('tr');
        tr.className = "border-b border-slate-200 hover:bg-yellow-50";

        // Logic Custom Item (Input Text) vs Standard (Text Readonly)
        let nameField = '';
        if(isCustom) {
            nameField = `<input type="text" class="qc-item-name w-full p-1 border border-slate-300 rounded text-xs font-bold text-indigo-700" value="${itemName}" placeholder="Nama Item Custom...">`;
        } else {
            nameField = `<span class="px-2 font-medium">${itemName}</span><input type="hidden" class="qc-item-name" value="${itemName}">`;
        }

        // Hidden input penanda custom
        let customMarker = `<input type="hidden" class="qc-is-custom" value="${isCustom ? 1 : 0}">`;

        // Value Input otomatis terisi dari parameter function (valMasuk, valKeluar)
        tr.innerHTML = `
            <td class="border border-slate-300 p-1 text-center bg-slate-50 font-bold row-num">${num}</td>
            <td class="border border-slate-300 p-1">${nameField} ${customMarker}</td>

            <td class="border border-slate-300 p-0">
                <input type="text" class="qc-val-masuk w-full h-full p-2 text-center text-xs border-0 focus:ring-2 focus:ring-blue-500 bg-transparent"
                       value="${valMasuk || ''}" placeholder="-">
            </td>

            <td class="border border-slate-300 p-0">
                <input type="text" class="qc-val-keluar w-full h-full p-2 text-center text-xs border-0 focus:ring-2 focus:ring-orange-500 bg-transparent"
                       value="${valKeluar || ''}" placeholder="-">
            </td>

            <td class="border border-slate-300 p-1 text-center">
                <button type="button" class="text-rose-500 hover:text-rose-700" onclick="deleteRow(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    // --- FUNGSI TAMBAHAN ---
    window.addCustomRow = function() {
        const tbody = document.getElementById('tbody_qc');
        const rowCount = tbody.rows.length + 1;
        appendRowToModal(rowCount, '', '', '', true); // Tambah row kosong
    }

    window.deleteRow = function(btn) {
        btn.closest('tr').remove();
        // Re-numbering
        $('#tbody_qc tr').each(function(index) {
            $(this).find('.row-num').text(index + 1);
        });
    }

    window.closeQcModal = function() {
        $('#qcModal').addClass('hidden');
    }

    $('#btnSaveQC').on('click', function() {
        if (!currentActiveRow || !currentProductId) {
            alert("Terjadi kesalahan: Produk tidak teridentifikasi.");
            return;
        }

        let arrMasuk = [];
        let arrKeluar = [];

        // 2. Loop Table untuk menyusun data JSON
        $('#tbody_qc tr').each(function() {
            let tr = $(this);
            let isCustom = tr.find('.qc-is-custom').val() == 1;

            // Ambil nama item (dari input jika custom, dari hidden jika default)
            let itemName = isCustom ? tr.find('input.qc-item-name').val() : tr.find('.qc-item-name').val();

            let valMasuk = tr.find('.qc-val-masuk').val();
            let valKeluar = tr.find('.qc-val-keluar').val();

            // Hanya simpan jika nama item tidak kosong
            if (itemName && itemName.trim() !== '') {
                // Struktur Object Data
                let itemObj = {
                    item: itemName,
                    value: "", // Default string kosong
                    is_custom: isCustom
                };

                // Push ke array Masuk
                let objMasuk = Object.assign({}, itemObj); // Copy object
                objMasuk.value = valMasuk;
                arrMasuk.push(objMasuk);

                // Push ke array Keluar
                let objKeluar = Object.assign({}, itemObj); // Copy object
                objKeluar.value = valKeluar;
                arrKeluar.push(objKeluar);
            }
        });

        // 3. Kirim via AJAX
        $.ajax({
            url: "{{ route('store-qc-data') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}", // Token CSRF wajib di Laravel
                product_id: currentProductId,
                qc_masuk: arrMasuk,
                qc_keluar: arrKeluar
            },
            beforeSend: function() {
                $('#btnSaveQC').text('Menyimpan...').attr('disabled', true);
            },
            success: function(response) {
                if (response.status === 'success') {
                    let btn = currentActiveRow.find('.open-qc-modal');
                    btn.removeClass('bg-blue-100 text-blue-600 border-blue-200')
                       .addClass('bg-emerald-100 text-emerald-700 border-emerald-200');
                    btn.text('QC Tersimpan');

                    let finalJson = JSON.stringify({ masuk: arrMasuk, keluar: arrKeluar });
                    currentActiveRow.find('.qc-data-json').val(finalJson);

                    closeQcModal();
                    if(typeof $.notify === 'function'){
                        $.notify(response.message, { className: 'success', position: 'top right' });
                    } else {
                        alert(response.message);
                    }
                } else {
                    alert('Gagal: ' + response.message);
                }
            },
            error: function(xhr) {
                console.log(xhr.responseText);
                alert("Gagal menyimpan data ke server. Cek console untuk detail.");
            },
            complete: function() {
                $('#btnSaveQC').text('Simpan QC').attr('disabled', false);
            }
        });
    });
</script>
</x-toko-layout>
