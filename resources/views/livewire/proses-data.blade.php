<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

<style>
    /* Agar modal filepond terlihat rapi */
    .filepond--root { font-family: sans-serif; }
    .filepond--panel-root { background-color: #f1f5f9; border: 1px solid #cbd5e1; }
    .filepond--drop-label { color: #64748b; }
</style>
<style>
    .select2-container .select2-selection--single {
        height: 38px;
        border-color: #e2e8f0;
        display: flex;
        align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    .technician-group {
        border: 2px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 1rem;
        margin-bottom: 1.5rem;
        background-color: #f8fafc;
    }
    .action-item {
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 0.375rem;
        padding: 0.75rem;
        margin-bottom: 0.75rem;
        position: relative;
    }
    .position-button-x{
        position: absolute;
        right: -3%;
        top: -16px;
        font-size: 23px!important;
        background: red!important;
        color: white!important;
        padding: 0px 8px 0px 8px!important;
        border-radius: 50%!important;
    }
</style>
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
            @if (Auth::user()->role != 'Investor')
            <div class="relative inline-flex" x-data="{ modalOpen: false }">
                <button
                    class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500 hover:text-slate-600 mb-2 md:mb-0"
                    @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                    <span class="sr-only">Print</span><wbr>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20"
                        height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <rect x="7" y="13" width="10" height="8" rx="2" />
                    </svg>
                </button>
                <!-- Modal backdrop -->
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true"
                    x-cloak></div>
                <!-- Modal dialog -->
                <div id="tambah-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog" aria-modal="true" x-show="modalOpen"
                    x-transition:enter="transition ease-in-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                        @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Atur Pencetakan Laporan</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path
                                            d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Modal content -->
                        <form action="{{ route('cetak-laporan-proses') }}" method="get"  target="_blank">
                            @csrf
                            <div class="px-5 py-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Mulai tanggal <span
                                                class="text-rose-500">*</span></label>
                                        <input id="start_date" name="start_date" class="form-input w-full py-2"
                                            type="date" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Sampai tanggal <span
                                                class="text-rose-500">*</span></label>
                                        <input id="end_date" name="end_date" class="form-input w-full py-2"
                                            type="date" required />
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
            @endif


            <!-- Search form -->
            {{-- <x-search-form placeholder="Pelanggan/Nomor Servis/Barang/IMEI" /> --}}

            @if (Auth::user()->role != 'Investor')
            <!-- Create invoice button -->
            <div x-data="{ modalOpen: false }">
                @if (allowTransaksiCabang() == 1)
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true"
                    aria-controls="tambah-modal">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path
                            d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Transaksi Baru</span>
                </button>
                @endif
                <!-- Modal backdrop -->
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true"
                    x-cloak></div>
                <!-- Modal dialog -->
                <div id="tambah-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog" aria-modal="true" x-show="modalOpen"
                    x-transition:enter="transition ease-in-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Tambah Transaksi Baru</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path
                                            d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
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
                                        <input type="radio" name="radio-buttons" class="form-radio" checked
                                            @click="tab = '1'" />
                                        <span class="text-sm ml-2">Ditinggal</span>
                                    </label>
                                    <!-- End -->
                                </div>
                                <div class="m-3">
                                    <!-- Start -->
                                    <label class="flex items-center">
                                        <input type="radio" name="radio-buttons" class="form-radio"
                                            @click="tab = '2'" />
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
                                            <label class="block text-sm font-medium mb-1">
                                                Nama Pelanggan <span class="text-rose-500">*</span>
                                            </label>

                                            <select name="customers_id" id="customers_select"
                                                class="form-select text-sm  selectjs2 py-1 w-full" required>
                                                <option value="">Pilih Pelanggan</option>
                                                @foreach ($customers as $item)
                                                    <option value="{{ $item->id }}">{{ $item->nama }} {{ $item->nomor_hp }}</option>
                                                @endforeach
                                            </select>

                                            <input type="text" name="customers_manual"
                                                id="customers_manual"
                                                class="form-input text-sm py-1 w-full mt-2 hidden"
                                                placeholder="Isi nama pelanggan manual">

                                            <label class="inline-flex items-center mt-1 text-sm">
                                                <input type="checkbox" class="form-checkbox"
                                                    onchange="toggleManual(this, 'customers_select', 'customers_manual')">
                                                <span class="ml-2">Isi manual <small class="text-rose-500">*data akan auto masuk ke master data</small></span>
                                            </label>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-1">
                                                Jenis Barang <span class="text-rose-500">*</span>
                                            </label>

                                            <select id="types_id" name="types_id"
                                                class="form-select selectjs2 text-sm py-1 w-full" required>
                                                @foreach ($types as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>

                                            <input type="text" name="types_manual"
                                                id="types_manual"
                                                class="form-input text-sm py-1 w-full mt-2 hidden"
                                                placeholder="Isi jenis barang manual">

                                            <label class="inline-flex items-center mt-1 text-sm">
                                                <input type="checkbox" class="form-checkbox"
                                                    onchange="toggleManual(this, 'types_id', 'types_manual')">
                                                <span class="ml-2">Isi manual <small class="text-rose-500">*data akan auto masuk ke master data</small></span>
                                            </label>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-1">
                                                Merek <span class="text-rose-500">*</span>
                                            </label>

                                            <select id="brands_id" name="brands_id"
                                                class="form-select selectjs2 text-sm py-1 w-full" required>
                                                <option value="">Pilih Merek</option>
                                                @foreach ($brands as $brand)
                                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>

                                            <input type="text" name="brands_manual"
                                                id="brands_manual"
                                                class="form-input text-sm py-1 w-full mt-2 hidden"
                                                placeholder="Isi merek manual">

                                            <label class="inline-flex items-center mt-1 text-sm">
                                                <input type="checkbox" class="form-checkbox"
                                                    onchange="toggleManual(this, 'brands_id', 'brands_manual')">
                                                <span class="ml-2">Isi manual <small class="text-rose-500">*data akan auto masuk ke master data</small></span>
                                            </label>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-1">
                                                Model Seri <span class="text-rose-500">*</span>
                                            </label>

                                            <select id="model_series_id" name="model_series_id"
                                                class="form-select selectjs2 text-sm py-1 w-full" required>
                                                <option value="">Pilih Model Seri</option>
                                            </select>

                                            <input type="text" name="model_series_manual"
                                                id="model_series_manual"
                                                class="form-input text-sm py-1 w-full mt-2 hidden"
                                                placeholder="Isi model seri manual">

                                            <label class="inline-flex items-center mt-1 text-sm">
                                                <input type="checkbox" class="form-checkbox"
                                                    onchange="toggleManual(this, 'model_series_id', 'model_series_manual')">
                                                <span class="ml-2">Isi manual <small class="text-rose-500">*data akan auto masuk ke master data</small></span>
                                            </label>
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
                                            <label class="block text-sm font-medium mb-1"
                                                for="capacities_id">Kapasitas <span
                                                    class="text-rose-500">*</span></label>
                                            <select id="capacities_id" name="capacities_id"
                                                class="form-select text-sm py-1 w-full" required>
                                                @foreach ($capacities as $capacity)
                                                    <option value="{{ $capacity->id }}">{{ $capacity->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1"
                                                for="kelengkapan">Kelengkapan</label>
                                            <input id="kelengkapan" name="kelengkapan"
                                                class="form-input w-full px-2 py-1" type="text"
                                                placeholder="Kosongkan jika kelengkapannya hanya unit" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="kerusakan">Kerusakan
                                                <span class="text-rose-500">*</span></label>
                                            <input id="kerusakan" name="kerusakan"
                                                class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        {{-- <div>
                                            <label class="block text-sm font-medium mb-1" for="qc_masuk">Pengecekan
                                                Fungsi <span class="text-rose-500">*</span></label>
                                            <input id="qc_masuk" name="qc_masuk" class="form-input w-full px-2 py-1"
                                                type="text" required
                                                placeholder="Contoh: Tombol, Kamera, Speaker, dll" />
                                        </div> --}}
                                        <div>
                                            <label class="block text-sm font-medium mb-1">List Pengecekan Fungsi (Masuk) <span class="text-rose-500">*</span></label>
                                            <small class="text-rose-500">*Jika ingin cepat silahkan isi kolom other.</small>
                                            <div class="overflow-x-auto border rounded-sm">
                                                <table class="w-full text-xs text-left border-collapse" id="table-qc-tab1">
                                                    <thead class="bg-slate-100 uppercase text-slate-500 font-semibold">
                                                        <tr>
                                                            <th class="border border-slate-300 p-2 w-8 text-center">No</th>
                                                            <th class="border border-slate-300 p-2 w-1/2">ITEM</th>
                                                            <th class="border border-slate-300 p-2 bg-blue-50 text-center">REMARK IN</th>
                                                            <th class="border border-slate-300 p-2 w-8 text-center"></th> </tr>
                                                    </thead>
                                                    <tbody id="checklist-tbody-tab1" class="text-slate-700">
                                                        </tbody>
                                                </table>
                                            </div>
                                            <button type="button" onclick="addCustomRowTab1()" class="mt-2 text-xs flex items-center text-indigo-600 font-bold hover:text-indigo-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                                Tambah Baris Custom
                                            </button>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1"
                                                for="estimasi_pengerjaan">Estimasi Pengerjaan</label>
                                            <select id="estimasi_pengerjaan" name="estimasi_pengerjaan"
                                                class="form-select text-sm py-2 w-full">
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
                                            <label class="block text-sm font-medium mb-1"
                                                for="estimasi_biaya">Estimasi Biaya Servis</label>
                                            <div class="relative">
                                                <input id="estimasi_biaya" name="estimasi_biaya"
                                                    class="form-input w-full pl-10 px-2 py-1 input-currency" type="text"
                                                    placeholder="Kosongkan jika tidak ada" />
                                                <div
                                                    class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                    <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="uang_muka">DP/Uang
                                                Muka</label>
                                            <div class="relative">
                                                <input id="uang_muka" name="uang_muka"
                                                    class="form-input w-full pl-10 px-2 py-1 input-currency" type="text"
                                                    placeholder="Kosongkan jika tidak ada" />
                                                <div
                                                    class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                    <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1"
                                                for="penerima">Penerima</label>
                                            <select id="penerima" name="penerima"
                                                class="form-select text-sm py-1 w-full" required>
                                                @foreach ($penerima as $worker)
                                                    <option value="{{ $worker->name }}">{{ $worker->name }}</option>
                                                @endforeach
                                            </select>
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

                            <!-- Item 2 -->
                            <div x-show="tab === '2'">
                                <form action="{{ route('servis-langsung') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="tgl_selesai" value="<?php echo date('Y/m/d'); ?>" />
                                    <input type="hidden" name="tgl_ambil" value="<?php echo date('Y-m-d H:i:s'); ?>" />
                                    <input type="hidden" name="tgl_disetujui" value="<?php echo date('Y/m/d'); ?>" />
                                    <div class="space-y-3">
                                        <!-- NAMA PELANGGAN -->
                                        <div class="manual-wrapper">
                                            <label class="block text-sm font-medium mb-1">
                                                Nama Pelanggan <span class="text-rose-500">*</span>
                                            </label>

                                            <select name="customers_id"
                                                class="form-select text-sm py-1 w-full selectjs3 manual-select" required>
                                                <option value="">Pilih Pelanggan</option>
                                                @foreach ($customers as $item)
                                                    <option value="{{ $item->id }}">{{ $item->nama }}</option>
                                                @endforeach
                                            </select>

                                            <input type="text"
                                                name="customers_manual"
                                                class="form-input text-sm py-1 w-full mt-2 hidden manual-input"
                                                placeholder="Isi nama pelanggan manual">

                                            <label class="inline-flex items-center mt-1 text-sm">
                                                <input type="checkbox" class="form-checkbox manual-toggle">
                                                <span class="ml-2">Isi manual <small class="text-rose-500">*data akan auto masuk ke master data</small></span>
                                            </label>
                                        </div>

                                        <!-- JENIS BARANG -->
                                        <div class="manual-wrapper mt-3">
                                            <label class="block text-sm font-medium mb-1">
                                                Jenis Barang <span class="text-rose-500">*</span>
                                            </label>

                                            <select name="types_id"
                                                class="form-select text-sm py-1 w-full manual-select" required>
                                                @foreach ($types as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>

                                            <input type="text"
                                                name="types_manual"
                                                class="form-input text-sm py-1 w-full mt-2 hidden manual-input"
                                                placeholder="Isi jenis barang manual">

                                            <label class="inline-flex items-center mt-1 text-sm">
                                                <input type="checkbox" class="form-checkbox manual-toggle">
                                                <span class="ml-2">Isi manual <small class="text-rose-500">*data akan auto masuk ke master data</small></span>
                                            </label>
                                        </div>

                                        <!-- MEREK -->
                                        <div class="manual-wrapper mt-3">
                                            <label class="block text-sm font-medium mb-1">
                                                Merek <span class="text-rose-500">*</span>
                                            </label>

                                            <select name="brands_id"
                                                class="form-select text-sm py-1 w-full manual-select" required>
                                                <option value="">Pilih Merek</option>
                                                @foreach ($brands as $brand)
                                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>

                                            <input type="text"
                                                name="brands_manual"
                                                class="form-input text-sm py-1 w-full mt-2 hidden manual-input"
                                                placeholder="Isi merek manual">

                                            <label class="inline-flex items-center mt-1 text-sm">
                                                <input type="checkbox" class="form-checkbox manual-toggle">
                                                <span class="ml-2">Isi manual <small class="text-rose-500">*data akan auto masuk ke master data</small></span>
                                            </label>
                                        </div>

                                        <!-- MODEL SERI -->
                                        <div class="manual-wrapper mt-3">
                                            <label class="block text-sm font-medium mb-1">
                                                Model Seri <span class="text-rose-500">*</span>
                                            </label>

                                            <select name="model_series_id"
                                                class="form-select text-sm py-1 w-full selectjs4 manual-select" required>
                                                <option value="">Pilih Model Seri</option>
                                            </select>

                                            <input type="text"
                                                name="model_series_manual"
                                                class="form-input text-sm py-1 w-full mt-2 hidden manual-input"
                                                placeholder="Isi model seri manual">

                                            <label class="inline-flex items-center mt-1 text-sm">
                                                <input type="checkbox" class="form-checkbox manual-toggle">
                                                <span class="ml-2">Isi manual <small class="text-rose-500">*data akan auto masuk ke master data</small></span>
                                            </label>
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
                                            <label class="block text-sm font-medium mb-1"
                                                for="capacities_id">Kapasitas <span
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
                                            <input id="kelengkapan" name="kelengkapan"
                                                class="form-input w-full px-2 py-1" type="text"
                                                placeholder="Kosongkan jika kelengkapannya hanya unit" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="kerusakan">Kerusakan
                                                <span class="text-rose-500">*</span></label>
                                            <input id="kerusakan" name="kerusakan"
                                                class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        {{-- <div>
                                            <label class="block text-sm font-medium mb-1" for="qc_masuk">Pengecekan
                                                Fungsi Masuk<span class="text-rose-500">*</span></label>
                                            <input id="qc_masuk" name="qc_masuk" class="form-input w-full px-2 py-1"
                                                type="text" required
                                                placeholder="Contoh: Tombol, Kamera, Speaker, dll" />
                                        </div> --}}
                                        <div>
                                            <label class="block text-sm font-medium mb-1">List Pengecekan Fungsi (Masuk & Keluar) <span class="text-rose-500">*</span></label>
                                            <small class="text-rose-500">*Jika ingin cepat silahkan isi kolom other.</small>

                                            <div class="overflow-x-auto border rounded-sm">
                                                <table class="w-full text-xs text-left border-collapse" id="table-qc-tab2">
                                                    <thead class="bg-slate-100 uppercase text-slate-500 font-semibold">
                                                        <tr>
                                                            <th class="border border-slate-300 p-2 w-8 text-center">No</th>
                                                            <th class="border border-slate-300 p-2 w-1/3">ITEM</th>
                                                            <th class="border border-slate-300 p-2 bg-blue-50 text-center">REMARK IN</th>
                                                            <th class="border border-slate-300 p-2 bg-blue-50 text-center">REMARK OUT</th>
                                                            <th class="border border-slate-300 p-2 w-8 text-center"></th> </tr>
                                                    </thead>
                                                    <tbody id="checklist-tbody-tab2" class="text-slate-700">
                                                        </tbody>
                                                </table>
                                            </div>
                                            <button type="button" onclick="addCustomRowTab2()" class="mt-2 text-xs flex items-center text-indigo-600 font-bold hover:text-indigo-800">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                                Tambah Baris Custom
                                            </button>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1"
                                                for="penerima">Penerima</label>
                                            <select id="penerima" name="penerima"
                                                class="form-select text-sm py-1 w-full" required>
                                                @foreach ($penerima as $worker)
                                                    <option value="{{ $worker->name }}">{{ $worker->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div x-data="{ showDetails: true }">
                                            <div class="space-y-3" x-data="{ showDetails: true }">

                                            <label class="block text-sm font-medium mb-1" for="kondisi_servis">Kondisi Servis <span class="text-rose-500">*</span></label>
                                            <div class="flex flex-wrap items-center -m-3">
                                                <div class="m-3">
                                                    <!-- Start -->
                                                    <label class="flex items-center">
                                                        <input type="radio" name="kondisi_servis" value="Sudah jadi" class="form-radio" checked x-on:click="showDetails = true"/>
                                                        <span class="text-sm ml-2">Sudah jadi</span>
                                                    </label>
                                                    <!-- End -->
                                                </div>
                                                <div class="m-3">
                                                    <!-- Start -->
                                                    <label class="flex items-center">
                                                        <input type="radio" name="kondisi_servis" value="Menunggu konfirmasi" class="form-radio" x-on:click="showDetails = true"/>
                                                        <span class="text-sm ml-2">Menunggu konfirmasi</span>
                                                    </label>
                                                    <!-- End -->
                                                </div>
                                                <div class="m-3">
                                                    <!-- Start -->
                                                    <label class="flex items-center">
                                                        <input type="radio" name="kondisi_servis" value="Dibatalkan" class="form-radio" x-on:click="showDetails = false"/>
                                                        <span class="text-sm ml-2">Dibatalkan</span>
                                                    </label>
                                                    <!-- End -->
                                                </div>
                                            </div>
                                            <div x-show="showDetails">
                                                <div id="main-container" >
                                                    {{-- Group Teknisi akan ditambahkan di sini via JS --}}
                                                </div>

                                                <div class="mb-4">
                                                    <button type="button" class="btn-sm bg-indigo-600 hover:bg-indigo-700 text-white w-full flex justify-center items-center" id="tambah-teknisi-baru">
                                                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                                                            <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                                                        </svg>
                                                        Tambah Teknisi Baru
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="bg-slate-100 p-3 rounded">
                                                <div class="mb-2">
                                                    <label class="block text-sm font-medium mb-1" for="total_modal_sparepart">Total Modal Sparepart <span class="text-rose-500">*</span></label>
                                                    <input class="form-input w-full px-2 py-1 bg-white" type="text" name="total_modal_sparepart" id="total_modal_sparepart" required />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-1" for="biaya">Total Biaya Servis (Ke Pelanggan) <span class="text-rose-500">*</span></label>
                                                    <input class="form-input w-full px-2 py-1 bg-white font-bold text-lg" type="text" name="biaya" id="biaya" required />
                                                </div>
                                            </div>


                                                    <div>
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
                                                            class="form-select text-sm py-1 w-full"
                                                            x-model="caraPembayaran">
                                                            <option selected value="Tunai">Tunai</option>
                                                            <option value="Transfer">Transfer</option>
                                                            <option value="Kredit">Kredit</option>
                                                            <option value="Tunai & Transfer">Tunai & Transfer</option>
                                                        </select>

                                                        <div x-show="caraPembayaran === 'Tunai & Transfer'"
                                                            class="mt-3">
                                                            <label
                                                                class="block text-sm font-medium text-indigo-500">Silahkan
                                                                isi hanya pada salah satu input saja: Tunai /
                                                                Transfer</label>
                                                            <div class="flex flex-row gap-3">
                                                                <div class="w-1/2 mb-3 md:mb-0">
                                                                    <label class="block text-sm font-medium mb-1"
                                                                        for="tunai">Tunai</label>
                                                                    <input class="form-input w-full py-1"
                                                                        type="text" name="tunai" id="tunai"
                                                                        value="0" />
                                                                </div>
                                                                <div class="w-1/2 mb-3 md:mb-0">
                                                                    <label class="block text-sm font-medium mb-1"
                                                                        for="transfer">Transfer</label>
                                                                    <input class="form-input w-full py-1"
                                                                        type="text" name="transfer" id="transfer"
                                                                        value="0" />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div x-show="caraPembayaran === 'Kredit'" class="mt-3">
                                                            <div>
                                                                <label class="block text-sm font-medium mb-1"
                                                                    for="pay">Jumlah Pembayaran <span
                                                                        class="text-rose-500">*</span></label>
                                                                <input id="pay" name="pay"
                                                                    class="form-input w-full px-2 py-1"
                                                                    type="number" />
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
                                                            <label class="block text-sm font-medium mt-3"
                                                                for="tempo">Waktu Tempo <span
                                                                    class="text-rose-500">*</span></label>
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
                                            {{-- <div id="total_modal_batal_wrapper" class="mt-4 space-y-3" style="display: none;">
                                                <label class="block text-sm font-medium mb-1" for="total_modal_batal">
                                                    Total Modal
                                                    <span class="text-rose-500">*</span>
                                                </label>
                                                <input class="form-input w-full px-2 py-1" type="number" name="total_modal_sparepart" id="total_modal_batal" />
                                            </div> --}}
                                            <div>
                                                <label class="block text-sm font-medium mb-1"
                                                    for="catatan">Catatan <small>(Kosongkan jika tidak
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
            @endif

        </div>

    </div>

    <!-- More actions -->
    <div class="sm:flex sm:justify-between sm:items-center mb-5">

        <!-- Left side -->
        <div class="mb-4 sm:mb-0">
            <ul class="flex flex-wrap -m-1">
                <li class="m-1">
                    <a href="{{ route('transaksi-servis.index') }}">
                        <button
                            class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-indigo-500 text-white duration-150 ease-in-out">Proses
                            <span class="ml-1 text-indigo-200">{{ $processes_count }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-bisa-diambil.index') }}">
                        <button
                            class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Bisa
                            Diambil <span class="ml-1 text-slate-400">{{ $jumlah_bisa_diambil }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-sudah-diambil.index') }}">
                        <button
                            class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Sudah
                            Diambil <span class="ml-1 text-slate-400">{{ $jumlah_sudah_diambil }}</span></button>
                    </a>
                </li>

                @if (Auth::user()->role == 'Kepala Toko' )
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-belum-disetujui.index') }}">
                        <button
                            class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Belum
                            Disetujui <span class="ml-1 text-slate-400">{{ $jumlah_belum_disetujui }}</span></button>
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                {{-- Left side --}}
                <h2 class="font-semibold text-slate-800">Proses <span
                        class="text-slate-400 font-medium">{{ $processes_count }}</span></h2>
                {{-- Right side --}}
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span>
                                item yang dipilih</div>
                            <button
                                class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600"
                                @click="deleteSelected">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Table -->
            <div class="overflow-x-auto">
                <table id="transaksi-servis-table" class="table-auto w-full">
                    <!-- Table header -->
                    <thead
                        class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            @if (Auth::user()->role == 'Kepala Toko')
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox"
                                            @click="toggleAll" />
                                    </label>
                                </div>
                            </th>
                            @endif
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
                            @if (Auth::user()->role != 'Investor')
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Status</div>
                            </th>
                            @endif
                            @if (Auth::user()->role != 'Investor')
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Aksi</div>
                            </th>
                            @endif
                        </tr>
                    </thead>

                </table>

            </div>
        </div>
    </div>

<div id="modal-upload-foto" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">

    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" aria-hidden="true" onclick="closeModalFoto()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block w-full text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl overflow-hidden sm:my-8 sm:align-middle sm:max-w-3xl">

            <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">

                        <div class="flex items-center justify-between pb-2 mb-4 border-b">
                            <h3 class="text-xl font-bold leading-6 text-gray-900" id="modal-title">
                                📸 Dokumentasi Foto Servis
                            </h3>
                            <button onclick="closeModalFoto()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <input type="hidden" id="current-servis-id">

                        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                            <div class="p-4 border rounded-lg bg-slate-50 border-slate-200">
                                <div class="flex items-center mb-2">
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">IN</span>
                                    <label class="block text-sm font-bold text-gray-700">Kondisi Masuk</label> <br>
                                </div>
                                <small class="text-rose-500">*Klik lagi untuk menambah foto lainnya (Multi-upload).</small>
                                <input type="file" class="filepond-masuk" name="file" multiple data-max-file-size="10MB">
                            </div>

                            <div class="p-4 border rounded-lg bg-slate-50 border-slate-200">
                                <div class="flex items-center mb-2">
                                    <span class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">OUT</span>
                                    <label class="block text-sm font-bold text-gray-700">Kondisi Selesai</label>
                                </div>
                                <small class="text-rose-500">*Klik lagi untuk menambah foto lainnya (Multi-upload).</small>
                                <input type="file" class="filepond-selesai" name="file" multiple data-max-file-size="10MB">
                            </div>
                        </div>

                        <div class="mt-4 text-xs italic text-gray-500">
                            * Foto otomatis tersimpan saat berhasil di-upload. Klik foto untuk memperbesar (zoom).
                        </div>

                    </div>
                </div>
            </div>

            <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-100 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeModalFoto()">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
<link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">

<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.min.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.min.js"></script>
<script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
<script>
    function toggleManual(checkbox, selectId, inputId) {
        const select = document.getElementById(selectId);
        const input  = document.getElementById(inputId);

        const select2Container = select.nextElementSibling; // .select2-container

        if (checkbox.checked) {
            // hide select2 UI
            select2Container.classList.add('hidden');
            select.required = false;
            select.disabled = true;

            // show manual input
            input.classList.remove('hidden');
            input.required = true;
            input.value = '';
        } else {
            // show select2 UI
            select2Container.classList.remove('hidden');
            select.required = true;
            select.disabled = false;

            // hide manual input
            input.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    }

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('manual-toggle')) return;

        const wrapper = e.target.closest('.manual-wrapper');
        const select  = wrapper.querySelector('.manual-select');
        const input   = wrapper.querySelector('.manual-input');

        const select2Container =
            select.nextElementSibling &&
            select.nextElementSibling.classList.contains('select2-container')
                ? select.nextElementSibling
                : null;

        if (e.target.checked) {
            // hide select / select2
            if (select2Container) {
                select2Container.classList.add('hidden');
                $(select).val(null).trigger('change');
            } else {
                select.classList.add('hidden');
            }

            select.disabled = true;
            select.required = false;

            // show manual input
            input.classList.remove('hidden');
            input.required = true;
            input.value = '';
        } else {
            // show select / select2
            if (select2Container) {
                select2Container.classList.remove('hidden');
            } else {
                select.classList.remove('hidden');
            }

            select.disabled = false;
            select.required = true;

            // hide manual input
            input.classList.add('hidden');
            input.required = false;
            input.value = '';
        }
    });
    // 1. Register Plugin
    FilePond.registerPlugin(
        FilePondPluginFileValidateType,
        FilePondPluginImageResize,
        FilePondPluginImageTransform,
        FilePondPluginImagePreview
    );

    let pondMasuk, pondSelesai;
    // Variabel untuk menyimpan instance Viewer.js (untuk zoom)
    let viewer;

    document.addEventListener('DOMContentLoaded', function() {
        // 2. Config Dasar FilePond
        const baseConfig = {
            allowMultiple: true,
            acceptedFileTypes: ['image/jpeg', 'image/png', 'image/webp'], // Batasi tipe file agar transform jalan
            labelIdle: 'Drag & Drop gambar atau <span class="filepond--label-action">Cari</span>',
            credits: false,

            // --- KONFIGURASI RESIZE (DIMENSI) ---
            allowImageResize: true,
            imageResizeTargetWidth: 1280,
            imageResizeTargetHeight: 1280,
            imageResizeMode: 'contain',
            imageResizeUpscale: false,

            // --- KONFIGURASI TRANSFORM (KOMPRESI) ---
            allowImageTransform: true,
            imageTransformOutputQuality: 70, // Turunkan sedikit ke 70 agar size lebih kecil
            imageTransformOutputMimeType: 'image/jpeg', // Paksa convert ke JPEG (lebih kecil dari PNG)

            // Fix untuk orientasi foto HP (EXIF data)
            imageTransformOutputStripImageHead: false,

            // Preview
            imagePreviewHeight: 150,

            // Event Zoom Viewer
            onactivatefile: (file) => {
                let imageUrl = file.getMetadata('url');
                if (!imageUrl && file.file) {
                    imageUrl = URL.createObjectURL(file.file);
                }
                if (imageUrl) showImagePopup(imageUrl);
            }
        };

        // 3. Create Instance
        const inputMasuk = document.querySelector('.filepond-masuk');
        const inputSelesai = document.querySelector('.filepond-selesai');

        pondMasuk = FilePond.create(inputMasuk, baseConfig);
        pondSelesai = FilePond.create(inputSelesai, baseConfig);

        // 4. Event Listener Tombol Buka Modal
        $(document).on('click', '.btn-upload-foto', function() {
            let id = $(this).data('id');
            $('#current-servis-id').val(id);
            $('#modal-upload-foto').removeClass('hidden');

            // Reset FilePond
            pondMasuk.removeFiles();
            pondSelesai.removeFiles();

            // Setup Server Config (Dynamic URL based on ID)
            setupPondServer(pondMasuk, id, 'masuk');
            setupPondServer(pondSelesai, id, 'selesai');

            // Load Existing Images
            loadExistingImages(id);
        });
    });

    // --- Config AJAX Server (Upload, Delete, & LOAD Preview) ---
    function setupPondServer(pondInstance, id, type) {
        pondInstance.setOptions({
            server: {
                // 1. Upload File Baru
                process: {
                    url: `/servis/transaksi-servis/${id}/upload-foto`,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    ondata: (formData) => {
                        formData.append('type', type);
                        return formData;
                    }
                },

                // 2. Hapus File yang BARU di-upload (belum direfresh page)
                revert: {
                    url: `/servis/transaksi-servis/${id}/delete-foto?type=${type}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                },

                // 3. Hapus File LAMA (yang diload dari database)
                remove: (source, load, error) => {
                    // source adalah nama file
                    fetch(`/servis/transaksi-servis/${id}/delete-foto?type=${type}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'text/plain'
                        },
                        body: source
                    }).then(() => {
                        load(); // Beritahu FilePond penghapusan sukses
                    }).catch((err) => {
                        error('Gagal menghapus');
                    });
                },

                // 4. LOAD PREVIEW (Kunci agar gambar muncul, bukan cuma nama)
                load: (source, load, error, progress, abort, headers) => {
                    // source disini adalah nama file dari database.
                    // Kita fetch blob dari url public storage
                    let myRequest = new Request(`/storage/servis/${source}`);

                    fetch(myRequest).then(function(response) {
                        response.blob().then(function(myBlob) {
                            load(myBlob); // Masukkan blob gambar ke FilePond
                        });
                    }).catch((err) => {
                        error('Gagal load gambar');
                    });
                }
            }
        });
    }

    // --- Load Existing Images ---
    function loadExistingImages(id) {
        fetch(`/servis/transaksi-servis/${id}/get-foto`)
            .then(res => res.json())
            .then(data => {
                if(data.masuk) {
                    pondMasuk.files = data.masuk;
                }
                if(data.selesai) {
                    pondSelesai.files = data.selesai;
                }
            })
            .catch(err => console.error("Gagal load foto", err));
    }

    // --- Fungsi Zoom Gambar (Viewer.js) ---
    function showImagePopup(imageUrl) {
        // Buat elemen gambar temporary hidden
        const image = new Image();
        image.src = imageUrl;

        // Inisialisasi Viewer.js
        const viewer = new Viewer(image, {
            hidden: function () {
                viewer.destroy(); // Hapus instance setelah ditutup
            },
            toolbar: {
                zoomIn: 1,
                zoomOut: 1,
                oneToOne: 1,
                reset: 1,
                rotateLeft: 1,
                rotateRight: 1,
                flipHorizontal: 1,
                flipVertical: 1,
            },
        });

        // Tampilkan
        viewer.show();
    }

    // --- Tutup Modal ---
    function closeModalFoto() {
        $('#modal-upload-foto').addClass('hidden');
    }
</script>
<script>
    // --- DATA ITEM STANDAR (Berlaku untuk Tab 1 & Tab 2) ---
    const defaultChecklist = [
        "CHECK FACE ID/FINGER", "CHECK FRONT CAM", "CHECK BACK CAM 1/2/3",
        "CHECK CAM 30PFS,60PFS", "TOP SPEAKER", "BOTTOM SPEAKER",
        "BODY HOUSING", "LCD (Truetone,Ts)", "NETWORK", "CALLING PHONE",
        "BATTERY", "BACK MIC", "BOTTOM MIC", "FRONT MIC",
        "TOP AUDIO", "BOTTOM AUDIO", "WIFI/BLUETOOTH", "FLASH LED",
        "ALL BUTTON", "COMPAS", "VIBRANT/SILENT", "CHARGING",
        "PANIC FULL", "OTHER"
    ];

    document.addEventListener('DOMContentLoaded', function() {
        renderAllChecklists();
    });

    // --- FUNGSI RENDER UTAMA ---
    function renderAllChecklists() {
        const tbodyTab1 = document.getElementById('checklist-tbody-tab1');
        const tbodyTab2 = document.getElementById('checklist-tbody-tab2');

        tbodyTab1.innerHTML = '';
        tbodyTab2.innerHTML = '';

        defaultChecklist.forEach((item, index) => {
            // === RENDER TAB 1 (Hanya IN) ===
            const tr1 = document.createElement('tr');
            tr1.className = "border-b border-slate-200 hover:bg-slate-50";
            tr1.innerHTML = `
                <td class="border border-slate-300 p-1 text-center font-bold row-num">${index + 1}</td>
                <td class="border border-slate-300 p-1 font-medium bg-slate-50">${item}</td>
                <td class="border border-slate-300 p-0">
                    <input type="text" name="qc_masuk[${item}]" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
                </td>
                <td class="border border-slate-300 p-1 text-center">
                    <button type="button" class="text-rose-500 hover:text-rose-700" onclick="deleteRow(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    </button>
                </td>
            `;
            tbodyTab1.appendChild(tr1);

            // === RENDER TAB 2 (IN & OUT) ===
            const tr2 = document.createElement('tr');
            tr2.className = "border-b border-slate-200 hover:bg-slate-50";
            tr2.innerHTML = `
                <td class="border border-slate-300 p-1 text-center font-bold row-num">${index + 1}</td>
                <td class="border border-slate-300 p-1 font-medium bg-slate-50">${item}</td>
                <td class="border border-slate-300 p-0">
                    <input type="text" name="qc_masuk[${item}]" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
                </td>
                <td class="border border-slate-300 p-0">
                    <input type="text" name="qc_keluar[${item}]" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
                </td>
                 <td class="border border-slate-300 p-1 text-center">
                    <button type="button" class="text-rose-500 hover:text-rose-700" onclick="deleteRow(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    </button>
                </td>
            `;
            tbodyTab2.appendChild(tr2);
        });
    }

    function addCustomRowTab1() {
        const tbody = document.getElementById('checklist-tbody-tab1');
        const rowCount = tbody.rows.length + 1;
        const tr = document.createElement('tr');
        tr.className = "border-b border-slate-200 hover:bg-yellow-50";

        tr.innerHTML = `
            <td class="border border-slate-300 p-1 text-center font-bold row-num">${rowCount}</td>
            <td class="border border-slate-300 p-0">
                <input type="text" name="custom_item_name[]" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent font-medium text-indigo-600" placeholder="Ketik Nama Item..." required>
            </td>
            <td class="border border-slate-300 p-0">
                <input type="text" name="custom_qc_masuk[]" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
            </td>
            <td class="border border-slate-300 p-1 text-center">
                <button type="button" class="text-rose-500 hover:text-rose-700" onclick="deleteRow(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    function addCustomRowTab2() {
        const tbody = document.getElementById('checklist-tbody-tab2');
        const rowCount = tbody.rows.length + 1;
        const tr = document.createElement('tr');
        tr.className = "border-b border-slate-200 hover:bg-yellow-50";

        tr.innerHTML = `
            <td class="border border-slate-300 p-1 text-center font-bold row-num">${rowCount}</td>
            <td class="border border-slate-300 p-0">
                <input type="text" name="custom_item_name[]" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent font-medium text-indigo-600" placeholder="Ketik Nama Item..." required>
            </td>
            <td class="border border-slate-300 p-0">
                <input type="text" name="custom_qc_masuk[]" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
            </td>
            <td class="border border-slate-300 p-0">
                <input type="text" name="custom_qc_keluar[]" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
            </td>
            <td class="border border-slate-300 p-1 text-center">
                <button type="button" class="text-rose-500 hover:text-rose-700" onclick="deleteRow(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    // --- FUNGSI HAPUS BARIS ---
    function deleteRow(btn) {
        const row = btn.closest('tr');
        const tbody = row.parentNode;
        row.remove();

        // Update nomor urut
        Array.from(tbody.rows).forEach((r, index) => {
            const numCell = r.querySelector('.row-num');
            if(numCell) numCell.innerText = index + 1;
        });
    }
</script>

<script>

document.addEventListener('DOMContentLoaded', function () {
    // --- Inisialisasi select2 pada elemen awal ---
    $('#selectjs6').select2();

    // --- Ketika sparepart dipilih, ambil harga_modal ---
    $(document).on('change', 'select[name="products_id[]"]', function () {
        let hargaModal = $(this).find(':selected').data('harga_modal') || 0;
        // cari input modal_sparepart dalam section yang sama
        $(this).closest('.konfirmasi-stok, [x-data]').find('.modal_sparepart').val(hargaModal).trigger('input');
    });

    // --- Kalkulasi total otomatis ---
    $(document).on('input', '.modal_sparepart', function () {
        hitungTotalModal();
    });

    function hitungTotalModal() {
        let total = 0;
        $('.modal_sparepart').each(function () {
            total += parseFloat($(this).val()) || 0;
        });
        $('#total_modal_sparepart').val(total);
    }

    // --- Saat tombol tambah tindakan diklik ---
    $('#tambah-servis').on('click', function () {
        $('#servis-lain').append(`{!! str_replace(["\n", "\r", "'"], ["", "", "\\'"], $konfirSparepartEl ?? '') !!}`);
        // Re-inisialisasi select2 untuk elemen baru
        $('.selectAction2').select2({
            placeholder: 'Pilih Sparepart'
        });

        // Pasang listener harga modal untuk elemen baru
        $('.selectAction2').off('change').on('change', function () {
            let hargaModal = $(this).find(':selected').data('harga_modal') || 0;
            $(this).closest('.konfirmasi-stok').find('.modal_sparepart').val(hargaModal).trigger('input');
        });
    });
});
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
    .then(response => response.json())
    .then(data => {
        if (data.status === true || data.success) {
            alert('✅ Pesan berhasil dikirim ke Pelanggan!');
        } else {
            alert('⚠️ Gagal mengirim ke Pelanggan. Coba lagi.');
        }
    })
    .catch(() => alert('❌ Terjadi kesalahan saat mengirim ke Fontee.'));
}
</script>


<script>
    function getCanvas(processId) {
        let canvas = document.getElementById('sig-canvas-' + processId);
        if (canvas) {
            // Pastikan width/height sesuai ukuran CSS
            if (canvas.width !== canvas.offsetWidth || canvas.height !== canvas.offsetHeight) {
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
            }
        }
        return canvas;
    }

    function resetCanvas(processId) {
        let canvas = getCanvas(processId);
        if (canvas) {
            let ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height); // benar-benar bersihin
        }

        // Kosongkan hidden input juga
        let polaInput = document.getElementById('polaInput-' + processId);
        if (polaInput) polaInput.value = '';
    }
    window.requestAnimFrame = (function(){
        return window.requestAnimationFrame ||
            window.webkitRequestAnimationFrame ||
            window.mozRequestAnimationFrame ||
            function(callback){ window.setTimeout(callback, 1000/60); };
    })();

    document.querySelectorAll(".sig-canvas").forEach((canvas) => {
        let ctx = canvas.getContext("2d");
        ctx.strokeStyle = "#222222";
        ctx.lineWidth = 4;

        let drawing = false;
        let mousePos = { x: 0, y: 0 };
        let lastPos = mousePos;

        function getMousePos(canvasDom, mouseEvent) {
            let rect = canvasDom.getBoundingClientRect();
            return { x: mouseEvent.clientX - rect.left, y: mouseEvent.clientY - rect.top };
        }

        function getTouchPos(canvasDom, touchEvent) {
            let rect = canvasDom.getBoundingClientRect();
            return { x: touchEvent.touches[0].clientX - rect.left, y: touchEvent.touches[0].clientY - rect.top };
        }

        function renderCanvas() {
            if (drawing) {
                ctx.beginPath();
                ctx.moveTo(lastPos.x, lastPos.y);
                ctx.lineTo(mousePos.x, mousePos.y);
                ctx.stroke();
                ctx.closePath();
                lastPos = mousePos;
            }
        }

        // Mouse
        canvas.addEventListener("mousedown", function(e) {
            drawing = true;
            lastPos = getMousePos(canvas, e);
        });
        canvas.addEventListener("mouseup", function() { drawing = false; });
        canvas.addEventListener("mousemove", function(e) { mousePos = getMousePos(canvas, e); });

        // Touch
        canvas.addEventListener("touchstart", function(e) {
            mousePos = getTouchPos(canvas, e);
            let touch = e.touches[0];
            canvas.dispatchEvent(new MouseEvent("mousedown", { clientX: touch.clientX, clientY: touch.clientY }));
        });
        canvas.addEventListener("touchmove", function(e) {
            let touch = e.touches[0];
            canvas.dispatchEvent(new MouseEvent("mousemove", { clientX: touch.clientX, clientY: touch.clientY }));
        });
        canvas.addEventListener("touchend", function() {
            canvas.dispatchEvent(new MouseEvent("mouseup", {}));
        });

        // Prevent scroll saat touch canvas
        ["touchstart","touchend","touchmove"].forEach(evt => {
            canvas.addEventListener(evt, function(e) {
                if (e.target === canvas) e.preventDefault();
            }, { passive:false });
        });

        // Loop render
        (function drawLoop() {
            requestAnimFrame(drawLoop);
            renderCanvas();
        })();
    });

</script>

    <script>
        function saveCanvasAjax(id) {
            let canvas = document.getElementById(`sig-canvas-${id}`);
            let polaInput = document.getElementById(`polaInput-${id}`);
            let pinInput = document.getElementById(`pinInput-${id}`);

            if (canvas) {
                let data = canvas.toDataURL();
                polaInput.value = data;
            }


            let payload = {
                pin: pinInput.value,
                pola: polaInput.value
            };
            console.log(payload);


            fetch(`/servis/transaksi-servis/${id}/update-pin-pola`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    alert(res.message);
                    window.dispatchEvent(new CustomEvent(`close-pin-modal-${id}`));
                } else {
                    alert("Gagal menyimpan data");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error saat menyimpan data");
            });
        }
</script>

<script>
    // Saat modal dibuka, isi canvas kalau ada pola lama
    document.addEventListener("open-pin-modal", (event) => {
        let pola = event.detail.pola;
        let canvas = document.querySelector(".sig-canvas");
        let ctx = canvas.getContext("2d");
        let polaInput = canvas.parentElement.querySelector(".polaInput");

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (pola) {
            let img = new Image();
            img.onload = function() {
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            };
            img.src = pola;
            polaInput.value = pola;
        } else {
            polaInput.value = "";
        }
    });
</script>




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

    <!-- Pagination -->
    {{-- <div class="mt-8">
        {{ $processes->links() }}
    </div> --}}
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
        $('input[name="kondisi_servis"]').on('change', function () {
            const selectedValue = $(this).val();

            if (selectedValue === 'Dibatalkan') {
                $('#total_modal_batal_wrapper').show();                       // Tampilkan input
                $('#total_modal_batal').attr('required', true);              // Jadikan required

                $('#total_modal_sparepart').closest('.mt-3').hide();         // Sembunyikan sparepart
                $('#total_modal_sparepart').removeAttr('required');          // Hilangkan required dari sparepart
            } else {
                $('#total_modal_batal_wrapper').hide();                      // Sembunyikan input
                $('#total_modal_batal').removeAttr('required');              // Hilangkan required

                $('#total_modal_sparepart').closest('.mt-3').show();         // Tampilkan sparepart
                $('#total_modal_sparepart').attr('required', true);          // Tambahkan required ke sparepart
            }
        });

        // Trigger change on load (misalnya ketika form reload)
        $('input[name="kondisi_servis"]:checked').trigger('change');
    });

</script>


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
        // PERBAIKAN 1: Gunakan parseRupiah() bukan parseInt() atau parseFloat() biasa
        // Ini agar angka "1.000.000" terbaca sebagai 1000000
        let biaya = parseRupiah($('#biaya').val());
        let diskon = parseRupiah($('#diskon').val());

        // Hitung subtotal
        let subtotal = Math.max(biaya - diskon, 0);

        // Tambah PPN kalau ada
        if (ppn > 0) {
            subtotal += Math.round(subtotal * ppn / 100);
        }

        return subtotal;
    }

    $(document).ready(function () {

        // 1. Kalau user ketik nominal Tunai
        $('#tunai').on('input', function () {
            $('#tunai').val(formatRupiah($(this).val()));

            if ($('#cara_pembayaran').val() === 'Tunai & Transfer') {
                let total = getTotal();

                // Ambil nilai tunai yang sedang diketik (bersihkan titiknya dulu)
                let tunai = parseRupiah($(this).val());

                // Hitung sisanya untuk transfer
                let transfer = total - tunai;

                // Tampilkan hasil ke input Transfer dengan format Rupiah
                // Jika hasil minus (tunai > total), set 0
                $('#transfer').val(transfer >= 0 ? formatRupiah(transfer) : 0);
            }
        });

        // 2. Kalau user ketik nominal Transfer
        $('#transfer').on('input', function () {
            $('#transfer').val(formatRupiah($(this).val()));

            if ($('#cara_pembayaran').val() === 'Tunai & Transfer') {
                let total = getTotal();

                // Ambil nilai transfer yang sedang diketik (bersihkan titiknya dulu)
                let transfer = parseRupiah($(this).val());

                // Hitung sisanya untuk tunai
                let tunai = total - transfer;

                // Tampilkan hasil ke input Tunai dengan format Rupiah
                $('#tunai').val(tunai >= 0 ? formatRupiah(tunai) : 0);
            }
        });

        // 3. Kalau Biaya atau Diskon berubah, trigger ulang kalkulasi
        $('#biaya, #diskon').on('input', function () {
            // Hanya trigger jika mode "Tunai & Transfer" sedang aktif
            if ($('#cara_pembayaran').val() === 'Tunai & Transfer') {
                // Reset tunai ke Total penuh dulu agar tidak bingung
                // Atau trigger input event di #tunai agar menghitung ulang transfer based on tunai yang ada
                $('#tunai').trigger('input');
            }
        });

        // 4. Saat cara pembayaran diganti
        $('#cara_pembayaran').on('change', function () {
            if ($(this).val() === 'Tunai & Transfer') {
                // Auto fill Tunai dengan Total (Format Rupiah), Transfer 0
                $('#tunai').val(formatRupiah(getTotal()));
                $('#transfer').val(0);
            } else {
                // Reset jadi 0
                $('#tunai, #transfer').val(0);
            }
        });
    });
</script>

 <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<div id="genericPinModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center border-b pb-2 mb-4">
            <h2 class="text-lg font-semibold text-gray-700">Service PIN & Pola</h2>
            <button onclick="closePinModal()" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>

        <input type="hidden" id="currentServiceId">

        <div class="space-y-4">
            <div>
                <label class="text-sm font-medium text-gray-600">PIN</label> <br>
                <input type="number" id="modalPinInput" class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:ring-indigo-200">
            </div>

            <div>
                <label class="text-sm font-medium text-gray-600">Pola</label>
                <div class="border rounded bg-gray-100">
                    <canvas id="signature-pad" class="w-full h-48 block" width="400" height="200"></canvas>
                </div>
                <small class="text-gray-400">Gambar pola pada area di atas</small>
            </div>

            <button type="button" onclick="clearSignature()" class="mt-2 px-3 py-1 bg-red-500 text-white text-sm rounded hover:bg-red-600">Reset Pola</button>
        </div>

        <div class="mt-6 flex justify-end space-x-2">
            <button onclick="closePinModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Batal</button>
            <button onclick="savePinPola()" class="px-4 py-2 bg-indigo-500 text-white rounded hover:bg-indigo-600">Simpan</button>
        </div>
    </div>
</div>
<script>
    let signaturePad;

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Signature Pad on the canvas
        var canvas = document.getElementById('signature-pad');
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)' // Transparent background
        });
    });

    // 1. Function to Open Modal & Fetch Data
    function openPinModal(id) {
        // Show loading state if needed

        // AJAX to fetch existing data
        $.ajax({
            url: '/transaksi-servis/get-pin-pola/' + id, // URL matches the route defined above
            type: 'GET',
            success: function(response) {
                if(response.status === 'success') {
                    // Populate ID
                    $('#currentServiceId').val(response.data.id);

                    // Populate PIN
                    $('#modalPinInput').val(response.data.pin);

                    // Populate Canvas (Pola)
                    signaturePad.clear(); // Clear first
                    if (response.data.pola) {
                        // Load existing signature data
                        signaturePad.fromData(JSON.parse(response.data.pola));
                    }

                    // Show Modal
                    $('#genericPinModal').removeClass('hidden');
                }
            },
            error: function(err) {
                alert('Gagal mengambil data PIN/Pola');
            }
        });
    }

    // 2. Function to Close Modal
    function closePinModal() {
        $('#genericPinModal').addClass('hidden');
    }

    // 3. Function to Clear Signature Pad
    function clearSignature() {
        signaturePad.clear();
    }

    // 4. Function to Save Data via AJAX
    function savePinPola() {
        let id = $('#currentServiceId').val();
        let pin = $('#modalPinInput').val();

        // Get canvas data as JSON string (to save structure, allowing re-editing)
        // If you strictly want an image, use signaturePad.toDataURL()
        // But for editing later, toData() is better.
        let polaData = JSON.stringify(signaturePad.toData());

        $.ajax({
            url: '/transaksi-servis/update-pin-pola/' + id,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}', // CSRF Token for Laravel
                pin: pin,
                pola: polaData
            },
            success: function(response) {
                if(response.status === 'success') {
                    alert(response.message);
                    closePinModal();
                    // Optional: Refresh DataTable if you show indicator of "Has PIN"
                    $('#transaksiTable').DataTable().ajax.reload(null, false);
                }
            },
            error: function(err) {
                alert('Gagal menyimpan data');
            }
        });
    }
