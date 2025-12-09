@section('title')
    Edit Transaksi Servis
@endsection

<x-toko-layout>
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
                            <a href="{{ route('transaksi-servis-belum-disetujui.index') }}" class="text-slate-400 hover:text-slate-500">
                                <div class="sr-only">Close</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Modal content -->
                    <form action="{{ route('transaksi-servis-belum-disetujui.update', $item->id) }}" method="post">
                        @method('PUT')
                        @csrf
                         <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="created_at">Tgl. Terima </label>
                                    <input id="created_at" name="created_at" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="tgl_ambil">Tgl. Ambil </label>
                                    <input id="tgl_ambil" name="tgl_ambil" class="form-input w-full px-2 py-1" type="datetime" value="{{ $item->tgl_ambil }}"/>
                                </div>
                                @if ($item->is_approve != null)
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="tgl_disetujui">Tgl. Disetujui </label>
                                    <input id="tgl_disetujui" name="tgl_disetujui" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->tgl_disetujui)->format('Y-m-d') }}"/>
                                </div>
                                @endif
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
                                    @if ($item->customer != null)
                                        <label class="block text-sm font-medium mb-1" for="customers_id">Nama Pelanggan</label>
                                        <select id="selectjs1" name="customers_id" class="form-select text-sm py-1 w-full">
                                            <option selected value="{{ $item->customer->id }}">{{ $item->customer->nama }}</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->nama }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <label class="block text-sm font-medium mb-1" for="customers_id">Nama Pelanggan</label>
                                        <select id="selectjs1" name="customers_id" class="form-select text-sm py-1 w-full">
                                            <option selected value="">Data pelanggan sudah dihapus</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->nama }}</option>
                                            @endforeach
                                        </select>
                                    @endif
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
                                    <label class="block text-sm font-medium mb-1" for="qc_masuk">Pengecekan Fungsi Masuk</label>
                                    <input id="qc_masuk" name="qc_masuk" class="form-input w-full px-2 py-1" type="text" value="{{ $item->qc_masuk }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="qc_keluar">Pengecekan Fungsi Keluar</label>
                                    <input id="qc_keluar" name="qc_keluar" class="form-input w-full px-2 py-1" type="text" value="{{ $item->qc_keluar }}"/>
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

                                {{-- <div>
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
                                </div> --}}
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="users_id">Penerima/Teknisi </label>
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
                                <div class="mb-4 mt-4 border-t border-slate-200 pt-4">
                                    <label class="block text-sm font-medium mb-2">Tindakan & Biaya Sparepart</label>

                                    <div id="service-container"></div>

                                    <button type="button" id="add-service-row" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white mt-2">
                                        + Tambah Tindakan
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 bg-slate-50 p-3 rounded">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="modal_sparepart">Total Modal Sparepart</label>
                                        <input id="modal_sparepart" name="modal_sparepart" class="form-input w-full px-2 py-1 bg-slate-200 text-slate-500"
                                            type="text" readonly value="{{ $item->modal_sparepart ?? 0 }}"/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="biaya">Total Biaya Servis</label>
                                        <input id="biaya" name="biaya" class="form-input w-full px-2 py-1 bg-slate-200 text-slate-500"
                                            type="text" readonly value="{{ $item->biaya ?? 0 }}"/>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="uang_muka">Uang Muka </label>
                                    <input id="uang_muka" name="uang_muka" class="form-input w-full px-2 py-1" type="number" value="{{ $item->uang_muka }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="diskon">Diskon </label>
                                    <input id="diskon" name="diskon" class="form-input w-full px-2 py-1" type="number"
                                    @if ($item->diskon != null)
                                        value="{{ $item->diskon }}"
                                    @else
                                        value="0"
                                    @endif
                                    />
                                </div>
                                    <div>
                                    <div x-data="{ caraPembayaran: @js($item->cara_pembayaran ?? 'Tunai') }">
                                        <label class="block text-sm font-medium mb-1" for="cara_pembayaran">Cara Pembayaran</label>

                                        <select id="cara_pembayaran" name="cara_pembayaran"
                                            class="form-select text-sm py-1 w-full"
                                            x-model="caraPembayaran">
                                            <option value="Tunai">Tunai</option>
                                            <option value="Transfer">Transfer</option>
                                            <option value="Kredit">Kredit</option>
                                            <option value="Tunai & Transfer">Tunai & Transfer</option>
                                        </select>

                                        <!-- Tunai & Transfer -->
                                        <div x-show="caraPembayaran === 'Tunai & Transfer'" class="mt-3">
                                            <label class="block text-sm font-medium text-indigo-500">
                                                Silahkan isi hanya pada salah satu input saja: Tunai / Transfer
                                            </label>
                                            <div class="flex flex-row gap-3">
                                                <div class="w-1/2">
                                                    <label class="block text-sm font-medium mb-1" for="tunai">Tunai</label>
                                                    <input class="form-input w-full py-1" type="number"
                                                        name="tunai" id="tunai"
                                                        value="{{ $item->tunai ?? 0 }}" />
                                                </div>
                                                <div class="w-1/2">
                                                    <label class="block text-sm font-medium mb-1" for="transfer">Transfer</label>
                                                    <input class="form-input w-full py-1" type="number"
                                                        name="transfer" id="transfer"
                                                        value="{{ $item->transfer ?? 0 }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Kredit -->
                                        <div x-show="caraPembayaran === 'Kredit'" class="mt-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="pay">
                                                    Jumlah Pembayaran <span class="text-rose-500">*</span>
                                                </label>
                                                <input id="pay" name="pay"
                                                    class="form-input w-full px-2 py-1"
                                                    type="number"
                                                    value="{{ old('pay') }}" />
                                            </div>

                                            <div class="flex items-center gap-5 mt-3">
                                                <label class="flex items-center">
                                                    <input name="tunai_check" type="checkbox" value="1"
                                                        class="form-checkbox"
                                                        {{ $item->tunai ? 'checked' : '' }}/>
                                                    <span class="text-sm ml-2">Tunai</span>
                                                </label>

                                                <label class="flex items-center">
                                                    <input name="transfer_check" type="checkbox" value="1"
                                                        class="form-checkbox"
                                                        {{ $item->transfer ? 'checked' : '' }}/>
                                                    <span class="text-sm ml-2">Transfer</span>
                                                </label>
                                            </div>

                                            <label class="block text-sm font-medium mt-3" for="tempo">
                                                Waktu Tempo <span class="text-rose-500">*</span>
                                            </label>
                                            <select id="tempo" name="tempo"
                                                class="form-select w-full mt-1">
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

                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1" for="exp_garansi">Masa Garansi</label>
                                    @if ($item->exp_garansi != null)
                                        <input id="exp_garansi" name="exp_garansi" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->exp_garansi)->format('Y-m-d') }}"/>
                                    @else
                                        <input id="exp_garansi" name="exp_garansi" class="form-input w-full px-2 py-1" type="date" value=""/>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="pengambil">Pengambil</label>
                                    <input id="pengambil" name="pengambil" class="form-input w-full px-2 py-1" type="text" value="{{ $item->pengambil }}"/>
                                </div>
                            </div>
                        </div>
                        <!-- Modal footer -->
                        <div class="px-5 py-4 border-t border-slate-200">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <a href="{{ route('transaksi-servis-belum-disetujui.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
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
    <script>
