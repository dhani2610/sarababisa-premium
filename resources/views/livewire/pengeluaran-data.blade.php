@section('title')
    Pengeluaran
@endsection

{{-- <x-toko-layout> --}}
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Pengeluaran ✨</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                {{-- <x-search-form placeholder="Cari pengeluaran..." /> --}}

                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="hidden xs:block ml-2">Tambah Pengeluaran</span>
                    </button>

                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-in duration-100" x-cloak></div>

                    <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-cloak>
                        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false">
                            <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Tambah Pengeluaran</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">&times;</button>
                            </div>
                            <form action="{{ route('pengeluaran.store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="px-5 py-4">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="name">Nama Pengeluaran <span class="text-rose-500">*</span></label>
                                            <input id="name" name="name" class="form-input w-full px-2 py-1" type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="price">Biaya <span class="text-rose-500">*</span></label>
                                            <div class="relative">
                                                <input id="price" name="price" class="form-input w-full pl-10 px-2 py-1 input-currency" type="text" required />
                                                <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                    <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="tipe">Tipe <span class="text-rose-500">*</span></label>
                                            <select id="tipe" name="tipe" class="form-select text-sm py-1 w-full" required>
                                                <option selected value="">Pilih Tipe</option>
                                                <option value="0">Operasional</option>
                                                <option value="1">Servis</option>
                                                <option value="2">Penjualan</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="users_id">Akun <span class="text-rose-500">*</span></label>
                                            <select id="users_id" name="users_id" class="form-select text-sm py-1 w-full" required>
                                                <option selected value="">Pilih Akun</option>
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div x-data="{ photoPreview: null }">
                                            <label class="block text-sm font-medium mb-1" for="foto">Foto Bukti (Opsional - Max 1MB)</label>
                                            <input id="foto" name="foto" type="file" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                                accept="image/*"
                                                @change="
                                                    const file = $event.target.files[0];
                                                    if (file) {
                                                        if (file.size > 1024 * 1024) {
                                                            alert('File terlalu besar! Maksimal 1MB');
                                                            $event.target.value = '';
                                                            photoPreview = null;
                                                        } else {
                                                            const reader = new FileReader();
                                                            reader.onload = (e) => { photoPreview = e.target.result; };
                                                            reader.readAsDataURL(file);
                                                        }
                                                    }
                                                " />

                                            <template x-if="photoPreview">
                                                <div class="mt-2">
                                                    <img :src="photoPreview" class="w-32 h-32 object-cover rounded border">
                                                </div>
                                            </template>
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

        <div class="bg-emerald-100 rounded border border-emerald-200 text-emerald-600 p-4 mb-3">
             <div class="text-left md:flex md:items-center md:space-x-2">
                <div class="text-sm">
                    Silahkan inputkan pengeluaran operasional toko seperti ATK, Konsumsi Karyawan, Alat Servis, dll. Jumlah pengeluaran akan memengaruhi informasi keuangan bulan ini pada halaman Dashboard.
                </div>
             </div>
        </div>

        @if (session('success'))
            <div class="mb-4 px-4 py-2 rounded-sm text-sm bg-emerald-500 text-white">{{ session('success') }}</div>
        @endif

        <div class="sm:flex sm:justify-between sm:items-center mb-5">
             <div class="mb-2"></div>
             <div class="relative inline-flex" x-data="{ modalOpen: false }">


                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500 hover:text-slate-600 mb-2 md:mb-0" @click.prevent="modalOpen = true">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16"><path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" /></svg>
                    <span class="hidden xs:block ml-2">Cetak Laporan</span>
                </button>
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-in duration-100" x-cloak></div>
                <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-cloak>
                     <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false">
                        <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Atur Pencetakan Laporan</div>
                            <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">&times;</button>
                        </div>
                        <form action="{{ route('cetak-laporan-pengeluaran') }}" method="get" target="_blank">
                             @csrf
                             <div class="px-5 py-4 space-y-3">

                                 <div>
                                     <label class="block text-sm font-medium mb-1">Tipe</label>
                                     <select name="tipe" class="form-select text-sm py-1 w-full">
                                         <option value="">Semua Tipe</option>
                                         <option value="0">Operasional</option>
                                         <option value="1">Servis</option>
                                         <option value="2">Penjualan</option>
                                     </select>
                                 </div>
                                 <div>
                                     <label class="block text-sm font-medium mb-1">Nama Akun</label>
                                     <select name="users_id" class="form-select text-sm py-1 w-full">
                                         <option value="">Semua Akun</option>
                                         @foreach ($users as $u)
                                             <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                                         @endforeach
                                     </select>
                                 </div>
                                 <div><label class="block text-sm font-medium mb-1">Mulai tanggal <span class="text-rose-500">*</span></label><input name="start_date" class="form-input w-full py-2" type="date" required /></div>
                                 <div><label class="block text-sm font-medium mb-1">Sampai tanggal <span class="text-rose-500">*</span></label><input name="end_date" class="form-input w-full py-2" type="date" required /></div>
                             </div>
                             <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Cetak</button>
                             </div>
                        </form>
                     </div>
                </div>
             </div>
        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">



            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Pengeluaran <span class="text-slate-400 font-medium">{{  $expenses_count  }}</span></h2>
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count">0</span> item yang dipilih</div>
                            <div class="space-x-1">
                                 <button class="btn bg-white border-slate-200 hover:border-slate-300 text-blue-500" onclick="bulkActionTipe('tipe', 0)">
                                    Operasional
                                </button>

                                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-amber-500" onclick="bulkActionTipe('tipe', 1)">
                                    Servis
                                </button>

                                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-emerald-500" onclick="bulkActionTipe('tipe', 2)">
                                    Penjualan
                                </button>
                                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-blue-500 hover:text-blue-600" onclick="bulkAction('approve')">Setujui</button>
                                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-gray-900 hover:text-gray-950" onclick="bulkAction('reject')">Tolak</button>
                                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" onclick="bulkAction('delete')">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-48 ml-2">
                <select id="filter-tipe" class="form-select text-sm py-1 w-full">
                    <option value="">Semua Tipe</option>
                    <option value="0">Operasional</option>
                    <option value="1">Servis</option>
                    <option value="2">Penjualan</option>
                </select>
            </div>

            <div class="overflow-x-auto p-4">
                <table id="pengeluaran-table" class="table-auto w-full">
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
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Tgl Pengeluaran</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Tgl Disetujui</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Pembuat</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Nama Akun</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Item Pengeluaran</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Tipe Pengeluaran</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Foto</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Biaya</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Status</th>
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
        // --- HELPER FORMAT RUPIAH ---
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
            return rupiah;
        }

        $(document).ready(function() {

            // 1. Format Rupiah Input
            $(document).on('input', '.input-currency', function() {
                $(this).val(formatRupiah($(this).val()));
            });

            // 2. Clean Input on Submit
            $('form').on('submit', function() {
                $(this).find('.input-currency').each(function() {
                    var cleanVal = $(this).val().replace(/\./g, '');
                    $(this).val(cleanVal);
                });
            });


            // 3. DataTables Init
            var table = $('#pengeluaran-table').DataTable({
                processing: false,
                serverSide: false,
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    {
                        data: null,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'tgl_disetujui', name: 'tgl_disetujui' },
                    { data: 'created_by', name: 'created_by' },
                    { data: 'user_name', name: 'user.name' },
                    { data: 'name', name: 'name' },
                    { data: 'tipe', name: 'tipe' },
                    { data: 'foto', name: 'foto' },
                    { data: 'price', name: 'price' },
                    { data: 'is_approve', name: 'is_approve' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                ],
                order: [[0, 'asc']], // Urut berdasarkan tanggal
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

            let batchSize = 200;
            let offset = 0;
            let isLoading = false;
            let totalSet = false;


            function loadBatch() {
                if (isLoading) return;

                isLoading = true;
                $('#filter-tipe').prop('disabled', true);
                $('#loading-info').removeClass('hidden');

                $.ajax({
                    url: "{{ route('pengeluaran.data') }}",
                    data: {
                        offset: offset,
                        limit: batchSize,
                        tipe: $('#filter-tipe').val()
                    },
                    success: function (res) {

                        if (!totalSet) {
                            $('#total-count').text(res.total);
                            totalSet = true;
                        }

                        if (res.data.length === 0) {
                            isLoading = false;
                            $('#filter-tipe').prop('disabled', false);
                            $('#loading-info').addClass('hidden');
                            return;
                        }

                        table.rows.add(res.data).draw(false);
                        offset += batchSize;

                        isLoading = false;

                        // lanjut batch berikutnya (smooth)
                        setTimeout(loadBatch, 80);
                    }
                });
            }


            // mulai load pertama
            loadBatch();

            $('#filter-tipe').on('change', function () {
                if (isLoading) {
                    alert('Data masih dimuat, tunggu sampai selesai');
                    return;
                }

                // reset
                table.clear().draw();
                offset = 0;
                totalSet = false;

                loadBatch();
            });


            // 4. Logic Checkbox & Bulk Actions
            function attachCheckboxHandlers() {
                $('#parent-checkbox').prop('checked', false);
                toggleBulkAction();

                $('#parent-checkbox').off('click').on('click', function() {
                    var checked = $(this).is(':checked');
                    $('input.table-item').prop('checked', checked);
                    toggleBulkAction();
                });

                $('#pengeluaran-table').off('change', '.table-item').on('change', '.table-item', function() {
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

            // Fungsi Bulk Actions
            window.bulkAction = async function(actionType) {
                var selectedIds = $('input.table-item:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) return alert('Pilih data terlebih dahulu.');

                var url = '';
                var confirmMsg = '';
                var extraData = {};

                if(actionType === 'delete') {
                    url = "{{ route('pengeluaran.delete-batch') }}";
                    confirmMsg = 'Yakin ingin menghapus data terpilih?';
                    if (!confirm(confirmMsg)) return;
                } else if(actionType === 'approve') {
                    url = "{{ route('pengeluaran.approve-batch') }}";
                    var tgl_disetujui = await promptTanggalDisetujui();
                    if (!tgl_disetujui) return;
                    extraData = { tgl_disetujui: tgl_disetujui };
                } else if(actionType === 'reject') {
                    url = "{{ route('pengeluaran.reject-batch') }}";
                    confirmMsg = 'Yakin ingin menolak data terpilih?';
                    if (!confirm(confirmMsg)) return;
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: Object.assign({
                        ids: selectedIds,
                        _token: "{{ csrf_token() }}"
                    }, extraData),
                    success: function(response) {
                        alert(response.message);
                        window.location.reload();
                        $('.table-items-action').addClass('hidden');
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memproses data.';
                        alert(msg);
                    }
                });
            };
            window.bulkActionTipe = function(actionType,value = null) {
                var selectedIds = $('input.table-item:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) return alert('Pilih data terlebih dahulu.');

                $.ajax({
                    url: "{{ route('pengeluaran.tipe-batch') }}",
                    method: 'POST',
                    data: {
                        ids: selectedIds,
                        tipe: value,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        alert(response.message);
                        window.location.reload();
                        $('.table-items-action').addClass('hidden');
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memproses data.';
                        alert(msg);
                    }
                });
            };
        });
    </script>
{{-- </x-toko-layout> --}}
