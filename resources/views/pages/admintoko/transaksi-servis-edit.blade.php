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
                            <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="hidden xs:block ml-2">Tambah Transaksi Baru</span>
                </button>

            </div>

        </div>
        <div x-data="{ modalOpen: true }">
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
                id="edit-modal"
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
                            <div class="font-semibold text-slate-800">Edit Transaksi Servis</div>
                            <a href="{{ route('admin-transaksi-servis.index') }}" class="text-slate-400 hover:text-slate-500">
                                <div class="sr-only">Close</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Modal content -->
                    <form action="{{ route('admin-transaksi-servis.update', $item->id) }}" method="post">
                        @method('PUT')
                        @csrf
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="created_at">Tgl. Terima</label>
                                    <input id="created_at" name="created_at" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="customers_id">Nama Pelanggan</label>
                                    <select id="selectjs1" name="customers_id" class="form-select text-sm py-1 w-full">
                                        <option selected value="{{ $item->customer->id }}">{{ $item->customer->nama }}</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="types_id">Jenis Barang</label>
                                    <select id="types_id" name="types_id" class="form-select text-sm py-1 w-full">
                                        <option selected value="{{ $item->type->id }}">{{ $item->type->name }}</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="brands_id">Merek</label>
                                    <select id="brands_id" name="brands_id" class="form-select text-sm py-1 w-full">
                                        <option selected value="{{ $item->brand->id }}">{{ $item->brand->name }}</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="model_series_id">Model Seri</label>
                                    <select id="selectjs2" name="model_series_id" class="form-select text-sm py-1 w-full">
                                        <option selected value="{{ $item->modelserie->id ?? '' }}">{{ $item->modelserie->name ?? '-' }}</option>
                                        @foreach ($model_series as $model_serie)
                                            <option value="{{ $model_serie->id }}">{{ $model_serie->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="imei">Nomor Imei</label>
                                    <input id="imei" name="imei" class="form-input w-full px-2 py-1" type="text" value="{{ $item->imei }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="warna">Warna</label>
                                    <input id="warna" name="warna" class="form-input w-full px-2 py-1" type="text" value="{{ $item->warna }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="capacities_id">Kapasitas</label>
                                    <select id="capacities_id" name="capacities_id" class="form-select text-sm py-1 w-full">
                                        <option selected value="{{ $item->capacity->id }}">{{ $item->capacity->name }}</option>
                                        @foreach ($capacities as $capacity)
                                            <option value="{{ $capacity->id }}">{{ $capacity->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="kelengkapan">Kelengkapan</label>
                                    <input id="kelengkapan" name="kelengkapan" class="form-input w-full px-2 py-1" type="text" placeholder="Kosongkan jika kelengkapannya hanya barang" value="{{ $item->kelengkapan }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="kerusakan">Kerusakan</label>
                                    <input id="kerusakan" name="kerusakan" class="form-input w-full px-2 py-1" type="text" value="{{ $item->kerusakan }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="qc_masuk">Pengecekan Fungsi</label>
                                    <input id="qc_masuk" name="qc_masuk" class="form-input w-full px-2 py-1" type="text" value="{{ $item->qc_masuk }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="estimasi_pengerjaan">Estimasi Pengerjaan</label>
                                    <select id="estimasi_pengerjaan" name="estimasi_pengerjaan" class="form-select text-sm py-1 w-full">
                                        <option selected value="{{ $item->estimasi_pengerjaan }}">{{ $item->estimasi_pengerjaan }}</option>
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
                                        <input id="estimasi_biaya" name="estimasi_biaya" class="form-input w-full pl-10 px-2 py-1" type="number" value="{{ $item->estimasi_biaya }}"/>
                                        <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                            <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="uang_muka">DP/Uang Muka</label>
                                    <div class="relative">
                                        <input id="uang_muka" name="uang_muka" class="form-input w-full pl-10 px-2 py-1" type="number" placeholder="Kosongkan jika tidak ada" value="{{ $item->uang_muka }}"/>
                                        <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                            <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="penerima">Penerima</label>
                                    <select id="penerima" name="penerima" class="form-select text-sm py-1 w-full">
                                        <option selected value="{{ $item->penerima }}">{{ $item->penerima }}</option>
                                        @foreach ($workers as $worker)
                                            <option value="{{ $worker->name }}">{{ $worker->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- Modal footer -->
                        <div class="px-5 py-4 border-t border-slate-200">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <a href="{{ route('admin-transaksi-servis.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                    Batal
                                </a>
                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

     @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#selectjs1').select2();
                $('#selectjs2').select2();
            });
        </script>
    @endpush
</x-admin-layout>
