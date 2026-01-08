@section('title', 'Lembur')

    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto"
         x-data="{
            modalOpen: false,
            editModal: false,
            editData: {},
            openEdit(data) {
                this.editData = data;
                this.editModal = true;
            }
         }"
         @open-edit-modal.window="openEdit($event.detail)"
         id="main-area">

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>
            [x-cloak] { display: none !important; }
            .dataTables_wrapper .dataTables_length select { padding-right: 30px; }
        </style>

        <div class="flex justify-between mb-4">
            <h1 class="text-2xl font-bold text-slate-800">Data Lembur ✨</h1>
            <button @click="modalOpen = true" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                + Tambah Lembur
            </button>
        </div>

        {{-- Statistik --}}
        @if (Auth::user()->role == 'Kepala Toko')
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border rounded-lg shadow p-4">
                    <h3 class="text-sm text-slate-500">Lembur Hari Ini</h3>
                    <div class="text-xl font-bold">{{ $stats['hariIni']['total'] }} Kali</div>
                </div>
                <div class="bg-white border rounded-lg shadow p-4">
                    <h3 class="text-sm text-slate-500">Nominal Hari Ini</h3>
                    <div class="text-xl font-bold">Rp {{ number_format($stats['hariIni']['total_nominal'], 0, ',', '.') }}</div>
                </div>
                <div class="bg-white border rounded-lg shadow p-4">
                    <h3 class="text-sm text-slate-500">Lembur Bulan Ini</h3>
                    <div class="text-xl font-bold">{{ $stats['bulanIni']['total'] }} Kali</div>
                </div>
                <div class="bg-white border rounded-lg shadow p-4">
                    <h3 class="text-sm text-slate-500">Nominal Bulan Ini</h3>
                    <div class="text-xl font-bold">Rp {{ number_format($stats['bulanIni']['total_nominal'], 0, ',', '.') }}</div>
                </div>
            </div>
        @endif

        {{-- Table Container --}}
        <div class="bg-white border rounded-lg shadow p-5">

            {{-- Search & Bulk Actions UI (Manual control for DataTables) --}}
            <div class="flex flex-wrap justify-between items-center mb-4 gap-2">

                {{-- Bagian Kiri: Bulk Actions --}}
                <div class="table-items-action hidden flex items-center gap-2">
                    <div class="text-sm text-slate-500">
                        <span class="table-items-count font-medium ml-1">0</span> data terpilih
                    </div>

                    {{-- Form Bulk Delete --}}
                    <form action="{{ route('master-overtime.deleteSelected') }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <input type="hidden" name="ids" id="bulk_delete_ids">
                        <button type="submit" class="btn-sm bg-red-500 text-white hover:bg-red-600">Hapus</button>
                    </form>

                    {{-- Form Bulk Approve --}}
                    <form action="{{ route('master-overtime.approveSelected') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="ids" id="bulk_approve_ids">
                        <button type="submit" class="btn-sm bg-green-500 text-white hover:bg-green-600">Approve</button>
                    </form>

                    {{-- Form Bulk Reject --}}
                    <form action="{{ route('master-overtime.rejectSelected') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="ids" id="bulk_reject_ids">
                        <button type="submit" class="btn-sm bg-yellow-500 text-white hover:bg-yellow-600">Reject</button>
                    </form>
                </div>

                {{-- Bagian Kanan: Search --}}
                <div class="ml-auto relative">
                    <input type="text" id="custom-search" placeholder="Cari..." class="form-input pl-9 rounded-full text-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fa fa-search text-slate-400"></i>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="overtime-table" class="table-auto w-full text-sm">
                    <thead class="bg-slate-100 text-slate-500">
                        <tr>
                            @if (Auth::user()->role == 'Kepala Toko')
                                <th class="px-2 py-3 text-center w-px">
                                    <input type="checkbox" id="parent-checkbox" class="form-checkbox">
                                </th>
                            @else
                                <th class="hidden"></th>
                            @endif
                            <th class="px-4 py-3 text-center">No</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3 text-center">Tanggal</th>
                            <th class="px-4 py-3 text-center">Mulai</th>
                            <th class="px-4 py-3 text-center">Selesai</th>
                            @if (Auth::user()->role == 'Kepala Toko')
                                <th class="px-4 py-3 text-center">Nominal</th>
                            @endif
                            <th class="px-4 py-3 text-center">Keterangan</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200"></tbody>
                </table>
            </div>
        </div>

        {{-- Modal Tambah --}}
        <div x-show="modalOpen" x-cloak
            class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50"
            @keydown.escape.window="modalOpen = false">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg" @click.outside="modalOpen = false">
                <h2 class="text-lg font-semibold mb-4">Tambah Lembur</h2>
                <form method="POST" action="{{ route('master-overtime.store') }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal</label>
                            <input type="date" name="tanggal" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Mulai</label>
                            <input type="time" name="waktu_start" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Selesai</label>
                            <input type="time" name="waktu_end" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Keterangan</label>
                            <textarea name="keterangan" class="form-input w-full"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" @click="modalOpen=false" class="btn-sm border-slate-300 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="btn-sm bg-indigo-500 text-white hover:bg-indigo-600">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Edit --}}
        <div x-show="editModal" x-cloak
            class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50"
            @keydown.escape.window="editModal = false">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg" @click.outside="editModal = false">
                <h2 class="text-lg font-semibold mb-4">Edit Lembur</h2>
                <form :action="'{{ url('master/master-overtime') }}/' + editData.id" method="POST">
                    @csrf @method('PUT')
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal</label>
                            <input type="date" name="tanggal" :value="editData.tanggal" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Mulai</label>
                            <input type="time" name="waktu_start" :value="editData.waktu_start" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Selesai</label>
                            <input type="time" name="waktu_end" :value="editData.waktu_end" class="form-input w-full" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Keterangan</label>
                            <textarea name="keterangan" class="form-input w-full" x-text="editData.keterangan"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" @click="editModal=false" class="btn-sm border-slate-300 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="btn-sm bg-indigo-500 text-white hover:bg-indigo-600">Update</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script>
            // --- 1. DEFINISI FUNGSI DI LUAR DOCUMENT READY (Supaya bisa dipanggil kapanpun) ---

            // Fungsi dispatch event untuk membuka modal edit
            function openEditModal(data) {
                window.dispatchEvent(new CustomEvent('open-edit-modal', { detail: data }));
            }

            // Fungsi Update UI Bulk Action
            function updateBulkUI() {
                var selectedIds = [];
                $('.table-item:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                var count = selectedIds.length;
                $('.table-items-count').text(count);

                // Isi input hidden di setiap form action
                var jsonIds = JSON.stringify(selectedIds);
                $('#bulk_delete_ids').val(jsonIds);
                $('#bulk_approve_ids').val(jsonIds);
                $('#bulk_reject_ids').val(jsonIds);

                if(count > 0) {
                    $('.table-items-action').removeClass('hidden');
                } else {
                    $('.table-items-action').addClass('hidden');
                }
            }

            // --- 2. EKSEKUSI LOGIC SAAT DOM SIAP ---
            $(document).ready(function() {
                var table = $('#overtime-table').DataTable({
                    processing: false,
                    serverSide: false,
                    ajax: "{{ route('master-overtime.index') }}",
                    columns: [
                        @if (Auth::user()->role == 'Kepala Toko')
                            {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center'},
                        @else
                            {data: null, visible: false},
                        @endif
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'nama', name: 'user.name'},
                        {data: 'tanggal', name: 'tanggal', className: 'text-center'},
                        {data: 'waktu_start', name: 'waktu_start', className: 'text-center'},
                        {data: 'waktu_end', name: 'waktu_end', className: 'text-center'},
                        @if (Auth::user()->role == 'Kepala Toko')
                            {data: 'nominal_overtime', name: 'nominal_overtime', className: 'text-center'},
                        @endif
                        {data: 'keterangan', name: 'keterangan', className: 'text-center'},
                        {data: 'status_label', name: 'status', className: 'text-center'},
                        {data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center'}
                    ],
                    dom: 'lrtip',
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            next: "Lanjut",
                            previous: "Kembali"
                        }
                    },
                    drawCallback: function() {
                        // Reset parent checkbox
                        $('#parent-checkbox').prop('checked', false);

                        // Panggil fungsi yang sudah didefinisikan di atas
                        updateBulkUI();

                        // Event listener untuk checkbox item (harus di-bind ulang setiap draw)
                        $('.table-item').off('change').on('change', function() {
                            updateBulkUI();
                        });
                    }
                });

                // Custom Search
                $('#custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });

                // Parent Checkbox Logic
                $('#parent-checkbox').change(function() {
                    var checked = this.checked;
                    $('.table-item').prop('checked', checked);
                    updateBulkUI();
                });
            });
        </script>
    @endpush
