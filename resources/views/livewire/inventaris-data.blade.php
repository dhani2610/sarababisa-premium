<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Inventaris ✨</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="hidden xs:block ml-2">Tambah Inventaris</span>
                    </button>
                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-cloak></div>
                    <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-cloak>
                        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false">
                            <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Tambah Inventaris</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">&times;</button>
                            </div>
                            <form action="{{ route('inventaris.store') }}" method="post">
                                @csrf
                                <div class="px-5 py-4">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="created_at">Tgl. Pembelian <span class="text-rose-500">*</span></label>
                                            <input id="created_at" name="created_at" class="form-input w-full px-2 py-1" type="date"/>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="name">Nama Barang <span class="text-rose-500">*</span></label>
                                            <input id="name" name="name" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="code">Kode Inventaris <span class="text-rose-500">*</span></label>
                                            <input id="code" name="code" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="price">Harga <span class="text-rose-500">*</span></label>
                                            <div class="relative">
                                                <input id="price" name="price" class="form-input w-full pl-10 px-2 py-1" type="number" required/>
                                                <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                    <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="supplier">Supplier</label>
                                            <input id="supplier" name="supplier" class="form-input w-full px-2 py-1" type="text" />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="masa_penggantian">Masa Penggantian <span class="text-rose-500">*</span></label>
                                            <select id="masa_penggantian" name="masa_penggantian" class="form-select text-sm py-1 w-full">
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
                                    </div>
                                </div>
                                <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                    <button type="button" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                    <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

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

        @if(session('success'))
            <div class="mb-4 px-4 py-2 rounded-sm text-sm bg-emerald-500 text-white">{{ session('success') }}</div>
        @endif

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Inventaris <span class="text-slate-400 font-medium">{{ $inventories_count }}</span></h2>
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count">0</span> item yang dipilih</div>
                            <div class="space-x-1">
                                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-blue-500 hover:text-blue-600" onclick="printSelected()">Cetak</button>
                                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" onclick="deleteSelected()">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto p-4">
                <table id="inventaris-table" class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox" />
                                    </label>
                                </div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">No</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Tanggal Pembelian</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Nama Barang</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Kode Inventaris</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Harga</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Supplier</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Masa Penggantian</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200"></tbody>
                </table>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_wrapper .dataTables_length select {
            padding-right: 30px;
            width: auto;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#inventaris-table').DataTable({
                processing: false,
                serverSide: false,
                ajax: "{{ route('inventaris.data') }}",
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'name', name: 'name' },
                    { data: 'code', name: 'code' },
                    { data: 'price', name: 'price' },
                    { data: 'supplier', name: 'supplier' },
                    { data: 'masa_penggantian', name: 'masa_penggantian' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                ],
                order: [[1, 'desc']], // Urut berdasarkan tgl pembelian
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

            // --- Logic Checkbox & Bulk Actions ---
            function attachCheckboxHandlers() {
                $('#parent-checkbox').prop('checked', false);
                toggleBulkAction();

                $('#parent-checkbox').off('click').on('click', function() {
                    var checked = $(this).is(':checked');
                    $('input.table-item').prop('checked', checked);
                    toggleBulkAction();
                });

                $('#inventaris-table').off('change', '.table-item').on('change', '.table-item', function() {
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

            // Fungsi Bulk Delete
            window.deleteSelected = function() {
                var selectedIds = $('input.table-item:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) return alert('Pilih data terlebih dahulu.');
                if (!confirm('Yakin ingin menghapus ' + selectedIds.length + ' data ini?')) return;

                $.ajax({
                    url: "{{ route('inventaris.delete-batch') }}",
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
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus data.';
                        alert(msg);
                    }
                });
            };

            // Fungsi Cetak Label Selected (Menggunakan form submit agar bisa download file)
            window.printSelected = function() {
                var selectedIds = $('input.table-item:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) return alert('Pilih data terlebih dahulu.');

                // Buat form hidden dinamis untuk submit POST
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('inventaris.print-selected') }}";
                form.target = '_blank'; // Buka di tab baru

                // Token CSRF
                var csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                form.appendChild(csrf);

                // Tambahkan ID sebagai array input
                selectedIds.forEach(function(id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'selectedIds[]';
                    input.value = id;
                    form.appendChild(input);
                });

                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            };
        });
    </script>
