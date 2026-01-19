@section('title')
    Tambah Pembelian Produk
@endsection

<x-toko-layout background="bg-white">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Tambah Pembelian Produk ✨</h1>
            </div>


            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Start -->
                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-slate-500 hover:bg-slate-600 text-white" @click.prevent="modalOpen = true" aria-controls="danger-modal">
                        <span class="sr-only">Exit</span>
                        <svg class="w-4 h-4 fill-current">
                            <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                        </svg>
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
                        id="danger-modal"
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
                            <div class="p-5 flex space-x-4">
                                <!-- Icon -->
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                                    <svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16">
                                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                                    </svg>
                                </div>
                                <!-- Content -->
                                <div>
                                    <!-- Modal header -->
                                    <div class="mb-2">
                                        <div class="text-lg font-semibold text-slate-800">Tinggalkan halaman ini ?</div>
                                    </div>
                                    <!-- Modal content -->
                                    <div class="text-sm mb-10">
                                        <div class="space-y-2">
                                            <p>Jika Anda keluar, inputan Anda tidak akan disimpan.</p>
                                        </div>
                                    </div>
                                    <!-- Modal footer -->
                                    <div class="flex flex-wrap justify-end space-x-2">
                                        <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white" @click="modalOpen = false">Tetap Disini</button>
                                        <a href="{{ route('purchase.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                            Keluar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End -->

            </div>

        </div>

        <div class="space-y-8 mt-8 mb-6">
                @include('pages.kepalatoko.pos.components.cms-produk')
            <div class="grid gap-5 md:grid-cols-3">

                <div>
                    <label class="block text-sm font-medium mb-1">Tipe</label>
                    <select id="tipe_select" class="form-select text-sm w-full" required>
                        <option value="">Pilih Tipe</option>
                        <option value="Supplier">Supplier</option>
                        <option value="Pelanggan">Pelanggan</option>
                    </select>
                </div>

                <!-- Supplier Dropdown -->
                <div id="supplier_box" class="hidden">
                    <label class="block text-sm font-medium mb-1">Supplier</label>
                    <select id="supplier_select" name="supplier_id" class="supplier_id form-select text-sm w-full">
                        <option value="">Pilih Supplier</option>
                        @foreach ($suppliers as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Pelanggan Dropdown -->
                <div id="pelanggan_box" class="hidden">
                    <label class="block text-sm font-medium mb-1">Pelanggan</label>
                    <select id="pelanggan_select" name="suppliers_id" class="supplier_id form-select text-sm w-full">
                        <option value="">Pilih Pelanggan</option>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->nama }} - {{ $c->nomor_hp }}</option>
                        @endforeach
                    </select>
                </div>


                <div>
                    <!-- Start -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="reference_number">No. Referensi</label>
                        <input id="reference_number" name="reference_number" class="form-input w-full" type="text" />
                    </div>
                    <!-- End -->
                </div>

                <div>
                    <!-- Start -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="date">Tanggal</label>
                        <input id="date" name="date" class="form-input w-full" type="date" value="<?php echo date('Y-m-d'); ?>"/>
                    </div>
                    <!-- End -->
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1" for="categories_id">Kategori <span class="text-rose-500">*</span></label>
            <select id="categories_id" name="categories_id" class="form-select text-sm py-1 w-full" required>
                <option selected="">Pilih Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-3">

            <select id="products_id" name="products_id" class="form-select text-sm w-full selectjs1" required>
                <option value="">Pilih Produk</option>
                @foreach (App\Models\Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->get() as $item)
                    <option value="{{ $item->id }}">{{ $item->product_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-3">
            <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white w-full addeventmore">
                <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                </svg>
                <span class="hidden xs:block ml-2">Masukkan Produk</span>
            </button>
        </div>

        <form method="post" action="{{ route('purchase.store') }}">
            @csrf
            <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
                <header class="px-5 py-4">
                    <h2 class="font-semibold text-slate-800">Data Produk</h2>
                </header>
                <!-- Table -->
                {{-- <div class=""> --}}
                    <div class="overflow-x-auto w-full">
                        <table class="min-w-max w-full">
                            <!-- Table header -->
                            <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                                <tr>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">Nama Produk</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" data-hp-field="kapasitas">
                                        <div class="font-semibold text-left">Kapasitas</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" data-hp-field="ram">
                                        <div class="font-semibold text-left">Ram</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" data-hp-field="warna">
                                        <div class="font-semibold text-left">Warna</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" data-hp-field="imei">
                                        <div class="font-semibold text-left">Imei</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">Kuantitas</div>
                                    </th>

                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" data-hp-field="harga_jual_toko">
                                        <div class="font-semibold text-left">Harga Jual Toko</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" data-hp-field="harga_jual_pelanggan">
                                        <div class="font-semibold text-left">Harga Jual Pelanggan</div>
                                    </th>
                                     <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">Harga Beli</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">Keterangan</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">Total</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">QC</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">Aksi</div>
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="addRow" class="text-sm divide-y divide-slate-200">

                            </tbody>

                            <!-- Table body -->
                            <tbody class="text-sm divide-y divide-slate-200">
                                <!-- Row -->
                                <tr>
                                    <td>
                                        <div class="font-semibold ml-3">Subtotal</div>
                                    </td>
                                    <td  class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <input type="text" name="estimated_amount" id="estimated_amount" size="float:right" value="0" class="form-input estimated_amount" readonly style="background-color: #ddd;">
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                {{-- </div> --}}
            </div>

            <div class="mt-3">
                <button type="submit" id="storeButton" class="btn bg-emerald-500 hover:bg-emerald-600 text-white w-full">
                    Simpan Data Pembelian
                </button>
            </div>
        </form>

        <div id="qcModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Pengecekan Masuk: <span id="qc_product_name" class="font-bold text-indigo-600">Nama Produk</span>
                                </h3>

                                <div class="mt-4 max-h-[60vh] overflow-y-auto">
                                    <div class="overflow-x-auto border rounded-sm">
                                        <table class="w-full text-xs text-left border-collapse">
                                            <thead class="bg-slate-100 uppercase text-slate-500 font-semibold sticky top-0">
                                                <tr>
                                                    <th class="border border-slate-300 p-2 w-8 text-center">No</th>
                                                    <th class="border border-slate-300 p-2 w-1/2">ITEM</th>
                                                    <th class="border border-slate-300 p-2 bg-blue-50 text-center">REMARK IN</th>
                                                    <th class="border border-slate-300 p-2 w-8 text-center"></th>
                                                </tr>
                                            </thead>
                                            <tbody id="qc_modal_tbody" class="text-slate-700">
                                                </tbody>
                                        </table>
                                    </div>

                                    <button type="button" onclick="addCustomRowModal()" class="mt-2 text-xs flex items-center text-indigo-600 font-bold hover:text-indigo-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                        Tambah Baris Custom
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" id="btnSaveQC" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Simpan & Tutup
                        </button>
                        <button type="button" onclick="closeQcModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.8/handlebars.min.js" integrity="sha512-E1dSFxg+wsfJ4HKjutk/WaCzK7S2wv1POn1RRPGh8ZK+ag9l244Vqxji3r6wgz9YBf6+vhQEYJZpSjqWFPg9gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js" integrity="sha512-efUTj3HdSPwWJ9gjfGR71X9cvsrthIA78/Fvd/IN+fttQVy7XWkOAXb295j8B3cmm/kFKVxjiNYzKw9IQJHIuQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script id="document-template" type="text/x-handlebars-template">

            <tr class="delete_add_more_item" id="delete_add_more_item">
                <input type="hidden" name="date[]" value="@{{date}}">
                <input type="hidden" name="reference_number[]" value="@{{reference_number}}">
                <input type="hidden" name="suppliers_id[]" value="@{{suppliers_id}}">
                <input type="hidden" name="tipe_select[]" value="@{{tipe_select}}">
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <div class="font-medium">@{{ product_name }}</div>
                    <input type="hidden" name="products_id[]" value="@{{products_id}}">
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" data-hp-field="kapasitas">
                    <select id="capacities_id" name="capacities_id[]" class="form-input text-sm " >
                        <option selected value="">Pilih Memori</option>
                        @foreach ($capacities as $capacity)
                            <option value="{{ $capacity->id }}">{{ $capacity->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" data-hp-field="ram">
                    <select id="ram" name="ram[]" class="form-input text-sm " >
                        <option selected value="">Pilih RAM</option>
                        <option value="2 GB">2 GB</option>
                        <option value="3 GB">3 GB</option>
                        <option value="4 GB">4 GB</option>
                        <option value="6 GB">6 GB</option>
                        <option value="8 GB">8 GB</option>
                        <option value="12 GB">12 GB</option>
                    </select>
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" data-hp-field="warna">
                    <select id="warna" name="warna[]" class="form-input text-sm " >
                        <option selected value="">Pilih Warna</option>
                        @foreach ($colors as $item)
                            <option value="{{ $item->name }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap" data-hp-field="imei">
                    <input id="nomor_seri" name="nomor_seri[]" class="form-input " type="text" />
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="number" min="1" class="form-input quantity text-right w-full px-2 py-1" name="quantity[]" value="">
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="text" class="form-input harga_jual_toko text-right harga-format" name="harga_jual_toko[]" value="" data-hp-field="harga_jual_toko">
                </td>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="text" class="form-input harga_jual_pelanggan text-right harga-format" name="harga_jual_pelanggan[]" value="" data-hp-field="harga_jual_pelanggan">
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="text" class="form-input product_price text-right harga-format" name="product_price[]" value="">
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="text" class="form-input" name="keterangan[]">
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="text" class="form-input total_price" name="total_price[]" value="0" readonly>
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="hidden" name="qc_data[]" class="qc-data-json" value="">

                    <button type="button" class="btn-sm bg-blue-100 text-blue-600 hover:bg-blue-200 rounded border border-blue-200 flex items-center gap-1 open-qc-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clipboard-check" width="18" height="18" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2"></path>
                        <path d="M9 3m0 2a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v0a2 2 0 0 1 -2 2h-2a2 2 0 0 1 -2 -2z"></path>
                        <path d="M9 14l2 2l4 -4"></path>
                        </svg>
                        <span>Cek</span>
                    </button>
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <button class="text-rose-500 hover:text-rose-600 rounded-full removeeventmore">
                        <span class="sr-only">Delete</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <line x1="4" y1="7" x2="20" y2="7" />
                            <line x1="10" y1="11" x2="10" y2="17" />
                            <line x1="14" y1="11" x2="14" y2="17" />
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                        </svg>
                    </button>
                </td>

            </tr>

        </script>

        <script>
        function formatRibuan(angka) {
            return angka.replace(/\D/g, "")      // Hapus semua non-digit
                        .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        $(document).on("input", ".harga-format", function () {
            let val = $(this).val();
            $(this).val(formatRibuan(val));
        });

        // Sebelum submit: hapus semua koma untuk jadi integer
        $("#storeButton").on("click", function () {
            $(".harga-format").each(function () {
                let clean = $(this).val().replace(/,/g, "");
                $(this).val(clean);
            });
        });
        // Ketika kategori berubah
        $("#categories_id").on("change", function () {
            const categoryId = $(this).val();

            // Jika kategori BUKAN 1, hide kapasitias, ram, warna, imei
            if (categoryId != 1) {
                $('[data-hp-field]').hide();
                $('[data-hp-field] select, [data-hp-field] input').val('');
            } else {
                $('[data-hp-field]').show();
            }
        });

        // Ketika user klik "Masukkan Produk", cek lagi kategori
        $(document).on("click", ".addeventmore", function () {
            setTimeout(() => {
                const categoryId = $("#categories_id").val();
                if (categoryId != 1) {
                    $('[data-hp-field]').hide();
                } else {
                    $('[data-hp-field]').show();
                }
            }, 200);
        });

        document.getElementById("tipe_select").addEventListener("change", function () {
            const tipe = this.value;

            const supplierBox = document.getElementById("supplier_box");
            const pelangganBox = document.getElementById("pelanggan_box");

            if (tipe === "Supplier") {
                supplierBox.classList.remove("hidden");
                pelangganBox.classList.add("hidden");
            } else if (tipe === "Pelanggan") {
                pelangganBox.classList.remove("hidden");
                supplierBox.classList.add("hidden");
            } else {
                supplierBox.classList.add("hidden");
                pelangganBox.classList.add("hidden");
            }
        });
        </script>


        <script type="text/javascript">
            $(document).ready(function(){
                $(document).on("click",".addeventmore", function(){
                    var date = $('#date').val();
                    var reference_number = $('#reference_number').val();
                    var tipe_select = $('#tipe_select').val();
                    if (tipe_select == 'Pelanggan') {
                        var suppliers_id =$('#pelanggan_select').val()
                    }else{
                        var suppliers_id =$('#supplier_select').val()
                    }
                    console.log('====================================');
                    console.log(suppliers_id);
                    console.log('====================================');
                    var products_id = $('#products_id').val();
                    var product_name = $('#products_id').find('option:selected').text();
                    if(date == ''){
                        $.notify("Tanggal wajib diisi" ,  {globalPosition: 'top right', className:'error' });
                        return false;
                        }
                        if(reference_number == ''){
                        $.notify("Nomor Referensi wajib diisi" ,  {globalPosition: 'top right', className:'error' });
                        return false;
                        }
                        if(tipe_select == ''){
                        $.notify("Tipe wajib diisi" ,  {globalPosition: 'top right', className:'error' });
                        return false;
                        }
                        if(suppliers_id == ''){
                        $.notify("Supplier wajib diisi" ,  {globalPosition: 'top right', className:'error' });
                        return false;
                        }
                        if(products_id == ''){
                        $.notify("Produk wajib dipilih" ,  {globalPosition: 'top right', className:'error' });
                        return false;
                        }
                        var source = $("#document-template").html();
                        var template = Handlebars.compile(source);
                        var data = {
                            date:date,
                            reference_number:reference_number,
                            suppliers_id:suppliers_id,
                            products_id:products_id,
                            product_name:product_name,
                            tipe_select:tipe_select
                        };
                        var html = template(data);
                        $("#addRow").append(html);
                });

                $(document).on("click",".removeeventmore",function(event){
                    $(this).closest(".delete_add_more_item").remove();
                    totalAmountPrice();
                });

                $(document).on('keyup click','.product_price,.quantity', function(){
                    var product_price = $(this).closest("tr").find("input.product_price").val().replace(/,/g, "");
                    var quantity = $(this).closest("tr").find("input.quantity").val();
                    var total = product_price * quantity;
                    $(this).closest("tr").find("input.total_price").val(total.toLocaleString('id'));
                     console.log('====================================');
                    console.log(product_price);
                    console.log(total);
                    console.log('====================================');
                    totalAmountPrice();
                });

                function totalAmountPrice(){
                    var sum = 0;
                    $(".total_price").each(function(){
                        var value = $(this).val();
                        console.log('====================================');
                        value = value.replace(/[^0-9]/g, "");
                        console.log(value);
                        console.log('====================================');
                        if(!isNaN(value) && value.length != 0){
                            sum += parseFloat(value);
                        }
                    });
                    const n = sum;
                    const formatted = n.toLocaleString('id');
                    $('#estimated_amount').val(formatted);
                }
            })
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('.selectjs1').select2();
            });
        </script>

        <script type="text/javascript">
            $(function(){
                $(document).on('change','#categories_id',function(){
                    var categories_id = $(this).val();
                    $.ajax({
                        url:"{{ route('get-product') }}",
                        type: "GET",
                        data:{categories_id:categories_id},
                        // success:function(data){
                        //     var html = '<option value="">Pilih Produk</option>';
                        //     $.each(data,function(key,v){
                        //         html += '<option value=" '+v.id+' "> '+v.product_name+'</option>';
                        //     });
                        //     $('#products_id').html(html);
                        // }
                        success: function(data) {
                            var html = '<option value="">Pilih Produk</option>';

                            $.each(data, function(key, v) {
                                var text = '';

                                if (v.categories_id == 1) {

                                    var capacityName = '-';
                                    if (v.capacity != null && v.capacity.name != null) {
                                        capacityName = v.capacity.name;
                                    }

                                    var kondisi = v.kondisi ? v.kondisi : '';
                                    var warna = v.warna ? v.warna : '';
                                    var ram = v.ram ? v.ram : '';
                                    var nomor_seri = v.nomor_seri ? v.nomor_seri : '';

                                    text = v.product_name + ' ' + kondisi + ' ' + warna + ' ' + ram + ' / ' + capacityName + ' (IMEI ' + nomor_seri + ')';

                                } else {
                                    var nomor_seri = v.nomor_seri ? v.nomor_seri : '';
                                    text = v.product_name + ' ' + nomor_seri;
                                }

                                html += '<option value="' + v.id + '">' + text + '</option>';
                            });

                            $('#products_id').html(html);
                        }
                    })
                });
            });
        </script>
        <script>
            // --- KONFIGURASI QC ---
            const defaultChecklist = [
                "CHECK FACE ID/FINGER", "CHECK FRONT CAM 1/2", "CHECK BACK CAM 1/2/3",
                "CHECK CAM 30PFS,60PFS", "TOP SPEAKER", "BOTTOM SPEAKER",
                "BODY HOUSING", "LCD (Truetone,Ts)", "NETWORK", "CALLING PHONE",
                "BATTERY", "BACK MIC", "BOTTOM MIC", "FRONT MIC",
                "TOP AUDIO", "BOTTOM AUDIO", "WIFI/BLUETOOTH", "FLASH LED",
                "ALL BUTTON", "COMPAS", "VIBRANT/SILENT", "CHARGING",
                "PANIC FULL", "OTHER"
            ];

            let currentActiveRow = null; // Menyimpan referensi baris TR yang sedang diedit QC-nya

            // --- FUNGSI MEMBUKA MODAL ---
            $(document).on('click', '.open-qc-modal', function() {
                // 1. Simpan baris yang sedang aktif
                currentActiveRow = $(this).closest('tr');

                // 2. Ambil Nama Produk untuk Judul Modal
                // Karena di template handlebar nama produk ada di div text, kita ambil textnya
                let productName = currentActiveRow.find('td:first .font-medium').text().trim();
                // Fallback jika ambil text gagal (tergantung struktur persisnya), coba ambil dari select/option logika sebelumnya
                if(!productName) productName = "Produk";
                $('#qc_product_name').text(productName);

                // 3. Ambil Data Lama (jika ada) dari hidden input
                let existingDataJson = currentActiveRow.find('.qc-data-json').val();
                let existingData = [];

                if (existingDataJson) {
                    try {
                        existingData = JSON.parse(existingDataJson);
                    } catch (e) {
                        console.error("Error parsing JSON QC", e);
                        existingData = [];
                    }
                }

                // 4. Render Tabel di Modal
                renderQcTable(existingData);

                // 5. Tampilkan Modal
                $('#qcModal').removeClass('hidden');
            });

            // --- FUNGSI RENDER TABEL MODAL ---
            function renderQcTable(savedData) {
                const tbody = document.getElementById('qc_modal_tbody');
                tbody.innerHTML = '';

                // Jika belum ada data tersimpan, gunakan default checklist
                if (!savedData || savedData.length === 0) {
                    defaultChecklist.forEach((item, index) => {
                        appendRowToModal(tbody, index + 1, item, '', false); // false = bukan custom
                    });
                } else {
                    // Jika sudah ada data, render berdasarkan data yang disimpan
                    savedData.forEach((data, index) => {
                        appendRowToModal(tbody, index + 1, data.item, data.value, data.is_custom);
                    });
                }
            }

            // Helper untuk append row
            function appendRowToModal(tbody, num, itemName, value, isCustom) {
                const tr = document.createElement('tr');
                tr.className = "border-b border-slate-200 hover:bg-slate-50 qc-row-item";

                // Input Item Name (Readonly jika standard, Editable jika custom)
                let nameInput = '';
                if(isCustom) {
                    nameInput = `<input type="text" class="qc-item-name w-full p-1 border-0 focus:ring-0 bg-transparent font-medium text-indigo-600" value="${itemName}" placeholder="Nama Item...">`;
                } else {
                    nameInput = `<span class="qc-item-name-text pl-1">${itemName}</span><input type="hidden" class="qc-item-name" value="${itemName}">`;
                }

                // Hidden input penanda custom
                let customMarker = `<input type="hidden" class="qc-is-custom" value="${isCustom ? 1 : 0}">`;

                tr.innerHTML = `
                    <td class="border border-slate-300 p-1 text-center font-bold row-num">${num}</td>
                    <td class="border border-slate-300 p-1 font-medium bg-slate-50">
                        ${nameInput} ${customMarker}
                    </td>
                    <td class="border border-slate-300 p-0">
                        <input type="text" class="qc-item-value w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" value="${value}" placeholder="-">
                    </td>
                    <td class="border border-slate-300 p-1 text-center">
                        <button type="button" class="text-rose-500 hover:text-rose-700" onclick="deleteModalRow(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            }

            // --- FUNGSI TAMBAH ROW CUSTOM DI MODAL ---
            function addCustomRowModal() {
                const tbody = document.getElementById('qc_modal_tbody');
                const rowCount = tbody.rows.length + 1;
                appendRowToModal(tbody, rowCount, '', '', true); // true = custom
            }

            // --- FUNGSI HAPUS ROW CUSTOM ---
            function deleteModalRow(btn) {
                const row = btn.closest('tr');
                const tbody = row.parentNode;
                row.remove();
                // Re-numbering
                Array.from(tbody.rows).forEach((r, index) => {
                    const numCell = r.querySelector('.row-num');
                    if(numCell) numCell.innerText = index + 1;
                });
            }

            // --- FUNGSI TUTUP MODAL ---
            function closeQcModal() {
                $('#qcModal').addClass('hidden');
                currentActiveRow = null;
            }

            // --- FUNGSI SIMPAN DATA DARI MODAL KE HIDDEN INPUT ---
            $('#btnSaveQC').on('click', function() {
                if (!currentActiveRow) return;

                let dataToSave = [];

                // Loop semua baris di modal
                $('#qc_modal_tbody tr').each(function() {
                    let itemName = $(this).find('.qc-item-name').val();
                    let itemVal = $(this).find('.qc-item-value').val();
                    let isCustom = $(this).find('.qc-is-custom').val() == 1;

                    // Simpan hanya jika nama item ada (untuk custom row yg kosong tidak disimpan)
                    if (itemName && itemName.trim() !== '') {
                        dataToSave.push({
                            item: itemName,
                            value: itemVal,
                            is_custom: isCustom
                        });
                    }
                });

                // Convert ke JSON String
                let jsonString = JSON.stringify(dataToSave);

                // Masukkan ke Hidden Input di Tabel Utama
                currentActiveRow.find('.qc-data-json').val(jsonString);

                // Ubah warna tombol biar user tau sudah diisi (Opsional)
                currentActiveRow.find('.open-qc-modal').removeClass('bg-blue-100 text-blue-600').addClass('bg-green-100 text-green-700 border-green-200');
                currentActiveRow.find('.open-qc-modal span').text('Sudah Dicek');

                closeQcModal();
                $.notify("Data QC disimpan sementara", { className: 'success', position: 'top right' });
            });

            // Close modal on click outside (optional)
            $(window).click(function(e) {
                if (e.target.id === 'qcModal') {
                    // closeQcModal(); // Uncomment jika ingin klik luar menutup modal
                }
            });
        </script>
    @endpush
</x-toko-layout>
