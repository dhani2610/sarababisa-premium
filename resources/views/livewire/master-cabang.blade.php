<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-3">

            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Cabang ✨</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <div x-data="{ modalOpen: false }">
                    @if ($allowShow)
                        <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white"
                                @click.prevent="modalOpen = true">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                            </svg>
                            <span class="hidden xs:block ml-2">Tambah Cabang</span>
                        </button>
                    @endif

                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-in duration-100" x-cloak></div>
                    
                    <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-cloak>
                        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false">
                            <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Tambah Cabang</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">&times;</button>
                            </div>
                            <form action="{{ route('master-cabang.store') }}" method="post">
                                @csrf
                                <div class="px-5 py-4">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="nama_cabang">Nama Cabang <span class="text-rose-500">*</span></label>
                                            <input id="nama_cabang" name="nama_cabang" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="owner">Owner <span class="text-rose-500">*</span></label>
                                            <input id="owner" name="owner" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="nama_toko">Nama Toko <span class="text-rose-500">*</span></label>
                                            <input id="nama_toko" name="nama_toko" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="deskripsi_toko">Deskripsi Toko <span class="text-rose-500">*</span></label>
                                            <input id="deskripsi_toko" name="deskripsi_toko" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="alamat_toko">Alamat Toko <span class="text-rose-500">*</span></label>
                                            <input id="alamat_toko" name="alamat_toko" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="nomor_hp_toko">Nomor Toko <span class="text-rose-500">*</span></label>
                                            <input id="nomor_hp_toko" name="nomor_hp_toko" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="bank">Bank <span class="text-rose-500">*</span></label>
                                            <input id="bank" name="bank" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="rekening">Rekening <span class="text-rose-500">*</span></label>
                                            <input id="rekening" name="rekening" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="pemilik_rekening">Pemilik Rekening <span class="text-rose-500">*</span></label>
                                            <input id="pemilik_rekening" name="pemilik_rekening" class="form-input w-full px-2 py-1" type="text" required />
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
                <div class="flex w-full justify-between items-start">
                    <div class="flex">
                        <ul class="list-disc pl-4">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Cabang <span class="text-slate-400 font-medium">{{ $cabang_count }}</span></h2>
                
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
                <table id="cabang-table" class="table-auto w-full">
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
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">No.</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Cabang</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        </tbody>
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
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script>
            $(document).ready(function() {
                var table = $('#cabang-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ route('master-cabang.data') }}",
                    columns: [
                        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                        
                        // Kolom No. Urut (Client Side Calculation)
                        { 
                            data: null, 
                            sortable: false,
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },

                        { data: 'nama_cabang', name: 'nama_cabang' },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                    ],
                    order: [[2, 'asc']], // Default urut berdasarkan Nama Cabang (index ke-2)
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
                        attachCheckboxHandlers();
                    }
                });

                // Logic Checkbox & Bulk Delete
                function attachCheckboxHandlers() {
                    $('#parent-checkbox').prop('checked', false);
                    toggleBulkAction();

                    $('#parent-checkbox').off('click').on('click', function() {
                        var checked = $(this).is(':checked');
                        $('input.table-item').prop('checked', checked);
                        toggleBulkAction();
                    });

                    $('#cabang-table').off('change', '.table-item').on('change', '.table-item', function() {
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
                        url: "{{ route('master-cabang.delete-batch') }}",
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
            });
        </script>