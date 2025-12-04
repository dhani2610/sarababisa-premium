@section('title')
    Edit Transaksi Servis
@endsection
<x-admin-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Transaksi Servis ✨</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Search form -->
                <x-search-form placeholder="Cari berdasarkan nama" />

                <!-- Create invoice button -->
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path
                            d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Transaksi Baru</span>
                </button>

            </div>

        </div>
        <div x-data="{ modalOpen: true }">
            <!-- Modal backdrop -->
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak>
            </div>
            <!-- Modal dialog -->
            <div id="edit-modal"
                class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                role="dialog" aria-modal="true" x-show="modalOpen"
                x-transition:enter="transition ease-in-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in-out duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
                x-cloak>
                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">
                    <!-- Modal header -->
                    <div class="px-5 py-3 border-b border-slate-200">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Edit Transaksi Servis</div>
                            <a href="{{ route('transaksi-servis-sudah-diambil.index') }}"
                                class="text-slate-400 hover:text-slate-500">
                                <div class="sr-only">Close</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path
                                        d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Modal content -->
                    <div class="px-5 py-4">

                        <form action="{{ route('transaksi-servis-sudah-diambil.update', $item->id) }}" method="post">
                            @csrf

                            {{-- <input type="hidden" name="tgl_selesai" value="<?php echo date('Y/m/d'); ?>" />
                            <input type="hidden" name="tgl_ambil" value="<?php echo date('Y-m-d H:i:s'); ?>" />
                            <input type="hidden" name="tgl_disetujui" value="<?php echo date('Y/m/d'); ?>" />--}}
                            <div class="space-y-3">
                                 <div>
                                    <label class="block text-sm font-medium mb-1" for="created_at">Tgl. Terima </label>
                                    <input id="created_at" name="created_at" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="tgl_ambil">Tgl. Ambil </label>
                                    <input id="tgl_ambil" name="tgl_ambil" class="form-input w-full px-2 py-1" type="datetime" value="{{ $item->tgl_ambil }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="customers_id">Nama
                                        Pelanggan <span class="text-rose-500">*</span></label>
                                    <select name="customers_id" class="form-select text-sm py-1 w-full" id="selectjs3"
                                        required style="width: 100%">
                                        <option selected value="">Pilih Pelanggan</option>
                                        @foreach ($customers as $itemcs)
                                            <option value="{{ $itemcs->id }}" {{ $item->customer->id == $itemcs->id  ? 'selected' : '' }}>{{ $itemcs->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="types_id">Jenis Barang
                                        <span class="text-rose-500">*</span></label>
                                    <select id="types_id" name="types_id" class="form-select text-sm py-1 w-full"
                                        required>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}" {{ $item->type->id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="brands_id">Merek <span
                                            class="text-rose-500">*</span></label>
                                    <select id="merek" name="brands_id" class="form-select text-sm py-1 w-full"
                                        required>
                                        <option selected="">Pilih Merek</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ $item->brand->id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="model_series_id">Model
                                        Seri <span class="text-rose-500">*</span></label>
                                    <select id="model" name="model_series_id"
                                        class="form-select text-sm py-1 w-full selectjs4" required style="width: 100%">
                                        <option selected="">Pilih Model Seri</option>
                                        @foreach ($model_series as $model_serie)
                                            <option value="{{ $model_serie->id }}" {{ $item->modelserie->id ?? '' == $model_serie->id ? 'selected' : ''  }}>{{ $model_serie->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="imei">Nomor Imei
                                        <span class="text-rose-500">*</span></label>
                                    <input id="imei" name="imei" class="form-input w-full px-2 py-1"
                                        type="text" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="warna">Warna <span
                                            class="text-rose-500">*</span></label>
                                    <input id="warna" name="warna" class="form-input w-full px-2 py-1"
                                        type="text" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="capacities_id">Kapasitas <span
                                            class="text-rose-500">*</span></label>
                                    <select id="capacities_id" name="capacities_id"
                                        class="form-select text-sm py-1 w-full" required>
                                        @foreach ($capacities as $capacity)
                                            <option value="{{ $capacity->id }}">{{ $capacity->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1"
                                        for="kelengkapan">Kelengkapan</label>
                                    <input id="kelengkapan" name="kelengkapan" class="form-input w-full px-2 py-1"
                                        type="text" placeholder="Kosongkan jika kelengkapannya hanya unit" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="kerusakan">Kerusakan
                                        <span class="text-rose-500">*</span></label>
                                    <input id="kerusakan" name="kerusakan" class="form-input w-full px-2 py-1"
                                        type="text" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="qc_masuk">Pengecekan
                                        Fungsi Masuk<span class="text-rose-500">*</span></label>
                                    <input id="qc_masuk" name="qc_masuk" class="form-input w-full px-2 py-1"
                                        type="text" required placeholder="Contoh: Tombol, Kamera, Speaker, dll" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="penerima">Penerima</label>
                                    <select id="penerima" name="penerima" class="form-select text-sm py-1 w-full"
                                        required>
                                        @foreach ($penerima as $worker)
                                            <option value="{{ $worker->name }}">{{ $worker->name }}</option>
                                        @endforeach
                                    </select>
                                </div>


                                <div x-data="{ showDetails: true }">
                                    <label class="block text-sm font-medium mb-1" for="kondisi_servis">Kondisi
                                        Servis <span class="text-rose-500">*</span></label>
                                    <div class="flex flex-wrap items-center -m-3">
                                        <div class="m-3">
                                            <!-- Start -->
                                            <label class="flex items-center">
                                                <input type="radio" name="kondisi_servis" value="Sudah jadi"
                                                    class="form-radio" checked x-on:click="showDetails = true" />
                                                <span class="text-sm ml-2">Sudah jadi</span>
                                            </label>
                                            <!-- End -->
                                        </div>
                                        <div class="m-3">
                                            <!-- Start -->
                                            <label class="flex items-center">
                                                <input type="radio" name="kondisi_servis" value="Dibatalkan"
                                                    class="form-radio" x-on:click="showDetails = false" />
                                                <span class="text-sm ml-2">Dibatalkan</span>
                                            </label>
                                            <!-- End -->
                                        </div>
                                    </div>
                                    <div x-show="showDetails" class="mt-3 space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="users_id">Teknisi <span
                                                    class="text-rose-500">*</span></label>
                                            <select id="users_id" name="users_id"
                                                class="form-select text-sm py-1 w-full">
                                                <option selected value="">Pilih Teknisi</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div x-data="{ showInputManual: false }">
                                            <div class="flex justify-between items-center mb-1">
                                                <label class="block text-sm font-medium">
                                                    Tindakan Servis
                                                    <span class="text-rose-500">*</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" class="form-checkbox"
                                                        x-on:click="showInputManual = true" />
                                                    <span class="text-sm ml-2">Isi Manual</span>
                                                </label>
                                            </div>
                                            <div class="pilih-tindakan">
                                                <select id="selectjs5" name="service_actions_id[]"
                                                    class="form-select text-sm py-1 w-full" style="width: 100%;">
                                                    <option selected value="">Pilih Tindakan</option>
                                                    @foreach ($service_actions as $action)
                                                        <option value="{{ $action->id }}">
                                                            {{ $action->nama_tindakan }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div x-show="showInputManual" class="mt-2">
                                                <input class="form-input w-full px-2 py-1" type="text"
                                                    name="tindakan_servis[]" />
                                            </div>
                                            <input type="hidden" name="prev_modal" value="0">
                                            <input type="hidden" name="prev_biaya" value="0">
                                        </div>
                                        <div x-data="{ showDetails: false, modalSparepart: 0 }" x-init="// hook ke event select2
                                        $('#selectjs6').on('select2:select', function(e) {
                                            let data = e.params.data.element.dataset.harga_modal;
                                            modalSparepart = data || 0;
                                        });">
                                            <label class="block text-sm font-medium mb-1" for="modal_sparepart">Apakah
                                                menggunakan stok sparepart
                                                toko?</label>
                                            <div class="flex flex-wrap items-center -m-3">
                                                <div class="m-3">
                                                    <!-- Start -->
                                                    <label class="flex items-center">
                                                        <input type="radio" name="radio-buttons" class="form-radio"
                                                            checked x-on:click="showDetails = false" />
                                                        <span class="text-sm ml-2">Tidak</span>
                                                    </label>
                                                    <!-- End -->
                                                </div>
                                                <div class="m-3">
                                                    <!-- Start -->
                                                    <label class="flex items-center">
                                                        <input type="radio" name="radio-buttons" class="form-radio"
                                                            x-on:click="showDetails = true" />
                                                        <span class="text-sm ml-2">Ya</span>
                                                    </label>
                                                    <!-- End -->
                                                </div>
                                            </div>
                                            <div x-show="showDetails" class="mt-3">
                                                <label class="block text-sm font-medium mb-1"
                                                    for="products_id">Sparepart Toko yg Digunakan</label>
                                                <select id="selectjs6" name="products_id[]"
                                                    class="form-select text-sm py-1 w-full" style="width: 100%;"
                                                    x-on:change="
                                                         let selected = $el.options[$el.selectedIndex];
                                                         modalSparepart = selected.dataset.hargaModal || 0;
                                                     ">
                                                    <option selected value="">Pilih Sparepart</option>
                                                    @foreach (App\Models\Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->get() as $item)
                                                        <option value="{{ $item->id }}"
                                                            data-harga_modal="{{ $item->harga_modal }}">
                                                            {{ $item->product_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div x-show="showDetails" class="mt-3">
                                                <label class="block text-sm font-medium mb-1" for="sales_id">Sales
                                                    Sparepart</label>
                                                <select id="sales_id" name="sales_id[]"
                                                    class="form-select text-sm py-1 w-full">
                                                    <option selected value="1">Tidak ada Sales</option>
                                                    @foreach ($sales as $user)
                                                        <option value="{{ $user->id }}">
                                                            {{ $user->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1"
                                                    for="garansi">Garansi</label>
                                                <select name="garansi[]" class="form-select text-sm py-1 w-full">
                                                    <option value="">Tidak Ada</option>
                                                    <option value="1">1 Hari</option>
                                                    <option value="2">2 Hari</option>
                                                    <option value="3">3 Hari</option>
                                                    <option value="4">4 Hari</option>
                                                    <option value="5">5 Hari</option>
                                                    <option value="6">6 Hari</option>
                                                    <option value="7">1 Minggu</option>
                                                    <option value="14">2 Minggu</option>
                                                    <option value="21">3 Minggu</option>
                                                    <option value="30">1 Bulan</option>
                                                    <option value="60">2 Bulan</option>
                                                    <option value="90">3 Bulan</option>
                                                    <option value="120">4 Bulan</option>
                                                    <option value="150">5 Bulan</option>
                                                    <option value="180">6 Bulan</option>
                                                    <option value="210">7 Bulan</option>
                                                    <option value="240">8 Bulan</option>
                                                    <option value="270">9 Bulan</option>
                                                    <option value="300">10 Bulan</option>
                                                    <option value="330">11 Bulan</option>
                                                    <option value="365">1 Tahun</option>
                                                    <option value="730">2 Tahun</option>
                                                    <option value="1095">3 Tahun</option>
                                                    <option value="1460">4 Tahun</option>
                                                    <option value="1825">5 Tahun</option>
                                                </select>
                                            </div>
                                            {{-- <div class="mt-3">
                                                 <label class="block text-sm font-medium mb-1"
                                                     for="modal_sparepart">Modal Sparepart <span
                                                         class="text-rose-500">*</span></label>
                                                 <input class="form-input w-full px-2 py-1 modal_sparepart"
                                                     type="number" name="modal_sparepart[]" :required="showDetails" />
                                             </div> --}}
                                            <div class="mt-3">
                                                <label class="block text-sm font-medium mb-1"
                                                    for="modal_sparepart">Modal Sparepart <span
                                                        class="text-rose-500">*</span></label>
                                                <input class="form-input w-full px-2 py-1 modal_sparepart"
                                                    type="number" name="modal_sparepart[]" x-model="modalSparepart"
                                                    :required="showDetails" />
                                            </div>

                                            <div class="mt-3">
                                                <label class="block text-sm font-medium mb-1" for="biaya_servis">Biaya
                                                    Servis <span class="text-rose-500">*</span></label>
                                                <input class="form-input w-full px-2 py-1 biaya_servis" type="number"
                                                    name="biaya_servis[]" :required="showDetails" />
                                            </div>
                                            <div id="servis-lain"></div>
                                            {{-- Tombol tambah servis --}}
                                            <div>
                                                <button type="button"
                                                    class="rounded-lg px-4 py-1 bg-blue-600 text-white"
                                                    id="tambah-servis">+ tambah
                                                    tindakan servis</button>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1"
                                                    for="qc_keluar">Pengecekan Fungsi Keluar <span
                                                        class="text-rose-500">*</span></label>
                                                <input id="qc_keluar" name="qc_keluar"
                                                    class="form-input w-full px-2 py-1" type="text"
                                                    placeholder="Contoh: Tombol, Kamera, Speaker, dll"
                                                    :required="showDetails" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1"
                                                    for="total_modal_sparepart">Total
                                                    Modal
                                                    Sparepart <span class="text-rose-500">*</span></label>
                                                <input class="form-input w-full px-2 py-1" type="number"
                                                    name="total_modal_sparepart" id="total_modal_sparepart" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="biaya">Total
                                                    Biaya
                                                    Servis
                                                    <span class="text-rose-500">*</span></label>
                                                <input class="form-input w-full px-2 py-1" type="number"
                                                    name="biaya" id="biaya" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1"
                                                    for="diskon">Diskon</label>
                                                <input id="diskon" name="diskon"
                                                    class="form-input w-full px-2 py-1" type="text"
                                                    placeholder="Kosongkan jika tidak ada diskon" />
                                            </div>
                                            <div x-data="{ caraPembayaran: 'Tunai' }">
                                                <label class="block text-sm font-medium mb-1"
                                                    for="cara_pembayaran">Cara Pembayaran</label>
                                                <select id="cara_pembayaran" name="cara_pembayaran"
                                                    class="form-select text-sm py-1 w-full" x-model="caraPembayaran">
                                                    <option selected value="Tunai">Tunai</option>
                                                    <option value="Transfer">Transfer</option>
                                                    <option value="Kredit">Kredit</option>
                                                    <option value="Tunai & Transfer">Tunai & Transfer</option>
                                                </select>

                                                <div x-show="caraPembayaran === 'Tunai & Transfer'" class="mt-3">
                                                    <label class="block text-sm font-medium text-indigo-500">Silahkan
                                                        isi hanya pada salah satu input saja: Tunai /
                                                        Transfer</label>
                                                    <div class="flex flex-row gap-3">
                                                        <div class="w-1/2 mb-3 md:mb-0">
                                                            <label class="block text-sm font-medium mb-1"
                                                                for="tunai">Tunai</label>
                                                            <input class="form-input w-full py-1" type="number"
                                                                name="tunai" id="tunai" value="0" />
                                                        </div>
                                                        <div class="w-1/2 mb-3 md:mb-0">
                                                            <label class="block text-sm font-medium mb-1"
                                                                for="transfer">Transfer</label>
                                                            <input class="form-input w-full py-1" type="number"
                                                                name="transfer" id="transfer" value="0" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div x-show="caraPembayaran === 'Kredit'" class="mt-3">
                                                    <div>
                                                        <label class="block text-sm font-medium mb-1"
                                                            for="pay">Jumlah Pembayaran <span
                                                                class="text-rose-500">*</span></label>
                                                        <input id="pay" name="pay"
                                                            class="form-input w-full px-2 py-1" type="number" />
                                                    </div>
                                                    <div class="flex flex-wrap items-center -m-3 mt-0">
                                                        <div class="m-3">
                                                            <!-- Start -->
                                                            <label class="flex items-center">
                                                                <input name="tunai" type="checkbox"
                                                                    class="form-checkbox" />
                                                                <span class="text-sm ml-2">Tunai</span>
                                                            </label>
                                                            <!-- End -->
                                                        </div>

                                                        <div class="m-3">
                                                            <!-- Start -->
                                                            <label class="flex items-center">
                                                                <input name="transfer" type="checkbox"
                                                                    class="form-checkbox" />
                                                                <span class="text-sm ml-2">Transfer</span>
                                                            </label>
                                                            <!-- End -->
                                                        </div>
                                                    </div>
                                                    <label class="block text-sm font-medium mt-3" for="tempo">Waktu
                                                        Tempo <span class="text-rose-500">*</span></label>
                                                    <select id="tempo" name="tempo"
                                                        class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1">
                                                        <option value="">Pilih Waktu Tempo</option>
                                                        <option value="1">Tempo 1 Hari</option>
                                                        <option value="2">Tempo 2 Hari</option>
                                                        <option value="3">Tempo 3 Hari</option>
                                                        <option value="4">Tempo 4 Hari</option>
                                                        <option value="5">Tempo 5 Hari</option>
                                                        <option value="6">Tempo 6 Hari</option>
                                                        <option value="7">Tempo 1 Minggu</option>
                                                        <option value="14">Tempo 2 Minggu</option>
                                                        <option value="21">Tempo 3 Minggu</option>
                                                        <option value="30">Tempo 1 Bulan</option>
                                                        <option value="60">Tempo 2 Bulan</option>
                                                        <option value="90">Tempo 3 Bulan</option>
                                                    </select>
                                                </div>
                                            </div>
                                            {{-- <div>
                                                 <label class="block text-sm font-medium mb-1"
                                                     for="catatan">Catatan</label>
                                                 <textarea id="catatan" name="catatan" class="form-textarea w-full px-2 py-1" rows="2"></textarea>
                                             </div> --}}
                                        </div>

                                    </div>
                                    <div id="total_modal_batal_wrapper" class="mt-4 space-y-3"
                                        style="display: none;">
                                        <label class="block text-sm font-medium mb-1" for="total_modal_batal">
                                            Total Modal
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <input class="form-input w-full px-2 py-1" type="number"
                                            name="total_modal_sparepart" id="total_modal_batal" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="catatan">Catatan
                                            <small>(Kosongkan jika tidak
                                                perlu)</small></label>
                                        <textarea id="catatan" name="catatan" class="form-textarea w-full px-2 py-1" rows="2"
                                            placeholder="Tulis catatan yang diperlukan untuk pelanggan, catatan akan muncul pada nota pengambilan."></textarea>
                                    </div>
                                </div>

                            </div>
                            <!-- Modal footer -->
                            <div class="pt-4 border-t border-slate-200 mt-4">
                                <div class="flex flex-wrap justify-between space-x-2">
                                    <a href="{{ route('pelanggan.index') }}"
                                        class="btn-sm bg-green-500 hover:bg-green-600 text-white">
                                        Tambah Pelanggan Baru
                                    </a>
                                    <div>
                                        <a href="{{ route('transaksi-servis.index') }}"
                                            class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                            Batal
                                        </a>
                                        <button
                                            class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    {{-- @push('scripts') --}}
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('handleSelect', () => ({
                    selectall: false,
                    selectAction() {
                        countEl = document.querySelector('.table-items-action');
                        if (!countEl) return;
                        checkboxes = document.querySelectorAll('input.table-item:checked');
                        document.querySelector('.table-items-count').innerHTML = checkboxes.length;
                        if (checkboxes.length > 0) {
                            countEl.classList.remove('hidden');
                        } else {
                            countEl.classList.add('hidden');
                        }
                    },
                    toggleAll() {
                        this.selectall = !this.selectall;
                        checkboxes = document.querySelectorAll('input.table-item');
                        [...checkboxes].map((el) => {
                            el.checked = this.selectall;
                        });
                        this.selectAction();
                    },
                    uncheckParent() {
                        this.selectall = false;
                        document.getElementById('parent-checkbox').checked = false;
                        this.selectAction();
                    },
                    deleteSelected() {
                        const checkboxes = document.querySelectorAll('input.table-item:checked');
                        const selectedIds = [...checkboxes].map((checkbox) => checkbox.value);

                        // Kirim permintaan penghapusan ke server
                        fetch('/services/delete', {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                                body: JSON.stringify({
                                    selectedIds
                                }),
                            })
                            .then(response => response.json())
                            .then(data => {
                                alert(data.message);
                                // Refresh halaman atau lakukan tindakan lain setelah penghapusan
                                window.location.reload();
                            })
                            .catch(error => {
                                console.error('Gagal menghapus data:', error);
                            });
                    },
                }))
            })
        </script>


        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                $('input[name="kondisi_servis"]').on('change', function() {
                    const selectedValue = $(this).val();

                    if (selectedValue === 'Dibatalkan') {
                        $('#total_modal_batal_wrapper').show(); // Tampilkan input
                        $('#total_modal_batal').attr('required', true); // Jadikan required

                        $('#total_modal_sparepart').closest('.mt-3').hide(); // Sembunyikan sparepart
                        $('#total_modal_sparepart').removeAttr('required'); // Hilangkan required dari sparepart
                    } else {
                        $('#total_modal_batal_wrapper').hide(); // Sembunyikan input
                        $('#total_modal_batal').removeAttr('required'); // Hilangkan required

                        $('#total_modal_sparepart').closest('.mt-3').show(); // Tampilkan sparepart
                        $('#total_modal_sparepart').attr('required', true); // Tambahkan required ke sparepart
                    }
                });

                // Trigger change on load (misalnya ketika form reload)
                $('input[name="kondisi_servis"]:checked').trigger('change');
            });
        </script>
        {{--
<script>
    function getTotal() {
        let biaya = parseInt($('#biaya').val()) || 0;
        let diskon = parseInt($('#diskon').val()) || 0;
        let totalFinal = Math.max(biaya - diskon, 0);
        console.log('totalFinal',totalFinal);

        return totalFinal;
    }

    $(document).ready(function() {
        // Kalau user ubah tunai
        $('#tunai').on('input', function() {
            if ($('#cara_pembayaran').val() === 'Tunai & Transfer') {
                let total = getTotal();
                let tunai = parseInt($(this).val()) || 0;
                let transfer = total - tunai;
                $('#transfer').val(transfer >= 0 ? transfer : 0);
            }
        });

        // Kalau user ubah transfer
        $('#transfer').on('input', function() {
            if ($('#cara_pembayaran').val() === 'Tunai & Transfer') {
                let total = getTotal();
                let transfer = parseInt($(this).val()) || 0;
                let tunai = total - transfer;
                $('#tunai').val(tunai >= 0 ? tunai : 0);
            }
        });

        // Kalau biaya atau diskon berubah, reset ulang input tunai & transfer
        $('#biaya, #diskon').on('input', function() {
            $('#tunai').trigger('input');
        });

        // Saat cara pembayaran diganti
        $('#cara_pembayaran').on('change', function() {
            if ($(this).val() === 'Tunai & Transfer') {
                $('#tunai').trigger('input');
            } else {
                $('#tunai, #transfer').val(0);
            }
        });
    });
</script> --}}


        @php
            $ppn = 0;
            $cekPPN = \App\Models\StoreSetting::where('cabang_id',getCabangId())->first();
            if (!empty($cekPPN)) {
                if ($cekPPN->is_tax == 1 && $cekPPN->ppn != 0) {
                    $ppn = $cekPPN->ppn;
                }
            }
        @endphp

        <script>
            // Ambil nilai ppn dari PHP
            let ppn = {{ $ppn }};

            function getTotal() {
                let biaya = parseInt($('#biaya').val()) || 0;
                let diskon = parseInt($('#diskon').val()) || 0;

                // Hitung subtotal
                let subtotal = Math.max(biaya - diskon, 0);

                // Tambah PPN kalau ada
                if (ppn > 0) {
                    subtotal += Math.round(subtotal * ppn / 100);
                }

                return subtotal;
            }

            $(document).ready(function() {
                // Kalau user ubah tunai
                $('#tunai').on('input', function() {
                    if ($('#cara_pembayaran').val() === 'Tunai & Transfer') {
                        let total = getTotal();
                        let tunai = parseInt($(this).val()) || 0;
                        let transfer = total - tunai;
                        $('#transfer').val(transfer >= 0 ? transfer : 0);
                    }
                });

                // Kalau user ubah transfer
                $('#transfer').on('input', function() {
                    if ($('#cara_pembayaran').val() === 'Tunai & Transfer') {
                        let total = getTotal();
                        let transfer = parseInt($(this).val()) || 0;
                        let tunai = total - transfer;
                        $('#tunai').val(tunai >= 0 ? tunai : 0);
                    }
                });

                // Kalau biaya atau diskon berubah, reset ulang input tunai & transfer
                $('#biaya, #diskon').on('input', function() {
                    $('#tunai').trigger('input');
                });

                // Saat cara pembayaran diganti
                $('#cara_pembayaran').on('change', function() {
                    if ($(this).val() === 'Tunai & Transfer') {
                        $('#tunai').trigger('input');
                    } else {
                        $('#tunai, #transfer').val(0);
                    }
                });
            });
        </script>
       <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM="
            crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#selectjs1').select2();
                $('.selectjs2').select2();
                $('#selectjs3').select2();
                $('.selectjs4').select2();
                $('#selectjs5').select2();
                $('#selectjs6').select2();
            });
        </script>
        <script type="text/javascript">
            $(function() {
                $(document).on('change', '#brands_id', function() {
                    var brands_id = $(this).val();
                    $.ajax({
                        url: "{{ route('get-modelserie') }}",
                        type: "GET",
                        data: {
                            brands_id: brands_id
                        },
                        success: function(data) {
                            var html = '<option value="">Pilih Model Seri</option>';
                            $.each(data, function(key, v) {
                                html += '<option value=" ' + v.id + ' "> ' + v.name +
                                    '</option>';
                            });
                            $('#model_series_id').html(html);
                        }
                    })
                });
            });
        </script>
        <script type="text/javascript">
            $(function() {
                $(document).on('change', '#merek', function() {
                    var brands_id = $(this).val();
                    $.ajax({
                        url: "{{ route('get-modelserie') }}",
                        type: "GET",
                        data: {
                            brands_id: brands_id
                        },
                        success: function(data) {
                            var html = '<option value="">Pilih Model Seri</option>';
                            $.each(data, function(key, v) {
                                html += '<option value=" ' + v.id + ' "> ' + v.name +
                                    '</option>';
                            });
                            $('#model').html(html);
                        }
                    })
                });
            });
        </script>
        <script>
            $(document).on('change', '.pilih-tindakan select', function() {
                var serviceActionId = $(this).val();
                const myEl = $(this)
                if (serviceActionId) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-action/' + serviceActionId,
                        dataType: 'json',
                        success: function(data) {
                            const curBiaya = $('#biaya').val() || 0;
                            const curModal = $('#total_modal_sparepart').val() || 0;
                            const prevModal = myEl.parent().parent().parent().find(
                                '[name="prev_modal"]:first');
                            const prevBiaya = myEl.parent().parent().parent().find(
                                '[name="prev_biaya"]:first');

                            $('#biaya').val((parseInt(curBiaya) - parseInt(prevBiaya.val()) + parseInt(data
                                .biaya)).toString());
                            $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal
                                    .val()) + parseInt(data
                                    .modal_sparepart))
                                .toString());
                            prevModal.val(data.modal_sparepart)
                            prevBiaya.val(data.biaya)
                            myEl.parent().parent().parent().find('[name="modal_sparepart"]:first').val(data
                                .modal_sparepart)
                            myEl.parent().parent().parent().find('[name="biaya_servis"]:first').val(data
                                .biaya)
                        }
                    });
                } else {
                    $('#biaya').val('');
                    $('#modal_sparepart').val('');
                }
            });

            $(document).on('change', '.modal_sparepart', function(e) {
                const myEl = $(this);
                const curModal = $('#total_modal_sparepart').val() || 0;
                const prevModal = myEl.parent().parent().parent().find('[name="prev_modal"]:first');
                $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal.val()) + parseInt(myEl.val()))
                    .toString());
                prevModal.val(myEl.val())
            })

            $(document).on('change', '.biaya_servis', function(e) {
                const myEl = $(this);
                const curModal = $('#biaya').val() || 0;
                const prevModal = myEl.parent().parent().parent().find('[name="prev_biaya"]:first');
                $('#biaya').val((parseInt(curModal) - parseInt(prevModal.val()) + parseInt(myEl.val()))
                    .toString());
                prevModal.val(myEl.val())
            })

            $(document).on('change', '.selectAction2', function() {
                var productId = $(this).val();
                if (productId) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-sparepart/' + productId,
                        dataType: 'json',
                        success: function(data) {
                            const prev = $('#modal_sparepart').val() || 0;
                            $('#modal_sparepart').val((parseInt(prev) + parseInt(data.modal_sparepart))
                                .toString());
                        }
                    });
                } else {
                    $('#modal_sparepart').val('');
                }
            });

            $(document).ready(function() {

                let pilihTindakanEl = `<div x-data="{ showInputManual: false }" class="tindakan-servis">
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-sm font-medium">
                        Tindakan Servis
                        <span class="text-rose-500">*</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox"
                            x-on:click="showInputManual = true" />
                        <span class="text-sm ml-2">Isi Manual</span>
                    </label>
                </div>
                <div class="flex flex-col pilih-tindakan">
                    <select class="selectAction" name="service_actions_id[]"
                        class="form-select text-sm py-1 w-full">
                        <option selected value="">Pilih Tindakan</option>
                        @foreach (App\Models\ServiceAction::all() as $action)
                            <option value="{{ $action->id }}">
                                {{ $action->nama_tindakan }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="showInputManual" class="mt-2">
                    <input class="form-input w-full px-2 py-1" type="text"
                        name="tindakan_servis[]" />
                </div>
                <input type="hidden" name="prev_modal" value="0">
                <input type="hidden" name="prev_biaya" value="0">
            </div>`
                let konfirSparepartEl = `<div x-data="{ showDetails: false }" class="konfirmasi-stok border-b-2 pb-4">
                    <label class="block text-sm font-medium mb-1" for="modal_sparepart">Apakah
                        menggunakan stok sparepart toko?</label>
                    <div class="flex flex-wrap items-center -m-3">
                        <div class="m-3">
                            <!-- Start -->
                            <label class="flex items-center">
                                <input type="radio" name="radio-buttons" class="form-radio"
                                    checked x-on:click="showDetails = false" />
                                <span class="text-sm ml-2">Tidak</span>
                            </label>
                            <!-- End -->
                        </div>
                        <div class="m-3">
                            <!-- Start -->
                            <label class="flex items-center">
                                <input type="radio" name="radio-buttons" class="form-radio"
                                    x-on:click="showDetails = true" />
                                <span class="text-sm ml-2">Ya</span>
                            </label>
                            <!-- End -->
                        </div>
                    </div>
                    <div x-show="showDetails" class="mt-3">
                        <label class="block text-sm font-medium mb-1"
                            for="products_id">Sparepart Toko yg Digunakan</label>
                        <select class="selectAction2" name="products_id[]"
                            class="form-select text-sm py-1 w-full" style="width: 100%;">
                            <option selected value="">Pilih Sparepart</option>
                            @foreach (App\Models\Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->get() as $item)
                                <option value="{{ $item->id }}">{{ $item->product_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="showDetails" class="mt-3">
                        <label class="block text-sm font-medium mb-1" for="sales_id">Sales
                            Sparepart</label>
                        <select id="sales_id" name="sales_id[]"
                            class="form-select text-sm py-1 w-full">
                            <option selected value="1">Tidak ada Sales</option>
                            @foreach (App\Models\User::where('role', 'Sales')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1"
                            for="garansi">Garansi</label>
                        <select name="garansi[]" class="form-select text-sm py-1 w-full">
                            <option value="">Tidak Ada</option>
                            <option value="1">1 Hari</option>
                            <option value="2">2 Hari</option>
                            <option value="3">3 Hari</option>
                            <option value="4">4 Hari</option>
                            <option value="5">5 Hari</option>
                            <option value="6">6 Hari</option>
                            <option value="7">1 Minggu</option>
                            <option value="14">2 Minggu</option>
                            <option value="21">3 Minggu</option>
                            <option value="30">1 Bulan</option>
                            <option value="60">2 Bulan</option>
                            <option value="90">3 Bulan</option>
                            <option value="120">4 Bulan</option>
                            <option value="150">5 Bulan</option>
                            <option value="180">6 Bulan</option>
                            <option value="210">7 Bulan</option>
                            <option value="240">8 Bulan</option>
                            <option value="270">9 Bulan</option>
                            <option value="300">10 Bulan</option>
                            <option value="330">11 Bulan</option>
                            <option value="365">1 Tahun</option>
                            <option value="730">2 Tahun</option>
                            <option value="1095">3 Tahun</option>
                            <option value="1460">4 Tahun</option>
                            <option value="1825">5 Tahun</option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label class="block text-sm font-medium mb-1"
                            for="modal_sparepart">Modal Sparepart <span
                                class="text-rose-500">*</span></label>
                        <input class="form-input modal_sparepart w-full px-2 py-1" type="number" value="0"
                            name="modal_sparepart[]" required />
                    </div>
                    <div class="mt-3">
                        <label class="block text-sm font-medium mb-1" for="biaya_servis">Biaya
                            Servis <span class="text-rose-500">*</span></label>
                        <input class="form-input w-full px-2 py-1 biaya_servis" type="number"
                            name="biaya_servis[]" required />
                    </div>
                </div>`

                $(document).on('change', '.pilih-tindakan select', function() {
                var serviceActionId = $(this).val();
                const myEl = $(this)
                if (serviceActionId) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-action/' + serviceActionId,
                        dataType: 'json',
                        success: function(data) {
                            const curBiaya = $('#biaya').val() || 0;
                            const curModal = $('#total_modal_sparepart').val() || 0;
                            const prevModal = myEl.parent().parent().parent().find(
                                '[name="prev_modal"]:first');
                            const prevBiaya = myEl.parent().parent().parent().find(
                                '[name="prev_biaya"]:first');

                            $('#biaya').val((parseInt(curBiaya) - parseInt(prevBiaya.val()) + parseInt(data
                                .biaya)).toString());
                            $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal
                                    .val()) + parseInt(data
                                    .modal_sparepart))
                                .toString());
                            prevModal.val(data.modal_sparepart)
                            prevBiaya.val(data.biaya)
                            myEl.parent().parent().parent().find('[name="modal_sparepart[]"]:first').val(
                                data
                                .modal_sparepart)
                            myEl.parent().parent().parent().find('[name="biaya_servis[]"]:first').val(data
                                .biaya)
                        }
                    });
                } else {
                    $('#biaya').val('');
                    $('#modal_sparepart').val('');
                }
            });

            $(document).on('change', '.modal_sparepart', function(e) {
                const myEl = $(this);
                const curModal = $('#total_modal_sparepart').val() || 0;
                const prevModal = myEl.parent().parent().parent().find('[name="prev_modal"]:first');
                $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal.val()) + parseInt(myEl.val()))
                    .toString());
                prevModal.val(myEl.val())
            })

            $(document).on('change', '.biaya_servis', function(e) {
                const myEl = $(this);
                const curModal = $('#biaya').val() || 0;
                const prevModal = myEl.parent().parent().parent().find('[name="prev_biaya"]:first');
                $('#biaya').val((parseInt(curModal) - parseInt(prevModal.val()) + parseInt(myEl.val()))
                    .toString());
                prevModal.val(myEl.val())
            })

            $(document).on('change', '.selectAction2', function() {
                var productId = $(this).val();
                if (productId) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-sparepart/' + productId,
                        dataType: 'json',
                        success: function(data) {
                            const prev = $('#modal_sparepart').val() || 0;
                            $('#modal_sparepart').val((parseInt(prev) + parseInt(data.modal_sparepart))
                                .toString());
                        }
                    });
                } else {
                    $('#modal_sparepart').val('');
                }
            });

            function getRandomName() {
                return 'radio_' + Math.random().toString(36).substr(2, 9);
            }

            $('#tambah-servis').click(function() {
                const parent = $("<div></div>")
                $(pilihTindakanEl).appendTo(parent)
                const cloned = $(konfirSparepartEl)
                let newName = getRandomName();

                cloned.find('input[type="radio"]').each(function() {
                    $(this).attr('name', newName);
                });
                cloned.appendTo(parent)
                parent.appendTo('#servis-lain')
                $('.selectAction').select2();
                $('.selectAction2').select2();
            })
            });
        </script>
    {{-- @endpush --}}
</x-admin-layout>
