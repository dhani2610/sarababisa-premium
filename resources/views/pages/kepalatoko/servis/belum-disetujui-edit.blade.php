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
                                {{-- <div>
                                    <label class="block text-sm font-medium mb-1" for="qc_masuk">Pengecekan Fungsi Masuk</label>
                                    <input id="qc_masuk" name="qc_masuk" class="form-input w-full px-2 py-1" type="text" value="{{ $item->qc_masuk }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="qc_keluar">Pengecekan Fungsi Keluar</label>
                                    <input id="qc_keluar" name="qc_keluar" class="form-input w-full px-2 py-1" type="text" value="{{ $item->qc_keluar }}"/>
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

                                 <div class="px-5 py-4">
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
                                                <input class="form-input w-full px-2 py-1 bg-white" type="number" name="total_modal_sparepart" id="total_modal_sparepart" required value="0" />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="biaya">Total Biaya Servis (Ke Pelanggan) <span class="text-rose-500">*</span></label>
                                                <input class="form-input w-full px-2 py-1 bg-white font-bold text-lg" type="number" name="biaya" id="biaya" required  value="0"/>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="catatan">Catatan <small>(Kosongkan jika tidak perlu)</small></label>
                                            <textarea id="catatan" name="catatan" class="form-textarea w-full px-2 py-1" rows="2" placeholder="Tulis catatan untuk pelanggan..."></textarea>
                                        </div>

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

                                {{-- <div>
                                    <label class="block text-sm font-medium mb-1" for="exp_garansi">Masa Garansi</label>
                                    @if ($item->exp_garansi != null)
                                        <input id="exp_garansi" name="exp_garansi" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->exp_garansi)->format('Y-m-d') }}"/>
                                    @else
                                        <input id="exp_garansi" name="exp_garansi" class="form-input w-full px-2 py-1" type="date" value=""/>
                                    @endif
                                </div> --}}
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
    // Struktur: [ArrayStandar, ObjectMasuk, ObjectKeluar]
    const fullData = @json([$qcItems, $qcMasuk, $qcKeluar]);

    // Pecah data ke variabel biar mudah dibaca
    const standardItems = fullData[0];       // List Nama Item
    const dataMasuk     = fullData[1] || {}; // Data Value Masuk
    const dataKeluar    = fullData[2] || {}; // Data Value Keluar

    document.addEventListener('DOMContentLoaded', function() {
        renderAllChecklists();
    });

    // --- FUNGSI RENDER UTAMA ---
    function renderAllChecklists() {
        const tbodyTab2 = document.getElementById('checklist-tbody-tab2');
        tbodyTab2.innerHTML = '';

        standardItems.forEach((item, index) => {
            let valMasuk = dataMasuk[item] || '';
            let valKeluar = dataKeluar[item] || '';

            if(valMasuk === null) valMasuk = '';
            if(valKeluar === null) valKeluar = '';

            const tr = document.createElement('tr');
            tr.className = "border-b border-slate-200 hover:bg-slate-50";

            tr.innerHTML = `
                <td class="border border-slate-300 p-1 text-center font-bold row-num">${index + 1}</td>
                <td class="border border-slate-300 p-1 font-medium bg-slate-50">${item}</td>
                <td class="border border-slate-300 p-0">
                    <input type="text" name="qc_masuk[${item}]" value="${valMasuk}" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
                </td>
                <td class="border border-slate-300 p-0">
                    <input type="text" name="qc_keluar[${item}]" value="${valKeluar}" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
                </td>
                <td class="border border-slate-300 p-1 text-center">
                    <button type="button" class="text-rose-500 hover:text-rose-700" onclick="deleteRow(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    </button>
                </td>
            `;
            tbodyTab2.appendChild(tr);
        });

        const allKeys = new Set([...Object.keys(dataMasuk), ...Object.keys(dataKeluar)]);

        allKeys.forEach(key => {
            if (!standardItems.includes(key)) {
                let valMasuk = dataMasuk[key] || '';
                let valKeluar = dataKeluar[key] || '';

                addCustomRowTab2(key, valMasuk, valKeluar);
            }
        });
    }

    // --- MODIFIKASI FUNGSI CUSTOM ROW ---
    // Tambahkan parameter agar bisa diisi value-nya saat load data
    function addCustomRowTab2(name = '', valMasuk = '', valKeluar = '') {
        const tbody = document.getElementById('checklist-tbody-tab2');
        const rowCount = tbody.rows.length + 1;
        const tr = document.createElement('tr');
        tr.className = "border-b border-slate-200 hover:bg-yellow-50";

        tr.innerHTML = `
            <td class="border border-slate-300 p-1 text-center font-bold row-num">${rowCount}</td>
            <td class="border border-slate-300 p-0">
                <input type="text" name="custom_item_name[]" value="${name}" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent font-medium text-indigo-600" placeholder="Ketik Nama Item..." required>
            </td>
            <td class="border border-slate-300 p-0">
                <input type="text" name="custom_qc_masuk[]" value="${valMasuk}" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
            </td>
            <td class="border border-slate-300 p-0">
                <input type="text" name="custom_qc_keluar[]" value="${valKeluar}" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
            </td>
            <td class="border border-slate-300 p-1 text-center">
                <button type="button" class="text-rose-500 hover:text-rose-700" onclick="deleteRow(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    function deleteRow(btn) {
        const row = btn.closest('tr');
        const tbody = row.parentNode;
        row.remove();
        Array.from(tbody.rows).forEach((r, index) => {
            const numCell = r.querySelector('.row-num');
            if(numCell) numCell.innerText = index + 1;
        });
    }
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
        salesOptions += '<option value="">Tidak ada sales</option>';
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
  <script src="https://code.jquery.com/jquery-3.7.0.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function () {

                // --- 1. TERIMA DATA DARI CONTROLLER ---
                const existingData = @json($teknisiServis ?? []);
                // Master data tindakan untuk pengecekan validitas ID
                const masterActions = @json($service_actions);
                let groupCounter = 0;

                // --- TEMPLATES (PHP BLADE RENDERED) ---
                const teknisiOptions = `
                    <option selected value="">Pilih Teknisi</option>
                    @foreach ($users as $user) <option value="{{ $user->id }}">{{ $user->name }}</option> @endforeach
                `;

                // Option dropdown tindakan
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

                // --- HTML GENERATOR ---
                // Parameter tambahan: isManual (boolean), manualValue (string text tindakan)
                function generateActionHtml(groupIndex, actionIndex, isManual = false, manualValue = '') {
                    const uniqueRadioId = 'radio_' + actionIndex;

                    // Logic display element berdasarkan isManual
                    const displaySelect = isManual ? 'none' : 'block';
                    const displayInput = isManual ? 'block' : 'none';
                    const checkedState = isManual ? 'true' : 'false'; // string 'true' untuk x-data alpine

                    return `
                    <div class="action-item" x-data="{ useSparepart: false, showInputManual: ${checkedState} }">
                        <button type="button" class="position-button-x remove-action absolute top-2 right-2 text-rose-500 hover:text-rose-700 font-bold" title="Hapus">&times;</button>
                        <div class="mb-2 pr-6">
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-sm font-medium">Tindakan Servis <span class="text-rose-500">*</span></label>
                                <label class="flex items-center">
                                    <input type="checkbox" class="form-checkbox checkbox-manual" x-model="showInputManual"/>
                                    <span class="text-sm ml-2">Isi Manual</span>
                                </label>
                            </div>

                            <div x-show="!showInputManual" class="wrapper-select-action" style="display:${displaySelect}">
                                <select name="teknisi[${groupIndex}][tindakan][${actionIndex}][service_actions_id]" class="form-select text-sm py-1 w-full selectAction">
                                    ${actionOptions}
                                </select>
                            </div>

                            <div x-show="showInputManual" class="mt-2 wrapper-input-manual" style="display:${displayInput}">
                                <input class="form-input w-full px-2 py-1 input-manual-text" type="text"
                                    name="teknisi[${groupIndex}][tindakan][${actionIndex}][tindakan_servis]"
                                    placeholder="Ketik manual..."
                                    value="${manualValue}"/>
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
                                <div><label class="block text-sm font-medium mb-1">Modal Part <span class="text-rose-500">*</span></label><input class="form-input w-full px-2 py-1 modal_sparepart" type="number" name="teknisi[${groupIndex}][tindakan][${actionIndex}][modal_sparepart]" value="0" required /></div>
                                <div><label class="block text-sm font-medium mb-1">Biaya Servis <span class="text-rose-500">*</span></label><input class="form-input w-full px-2 py-1 biaya_servis" type="number" name="teknisi[${groupIndex}][tindakan][${actionIndex}][biaya_servis]" value="0" required /></div>
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

                    // --- LOGIC DETEKSI MANUAL ---
                    let isManual = false;
                    let manualText = '';

                    if (actionData) {
                        // Logic: Jika act_id tidak ada di masterActions atau null => Manual
                        const existsInMaster = masterActions.some(act => act.id == actionData.act_id);

                        if (!actionData.act_id || actionData.act_id === 'null' || !existsInMaster) {
                            isManual = true;
                            // Ambil text dari parameter manual_text yang dikirim
                            manualText = actionData.manual_text || '';
                        }
                    }

                    const html = generateActionHtml(groupIndex, actionIndex, isManual, manualText);
                    const $newItem = $(html);
                    $groupElement.find('.actions-list-container').append($newItem);

                    const $selAction = $newItem.find('.selectAction').select2();
                    const $selSparepart = $newItem.find('.selectSparepart').select2();
                    const $selSales = $newItem.find('.selectSales').select2();

                    if (actionData) {
                        $newItem.find('.biaya_servis').val(actionData.biaya || 0);
                        $newItem.find('.modal_sparepart').val(actionData.modal || 0);

                        // Set value dropdown jika BUKAN manual
                        if (!isManual && actionData.act_id && actionData.act_id !== "null") {
                            $selAction.val(actionData.act_id).trigger('change.select2');
                        }

                        if (actionData.prod_id && actionData.prod_id != "null" && actionData.prod_id != "") {
                            setTimeout(() => { $newItem.find('.radio-sparepart-yes')[0].click(); }, 50);
                            setTimeout(() => {
                                $selSparepart.val(actionData.prod_id).trigger('change.select2');
                                const hargaModalOtomatis = $selSparepart.find(':selected').data('harga_modal') || 0;
                                if(actionData.modal > 0) {
                                    $newItem.find('.modal_sparepart').val(actionData.modal);
                                } else {
                                    $newItem.find('.modal_sparepart').val(hargaModalOtomatis);
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
                        let actionsArr = [], productsArr = [], biayaArr = [], modalArr = [], manualArr = [];
                        try {
                            actionsArr = JSON.parse(tech.service_actions) || [];
                            productsArr = JSON.parse(tech.products) || [];
                            biayaArr = JSON.parse(tech.biaya_j) || [];
                            modalArr = JSON.parse(tech.modal_j) || [];

                            // Parsing nama tindakan (untuk case manual)
                            manualArr = JSON.parse(tech.tindakan_servis) || [];
                        } catch (e) { console.error(e); }

                        if (actionsArr.length > 0) {
                            actionsArr.forEach(function(actId, i) {
                                const detailData = {
                                    act_id: actId,
                                    prod_id: productsArr[i] ?? null,
                                    biaya: biayaArr[i] ?? 0,
                                    modal: modalArr[i] ?? 0,
                                    manual_text: manualArr[i] ?? '' // Kirim teks manual ke fungsi addAction
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
                                $container.find('.biaya_servis').val(data.biaya);
                                recalculateAll();
                            }
                        });
                    }
                });

                $(document).on('change', '.selectSparepart', function() {
                    const $select = $(this);
                    const $container = $select.closest('.action-item');
                    const hargaModal = $select.find(':selected').data('harga_modal') || 0;
                    $container.find('.modal_sparepart').val(hargaModal);
                    recalculateAll();
                });

                $(document).on('input', '.biaya_servis, .modal_sparepart', function() { recalculateAll(); });

                function recalculateAll() {
                    let totalBiaya = 0;
                    let totalModal = 0;
                    $('.biaya_servis').each(function() { totalBiaya += parseFloat($(this).val()) || 0; });
                    $('.modal_sparepart').each(function() { totalModal += parseFloat($(this).val()) || 0; });
                    $('#biaya').val(totalBiaya);
                    $('#total_modal_sparepart').val(totalModal);
                }
            });
        </script>
    @endpush
</x-admin-layout>
