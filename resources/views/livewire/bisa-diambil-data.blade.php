<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Transaksi Servis ✨</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            @if (Auth::user()->role != 'Investor')
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
                        <form action="{{ route('cetak-laporan-bisa-diambil') }}" method="get"  target="_blank">
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
            @endif

            <!-- Search form -->
            {{-- <x-search-form placeholder="Pelanggan/Nomor Servis/Barang/Tindakan/IMEI" /> --}}

            @if (Auth::user()->role != 'Investor')
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
                        <form action="{{ route('transaksi-servis-bisa-diambil.store') }}" method="post">
                            @csrf
                            <input type="hidden" name="status_servis" value="Belum cek">
                            <div class="px-5 py-4">
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
                                        <input id="qc_masuk" name="qc_masuk" class="form-input w-full px-2 py-1" type="text" placeholder="Contoh: Tombol, Kamera, Speaker, dll" required/>
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
                                            @foreach ($workers as $worker)
                                                <option value="{{ $worker->name }}">{{ $worker->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal footer -->
                            <div class="px-5 py-4 border-t border-slate-200">
                                <div class="flex flex-wrap justify-between space-x-2">
                                    <a href="{{ route('pelanggan.index') }}" class="btn-sm bg-green-500 hover:bg-green-600 text-white">
                                        Tambah Pelanggan Baru
                                    </a>
                                    <div>
                                        <a href="{{ route('transaksi-servis-bisa-diambil.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
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
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Proses <span class="ml-1 text-indigo-200">{{ $processes_count }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-bisa-diambil.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-indigo-500 text-white duration-150 ease-in-out">Bisa Diambil <span class="ml-1 text-slate-400">{{ $jumlah_bisa_diambil }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-sudah-diambil.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Sudah Diambil <span class="ml-1 text-slate-400">{{ $jumlah_sudah_diambil }}</span></button>
                    </a>
                </li>
                @if (Auth::user()->role == 'Kepala Toko')
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-belum-disetujui.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Belum Disetujui <span class="ml-1 text-slate-400">{{ $jumlah_belum_disetujui }}</span></button>
                    </a>
                </li>
                @endif
            </ul>
        </div>
        <!-- Right side -->
        {{-- <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
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
                                <input type="checkbox" class="form-checkbox" wire:model="kondisi.0" value="Sudah jadi"/>
                                <span class="text-sm font-medium ml-2">Sudah jadi</span>
                            </label>
                        </li>
                        <li class="py-1 px-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="form-checkbox" wire:model="kondisi.1" value="Tidak bisa"/>
                                <span class="text-sm font-medium ml-2">Tidak bisa</span>
                            </label>
                        </li>
                        <li class="py-1 px-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="form-checkbox" wire:model="kondisi.2" value="Dibatalkan"/>
                                <span class="text-sm font-medium ml-2">Dibatalkan</span>
                            </label>
                        </li>
                        <li class="py-1 px-3">
                            <label class="flex items-center">
                                <input type="checkbox" class="form-checkbox" wire:model="kondisi.3" value="Menunggu konfirmasi"/>
                                <span class="text-sm font-medium ml-2">Menunggu konfirmasi</span>
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
        </div> --}}

    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                {{-- Left side --}}
                <h2 class="font-semibold text-slate-800">Bisa Diambil <span class="text-slate-400 font-medium">{{ $jumlah_bisa_diambil }}</span></h2>
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
                <table id="transaksi-servis-table" class="table-auto w-full">
                    <!-- Table header -->
                    <thead  class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            @if (Auth::user()->role == 'Investor')
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox" @click="toggleAll" />
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
                            @if (Auth::user()->role != 'Investor')
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Hubungi</div>
                            </th>
                            @endif
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama Barang</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Kerusakan</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Fungsi</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Kondisi</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Tindakan</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Teknisi</div>
                            </th>
                            @if (Auth::user()->role != 'Investor')
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Modal Sparepart</div>
                            </th>
                            @endif
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Biaya</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Tgl Selesai</div>
                            </th>
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
        {{ $bisadiambil->links() }}
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

            console.log('payload:', payload);


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

