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
                            <a href="{{ route('admin-servis-bisa-diambil.index') }}" class="text-slate-400 hover:text-slate-500">
                                <div class="sr-only">Close</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Modal content -->
                    <form action="{{ route('admin-servis-bisa-diambil.update', $item->id) }}" method="post">
                        @method('PUT')
                        @csrf
                        <input type="hidden" name="persen_admin" value="{{ $item->persen_admin }}">
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="created_at">Tgl. Terima </label>
                                    <input id="created_at" name="created_at" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="penerima">Penerima </label>
                                    <select id="penerima" name="penerima" class="form-select text-sm py-1 w-full">
                                        <option selected value="{{ $item->penerima }}">{{ $item->penerima }}</option>
                                        @foreach ($workers as $worker)
                                            <option value="{{ $worker->name }}">{{ $worker->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="customers_id">Nama Pelanggan </label>
                                    <select id="selectjs1" name="customers_id" class="form-select text-sm py-1 w-full" >
                                        <option selected value="{{ $item->customer->id }}">{{ $item->customer->nama }}</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="types_id">Jenis Barang </label>
                                    <select id="types_id" name="types_id" class="form-select text-sm py-1 w-full" >
                                        <option selected value="{{ $item->type->id }}">{{ $item->type->name }}</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
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
                                    <select id="selectjs2" name="model_series_id" class="form-select text-sm py-1 w-full" >
                                        <option selected value="{{ $item->modelserie->id ?? '' }}">{{ $item->modelserie->name ?? '-' }}</option>
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
                                            <option value="Sudah jadi">Sudah Jadi</option>
                                            <option value="Menunggu konfirmasi">Menunggu Konfirmasi</option>
                                            <option value="Tidak bisa">Tidak Bisa</option>
                                            <option value="Dibatalkan">Dibatalkan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="service_actions_id">Tindakan Servis  </label>
                                    @if ($item->service_actions_id != null)
                                        <select id="selectjs3" name="service_actions_id" class="form-select text-sm py-1 w-full">
                                            @if ($item->serviceaction != null)
                                                <option selected value="{{ $item->serviceaction->id }}">{{ $item->serviceaction->nama_tindakan }}</option>
                                            @else
                                                <option selected value=""></option>
                                            @endif
                                            @foreach ($service_actions as $action)
                                                <option value="{{ $action->id }}">{{ $action->nama_tindakan }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input id="tindakan_servis" name="tindakan_servis" class="form-input w-full px-2 py-1" type="text" value="{{ $item->tindakan_servis }}"/>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="products_id">Sparepart yang digunakan  </label>
                                    <select id="selectjs4" name="products_id" class="form-select text-sm py-1 w-full">
                                        @if ($item->product != null)
                                            <option selected value="{{ $item->product->id }}">{{ $item->product->product_name }}</option>
                                        @else
                                            <option selected value=""></option>
                                        @endif
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->product_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="users_id">Teknisi </label>
                                    <select id="users_id" name="users_id" class="form-select text-sm py-1 w-full" >
                                        @if ($item->user != null)
                                            <option selected value="{{ $item->user->id }}">{{ $item->user->name }}</option>
                                        @else
                                            <option selected value=""></option>
                                        @endif
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                 <div>
                                    <label class="block text-sm font-medium mb-1" for="modal_sparepart">Biaya Modal Sparepart </label>
                                    <input id="modal_j" name="modal_j" class="form-input w-full px-2 py-1" type="text" value="{{ $item->modal_j }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="modal_sparepart">Biaya Pengerjaan Sparepart </label>
                                    <input id="biaya_j" name="biaya_j" class="form-input w-full px-2 py-1" type="text" value="{{ $item->biaya_j }}"/>
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
                                <a href="{{ route('admin-servis-bisa-diambil.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
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
                $('#selectjs3').select2();
                $('#selectjs4').select2();
            });
        </script>
    @endpush
     <script>
        document.addEventListener("DOMContentLoaded", function () {
            const modalJInput = document.getElementById("modal_j");
            const modalSparepartInput = document.getElementById("modal_sparepart");

            function calculateModal() {
                try {
                    // ambil nilai input, parse JSON (["225000","115000","30000"])
                    let values = JSON.parse(modalJInput.value);

                    // pastikan array angka
                    let numbers = values.map(v => parseInt(v) || 0);

                    // jumlahkan
                    let total = numbers.reduce((a, b) => a + b, 0);

                    // taruh ke input modal_sparepart
                    modalSparepartInput.value = total;
                } catch (e) {
                    modalSparepartInput.value = 0; // kalau format salah
                }
            }

            // hitung pertama kali
            calculateModal();

            // update realtime kalau ada perubahan
            modalJInput.addEventListener("input", calculateModal);
        });
        document.addEventListener("DOMContentLoaded", function () {
            const biayaJInput = document.getElementById("biaya_j");
            const biayaSparepartInput = document.getElementById("biaya");

            function calculatebiaya() {
                try {
                    // ambil nilai input, parse JSON (["225000","115000","30000"])
                    let values = JSON.parse(biayaJInput.value);

                    // pastikan array angka
                    let numbers = values.map(v => parseInt(v) || 0);

                    // jumlahkan
                    let total = numbers.reduce((a, b) => a + b, 0);

                    // taruh ke input biaya_sparepart
                    biayaSparepartInput.value = total;
                } catch (e) {
                    biayaSparepartInput.value = 0; // kalau format salah
                }
            }

            // hitung pertama kali
            calculatebiaya();

            // update realtime kalau ada perubahan
            biayaJInput.addEventListener("input", calculatebiaya);
        });
        </script>
</x-admin-layout>
