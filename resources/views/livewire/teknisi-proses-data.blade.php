<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Transaksi Servis ✨</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            <!-- Search form -->
            <x-search-form placeholder="Pelanggan/Nomor Servis/Barang" />

            {{-- <form action="{{ route('teknisi-transaksi-servis.store') }}" method="post">
            </form> --}}

            <!-- Create invoice button -->
            <div x-data="{ modalOpen: false }">
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Transaksi Baru</span>
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
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Tambah Transaksi Baru</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Modal content -->
                        <div x-data="{ tab: '1' }" class="px-5 py-4">

                            <!-- Tabs buttons -->
                            <div class="flex flex-wrap items-center -m-3 mb-0">
                                <div class="m-3">
                                    <!-- Start -->
                                    <label class="flex items-center">
                                        <input type="radio" name="radio-buttons" class="form-radio" checked @click="tab = '1'"/>
                                        <span class="text-sm ml-2">Ditinggal</span>
                                    </label>
                                    <!-- End -->
                                </div>
                                <div class="m-3">
                                    <!-- Start -->
                                    <label class="flex items-center">
                                        <input type="radio" name="radio-buttons" class="form-radio" @click="tab = '2'"/>
                                        <span class="text-sm ml-2">Langsung</span>
                                    </label>
                                    <!-- End -->
                                </div>
                            </div>
                            <!-- Item 1 -->
                            <div x-show="tab === '1'">
                                <form action="{{ route('teknisi-transaksi-servis.store') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="status_servis" value="Belum cek">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="customers_id">Nama Pelanggan <span class="text-rose-500">*</span></label>
                                            <select name="customers_id" class="form-select text-sm py-1 w-full" id="selectjs1" required style="width: 100%">
                                                <option selected value="">Pilih Pelanggan</option>
                                                @foreach ($customers as $item)
                                                    <option value="{{ $item->id }}">{{ $item->nama }} {{ $item->nomor_hp }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="types_id">Jenis Barang <span class="text-rose-500">*</span></label>
                                            <select id="types_id" name="types_id" class="form-select text-sm py-1 w-full" required>
                                                @foreach ($types as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="brands_id">Merek <span class="text-rose-500">*</span></label>
                                            <select id="brands_id" name="brands_id" class="form-select text-sm py-1 w-full" required>
                                                <option selected="">Pilih Merek</option>
                                                @foreach ($brands as $brand)
                                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="model_series_id">Model Seri <span class="text-rose-500">*</span></label>
                                            <select id="model_series_id" name="model_series_id" class="form-select text-sm py-1 w-full selectjs2" required style="width: 100%">
                                                <option selected="">Pilih Model Seri</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="imei">Nomor Imei <span class="text-rose-500">*</span></label>
                                            <input id="imei" name="imei" class="form-input w-full px-2 py-1" type="text" required/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="warna">Warna <span class="text-rose-500">*</span></label>
                                            <input id="warna" name="warna" class="form-input w-full px-2 py-1" type="text" required/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="capacities_id">Kapasitas <span class="text-rose-500">*</span></label>
                                            <select id="capacities_id" name="capacities_id" class="form-select text-sm py-1 w-full" required>
                                                @foreach ($capacities as $capacity)
                                                    <option value="{{ $capacity->id }}">{{ $capacity->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="kelengkapan">Kelengkapan</label>
                                            <input id="kelengkapan" name="kelengkapan" class="form-input w-full px-2 py-1" type="text" placeholder="Kosongkan jika kelengkapannya hanya unit"/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="kerusakan">Kerusakan <span class="text-rose-500">*</span></label>
                                            <input id="kerusakan" name="kerusakan" class="form-input w-full px-2 py-1" type="text" required/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="qc_masuk">Pengecekan Fungsi <span class="text-rose-500">*</span></label>
                                            <input id="qc_masuk" name="qc_masuk" 
                                            class="form-input w-full px-2 py-1" 
                                            type="text" required
                                            placeholder="Contoh: Tombol, Kamera, Speaker, dll"
                                            />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="estimasi_pengerjaan">Estimasi Pengerjaan</label>
                                            <select id="estimasi_pengerjaan" name="estimasi_pengerjaan" class="form-select text-sm py-2 w-full">
                                                <option selected value="">Pilih Estimasi Pengerjaan</option>
                                                <option value="1 Hari">1 Hari</option>
                                                <option value="2 Hari">2 Hari</option>
                                                <option value="3 Hari">3 Hari</option>
                                                <option value="4 Hari">4 Hari</option>
                                                <option value="5 Hari">5 Hari</option>
                                                <option value="6 Hari">6 Hari</option>
                                                <option value="1 Minggu">1 Minggu</option>
                                                <option value="2 Minggu">2 Minggu</option>
                                                <option value="3 Minggu">3 Minggu</option>
                                                <option value="1 Bulan">1 Bulan</option>
                                                <option value="2 Bulan">2 Bulan</option>
                                                <option value="3 Bulan">3 Bulan</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="estimasi_biaya">Estimasi Biaya Servis</label>
                                            <div class="relative">
                                                <input id="estimasi_biaya" name="estimasi_biaya" class="form-input w-full pl-10 px-2 py-1" type="number" placeholder="Kosongkan jika tidak ada"/>
                                                <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                    <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="uang_muka">DP/Uang Muka</label>
                                            <div class="relative">
                                                <input id="uang_muka" name="uang_muka" class="form-input w-full pl-10 px-2 py-1" type="number" placeholder="Kosongkan jika tidak ada"/>
                                                <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                    <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="penerima">Penerima</label>
                                            <input type="text" id="penerima" name="penerima" class="form-input w-full px-2 py-1 disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" value="{{ optional(Auth::user()->worker)->name }}" disabled>
                                        </div>
                                    </div>
                                    <!-- Modal footer -->
                                    <div class="pt-4 border-t border-slate-200 mt-4">
                                        <div class="flex flex-wrap justify-between space-x-2">
                                            <a href="{{ route('teknisi-pelanggan.index') }}" class="btn-sm bg-green-500 hover:bg-green-600 text-white">
                                                Tambah Pelanggan Baru
                                            </a>
                                            <div>
                                                <a href="{{ route('teknisi-transaksi-servis.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                                    Batal
                                                </a>
                                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Item 2 -->
                            <div x-show="tab === '2'">
                                <form action="{{ route('teknisi-servis-langsung') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="tgl_selesai" value="<?php echo date('Y/m/d') ?>"/>
                                    <input type="hidden" name="tgl_ambil" value="<?php echo date('Y-m-d H:i:s') ?>"/>
                                    <input type="hidden" name="tgl_disetujui" value="<?php echo date('Y/m/d') ?>"/>
                                    <input type="hidden" id="users_id" name="users_id" value="{{ Auth::user()->id }}">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="customers_id">Nama Pelanggan <span class="text-rose-500">*</span></label>
                                            <select name="customers_id" class="form-select text-sm py-1 w-full" id="selectjs3" required style="width: 100%">
                                                <option selected value="">Pilih Pelanggan</option>
                                                @foreach ($customers as $item)
                                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="types_id">Jenis Barang <span class="text-rose-500">*</span></label>
                                            <select id="types_id" name="types_id" class="form-select text-sm py-1 w-full" required>
                                                @foreach ($types as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="brands_id">Merek <span class="text-rose-500">*</span></label>
                                            <select id="merek" name="brands_id" class="form-select text-sm py-1 w-full" required>
                                                <option selected="">Pilih Merek</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="model_series_id">Model Seri <span class="text-rose-500">*</span></label>
                                            <select id="model" name="model_series_id" class="form-select text-sm py-1 w-full selectjs4" required style="width: 100%">
                                                <option selected="">Pilih Model Seri</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="imei">Nomor Imei <span class="text-rose-500">*</span></label>
                                            <input id="imei" name="imei" class="form-input w-full px-2 py-1" type="text" required/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="warna">Warna <span class="text-rose-500">*</span></label>
                                            <input id="warna" name="warna" class="form-input w-full px-2 py-1" type="text" required/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="capacities_id">Kapasitas <span class="text-rose-500">*</span></label>
                                            <select id="capacities_id" name="capacities_id" class="form-select text-sm py-1 w-full" required>
                                                @foreach ($capacities as $capacity)
                                                    <option value="{{ $capacity->id }}">{{ $capacity->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="kelengkapan">Kelengkapan</label>
                                            <input id="kelengkapan" name="kelengkapan" class="form-input w-full px-2 py-1" type="text" placeholder="Kosongkan jika kelengkapannya hanya unit"/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="kerusakan">Kerusakan <span class="text-rose-500">*</span></label>
                                            <input id="kerusakan" name="kerusakan" class="form-input w-full px-2 py-1" type="text" required/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="qc_masuk">Pengecekan Fungsi Masuk<span class="text-rose-500">*</span></label>
                                            <input id="qc_masuk" name="qc_masuk" class="form-input w-full px-2 py-1" type="text" required placeholder="Contoh: Tombol, Kamera, Speaker, dll"/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="penerima">Penerima</label>
                                            <input type="text" id="penerima" name="penerima" class="form-input w-full px-2 py-1 disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" value="{{ optional(Auth::user()->worker)->name }}" disabled>
                                        </div>
                                        <div x-data="{ showInputManual: false }">
                                            <div class="flex justify-between items-center mb-1">
                                                <label class="block text-sm font-medium">
                                                    Tindakan Servis
                                                    <span class="text-rose-500">*</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="checkbox" class="form-checkbox" x-on:click="showInputManual = true"/>
                                                    <span class="text-sm ml-2">Isi Manual</span>
                                                </label>
                                            </div>
                                            <div class="pilih-tindakan">
                                                <select id="selectjs5" name="service_actions_id[]"
                                                    class="form-select text-sm py-1 w-full" style="width: 100%;">
                                                    <option selected value="">Pilih Tindakan</option>
                                                    @foreach ($actions as $action)
                                                        <option value="{{ $action->id }}">
                                                            {{ $action->nama_tindakan }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div x-show="showInputManual" class="mt-2">
                                                <input class="form-input w-full px-2 py-1" type="text" name="tindakan_servis[]"/>
                                            </div>
                                            <input type="hidden" name="prev_modal" value="0">
                                            <input type="hidden" name="prev_biaya" value="0">
                                        </div>
                                        <div x-data="{ showDetails: false }">
                                            <label class="block text-sm font-medium mb-1" for="modal_sparepart">Apakah menggunakan stok sparepart toko?</label>
                                            <div class="flex flex-wrap items-center -m-3">
                                                <div class="m-3">
                                                    <!-- Start -->
                                                    <label class="flex items-center">
                                                        <input type="radio" name="radio-buttons" class="form-radio" checked x-on:click="showDetails = false"/>
                                                        <span class="text-sm ml-2">Tidak</span>
                                                    </label>
                                                    <!-- End -->
                                                </div>
                                                <div class="m-3">
                                                    <!-- Start -->
                                                    <label class="flex items-center">
                                                        <input type="radio" name="radio-buttons" class="form-radio" x-on:click="showDetails = true"/>
                                                        <span class="text-sm ml-2">Ya</span>
                                                    </label>
                                                    <!-- End -->
                                                </div>
                                            </div>
                                            <div x-show="showDetails" class="mt-3">
                                                <label class="block text-sm font-medium mb-1" for="products_id">Sparepart Toko yg Digunakan</label>
                                                <select id="selectjs6" name="products_id[]" class="form-select text-sm py-1 w-full" style="width: 100%;">
                                                    <option selected value="">Pilih Sparepart</option>
                                                    @foreach ($products as $item)
                                                        <option value="{{ $item->id }}">{{ $item->product_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div x-show="showDetails" class="mt-3">
                                                <label class="block text-sm font-medium mb-1" for="sales_id">Sales Sparepart</label>
                                                <select id="sales_id" name="sales_id[]" class="form-select text-sm py-1 w-full">
                                                    <option selected value="1">Tidak ada Sales</option>
                                                    @foreach ($sales as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
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
                                                <input class="form-input w-full px-2 py-1 modal_sparepart"
                                                    type="number" name="modal_sparepart[]" required />
                                            </div>

                                            <div class="mt-3">
                                                <label class="block text-sm font-medium mb-1" for="biaya_servis">Biaya
                                                    Servis <span class="text-rose-500">*</span></label>
                                                <input class="form-input w-full px-2 py-1 biaya_servis" type="number"
                                                    name="biaya_servis[]" required />
                                            </div>
                                        </div>
                                        <div id="servis-lain"></div>
                                        {{-- Tombol tambah servis --}}
                                        <div>
                                            <button type="button" class="rounded-lg px-4 py-1 bg-blue-600 text-white"
                                                id="tambah-servis">+ tambah
                                                tindakan servis</button>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="qc_keluar">Pengecekan Fungsi Keluar <span class="text-rose-500">*</span></label>
                                            <input id="qc_keluar" name="qc_keluar" class="form-input w-full px-2 py-1" type="text" placeholder="Contoh: Tombol, Kamera, Speaker, dll" required/>
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
                                            <label class="block text-sm font-medium mb-1" for="biaya">Total Biaya
                                                Servis
                                                <span class="text-rose-500">*</span></label>
                                            <input class="form-input w-full px-2 py-1" type="number" name="biaya"
                                                id="biaya" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1"
                                                for="diskon">Diskon</label>
                                            <input id="diskon" name="diskon" class="form-input w-full px-2 py-1"
                                                type="text" placeholder="Kosongkan jika tidak ada diskon" />
                                        </div>
                                        <div x-data="{ caraPembayaran: 'Tunai' }">
                                            <label class="block text-sm font-medium mb-1" for="cara_pembayaran">Cara Pembayaran</label>
                                            <select id="cara_pembayaran" name="cara_pembayaran" class="form-select text-sm py-1 w-full" x-model="caraPembayaran">
                                                <option selected value="Tunai">Tunai</option>
                                                <option value="Transfer">Transfer</option>
                                                <option value="Kredit">Kredit</option>
                                                <option value="Tunai & Transfer">Tunai & Transfer</option>
                                            </select>

                                            <div x-show="caraPembayaran === 'Tunai & Transfer'" class="mt-3">
                                                <label class="block text-sm font-medium text-indigo-500">Silahkan isi hanya pada salah satu input saja: Tunai / Transfer</label>
                                                <div class="flex flex-row gap-3">
                                                    <div class="w-1/2 mb-3 md:mb-0">
                                                        <label class="block text-sm font-medium mb-1" for="tunai">Tunai</label>
                                                        <input class="form-input w-full py-1" type="number" name="tunai" id="tunai" value="0"/>
                                                    </div>
                                                    <div class="w-1/2 mb-3 md:mb-0">
                                                        <label class="block text-sm font-medium mb-1" for="transfer">Transfer</label>
                                                        <input class="form-input w-full py-1" type="number" name="transfer" id="transfer" value="0"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div x-show="caraPembayaran === 'Kredit'" class="mt-3">
                                                <div>
                                                    <label class="block text-sm font-medium mb-1" for="pay">Jumlah Pembayaran <span class="text-rose-500">*</span></label>
                                                    <input id="pay" name="pay" class="form-input w-full px-2 py-1" type="number"/>
                                                </div>
                                                <div class="flex flex-wrap items-center -m-3 mt-0">
                                                    <div class="m-3">
                                                        <!-- Start -->
                                                        <label class="flex items-center">
                                                            <input name="tunai" type="checkbox" class="form-checkbox"/>
                                                            <span class="text-sm ml-2">Tunai</span>
                                                        </label>
                                                        <!-- End -->
                                                    </div>

                                                    <div class="m-3">
                                                        <!-- Start -->
                                                        <label class="flex items-center">
                                                            <input name="transfer" type="checkbox" class="form-checkbox" />
                                                            <span class="text-sm ml-2">Transfer</span>
                                                        </label>
                                                        <!-- End -->
                                                    </div>
                                                </div>
                                                <label class="block text-sm font-medium mt-3" for="tempo">Waktu Tempo <span class="text-rose-500">*</span></label>
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
                                        
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="catatan">Catatan</label>
                                            <textarea id="catatan" name="catatan" class="form-textarea w-full px-2 py-1" rows="2"></textarea>
                                        </div>
                                    </div>
                                    <!-- Modal footer -->
                                    <div class="pt-4 border-t border-slate-200 mt-4">
                                        <div class="flex flex-wrap justify-between space-x-2">
                                            <a href="{{ route('teknisi-pelanggan.index') }}" class="btn-sm bg-green-500 hover:bg-green-600 text-white">
                                                Tambah Pelanggan Baru
                                            </a>
                                            <div>
                                                <a href="{{ route('teknisi-transaksi-servis.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                                    Batal
                                                </a>
                                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            
        </div>

    </div>

    <!-- More actions -->
    <div class="mb-5">
        <select wire:model="paginate" id="" class="form-select">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <header class="px-5 py-4">
            <h2 class="font-semibold text-slate-800">Proses <span class="text-slate-400 font-medium">{{ $processes_count }}</span></h2>
        </header>
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <!-- Table header -->
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">No.</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Nomor Servis</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Tgl Terima</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Pelanggan</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Hubungi</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Nama Barang</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Kelengkapan</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Kerusakan</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Fungsi</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">DP</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Est. Biaya</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Est. Pengerjaan</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Status</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Aksi</div>
                        </th>
                    </tr>
                </thead>
                <!-- Table body -->
                <tbody class="text-sm divide-y divide-slate-200">
                    <!-- Row -->
                    @php
                        $i = 1
                    @endphp
                    @foreach($processes as $process)
                        @php                    
                            if ($process->status_servis === 'Sedang Dikerjakan') :
                                $status_color = 'bg-emerald-100 text-emerald-600';
                                $total_color = 'text-emerald-500';
                            elseif ($process->status_servis === 'Menunggu Sparepart') :
                                $status_color = 'bg-amber-100 text-amber-600';
                                $total_color = 'text-amber-500';
                            elseif ($process->status_servis === 'Menunggu Konfirmasi') :
                                $status_color = 'bg-rose-100 text-rose-500';
                                $total_color = 'text-rose-500';
                            elseif ($process->status_servis === 'Sedang Tes') :
                                $status_color = 'bg-blue-100 text-blue-600';
                                $total_color = 'text-blue-500';
                            else :
                                $status_color = 'bg-slate-100 text-slate-500';
                                $total_color = 'text-slate-500';
                            endif;
                        @endphp                  
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ $i++ }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="flex items-center text-blue-600">
                                    <div class="font-medium">{{ $process->nomor_servis }}</div>
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div>{{ \Carbon\Carbon::parse($process->created_at)->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if ($process->customer)
                                    @if ($process->customer->exists())
                                        <div class="font-medium">{{ $process->customer->nama }}</div>
                                    @else
                                        <div class="font-medium text-rose-600">Data pelanggan telah dihapus</div>
                                    @endif
                                @else
                                    <div class="font-medium text-rose-600">Data pelanggan telah dihapus</div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="flex space-x-1">
                                    @php
                                    if (optional($process->customer)->nomor_hp == null) {
                                        $nomor = '-';
                                    }else{
                                        $nomor = $process->customer->nomor_hp;
                                        $nomorwa = preg_replace('/^08/', 628, $nomor);
                                    }
                                    @endphp
                                    <!-- Start -->
                                    <div
                                        class="relative"
                                        x-data="{ open: false }"
                                        @mouseenter="open = true"
                                        @mouseleave="open = false"
                                    >
                                        <a href="https://api.whatsapp.com/send?phone={{$nomorwa}}&text="  hidetargetblank>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                                <path d="M9 10a0.5 .5 0 0 0 1 0v-1a0.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a0.5 .5 0 0 0 0 -1h-1a0.5 .5 0 0 0 0 1" />
                                            </svg>
                                        </a>
                                        <div class="z-10 absolute bottom-full left-1/2 -translate-x-1/2">
                                            <div
                                                class="bg-slate-800 p-2 rounded overflow-hidden mb-2"
                                                x-show="open"
                                                x-transition:enter="transition ease-out duration-200 transform"
                                                x-transition:enter-start="opacity-0 translate-y-2"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                x-transition:leave="transition ease-out duration-200"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0"
                                                x-cloak
                                            >
                                                <div class="text-xs text-slate-200 whitespace-nowrap">Kirim pesan melalui Whatsapp</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End -->
                                    
                                    <!-- Start -->
                                    <div
                                        class="relative"
                                        x-data="{ open: false }"
                                        @mouseenter="open = true"
                                        @mouseleave="open = false"
                                    >
                                        <a href="https://wa.me/{{ $nomorwa }}/?text=*Notifikasi%20Service*%0A{{ $toko->nama_toko }}%0A%0ANo.%20Service%20:%20{{ $process->nomor_servis }}%0ANama%20user%20:%20*{{ $process->nama_pelanggan }}*%0AUnit%20:%20{{ $process->nama_barang }}%0ADiterima%20:%20{{ $process->penerima }}%0ATanggal%20:%20{{ \Carbon\Carbon::parse($process->created_at)->translatedFormat('d F Y h:i') }}%0AKerusakan%20:%20{{ $process->kerusakan }}%0A%0ALink%20tracking%20:%20{{ env('APP_URL') }}/tracking%0ATerimakasih"  hidetargetblank>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-invoice" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                            <line x1="9" y1="7" x2="10" y2="7" />
                                            <line x1="9" y1="13" x2="15" y2="13" />
                                            <line x1="13" y1="17" x2="15" y2="17" />
                                            </svg>
                                        </a>
                                        <div class="z-10 absolute bottom-full left-1/2 -translate-x-1/2">
                                            <div
                                                class="min-w-56 bg-slate-800 p-2 rounded overflow-hidden mb-2"
                                                x-show="open"
                                                x-transition:enter="transition ease-out duration-200 transform"
                                                x-transition:enter-start="opacity-0 translate-y-2"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                x-transition:leave="transition ease-out duration-200"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0"
                                                x-cloak
                                            >
                                                <div class="text-xs text-slate-200">Kirim tanda terima servis dan link tracking.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End -->
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ $process->nama_barang }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">
                                    @if ($process->kelengkapan != null)
                                        {{ $process->kelengkapan }}
                                    @else
                                        Hanya Barang
                                    @endif
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium capitalize">{{ $process->kerusakan }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium capitalize">{{ $process->qc_masuk }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">Rp. {{ number_format($process->uang_muka) }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">Rp. {{ number_format($process->estimasi_biaya) }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ $process->estimasi_pengerjaan }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <a href="{{ route('teknisi-ubah-status-proses-edit', $process->id) }}">
                                    <div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 {{$status_color}}">{{ $process->status_servis }}</div>
                                </a>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="space-x-1 flex">
                                    <!-- Start -->
                                    <div
                                        class="relative"
                                        x-data="{ open: false }"
                                        @mouseenter="open = true"
                                        @mouseleave="open = false"
                                    >
                                        @if ($process->status_servis == 'Belum cek')
                                            <a href="{{ route('teknisi-ubah-bisa-diambil-edit', $process->id) }}">
                                                <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                                    <span class="sr-only">Konfirmasi</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clipboard-check" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                                                        <rect x="9" y="3" width="6" height="4" rx="2" />
                                                        <path d="M9 14l2 2l4 -4" />
                                                    </svg>
                                                </button>
                                            </a>
                                            <div class="z-10 absolute right-full top-1/2 -translate-y-1/2">
                                                <div
                                                    class="bg-slate-800 p-2 rounded overflow-hidden mb-2"
                                                    x-show="open"
                                                    x-transition:enter="transition ease-out duration-200 transform"
                                                    x-transition:enter-start="opacity-0 translate-y-2"
                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                    x-transition:leave="transition ease-out duration-200"
                                                    x-transition:leave-start="opacity-100"
                                                    x-transition:leave-end="opacity-0"
                                                    x-cloak
                                                >
                                                    <div class="text-xs text-slate-200 whitespace-nowrap">Ubah status menjadi bisa diambil</div>
                                                </div>
                                        @endif
                                        </div>
                                    </div>
                                    <!-- End -->
                                    
                                    <!-- Start -->
                                    <div x-data="{ modalOpen: false }">
                                        <button
                                            @click.prevent="modalOpen = true"
                                            aria-controls="basic-modal"
                                        >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                            <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                            <rect x="7" y="13" width="10" height="8" rx="2" />
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
                                                            <a href="{{ route('teknisi-cetak-termal', $process->id) }}"  hidetargetblank>
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
                                                            <a href="{{ route('teknisi-cetak-tanda-terima', $process->id) }}"  hidetargetblank>
                                                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">
                                                                    <span class="mr-1">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                                        <rect x="7" y="13" width="10" height="8" rx="2" />
                                                                        </svg>
                                                                    </span>
                                                                    Printer Inkjet/Laser
                                                                </button>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>                                            
                                    </div>
                                    <!-- End -->
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
    <!-- Pagination -->
    <div class="mt-8">
        {{ $processes->links() }}
    </div>
</div>