</script>
 {{-- <script src="https://code.jquery.com/jquery-3.7.0.js" crossorigin="anonymous"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    // --- 1. HELPER FUNCTIONS FORMAT RUPIAH ---
    function formatRupiah(angka) {
        if (!angka) return '';
        var number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
            split = number_string.split(','),
            sisa = split[0].length % 3,
            rupiah = split[0].substr(0, sisa),
            ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        return rupiah;
    }

    function parseRupiah(str) {
        if (!str) return 0;
        // Hapus semua titik, lalu ubah ke integer
        return parseInt(str.toString().replace(/\./g, '')) || 0;
    }

    // --- 2. LOGIC UTAMA ---
    $(document).ready(function() {

        // A. EVENT LISTENER FORMATTING INPUT
        $(document).on('input', '.input-currency', function() {
            $(this).val(formatRupiah($(this).val()));
        });

        // B. CLEAN DATA SEBELUM SUBMIT
        $('form').on('submit', function() {
            // Hapus titik di semua input currency sebelum dikirim ke backend
            $(this).find('.input-currency').each(function() {
                var cleanVal = $(this).val().replace(/\./g, '');
                $(this).val(cleanVal);
            });
            // Bersihkan juga field total (readonly)
            $('#biaya').val($('#biaya').val().replace(/\./g, ''));
            $('#total_modal_sparepart').val($('#total_modal_spaInirepart').val().replace(/\./g, ''));
            $('#tunai').val($('#tunai').val().replace(/\./g, ''));
            $('#transfer').val($('#transfer').val().replace(/\./g, ''));
        });

        // C. LOGIC DINAMIS TEKNISI
        const existingData = @json($teknisiServis ?? []);
        let groupCounter = 0;

        // TEMPLATES
        const teknisiOptions = `
            <option selected value="">Pilih Teknisi</option>
            @foreach ($users as $user) <option value="{{ $user->id }}">{{ $user->name }}</option> @endforeach
        `;
        const actionOptions = `
            <option selected value="">Pilih Tindakan</option>
            @foreach ($service_actions as $action) <option value="{{ $action->id }}">{{ $action->nama_tindakan }}</option> @endforeach
        `;
        const sparepartOptions = `
            <option selected value="">Pilih Sparepart</option>
            @foreach (App\Models\Product::where('cabang_id',getCabangId())->get() as $item)
                <option value="{{ $item->id }}" data-harga_modal="{{ $item->harga_modal }}">{{ addslashes($item->product_name) }}</option>
            @endforeach
        `;
        const salesOptions = `
            <option selected value="1">Tidak ada Sales</option>
            @foreach ($sales as $user) <option value="{{ $user->id }}">{{ $user->name }}</option> @endforeach
        `;

        function generateActionHtml(groupIndex, actionIndex) {
            const uniqueRadioId = 'radio_' + actionIndex;
            // PERUBAHAN: Input modal & biaya jadi type="text" + class="input-currency"
            return `
            <div class="action-item" x-data="{ useSparepart: false, showInputManual: false }">
                <button type="button" class="position-button-x remove-action absolute top-2 right-2 text-rose-500 hover:text-rose-700 font-bold" title="Hapus">&times;</button>
                <div class="mb-2 pr-6">
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-sm font-medium">Tindakan Servis <span class="text-rose-500">*</span></label>
                        <label class="flex items-center"><input type="checkbox" class="form-checkbox checkbox-manual" x-on:click="showInputManual = !showInputManual"/><span class="text-sm ml-2">Isi Manual</span></label>
                    </div>
                    <div x-show="!showInputManual" class="wrapper-select-action">
                        <select name="teknisi[${groupIndex}][tindakan][${actionIndex}][service_actions_id]" class="form-select text-sm py-1 w-full selectAction">${actionOptions}</select>
                    </div>
                    <div x-show="showInputManual" class="mt-2 wrapper-input-manual" style="display:none;">
                        <input class="form-input w-full px-2 py-1 input-manual-text" type="text" name="teknisi[${groupIndex}][tindakan][${actionIndex}][tindakan_servis]" placeholder="Ketik manual..."/>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="garansi">Garansi</label>
                    <select name="teknisi[${groupIndex}][tindakan][${actionIndex}][garansi]" class="form-select text-sm py-1 w-full">
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
                <div class="konfirmasi-stok border-t border-slate-200 pt-2 mt-2">
                    <label class="block text-sm font-medium mb-1">Pakai Sparepart Toko?</label>
                    <div class="flex flex-wrap items-center -m-3 mb-2">
                        <div class="m-3"><label class="flex items-center"><input type="radio" name="${uniqueRadioId}" value="tidak" class="form-radio radio-sparepart-no" checked x-on:click="useSparepart = false"/><span class="text-sm ml-2">Tidak</span></label></div>
                        <div class="m-3"><label class="flex items-center"><input type="radio" name="${uniqueRadioId}" value="ya" class="form-radio radio-sparepart-yes" x-on:click="useSparepart = true"/><span class="text-sm ml-2">Ya</span></label></div>
                    </div>
                    <div x-show="useSparepart" style="display: none;" class="wrapper-sparepart-area">
                        <div class="mb-2"><label class="block text-sm font-medium mb-1">Sparepart</label><select name="teknisi[${groupIndex}][tindakan][${actionIndex}][products_id]" class="form-select text-sm py-1 w-full selectSparepart" style="width: 100%;">${sparepartOptions}</select></div>
                        <div class="mb-2"><label class="block text-sm font-medium mb-1">Sales Sparepart</label><select name="teknisi[${groupIndex}][tindakan][${actionIndex}][sales_id]" class="form-select text-sm py-1 w-full selectSales">${salesOptions}</select></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <div>
                            <label class="block text-sm font-medium mb-1">Modal Part <span class="text-rose-500">*</span></label>
                            <input class="form-input w-full px-2 py-1 modal_sparepart input-currency" type="text" name="teknisi[${groupIndex}][tindakan][${actionIndex}][modal_sparepart]" value="0" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Biaya Servis <span class="text-rose-500">*</span></label>
                            <input class="form-input w-full px-2 py-1 biaya_servis input-currency" type="text" name="teknisi[${groupIndex}][tindakan][${actionIndex}][biaya_servis]" value="0" required />
                        </div>
                    </div>
                </div>
            </div>`;
        }

        function addTechnicianGroup(techData = null) {
            const currentGroupIndex = groupCounter++;
            const groupHtml = `
            <div class="technician-group relative" data-group-index="${currentGroupIndex}">
                <button style="position: absolute;right: 2%;" type="button" class="remove-group absolute top-2 right-2 text-white bg-rose-500 hover:bg-rose-600 rounded px-2 py-1 text-xs z-10">Hapus Teknisi</button>
                <div class="bg-indigo-50 -m-4 mb-4 p-4 border-b border-indigo-100 rounded-t">
                    <h3 class="font-bold text-indigo-800 mb-2">Data Teknisi</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div><label class="block text-sm font-medium mb-1">Nama Teknisi <span class="text-rose-500">*</span></label><select name="teknisi[${currentGroupIndex}][user_id]" class="form-select text-sm py-1 w-full selectUser" >${teknisiOptions}</select></div>
                        <div><label class="block text-sm font-medium mb-1">Tipe Bagi Hasil <span class="text-rose-500">*</span></label><select name="teknisi[${currentGroupIndex}][tipe]" class="form-select text-sm py-1 w-full selectType" ><option selected value="">Pilih Tipe</option><option value="Interface">Interface (bonus pertipe)</option><option value="Hardware">Hardware & interface (bonus persen)</option></select></div>
                    </div>
                </div>
                <div class="actions-list-container space-y-3"></div>
                <div class="mt-3 text-center border-t border-dashed border-slate-300 pt-3"><button type="button" class="add-action-btn btn-sm bg-emerald-500 hover:bg-emerald-600 text-white">+ Tambah Tindakan Lain (Untuk Teknisi Ini)</button></div>
            </div>`;

            const $newGroup = $(groupHtml);
            $('#main-container').append($newGroup);

            if (techData) {
                $newGroup.find('.selectUser').val(techData.users_id);
                $newGroup.find('.selectType').val(techData.tipe);
            } else {
                addActionToGroup($newGroup, currentGroupIndex);
            }
            return { $element: $newGroup, index: currentGroupIndex };
        }

        function addActionToGroup($groupElement, groupIndex, actionData = null) {
            if (groupIndex === undefined) groupIndex = $groupElement.attr('data-group-index');
            const actionIndex = Date.now() + Math.floor(Math.random() * 10000);
            const html = generateActionHtml(groupIndex, actionIndex);
            const $newItem = $(html);
            $groupElement.find('.actions-list-container').append($newItem);

            const $selAction = $newItem.find('.selectAction').select2();
            const $selSparepart = $newItem.find('.selectSparepart').select2();
            const $selSales = $newItem.find('.selectSales').select2();

            if (actionData) {
                // FORMAT VALUE SAAT LOAD
                $newItem.find('.biaya_servis').val(formatRupiah(actionData.biaya || 0));
                $newItem.find('.modal_sparepart').val(formatRupiah(actionData.modal || 0));

                if (actionData.act_id && actionData.act_id !== "null") $selAction.val(actionData.act_id).trigger('change.select2');

                if (actionData.prod_id && actionData.prod_id != "null" && actionData.prod_id != "") {
                    setTimeout(() => { $newItem.find('.radio-sparepart-yes')[0].click(); }, 50);
                    setTimeout(() => {
                        $selSparepart.val(actionData.prod_id).trigger('change.select2');
                        const hargaModalOtomatis = $selSparepart.find(':selected').data('harga_modal') || 0;

                        if(actionData.modal > 0) {
                            $newItem.find('.modal_sparepart').val(formatRupiah(actionData.modal));
                        } else {
                            $newItem.find('.modal_sparepart').val(formatRupiah(hargaModalOtomatis));
                        }
                        recalculateAll();
                    }, 100);
                } else {
                    recalculateAll();
                }
            }
        }

        // LOAD DATA
        if (existingData && existingData.length > 0) {
            existingData.forEach(function(tech) {
                const groupObj = addTechnicianGroup(tech);
                let actionsArr = [], productsArr = [], biayaArr = [], modalArr = [];
                try {
                    actionsArr = JSON.parse(tech.service_actions) || [];
                    productsArr = JSON.parse(tech.products) || [];
                    biayaArr = JSON.parse(tech.biaya_j) || [];
                    modalArr = JSON.parse(tech.modal_j) || [];
                } catch (e) { console.error(e); }

                if (actionsArr.length > 0) {
                    actionsArr.forEach(function(actId, i) {
                        const detailData = {
                            act_id: actId,
                            prod_id: productsArr[i] ?? null,
                            biaya: biayaArr[i] ?? 0,
                            modal: modalArr[i] ?? 0
                        };
                        addActionToGroup(groupObj.$element, groupObj.index, detailData);
                    });
                } else {
                    addActionToGroup(groupObj.$element, groupObj.index);
                }
            });
            setTimeout(recalculateAll, 1000);
        } else {
            addTechnicianGroup();
        }

        // EVENT HANDLERS
        $('#tambah-teknisi-baru').on('click', function() { addTechnicianGroup(); });
        $(document).on('click', '.add-action-btn', function() { addActionToGroup($(this).closest('.technician-group')); });
        $(document).on('click', '.remove-action', function() {
            if ($(this).closest('.actions-list-container').children().length > 1) {
                if(confirm('Hapus?')) { $(this).closest('.action-item').remove(); recalculateAll(); }
            } else alert('Minimal 1 tindakan.');
        });
        $(document).on('click', '.remove-group', function() {
            if(confirm('Hapus Teknisi?')) { $(this).closest('.technician-group').remove(); recalculateAll(); }
        });

        // Event Select Action
        $(document).on('select2:select', '.selectAction', function(e) {
            const $select = $(this);
            const $container = $select.closest('.action-item');
            const actionId = $select.val();
            if(actionId) {
                $.ajax({
                    url: '/get-action/' + actionId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        // FORMAT RUPIAH SAAT ISI OTOMATIS
                        $container.find('.biaya_servis').val(formatRupiah(data.biaya));
                        recalculateAll();
                    }
                });
            }
        });

        // Event Select Sparepart
        $(document).on('change', '.selectSparepart', function() {
            const $select = $(this);
            const $container = $select.closest('.action-item');
            const hargaModal = $select.find(':selected').data('harga_modal') || 0;
            // FORMAT RUPIAH SAAT ISI OTOMATIS
            $container.find('.modal_sparepart').val(formatRupiah(hargaModal));
            recalculateAll();
        });

        $(document).on('input', '.biaya_servis, .modal_sparepart', function() { recalculateAll(); });

        // RECALCULATE ALL (UPDATED: Parse Rupiah)
        function recalculateAll() {
            let totalBiaya = 0;
            let totalModal = 0;
            // Bersihkan format rupiah sebelum menjumlah
            $('.biaya_servis').each(function() { totalBiaya += parseRupiah($(this).val()); });
            $('.modal_sparepart').each(function() { totalModal += parseRupiah($(this).val()); });

            // Set kembali ke input Total dengan format rupiah
            $('#biaya').val(formatRupiah(totalBiaya));
            $('#total_modal_sparepart').val(formatRupiah(totalModal));

            // Trigger input agar kalkulasi diskon/tunai di bawah ikut update
            $('#biaya').trigger('input');
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Seleksi semua input dengan class "input-currency"
        const currencyInputs = document.querySelectorAll('.input-currency');

        currencyInputs.forEach(function(input) {
            // Format saat user mengetik
            input.addEventListener('input', function(e) {
                e.target.value = formatRupiah(e.target.value);
            });
        });

        // Optional: Bersihkan titik saat submit form agar data bersih masuk database
        const forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            form.addEventListener('submit', function() {
                currencyInputs.forEach(function(input) {
                    // Hapus titik sebelum submit
                    input.value = input.value.replace(/\./g, '');
                });
            });
        });
    });
</script>
