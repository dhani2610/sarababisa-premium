@section('title')
    Edit Transaksi Produk
@endsection

<x-admin-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Transaksi Produk ✨</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Search form -->
                <x-search-form placeholder="Masukkan nama pelanggan" />

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
                            <div class="font-semibold text-slate-800">Edit Transaksi Produk</div>
                            <a href="{{ route('admin-transaksi-produk.index') }}" class="text-slate-400 hover:text-slate-500">
                                <div class="sr-only">Close</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Modal content -->
                    <form action="{{ route('admin-transaksi-produk.update', $item->id) }}" method="post">
                        @method('PUT')
                        @csrf
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="order_date">Tgl. Transaksi </label>
                                    <input id="order_date" name="order_date" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->order_date)->format('Y-m-d') }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="customers_id">Nama Pelanggan </label>
                                    <select id="customers_id" name="customers_id" class="form-select text-sm py-1 w-full" >
                                        <option selected value="{{ $item->customer->id }}">{{ $item->customer->nama }}</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="payment_method">Metode Pembayaran</label>
                                    <select id="payment_method" name="payment_method" class="form-select text-sm py-1 w-full" >
                                        <option selected value="{{ $item->payment_method }}">{{  $item->payment_method }}</option>
                                        <option value="Tunai">Tunai</option>
                                        <option value="Transfer">Transfer</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="brands_id">Merek </label>
                                    <select id="brands_id" name="brands_id" class="form-select text-sm py-1 w-full" >
                                        <option selected value="{{ $item->brand->id }}">{{ $item->brand->name }}</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="model_series_id">Model Seri </label>
                                    <select id="model_series_id" name="model_series_id" class="form-select text-sm py-1 w-full" >
                                        <option selected value="{{ $item->modelserie->id }}">{{ $item->modelserie->name ?? '-' }}</option>
                                        @foreach ($model_series as $model_serie)
                                            <option value="{{ $model_serie->id }}">{{ $model_serie->name }}</option>
                                        @endforeach
                                    </select>
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
                                    <label class="block text-sm font-medium mb-1" for="kondisi_servis">Kondisi Servis  </label>
                                    <select id="kondisi_servis" name="kondisi_servis" class="form-select text-sm py-1 w-full" >
                                            <option selected value="{{ $item->kondisi_servis }}">{{ $item->kondisi_servis }}</option>
                                            <option value="Sudah Jadi">Sudah Jadi</option>
                                            <option value="Tidak Bisa">Tidak Bisa</option>
                                            <option value="Dibatalkan">Dibatalkan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="service_actions_id">Tindakan Servis  </label>
                                    <select id="service_actions_id" name="service_actions_id" class="form-select text-sm py-1 w-full">
                                        <option selected value="{{ $item->serviceaction->id }}">{{ $item->serviceaction->nama_tindakan }}</option>
                                        @foreach ($service_actions as $action)
                                            <option value="{{ $action->id }}">{{ $action->nama_tindakan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="users_id">Teknisi </label>
                                    <select id="users_id" name="users_id" class="form-select text-sm py-1 w-full" >
                                        <option selected value="{{ $item->user->id }}">{{ $item->user->name }}</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="biaya">Biaya Servis </label>
                                    <input id="biaya" name="biaya" class="form-input w-full px-2 py-1" type="number" value="{{ $item->biaya }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="modal_sparepart">Modal Sparepart </label>
                                    <input id="modal_sparepart" name="modal_sparepart" class="form-input w-full px-2 py-1" type="number" value="{{ $item->modal_sparepart }}"/>
                                </div>
                            </div>
                        </div>
                        <!-- Modal footer -->
                        <div class="px-5 py-4 border-t border-slate-200">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <a href="{{ route('admin-transaksi-servis-bisa-diambil.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
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
</x-admin-layout>
