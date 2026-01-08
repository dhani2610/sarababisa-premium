@section('title', 'Master Izin')

    <div class=" sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto"
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

        <style>
            [x-cloak] { display: none !important; }
            .dataTables_wrapper .dataTables_length select { padding-right: 30px; }
            .dataTables_processing { z-index: 50; }
        </style>

        <div class="space-y-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Statistik Izin</h2>
                </div>
                <div>
                    <button type="button" class="btn bg-slate-500 hover:bg-slate-600 text-white"
                        @click="$dispatch('open-filter-modal')">
                        <i class="fa fa-filter mr-2"></i>Filter \ Export
                    </button>
                </div>
            </div>

            <div x-data="{ open: false }"
                 x-on:open-filter-modal.window="open = true"
                 x-on:close-filter-modal.window="open = false"
                 x-show="open" x-cloak
                 class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md relative">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Filter Berdasarkan Range Tanggal</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Dari Tanggal</label>
                            <input type="date" id="filter_start_date" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Sampai Tanggal</label>
                            <input type="date" id="filter_end_date" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-400">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button @click="open = false" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-700">Batal</button>
                        <button onclick="applyFilter()" @click="open = false" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Filter</button>
                        {{-- Export tetap menggunakan route biasa jika diperlukan, atau ajax --}}
                        {{-- <button onclick="exportExcel()" class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700">Export</button> --}}
                        <button onclick="resetFilter()" @click="open = false" class="px-4 py-2 rounded bg-gray-500 text-white hover:bg-gray-600">Reset</button>
                    </div>
                </div>
            </div>

            <div class="flex space-x-3 border-b border-slate-200 mb-2" id="tipe-tabs">
                @foreach (['izin', 'sakit', 'alfa','cuti'] as $tipe)
                    <button onclick="filterTipe('{{ $tipe }}')"
                        class="tab-btn py-2 px-4 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 transition-all duration-200"
                        data-tipe="{{ $tipe }}">
                        {{ ucfirst($tipe) }}
                    </button>
                @endforeach
                <button onclick="filterTipe('all')"
                    class="tab-btn py-2 px-4 text-sm font-medium border-b-2 border-indigo-500 text-indigo-600 transition-all duration-200"
                    data-tipe="all">
                    Semua
                </button>
            </div>

            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-purple-100 text-purple-600 rounded-full"><i class="fas fa-calendar-day text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                    </div>
                </div>
                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-green-100 text-green-600 rounded-full"><i class="fas fa-user-check text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Izin Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['hariIni']['izin'] }} Orang</div>
                    </div>
                </div>
                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-yellow-100 text-yellow-600 rounded-full"><i class="fas fa-user-md text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Sakit Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['hariIni']['sakit'] }} Orang</div>
                    </div>
                </div>
                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-red-100 text-red-600 rounded-full"><i class="fas fa-user-times text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Alfa Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['hariIni']['alfa'] }} Orang</div>
                    </div>
                </div>
                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-users text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Total Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['hariIni']['total'] }} Orang</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Izin ✨</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <div class="relative">
                    <input type="text" id="custom-search" placeholder="Cari berdasarkan nama..." class="form-input pl-9 rounded-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 fill-current text-slate-400" viewBox="0 0 16 16"><path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z"></path><path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a1 1 0 001.414-1.414z"></path></svg>
                    </div>
                </div>

                <button type="button" class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click="modalOpen = true">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Izin</span>
                </button>
            </div>
        </div>

        <div x-show="modalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @keydown.escape.window="modalOpen = false" @click.self="modalOpen = false" x-transition.opacity>
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6" x-transition.scale>
                <h2 class="text-xl font-semibold mb-4">Tambah Izin</h2>
                <form action="{{ route('master-izin.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Karyawan</label>
                            <select name="user_id" class="form-select w-full" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tipe</label>
                            <select name="tipe" class="form-select w-full" required>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="cuti">Cuti</option>
                                <option value="alfa">Alfa</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal Dibuat</label>
                            <input type="date" name="tanggal" class="form-input w-full" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Dari Tanggal</label>
                                <input type="date" name="tanggal_mulai" class="form-input w-full" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Sampai Tanggal</label>
                                <input type="date" name="tanggal_selesai" class="form-input w-full" required>
                            </div>
                        </div>
                        @if (Auth::user()->role == 'Kepala Toko')
                        <div>
                            <label class="block text-sm font-medium mb-1">Nominal Potongan <small style="color:red">*jika tidak di isi maka akan otomatis dari pengaturan sistem</small> </label>
                            <input type="text" name="nominal_potongan" class="form-input w-full sapator">
                        </div>
                        @else
                        <input type="hidden" name="nominal_potongan" value="0">
                        @endif
                        <div>
                            <label class="block text-sm font-medium mb-1">Keterangan</label>
                            <textarea name="keterangan" class="form-input w-full" rows="2"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Upload Dokumen (Opsional)</label>
                            <input type="file" name="dokumen" class="form-input w-full" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end space-x-2">
                        <button type="button" class="btn-sm border-slate-200 text-slate-600"
                            @click="modalOpen = false">Batal</button>
                        <button type="submit"
                            class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        @if (auth()->user()->role == 'Kepala Toko')
        <div class="px-4 py-2 rounded-sm text-sm bg-amber-100 border border-amber-200 text-amber-600 mt-4">
            <div class="flex w-full justify-between items-start">
                <div class="flex">
                    <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                    </svg>
                    <div class="font-medium">Data izin yang sudah di setujui oleh kepala toko di popup edit maka akan ada potongan gaji dari nominal potongan.</div>
                </div>
            </div>
        </div>
        @endif

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Izin <span class="text-slate-400 font-medium">{{ $count }}</span></h2>
            </div>

            <div class="table-items-action hidden flex items-center gap-2 px-5 pb-4">
                <div class="text-sm text-slate-500">
                    <span class="table-items-count ml-2">0</span> data terpilih
                </div>
                <button type="button" class="btn bg-rose-500 hover:bg-rose-600 text-white btn-sm" onclick="bulkDelete()">Hapus Terpilih</button>
            </div>

            <div class="overflow-x-auto px-5 pb-5">
                <table id="izin-table" class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 text-center w-px">
                                <label class="inline-flex">
                                    <span class="sr-only">Select all</span>
                                    <input id="parent-checkbox" type="checkbox" class="form-checkbox">
                                </label>
                            </th>
                            <th class="px-2 py-3 text-center">No</th>
                            <th class="px-2 py-3 text-left">Nama Karyawan</th>
                            <th class="px-2 py-3 text-center">Tipe</th>
                            <th class="px-2 py-3 text-center">Tanggal Dibuat</th>
                            <th class="px-2 py-3 text-center">Periode</th>
                            @if (Auth::user()->role == 'Kepala Toko')
                            <th class="px-2 py-3 text-center">Nominal</th>
                            @endif
                            {{-- <th class="px-2 py-3 text-center">Keterangan</th> --}}
                            <th class="px-2 py-3 text-center">Dokumen</th>
                            <th class="px-2 py-3 text-center">Status</th>
                            <th class="px-2 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200"></tbody>
                </table>
            </div>
        </div>

        <div x-show="editModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @keydown.escape.window="editModal = false" @click.self="editModal = false" x-transition.opacity>
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6" x-transition.scale>
                <h2 class="text-xl font-semibold mb-4">Edit Izin</h2>
                <form :action="'/master/master-izin/' + editData.id" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Karyawan</label>
                            <select name="user_id" class="form-select w-full" x-model="editData.user_id" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tipe</label>
                            <select name="tipe" class="form-select w-full" x-model="editData.tipe" required>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="cuti">Cuti</option>
                                <option value="alfa">Alfa</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal Dibuat</label>
                            <input type="date" name="tanggal" class="form-input w-full" x-model="editData.tanggal" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Dari Tanggal</label>
                                <input type="date" x-model="editData.tanggal_mulai" name="tanggal_mulai" class="form-input w-full" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Sampai Tanggal</label>
                                <input type="date" x-model="editData.tanggal_selesai" name="tanggal_selesai" class="form-input w-full" required>
                            </div>
                        </div>
                        @if (Auth::user()->role == 'Kepala Toko')
                        <div>
                            <label class="block text-sm font-medium mb-1">Nominal Potongan</label>
                            <input type="text" name="nominal_potongan" class="form-input w-full sapator"
                                x-model="editData.nominal_potongan">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Status</label>
                            <select name="status" class="form-select w-full" x-model="editData.status" required>
                                <option value="1">Disetujui</option>
                                <option value="0">Pending</option>
                                <option value="2">Ditolak</option>
                            </select>
                        </div>
                        @else
                        <input type="hidden" name="nominal_potongan" x-model="editData.nominal_potongan">
                        @endif
                        <div>
                            <label class="block text-sm font-medium mb-1">Keterangan</label>
                            <textarea name="keterangan" class="form-input w-full" rows="2" x-model="editData.keterangan"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Upload Dokumen (Opsional)</label>
                            <input type="file" name="dokumen" class="form-input w-full" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end space-x-2">
                        <button type="button" class="btn-sm border-slate-200 text-slate-600"
                            @click="editModal = false">Batal</button>
                        <button type="submit"
                            class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Update</button>
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
            // Format Sapator untuk input manual
            document.addEventListener('input', function (e) {
                if (e.target.classList.contains('sapator')) {
                    let value = e.target.value.replace(/\D/g, '');
                    e.target.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                }
            });

            // Global Variables for Filters
            var currentTipe = 'all';

            // Fungsi untuk integrasi tombol Edit Yajra dengan Alpine Modal
            function openEditModal(data) {
                // Dispatch event ke window agar ditangkap oleh AlpineJS
                window.dispatchEvent(new CustomEvent('open-edit-modal', { detail: data }));
            }

            $(document).ready(function() {
                // Init DataTable
                var table = $('#izin-table').DataTable({
                    processing: false,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('master-izin.index') }}",
                        data: function (d) {
                            d.filter_tipe = currentTipe;
                            d.start_date = $('#filter_start_date').val();
                            d.end_date = $('#filter_end_date').val();
                        }
                    },
                    columns: [
                        {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'nama_karyawan', name: 'user.name'},
                        {data: 'tipe', name: 'tipe', className: 'text-center'},
                        {data: 'tanggal', name: 'tanggal', className: 'text-center'},
                        {data: 'periode', name: 'periode', className: 'text-center', orderable: false},
                        @if (Auth::user()->role == 'Kepala Toko')
                        {data: 'nominal_potongan', name: 'nominal_potongan', className: 'text-center'},
                        @endif
                        {data: 'dokumen', name: 'dokumen', className: 'text-center', orderable: false},
                        {data: 'status', name: 'status', className: 'text-center'},
                        {data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center'}
                    ],
                    dom: 'lrtip', // Hide default search box
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
                        // Reset checkbox parent saat draw ulang
                        $('#parent-checkbox').prop('checked', false);
                        updateBulkUI();

                        // Event listener untuk checkbox item
                        $('.table-item').off('change').on('change', function() {
                            updateBulkUI();
                        });
                    }
                });

                // Custom Search
                $('#custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });

                // Filter Tipe (Tab) Logic
                window.filterTipe = function(tipe) {
                    currentTipe = tipe;

                    // Update visual tab buttons
                    $('.tab-btn').removeClass('border-indigo-500 text-indigo-600').addClass('border-transparent text-slate-500');
                    $(`.tab-btn[data-tipe="${tipe}"]`).removeClass('border-transparent text-slate-500').addClass('border-indigo-500 text-indigo-600');

                    table.draw();
                }

                // Apply Filter Date
                window.applyFilter = function() {
                    table.draw();
                }

                window.resetFilter = function() {
                    $('#filter_start_date').val('');
                    $('#filter_end_date').val('');
                    table.draw();
                }

                // Parent Checkbox Logic
                $('#parent-checkbox').change(function() {
                    var checked = this.checked;
                    $('.table-item').prop('checked', checked);
                    updateBulkUI();
                });

                window.updateBulkUI = function() {
                    var count = $('.table-item:checked').length;
                    $('.table-items-count').text(count);
                    if(count > 0) {
                        $('.table-items-action').removeClass('hidden');
                    } else {
                        $('.table-items-action').addClass('hidden');
                    }
                }

                window.bulkDelete = function() {
                    var selectedIds = [];
                    $('.table-item:checked').each(function() {
                        selectedIds.push($(this).val());
                    });

                    if (selectedIds.length === 0) return;

                    if (confirm('Yakin hapus ' + selectedIds.length + ' data terpilih?')) {
                        $.ajax({
                            url: "{{ route('master-izin.deleteSelected') }}",
                            type: "DELETE",
                            data: {
                                selectedIds: selectedIds,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                alert(response.message);
                                table.draw();
                                $('#parent-checkbox').prop('checked', false);
                                updateBulkUI();
                            },
                            error: function(xhr) {
                                alert('Gagal menghapus data.');
                            }
                        });
                    }
                }
            });
        </script>
    @endpush
