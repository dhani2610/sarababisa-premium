<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Transaksi Servis ✨</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            <!-- Print button -->
            <div class="relative inline-flex" x-data="{ modalOpen: false }">
                <button
                    class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500 hover:text-slate-600 mb-2 md:mb-0"
                    @click.prevent="modalOpen = true" aria-controls="tambah-modal"
                >
                    <span class="sr-only">Print</span><wbr>
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
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Atur Pencetakan Laporan</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Modal content -->
                        <form action="{{ route('cetak-laporan-proses') }}" method="get" target="__blank">
                            @csrf
                            <div class="px-5 py-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Mulai tanggal <span class="text-rose-500">*</span></label>
                                        <input id="start_date" name="start_date" class="form-input w-full py-2" type="date" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Sampai tanggal <span class="text-rose-500">*</span></label>
                                        <input id="end_date" name="end_date" class="form-input w-full py-2" type="date" required />
                                    </div>
                                </div>
                            </div>
                            <!-- Modal footer -->
                            <div class="px-5 py-4 border-t border-slate-200">
                                <div class="flex flex-wrap justify-end space-x-2">
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Cetak</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Search form -->
            <x-search-form placeholder="Pelanggan/Nomor Servis/Barang/IMEI" />

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
                                <form action="{{ route('transaksi-servis.store') }}" method="post">
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
                                            <select id="penerima" name="penerima" class="form-select text-sm py-1 w-full" required>
                                                @foreach ($penerima as $worker)
                                                    <option value="{{ $worker->name }}">{{ $worker->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- Modal footer -->
                                    <div class="pt-4 border-t border-slate-200 mt-4">
                                        <div class="flex flex-wrap justify-between space-x-2">
                                            <a href="{{ route('pelanggan.index') }}" class="btn-sm bg-green-500 hover:bg-green-600 text-white">
                                                Tambah Pelanggan Baru
                                            </a>
                                            <div>
                                                <a href="{{ route('transaksi-servis.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
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
                                <form action="{{ route('servis-langsung') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="tgl_selesai" value="<?php echo date('Y/m/d') ?>"/>
                                    <input type="hidden" name="tgl_ambil" value="<?php echo date('Y-m-d H:i:s') ?>"/>
                                    <input type="hidden" name="tgl_disetujui" value="<?php echo date('Y/m/d') ?>"/>
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
                                            <select id="penerima" name="penerima" class="form-select text-sm py-1 w-full" required>
                                                @foreach ($penerima as $worker)
                                                    <option value="{{ $worker->name }}">{{ $worker->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="users_id">Teknisi <span class="text-rose-500">*</span></label>
                                            <select id="users_id" name="users_id" class="form-select text-sm py-1 w-full">
                                                <option selected value="">Pilih Teknisi</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
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
                                                    <input type="checkbox" class="form-checkbox" x-on:click="showInputManual = true"/>
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
                                            <label class="block text-sm font-medium mb-1" for="diskon">Diskon</label>
                                            <input id="diskon" name="diskon" class="form-input w-full px-2 py-1" type="text" placeholder="Kosongkan jika tidak ada diskon"/>
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
                                            <a href="{{ route('pelanggan.index') }}" class="btn-sm bg-green-500 hover:bg-green-600 text-white">
                                                Tambah Pelanggan Baru
                                            </a>
                                            <div>
                                                <a href="{{ route('transaksi-servis.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
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
    <div class="sm:flex sm:justify-between sm:items-center mb-5">
    
        <!-- Left side -->
        <div class="mb-4 sm:mb-0">
            <ul class="flex flex-wrap -m-1">
                <li class="m-1">
                    <a href="{{ route('transaksi-servis.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-indigo-500 text-white duration-150 ease-in-out">Proses <span class="ml-1 text-indigo-200">{{ $processes_count }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-bisa-diambil.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Bisa Diambil <span class="ml-1 text-slate-400">{{ $jumlah_bisa_diambil }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-sudah-diambil.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Sudah Diambil <span class="ml-1 text-slate-400">{{ $jumlah_sudah_diambil }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-belum-disetujui.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Belum Disetujui <span class="ml-1 text-slate-400">{{ $jumlah_belum_disetujui }}</span></button>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Right side -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Filter button -->
            <div class="relative inline-flex" x-data="{ open: false }">
                <button
                    class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500 hover:text-slate-600"
                    aria-haspopup="true"
                    @click.prevent="open = !open"
                    :aria-expanded="open"
                >
                    <span class="sr-only">Filter</span><wbr>
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 16 16">
                        <path d="M9 15H7a1 1 0 010-2h2a1 1 0 010 2zM11 11H5a1 1 0 010-2h6a1 1 0 010 2zM13 7H3a1 1 0 010-2h10a1 1 0 010 2zM15 3H1a1 1 0 010-2h14a1 1 0 010 2z" />
                    </svg>
                </button>
                <div
                    class="origin-top-left z-10 absolute top-full min-w-56 bg-white border border-slate-200 pt-1.5 rounded shadow-lg overflow-hidden mt-1 left-4"                
                    @click.outside="open = false"
                    @keydown.escape.window="open = false"
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200 transform"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-out duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    x-cloak                
                >
                    <div class="text-xs font-semibold text-slate-400 uppercase pt-1.5 pb-2 px-4">Filter</div>
                    <ul class="mb-4">
                        @foreach ($types as $item)
                            <li class="py-1 px-3">
                                <label class="flex items-center">
                                    <input type="checkbox" class="form-checkbox" wire:model="type" value="{{ $item->id }}"/>
                                    <span class="text-sm font-medium ml-2">{{ $item->name }}</span>
                                </label>
                            </li>
                        @endforeach
                        <li class="py-1 px-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="form-checkbox" wire:model="status.0" value="Belum cek"/>
                                <span class="text-sm font-medium ml-2">Belum Cek</span>
                            </label>
                        </li>
                        <li class="py-1 px-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="form-checkbox" wire:model="status.1" value="Sedang Tes"/>
                                <span class="text-sm font-medium ml-2">Sedang Tes</span>
                            </label>
                        </li>
                        <li class="py-1 px-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="form-checkbox" wire:model="status.2" value="Menunggu Konfirmasi"/>
                                <span class="text-sm font-medium ml-2">Menunggu Konfirmasi</span>
                            </label>
                        </li>
                        <li class="py-1 px-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="form-checkbox" wire:model="status.3" value="Sedang Dikerjakan"/>
                                <span class="text-sm font-medium ml-2">Sedang Dikerjakan</span>
                            </label>
                        </li>
                        <li class="py-1 px-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="form-checkbox" wire:model="status.4" value="Menunggu Sparepart"/>
                                <span class="text-sm font-medium ml-2">Menunggu Sparepart</span>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mb-0">
                <select wire:model="paginate" id="" class="form-select">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="250">250</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                </select>
            </div>
        </div>
    
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                {{-- Left side --}}
                <h2 class="font-semibold text-slate-800">Proses <span class="text-slate-400 font-medium">{{ $processes_count }}</span></h2>
                {{-- Right side --}}
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span> item yang dipilih</div>
                            <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" @click="deleteSelected">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <!-- Table header -->
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox" @click="toggleAll" />
                                    </label>
                                </div>
                            </th>
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
                                <div class="font-semibold text-left">Penerima</div>
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
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="flex items-center">
                                        <label class="inline-flex">
                                            <span class="sr-only">Select</span>
                                            <input class="table-item form-checkbox" type="checkbox" value="{{ $process->id }}" @click="uncheckParent" />
                                        </label>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $i++ }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <a href="{{ route('transaksi-servis.edit', $process->id) }}">
                                        <div class="flex items-center text-blue-600">
                                            <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
                                                <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                            </svg>
                                            <div class="font-medium">{{ $process->nomor_servis }}</div>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div>{{ \Carbon\Carbon::parse($process->created_at)->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $process->penerima }}</div>
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
                                            $nomor = $process->customer->nomor_hp;
                                            $nomorwa = preg_replace('/^08/', 628, $nomor);
                                        @endphp
                                        <!-- Start -->
                                        <div
                                            class="relative"
                                            x-data="{ open: false }"
                                            @mouseenter="open = true"
                                            @mouseleave="open = false"
                                        >
                                            <a href="https://api.whatsapp.com/send?phone={{$nomorwa}}&text=" target="__blank">
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
                                            <a href="https://wa.me/{{ $nomorwa }}/?text=*Notifikasi%20Service*%0A{{ $toko->nama_toko }}%0A%0ANo.%20Service%20:%20{{ $process->nomor_servis }}%0ANama%20user%20:%20*{{ $process->nama_pelanggan }}*%0AUnit%20:%20{{ $process->nama_barang }}%0ADiterima%20:%20{{ $process->penerima }}%0ATanggal%20:%20{{ \Carbon\Carbon::parse($process->created_at)->translatedFormat('d F Y h:i') }}%0AKerusakan%20:%20{{ $process->kerusakan }}%0A%0ALink%20tracking%20:%20{{ $toko->link_toko }}/tracking%0ATerimakasih" target="__blank">
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
                                    <a href="{{ route('ubah-status-proses-edit', $process->id) }}">
                                        <div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 {{$status_color}}">{{ $process->status_servis }}</div>
                                    </a>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="space-x-1 flex">
                                        <!-- Start -->
                                        <div
                                            class="relative"
                                            x-data="{ open: false }"
                                            @mouseenter="open = true"
                                            @mouseleave="open = false"
                                        >
                                            <a href="{{ route('ubah-bisa-diambil-edit', $process->id) }}">
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
                                                    <div class="text-xs text-slate-200 whitespace-nowrap">Ubah status menjadi Bisa Diambil</div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End -->
                                        <!-- Start Printer -->
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
                                                                <a href="{{ route('kepalatoko-cetak-termal', $process->id) }}" target="__blank">
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
                                                                <a href="{{ route('kepalatoko-cetak-inkjet', $process->id) }}" target="__blank">
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
                                        <!-- End Printer-->
                                        <!-- Start Remove -->
                                        <div x-data="{ deleteOpen: false }">
                                            <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="deleteOpen = true" aria-controls="danger-modal">
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
                                            <!-- Modal backdrop -->
                                            <div
                                                class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                                                x-show="deleteOpen"
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
                                                x-show="deleteOpen"
                                                x-transition:enter="transition ease-in-out duration-200"
                                                x-transition:enter-start="opacity-0 translate-y-4"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                x-transition:leave="transition ease-in-out duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0"
                                                x-transition:leave-end="opacity-0 translate-y-4"
                                                x-cloak
                                            >
                                                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="deleteOpen = false" @keydown.escape.window="deleteOpen = false">
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
                                                                <div class="text-lg font-semibold text-slate-800">Apakah anda sudah yakin ?</div>
                                                            </div>
                                                            <!-- Modal content -->
                                                            <div class="text-sm mb-10">
                                                                <div class="space-y-2">
                                                                    <p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi.</p>
                                                                </div>
                                                            </div>
                                                            <!-- Modal footer -->
                                                            <div class="flex flex-wrap justify-end space-x-2">
                                                                <form action="{{ route('transaksi-servis.destroy', $process->id) }}" method="post">
                                                                    @method('delete')
                                                                    @csrf
                                                                    <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Remove -->
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
        </div>
    </div>

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
                        body: JSON.stringify({ selectedIds }),
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

    <!-- Pagination -->
    <div class="mt-8">
        {{ $processes->links() }}
    </div>
</div>
