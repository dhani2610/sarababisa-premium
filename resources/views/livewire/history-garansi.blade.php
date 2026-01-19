@section('title')
    Riwayat Garansi
@endsection

{{-- <x-toko-layout> --}}
    <style>
        .btn-status {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Menunggu Konfirmasi (Kuning) */
        .status-menunggu { background-color: #FFF7D1; color: #B58105; }
        /* Sudah Selesai (Hijau) */
        .status-selesai { background-color: #D1FADF; color: #027A48; }
        /* Dibatalkan (Merah) */
        .status-batal { background-color: #FEE2E2; color: #B91C1C; }

        /* Select2 Fix for Modal */
        .select2-container { z-index: 999999 !important; }

        .dataTables_wrapper .dataTables_length select {
            padding-right: 30px;
            width: auto;
        }
    </style>

    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-3">

            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Riwayat Garansi ✨</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                <div class="relative inline-flex" x-data="{ modalOpen: false }">
                    <button class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500 hover:text-slate-600 mb-2 md:mb-0" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                        <span class="sr-only">Print</span><wbr>
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                            <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                            <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                            <rect x="7" y="13" width="10" height="8" rx="2" />
                        </svg>
                    </button>
                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-out duration-100" x-cloak></div>
                    <div id="tambah-modal" class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-cloak>
                        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false">
                            <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Atur Pencetakan Riwayat Garansi</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">&times;</button>
                            </div>
                            <form action="{{ route('history-garansi.cetak') }}" method="get" target="_blank">
                                @csrf
                                <div class="px-5 py-4 space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Mulai tanggal <span class="text-rose-500">*</span></label>
                                        <input id="start_date" name="start_date" class="form-input w-full py-2" type="date" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Sampai tanggal <span class="text-rose-500">*</span></label>
                                        <input id="end_date" name="end_date" class="form-input w-full py-2" type="date" required />
                                    </div>
                                </div>
                                <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Cetak</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endif

                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                        + Tambah Riwayat Garansi
                    </button>
                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition x-cloak></div>
                    <div id="tambah-modal-data" class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition x-cloak>
                        <div class="bg-white rounded shadow-lg overflow-auto max-w-2xl w-full max-h-full" @click.outside="if(!$event.target.closest('.select2-container')) modalOpen = false" @keydown.escape.window="modalOpen = false">
                            <form action="{{ route('history-garansi.store') }}" method="POST" id="formGaransi">
                                @csrf
                                <div class="px-5 py-4 space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Tanggal <span class="text-rose-500">*</span></label>
                                        <input type="date" name="date" id="date" class="form-input w-full" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Nomor Servis <span class="text-rose-500">*</span></label>
                                        <select name="service_id" id="service_id" class="form-select select2 w-full" required>
                                            <option value="">-- Pilih Nomor Service --</option>
                                            @foreach ($serviceTransactions as $st)
                                                <option value="{{ $st->id }}" data-teknisi="{{ $st->user->name ?? '' }}" data-expired="{{ $st->exp_garansi }}" data-nota="{{ route('kepalatoko-pengambilan-cetak-inkjet', $st->id) }}">
                                                    {{ $st->nomor_servis }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-slate-500">Teknisi sebelumnya: <span id="prev_teknisi"></span></small><br>
                                        <small class="text-slate-500">Exp Garansi: <span id="exp_garansi"></span></small><br>
                                        <small class="text-slate-500">Link Nota: <span id="link_nota"></span></small>
                                        <div id="history_garansi" class="mt-2 text-sm text-slate-700"></div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Pelanggan <span class="text-rose-500">*</span></label>
                                        <select name="id_customer" class="form-select select2 w-full" required>
                                            <option value="">-- Pilih Pelanggan --</option>
                                            @foreach ($customer as $cs)
                                                <option value="{{ $cs->id }}">{{ $cs->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Penerima <span class="text-rose-500">*</span></label>
                                        <select name="penerima_id" class="form-select w-full" required>
                                            <option value="">-- Pilih Penerima --</option>
                                            @foreach ($users as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Keluhan <span class="text-rose-500">*</span></label>
                                        <input type="text" name="keluhan" class="form-input w-full" required>
                                    </div>
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
                                                        <th class="border border-slate-300 p-2 w-8 text-center"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="checklist-tbody-tab1" class="text-slate-700"></tbody>
                                            </table>
                                        </div>
                                        <button type="button" onclick="addCustomRowTab1()" class="mt-2 text-xs flex items-center text-indigo-600 font-bold hover:text-indigo-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                            Tambah Baris Custom
                                        </button>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="estimasi_pengerjaan">Estimasi Pengerjaan</label>
                                        <select id="estimasi_pengerjaan" name="estimasi_pengerjaan" class="form-select text-sm py-2 w-full">
                                            <option selected value="">Pilih Estimasi Pengerjaan</option>
                                            <option value="1 Hari">1 Hari</option>
                                            <option value="2 Hari">2 Hari</option>
                                            <option value="3 Hari">3 Hari</option>
                                            <option value="1 Minggu">1 Minggu</option>
                                            <option value="1 Bulan">1 Bulan</option>
                                            </select>
                                    </div>
                                </div>
                                <div class="px-5 py-4 border-t flex justify-end space-x-2">
                                    <button type="button" class="btn-sm border-slate-200" @click="modalOpen=false">Batal</button>
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sm:flex sm:justify-between sm:items-center mb-5">
            <div class="mb-4 sm:mb-0">
                <ul class="flex flex-wrap -m-1" id="status-filters">
                    <li class="m-1">
                        <button class="filter-btn inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border shadow-sm bg-indigo-500 text-white" data-status="">
                            Semua <span class="ml-1 text-slate-200">{{ $count }}</span>
                        </button>
                    </li>
                    <li class="m-1">
                        <button class="filter-btn inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border shadow-sm bg-white text-slate-500 border-slate-200" data-status="1">
                            Proses <span class="ml-1 text-slate-400">{{ $prosesCount }}</span>
                        </button>
                    </li>
                    <li class="m-1">
                        <button class="filter-btn inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border shadow-sm bg-white text-slate-500 border-slate-200" data-status="2">
                            Selesai <span class="ml-1 text-slate-400">{{ $selesaiCount }}</span>
                        </button>
                    </li>
                    <li class="m-1">
                        <button class="filter-btn inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border shadow-sm bg-white text-slate-500 border-slate-200" data-status="3">
                            Dibatalkan <span class="ml-1 text-slate-400">{{ $dibatalkanCount }}</span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 px-4 py-2 rounded-sm text-sm bg-rose-500 text-white">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Riwayat Garansi</h2>
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count">0</span> item yang dipilih</div>
                            <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" onclick="deleteSelected()">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto p-4">
                <table id="history-garansi-table" class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 py-3 w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox" />
                                    </label>
                                </div>
                            </th>
                            <th class="px-2 py-3">No.</th>
                            <th class="px-2 py-3">Tgl Masuk</th>
                            <th class="px-2 py-3">Tgl Selesai</th>
                            <th class="px-2 py-3">Nomor Servis</th>
                            <th class="px-2 py-3">Pelanggan</th>
                            <th class="px-2 py-3">Penerima</th>
                            <th class="px-2 py-3">Keluhan</th>
                            <th class="px-2 py-3">QC</th>
                            <th class="px-2 py-3">Teknisi</th>
                            <th class="px-2 py-3">Tindakan</th>
                            <th class="px-2 py-3">Sparepart</th>
                            <th class="px-2 py-3">Total Modal</th>
                            <th class="px-2 py-3">Catatan</th>
                            <th class="px-2 py-3">Status</th>
                            @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                            <th class="px-2 py-3">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200"></tbody>
                </table>
            </div>
        </div>

    </div>

    <div id="delete-modal" class="fixed inset-0 z-50 hidden overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6">
        <div class="fixed inset-0 bg-slate-900 bg-opacity-30 transition-opacity" onclick="closeDeleteModal()"></div>
        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full relative z-10">
            <div class="p-5 flex space-x-4">
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                    <svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16"><path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" /></svg>
                </div>
                <div>
                    <div class="mb-2"><div class="text-lg font-semibold text-slate-800">Apakah anda sudah yakin?</div></div>
                    <div class="text-sm mb-10"><p>Jika sudah terhapus, maka data tidak bisa dikembalikan lagi.</p></div>
                    <div class="flex flex-wrap justify-end space-x-2">
                        <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" onclick="closeDeleteModal()">Batal</button>
                        <form id="delete-form" action="" method="post">
                            @method('delete')
                            @csrf
                            <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- </x-toko-layout> --}}

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

{{-- @push('scripts') --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            var currentStatus = '';

            var table = $('#history-garansi-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('history-garansi.data') }}",
                    data: function (d) {
                        d.status = currentStatus;
                    }
                },
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'date', name: 'date' },
                    { data: 'tgl_selesai', name: 'tgl_selesai' },
                    { data: 'nomor_servis', name: 'nomor_servis' },
                    { data: 'pelanggan_nama', name: 'pelanggan_nama' },
                    { data: 'penerima_nama', name: 'penerima_nama' },
                    { data: 'keluhan', name: 'keluhan' },
                    { data: 'qc_button', name: 'qc_button', orderable: false, searchable: false },
                    { data: 'teknisi_nama', name: 'teknisi_nama' },
                    { data: 'tindakan_list', name: 'tindakan_list', orderable: false, searchable: false },
                    { data: 'sparepart_list', name: 'sparepart_list', orderable: false, searchable: false },
                    { data: 'total_biaya', name: 'total_biaya' },
                    { data: 'catatan', name: 'catatan' },
                    { data: 'status_badge', name: 'status_badge', orderable: false, searchable: false },
                    @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                    @endif
                ],
                order: [[2, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Kembali" }
                },
                drawCallback: function() {
                    attachCheckboxHandlers();
                }
            });

            // Filter Status Button Click Logic
            $('.filter-btn').on('click', function() {
                // Update styling
                $('.filter-btn').removeClass('bg-indigo-500 text-white').addClass('bg-white text-slate-500 border-slate-200');
                $(this).removeClass('bg-white text-slate-500 border-slate-200').addClass('bg-indigo-500 text-white');
                $(this).find('span').removeClass('text-slate-400').addClass('text-slate-200');

                // Reload Table
                currentStatus = $(this).data('status');
                table.draw();
            });

            // --- CHECKBOX & BULK DELETE LOGIC ---
            function attachCheckboxHandlers() {
                $('#parent-checkbox').prop('checked', false);
                toggleBulkAction();

                $('#parent-checkbox').off('click').on('click', function() {
                    var checked = $(this).is(':checked');
                    $('input.table-item').prop('checked', checked);
                    toggleBulkAction();
                });

                $('#history-garansi-table').off('change', '.table-item').on('change', '.table-item', function() {
                    var all = $('input.table-item').length;
                    var checked = $('input.table-item:checked').length;
                    $('#parent-checkbox').prop('checked', all === checked && all > 0);
                    toggleBulkAction();
                });
            }

            function toggleBulkAction() {
                var checkedCount = $('input.table-item:checked').length;
                $('.table-items-count').text(checkedCount);
                if (checkedCount > 0) {
                    $('.table-items-action').removeClass('hidden');
                } else {
                    $('.table-items-action').addClass('hidden');
                }
            }

            window.deleteSelected = function() {
                var selectedIds = $('input.table-item:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) return alert('Pilih data terlebih dahulu.');
                if (!confirm('Yakin ingin menghapus ' + selectedIds.length + ' data ini?')) return;

                $.ajax({
                    url: "{{ route('history-garansi.bulkDelete') }}",
                    method: 'POST',
                    data: {
                        ids: selectedIds,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        alert(response.message);
                        table.ajax.reload();
                        $('.table-items-action').addClass('hidden');
                    },
                    error: function(xhr) {
                        alert('Gagal menghapus data.');
                    }
                });
            };
        });

        // SINGLE DELETE MODAL LOGIC
        function confirmDelete(id) {
            var url = "{{ url('history-garansi') }}/" + id;
            $('#delete-form').attr('action', url);
            $('#delete-modal').removeClass('hidden').addClass('flex');
        }

        function closeDeleteModal() {
            $('#delete-modal').addClass('hidden').removeClass('flex');
        }
    </script>

    <script>
    // --- DATA ITEM STANDAR (Berlaku untuk Tab 1 & Tab 2) ---
    const defaultChecklist = [
        "CHECK FACE ID/FINGER", "CHECK FRONT CAM 1/2", "CHECK BACK CAM 1/2/3",
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

    function renderAllChecklists() {
        const tbodyTab1 = document.getElementById('checklist-tbody-tab1');
        tbodyTab1.innerHTML = '';

        defaultChecklist.forEach((item, index) => {
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
            $('.select2').select2({
                width: '100%',
                dropdownParent: $('#tambah-modal-data')
            });

            $('#service_id').on('change', function() {
                let selected = $(this).find(':selected');
                $('#prev_teknisi').text(selected.data('teknisi'));
                $('#exp_garansi').text(selected.data('expired'));

                let notaUrl = selected.data('nota');
                if (notaUrl) {
                    $('#link_nota').html(`<a href="${notaUrl}" target="_blank" class="text-blue-600 underline">Lihat Nota</a>`);
                } else {
                    $('#link_nota').html('');
                }

                let service_id = $(this).val();
                let historyBox = $('#history_garansi');
                historyBox.html('<span class="text-gray-500">Memuat...</span>');

                if(!service_id) { historyBox.html(''); return; }

                let url = `/history-garansi/list-data/${service_id}`
                $.ajax({
                    url: url,
                    method: 'GET',
                    success: function(response) {
                        if (response.length === 0) {
                            historyBox.html('<span class="text-gray-500">Tidak ada riwayat garansi.</span>');
                            return;
                        }
                        let html = '';
                        response.forEach((item, index) => {
                            let tindakanHTML = '-';
                            if (item.tindakan_list && item.tindakan_list.length > 0) {
                                tindakanHTML = '<ul class="list-disc ml-4">';
                                item.tindakan_list.forEach(t => {
                                    tindakanHTML += `<li>${t.nama} - Rp${new Intl.NumberFormat().format(t.harga)}</li>`;
                                });
                                tindakanHTML += '</ul>';
                            }
                            html += `
                                <div class="mb-2 p-2 border rounded bg-slate-50">
                                    <div class="font-semibold">Klaim Garansi ${index + 1}</div>
                                    <div>Tgl Klaim: <b>${item.date || '-'}</b></div>
                                    <div>Tgl Selesai: <b>${item.tgl_selesai || '-'}</b></div>
                                    <div>Tindakan: ${tindakanHTML}</div>
                                    <div>Keluhan: ${item.keluhan || '-'}</div>
                                    <div>Total Biaya: Rp${item.total_biaya}</div>
                                </div>
                            `;
                        });
                        historyBox.html(html);
                    },
                    error: function() {
                        historyBox.html('<span class="text-red-500">Error mengambil data.</span>');
                    }
                });
            });
        });
    </script>

    <script>
        $(document).on('click', '.toggle-status', function() {
            let id = $(this).data('id');
            let btn = $(this);

            $.ajax({
                url: `/history-garansi/${id}/toggle-status`,
                method: "PATCH",
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    if (res.success) {
                        btn.text(res.label);
                        btn.removeClass('status-menunggu status-selesai status-batal');
                        if (res.status == 1) {
                            btn.addClass('status-menunggu');
                        } else if (res.status == 2) {
                            btn.addClass('status-selesai');
                        } else {
                            btn.addClass('status-batal');
                        }
                    }
                },
                error: function() { alert('Gagal update status!'); }
            });
        });
    </script>

{{-- @endpush --}}
