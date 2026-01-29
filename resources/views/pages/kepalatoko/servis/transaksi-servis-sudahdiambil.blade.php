@section('title')
    Perbarui Status Menjadi Sudah Diambil
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
                <x-search-form placeholder="Cari berdasarkan nomor servis" />

                <!-- Create invoice button -->
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
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
                <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full">
                    <!-- Modal header -->
                    <div class="px-5 py-3 border-b border-slate-200">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-sm text-slate-800">Ubah status untuk nomor servis #{{ $item->nomor_servis }} menjadi <strong>Sudah Diambil</strong></div>
                            <a href="{{ route('transaksi-servis-bisa-diambil.index') }}">
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </a>
                        </div>
                    </div>
                    <!-- Modal content -->
                    <form action="{{ route('ubah-sudah-diambil-update', $item->id) }}" method="post">
                        @csrf
                        <input type="hidden" name="status_servis" value="Sudah Diambil"/>
                        <input type="hidden" name="tgl_ambil" value="<?php echo date('Y-m-d H:i:s') ?>"/>
                        <input type="hidden" name="tgl_disetujui" value="<?php echo date('Y-m-d') ?>"/>
                        <input type="hidden" name="modal_sparepart" value="{{ $item->modal_sparepart }}"/>
                        <input type="hidden" name="biaya" value="{{ $item->biaya }}"/>
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="customers_id">Pelanggan</label>
                                    <input id="customers_id" name="customers_id" class="form-input w-full px-2 py-1 disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" type="text" value="{{ $item->customer->nama }}" disabled />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Nama Barang</label>
                                    <input class="form-input w-full px-2 py-1 disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" type="text" value="{{ $item->nama_barang }}" disabled />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Warna & Kapasitas Barang</label>
                                    <input class="form-input w-full px-2 py-1 disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" type="text" value="{{ $item->warna }} - @if ($item->capacity != null){{ $item->capacity->name }}@endif" disabled />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Kerusakan</label>
                                    <input class="form-input w-full px-2 py-1 disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" type="text" value="{{ $item->kerusakan }}" disabled />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Kondisi Servis</label>
                                    <input class="form-input w-full px-2 py-1 disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" type="text" value="{{ $item->kondisi_servis }}" disabled />
                                </div>
                                {{-- <div>
                                    <label class="block text-sm font-medium mb-1">Tindakan</label>
                                    <input
                                        class="form-input w-full px-2 py-1 disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                                        type="text"
                                        value="{{ !empty($viewTindakan) ? implode(', ', $viewTindakan) : '-' }}"
                                        disabled />
                                </div> --}}
                                <div>
                                    <label class="block text-sm font-medium mb-1">Biaya</label>
                                    <input
                                        class="form-input w-full px-2 py-1 disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed"
                                        type="text" value="{{ number_format($item->biaya) }}" disabled />
                                </div>
                                <input type="hidden" id="total_biaya" value="{{ $item->biaya }}">
                                {{-- <div>
                                    <label class="block text-sm font-medium mb-1">Pengecekan Fungsi Masuk</label>
                                    <input class="form-input w-full px-2 py-1 disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" type="text" value="{{ $item->qc_masuk }}" disabled />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="qc_keluar">Pengecekan Fungsi Keluar <span class="text-rose-500">*</span></label>
                                    <input id="qc_keluar" name="qc_keluar" class="form-input w-full px-2 py-1" type="text" placeholder="Contoh: Tombol, Kamera, Speaker, dll" required/>
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
                                 @if ($item->kondisi_servis != "Sudah jadi")

                                @else
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="diskon">Diskon</label>
                                    <input id="diskon" name="diskon" class="form-input w-full px-2 py-1" type="text" placeholder="Kosongkan jika tidak ada diskon"/>
                                </div>
                                @endif
                                @if ($item->kondisi_servis != "Sudah jadi")

                                @else
                                <div x-data="{ caraPembayaran: 'Tunai' }">
                                    <label class="block text-sm font-medium mb-1" for="cara_pembayaran">Cara Pembayaran</label>
                                    <select id="cara_pembayaran" name="cara_pembayaran" class="form-select text-sm py-1 w-full" x-model="caraPembayaran">
                                        <option selected value="Tunai">Tunai</option>
                                        <option value="Transfer">Transfer</option>
                                        <option value="Kredit">Kredit</option>
                                        <option value="Tunai & Transfer">Tunai & Transfer</option>
                                        @foreach (getMetodePembayaran() as $mp)
                                        <option value="{{ $mp->nama }}">{{  $mp->nama  }}</option>
                                        @endforeach
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
                                @endif

                                @if ($item->kondisi_servis != 'Sudah jadi')
                                @else
                                    @if (json_decode($item->tindakan_servis) == null)
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
                                    @else
                                        @foreach ($teknisiServis as $tek)
                                            @php
                                                $tindakanList = json_decode($tek->tindakan_servis);
                                                if (is_string($tindakanList)) {
                                                    $tindakanList = json_decode($tindakanList);
                                                }
                                                if (!is_array($tindakanList)) {
                                                    $tindakanList = [];
                                                }

                                                $tekId = $tek->id ?? 'main';
                                            @endphp

                                            @foreach ($tindakanList as $index => $tindakanItem)
                                                <div>
                                                    <label class="block text-sm font-medium mb-1" for="garansi_{{ $tekId }}_{{ $index }}">
                                                        Garansi {{ $tindakanItem }}
                                                        {{-- <span class="text-xs text-slate-500">({{ $tek->user->name ?? 'Teknisi' }})</span> --}}
                                                    </label>

                                                    <select name="garansi_teknisi[{{ $tekId }}][]"
                                                            id="garansi_{{ $tekId }}_{{ $index }}"
                                                            class="form-select text-sm py-1 w-full">
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
                                            @endforeach
                                        @endforeach
                                    @endif
                                @endif
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="pengambil">Pengambil <span class="text-rose-500">*</span></label>
                                    <input id="pengambil" name="pengambil" class="form-input w-full px-2 py-1" type="text" required/>
                                </div>
                            </div>
                        </div>
                        <!-- Modal footer -->
                        <div class="px-5 py-4 border-t border-slate-200">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <a href="{{ route('transaksi-servis.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
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
</x-toko-layout>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
{{-- @php
    $ppn = 0;
    $cekPPN = \App\Models\StoreSetting::where('cabang_id',getCabangId())->first();
    if (!empty($cekPPN)) {
        if ($cekPPN->is_tax == 1) {
            $ppn = $cekPPN->ppn;
        }else{
            $ppn = 0;
        }
    }
@endphp
<script>
    $(document).ready(function () {
        // Ambil total dari input yang disabled (karena kamu format pakai number_format)
        let totalBiaya = parseInt($('#total_biaya').val()) || 0;


        function updateSisa(from, to) {
            let diskon = parseInt($('#diskon').val()) || 0;

            let fromVal = parseInt($(from).val()) || 0;
            let finalvalTotal = totalBiaya - diskon;
            if (fromVal > finalvalTotal) {
                fromVal = finalvalTotal;
                $(from).val(fromVal);
            }

            // Hitung sisa dan masukkan ke input lawan
            $(to).val(finalvalTotal - fromVal);
        }

        $('#tunai').on('input', function () {
            updateSisa('#tunai', '#transfer');
        });

        $('#transfer').on('input', function () {
            updateSisa('#transfer', '#tunai');
        });
    });
</script> --}}

@php
    $ppn = 0;
    $cekPPN = \App\Models\StoreSetting::where('cabang_id',getCabangId())->first();
    if (!empty($cekPPN)) {
        if ($cekPPN->is_tax == 1 && $cekPPN->ppn != 0) {
            $ppn = $cekPPN->ppn;
        } else {
            $ppn = 0;
        }
    }
@endphp

<script>
    // --- HELPER FUNCTIONS ---
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

    // --- LOGIC ---
    $(document).ready(function() {

        // 1. Ambil total (Gunakan parseRupiah jaga-jaga jika inputnya sudah ada format titik)
        let totalBiaya = parseRupiah($('#total_biaya').val());

        // 2. Tambahkan PPN kalau ada
        let ppn = {{ $ppn }};
        if (ppn !== 0) {
            totalBiaya = totalBiaya + Math.round((totalBiaya * ppn) / 100);
        }

        // Fungsi Update Kalkulasi
        function updateSisa(from, to) {
            // Gunakan parseRupiah untuk mengambil nilai angka murni
            let diskon = parseRupiah($('#diskon').val());
            let fromVal = parseRupiah($(from).val());

            let finalvalTotal = totalBiaya - diskon;

            // Pastikan tidak minus
            if (finalvalTotal < 0) finalvalTotal = 0;

            // Cek jika input melebihi total tagihan
            if (fromVal > finalvalTotal) {
                fromVal = finalvalTotal;
            }

            // Format ulang input 'from' agar titiknya muncul saat mengetik
            $(from).val(formatRupiah(fromVal));

            // Hitung sisa
            let sisa = finalvalTotal - fromVal;

            // Masukkan sisa ke input 'to' dengan format rupiah
            $(to).val(formatRupiah(sisa));
        }

        // --- Event Listeners ---

        $('#tunai').on('input', function() {
            updateSisa('#tunai', '#transfer');
        });

        $('#transfer').on('input', function() {
            updateSisa('#transfer', '#tunai');
        });

        // Tambahan: Jika diskon berubah, hitung ulang (misal based on Tunai)
        $('#diskon').on('input', function() {
            $(this).val(formatRupiah($(this).val())); // Format input diskon juga
            updateSisa('#tunai', '#transfer');
        });
    });
</script>