$(document).ready(function() {

    const serviceActions = @json($service_actions);
    const products = @json($products);
    const salesUsers = @json($sales);
    console.log(salesUsers);


    let existingData = [];

    @if($item->tindakan_servis)
        let rawTindakan = @json(json_decode($item->tindakan_servis));
        let rawModal = @json(json_decode($item->modal_j));
        let rawBiaya = @json(json_decode($item->biaya_j));

        if(Array.isArray(rawTindakan)) {
            rawTindakan.forEach((val, index) => {
                existingData.push({
                    action_val: val, // Bisa ID action atau Text manual
                    modal: rawModal[index] ?? 0,
                    biaya: rawBiaya[index] ?? 0,
                    product_id: '',
                    sales_id: ''
                });
            });
        }
    @endif

    function addRow(data = null) {
        let uniqueId = Date.now() + Math.floor(Math.random() * 1000);

        let actionVal = data ? data.action_val : '';
        let modalVal = data ? formatRupiah(data.modal.toString()) : '';
        let biayaVal = data ? formatRupiah(data.biaya.toString()) : '';
        let productId = data ? data.product_id : '';
        let salesId = data ? data.sales_id : '';

        // Generate Options Action
        let actionOptions = '<option value="">Pilih Tindakan</option>';
        serviceActions.forEach(act => {
            let selected = (act.id == actionVal || act.nama_tindakan == actionVal) ? 'selected' : '';
            actionOptions += `<option value="${act.id}" ${selected}>${act.nama_tindakan}</option>`;
        });

        // Generate Options Product
        let productOptions = '<option value="">Pilih Sparepart</option>';
        products.forEach(prod => {
            let selected = (prod.id == productId) ? 'selected' : '';
            // Simpan harga modal di data-attribute
            productOptions += `<option value="${prod.id}" data-harga_modal="${prod.harga_modal}" ${selected}>${prod.product_name}</option>`;
        });

        // Generate Options Sales
        let salesOptions = '<option value="">Pilih Sales</option>';
        let salesOptions = '<option value="">Tidak ada sales</option>';
        salesUsers.forEach(user => {
            let selected = (user.id == salesId) ? 'selected' : '';
            salesOptions += `<option value="${user.id}" ${selected}>${user.name}</option>`;
        });

        let html = `
        <div class="service-row border border-slate-200 rounded-lg p-4 mb-4 bg-slate-50 shadow-sm" id="row-${uniqueId}">

            <div class="flex justify-between items-start mb-2">
                <label class="block text-sm font-bold text-slate-800">
                    Tindakan Servis <span class="text-rose-500">*</span>
                </label>

                <button type="button"
                        class="text-xs text-red-500 hover:text-white border border-red-500 hover:bg-red-500 font-medium rounded-md px-2 py-1 transition duration-150 ease-in-out flex items-center gap-1"
                        onclick="removeRow('${uniqueId}')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash w-3 h-3" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                       <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                       <path d="M4 7l16 0"></path>
                       <path d="M10 11l0 6"></path>
                       <path d="M14 11l0 6"></path>
                       <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                       <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                    </svg>
                    Hapus
                </button>
            </div>

            <div class="mb-3" x-data="{ showManual: ${actionVal && !Number.isInteger(parseInt(actionVal)) ? 'true' : 'false'} }">

                <div x-show="!showManual">
                    <select name="service_actions_id[]" class="form-select w-full select2-class action-select">
                        ${actionOptions}
                    </select>
                </div>
                <div x-show="showManual" class="mt-1">
                    <input type="text" name="tindakan_servis_manual[]" class="form-input w-full" placeholder="Isi tindakan manual" value="${actionVal}">
                </div>
                <label class="inline-flex items-center mt-2 text-xs text-slate-500 cursor-pointer hover:text-slate-700">
                    <input type="checkbox" class="form-checkbox h-3 w-3 text-indigo-500 rounded border-gray-300" x-model="showManual">
                    <span class="ml-2">Input Manual / Tidak ada di list</span>
                </label>
            </div>

            <div x-data="{ useSparepart: ${productId ? 'true' : 'false'} }" class="border-t border-slate-200 pt-3 mt-3">
                <label class="flex items-center text-sm font-medium mb-3 cursor-pointer">
                    <input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-500 rounded border-gray-300" x-model="useSparepart">
                    <span class="ml-2 text-slate-700">Gunakan Sparepart Toko?</span>
                </label>

                <div x-show="useSparepart" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3 transition-all duration-300 ease-in-out">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Sparepart</label>
                        <select name="products_id[]" class="form-select w-full select2-class product-select">
                            ${productOptions}
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Sales</label>
                        <select name="sales_id[]" class="form-select w-full select2-class">
                            ${salesOptions}
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Garansi</label>
                    <select name="garansi[]" class="form-select w-full text-sm py-2">
                        <option value="">Tidak Ada</option>
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
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Modal Sparepart (Rp)</label>
                    <div class="relative">
                        <input type="number" name="modal_j[]" class="form-input w-full pl-3 pr-2 py-2 format-rupiah input-modal" value="${modalVal}" placeholder="0">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Biaya Jasa (Rp)</label>
                    <div class="relative">
                        <input type="number" name="biaya_j[]" class="form-input w-full pl-3 pr-2 py-2 format-rupiah input-biaya" value="${biayaVal}" placeholder="0">
                    </div>
                </div>
            </div>
        </div>
        `;

        $('#service-container').append(html);

        // Inisialisasi Select2 pada elemen baru
        $(`#row-${uniqueId} .select2-class`).select2({
            width: '100%'
        });
    }

    // --- 3. EVENT LISTENERS ---

    // Tombol Tambah Row
    $('#add-service-row').click(function() {
        addRow();
    });

    // Hapus Row (Global function agar bisa dipanggil dari HTML string)
    window.removeRow = function(id) {
        if(confirm('Hapus baris tindakan ini?')) {
            $(`#row-${id}`).remove();
            calculateGrandTotal();
        }
    }

    // Auto-fill Modal saat Sparepart dipilih
    $(document).on('change', '.product-select', function() {
        let hargaModal = $(this).find(':selected').data('harga_modal') || 0;
        let row = $(this).closest('.service-row');
        row.find('.input-modal').val(formatRupiah(hargaModal.toString()));
        calculateGrandTotal();
    });

    // Format Rupiah saat mengetik (Event Delegation)
    $(document).on('keyup', '.format-rupiah', function() {
        $(this).val(formatRupiah($(this).val()));
        calculateGrandTotal();
    });

    // --- 4. KALKULASI TOTAL ---
    function calculateGrandTotal() {
        let totalModal = 0;
        let totalBiaya = 0;

        $('.input-modal').each(function() {
            // Hapus titik sebelum menjumlahkan
            let val = $(this).val().replace(/\./g, '') || 0;
            totalModal += parseInt(val);
        });

        $('.input-biaya').each(function() {
            let val = $(this).val().replace(/\./g, '') || 0;
            totalBiaya += parseInt(val);
        });

        // Set value ke input readonly (dengan format rupiah)
        $('#modal_sparepart').val(formatRupiah(totalModal.toString()));
        $('#biaya').val(formatRupiah(totalBiaya.toString()));

        // Trigger perubahan untuk logic pembayaran (Tunai/Transfer) yang sudah ada
        $('#biaya').trigger('input');
    }

    // Helper: Format Rupiah (10000 -> 10.000)
    function formatRupiah(angka, prefix) {
        // if(!angka) return '';
        // var number_string = angka.replace(/[^,\d]/g, '').toString(),
        //     split = number_string.split(','),
        //     sisa = split[0].length % 3,
        //     rupiah = split[0].substr(0, sisa),
        //     ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        // if (ribuan) {
        //     separator = sisa ? '.' : '';
        //     rupiah += separator + ribuan.join('.');
        // }

        // rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
        // return prefix == undefined ? rupiah : (rupiah ? 'Rp. ' + rupiah : '');
        return angka;
    }

    // --- 5. SUBMIT FORM HANDLER (PENTING: Hapus titik) ---
    $('form').on('submit', function() {
        // Loop semua input yang punya class 'format-rupiah'
        $('.format-rupiah').each(function() {
            let rawValue = $(this).val().replace(/\./g, ''); // Hapus titik
            $(this).val(rawValue); // Set nilai bersih kembali ke input
        });

        // Bersihkan juga input total readonly agar tersimpan bersih
        let cleanModal = $('#modal_sparepart').val().replace(/\./g, '');
        let cleanBiaya = $('#biaya').val().replace(/\./g, '');
        $('#modal_sparepart').val(cleanModal);
        $('#biaya').val(cleanBiaya);

        return true; // Lanjutkan submit
    });

    // --- 6. INISIALISASI SAAT LOAD ---
    // Load existing data jika ada
    if(existingData.length > 0) {
        existingData.forEach(item => addRow(item));
    } else {
        // Jika data kosong (atau error parse), tambah 1 baris kosong
        addRow();
    }

    // Hitung total awal
    calculateGrandTotal();

    // Format input static (uang muka & diskon) saat load
    $('.format-rupiah').each(function(){
       $(this).val(formatRupiah($(this).val()));
    });

});
</script>

        <script>
       document.addEventListener("DOMContentLoaded", function () {
            const modalJInput = document.getElementById("modal_j");
            const modalSparepartInput = document.getElementById("modal_sparepart");

            function calculateModal() {
                try {
                    let raw = modalJInput.value.trim();

                    // perbaiki kutip miring dan koma aneh
                    raw = raw.replace(/[“”]/g, '"').replace(/‘’/g, "'").replace(/，/g, ",");

                    // parse JSON aman
                    let values = JSON.parse(raw);

                    // pastikan array angka
                    if (!Array.isArray(values)) values = [values];
                    let numbers = values.map(v => parseInt(v) || 0);

                    // jumlahkan
                    let total = numbers.reduce((a, b) => a + b, 0);

                    // taruh ke input modal_sparepart
                    modalSparepartInput.value = total;
                } catch (e) {
                    modalSparepartInput.value = 0;
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


        let ppn = {{ $item->ppn }};

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

        $(document).ready(function () {
            // Kalau user ubah tunai
            $('#tunai').on('input', function () {
                if ($('#cara_pembayaran').val() === 'Tunai & Transfer') {
                    let total = getTotal();
                    let tunai = parseInt($(this).val()) || 0;
                    let transfer = total - tunai;
                    $('#transfer').val(transfer >= 0 ? transfer : 0);
                }
            });

            // Kalau user ubah transfer
            $('#transfer').on('input', function () {
                if ($('#cara_pembayaran').val() === 'Tunai & Transfer') {
                    let total = getTotal();
                    let transfer = parseInt($(this).val()) || 0;
                    let tunai = total - transfer;
                    $('#tunai').val(tunai >= 0 ? tunai : 0);
                }
            });

            // Kalau biaya atau diskon berubah, reset ulang input tunai & transfer
            $('#biaya, #diskon').on('input', function () {
                $('#tunai').trigger('input');
            });

            // Saat cara pembayaran diganti
            $('#cara_pembayaran').on('change', function () {
                if ($(this).val() === 'Tunai & Transfer') {
                    $('#tunai').trigger('input');
                } else {
                    $('#tunai, #transfer').val(0);
                }
            });
        });
        </script>
    @endpush
</x-admin-layout>
