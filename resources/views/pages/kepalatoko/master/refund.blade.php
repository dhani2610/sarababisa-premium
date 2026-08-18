@section('title', 'Pengembalian Dana')

<x-toko-layout>
    <style>
        .dataTables_wrapper .dataTables_length select{
            width: 68px!important;
        }
    </style>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Pengembalian Dana ✨</h1>
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
                        <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>
                        <div id="tambah-modal" class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                            <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                                <div class="px-5 py-3 border-b border-slate-200">
                                    <div class="flex justify-between items-center">
                                        <div class="font-semibold text-slate-800">Atur Pencetakan</div>
                                        <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                            <div class="sr-only">Close</div>
                                            <svg class="w-4 h-4 fill-current">
                                                <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <form action="{{ route('refunds.cetak') }}" method="get" target="_blank">
                                    @csrf
                                    <div class="px-5 py-4">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Mulai tanggal <span class="text-rose-500">*</span></label>
                                                <input id="start_date" name="start_date" class="form-input w-full py-2" type="date" required />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Sampai tanggal <span class="text-rose-500">*</span></label>
                                                <input id="end_date" name="end_date" class="form-input w-full py-2" type="date" required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-5 py-4 border-t border-slate-200">
                                        <div class="flex flex-wrap justify-end space-x-2">
                                            <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Cetak</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                    <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                        <div x-data="{ modalOpen: false }">
                            <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                                <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                                </svg>
                                <span class="hidden xs:block ml-2">Tambah Refund</span>
                            </button>

                            <div x-show="modalOpen" x-cloak>
                                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"></div>
                                <div class="fixed inset-0 z-50 overflow-auto flex items-center justify-center p-4">
                                    <div class="bg-white rounded shadow-lg w-full max-w-lg">
                                        <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                            <div class="font-semibold text-slate-800">Tambah Refund</div>
                                            <button class="text-slate-400" @click="modalOpen = false">✕</button>
                                        </div>
                                        <form action="{{ route('refund.store') }}" method="post">
                                            @csrf
                                            <div class="px-5 py-4 space-y-3">
                                                <div>
                                                    <label class="block text-sm font-medium mb-1">Nomor Servis <span class="text-rose-500">*</span></label>
                                                    <select id="servis_select" name="servis_transaction_id" class="form-select select2 w-full" required>
                                                        <option value="">-- Pilih Nomor Servis --</option>
                                                        @foreach ($servis as $s)
                                                            <option value="{{ $s->id }}">#{{ $s->nomor_servis }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-1">Nominal Potongan Bonus Teknisi<span class="text-rose-500">*</span></label>
                                                    <input name="nominal" type="number" id="nominal_input" class="form-input w-full disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" readonly style="background: rgb(223, 221, 221)" />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-1">Pengembalian Biaya<span class="text-rose-500">*</span></label>
                                                    <input name="nominal_servis" type="text" id="nominal_input_servis" class="form-input w-full input-currency disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" readonly style="background: rgb(223, 221, 221)" />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-1">Teknisi (otomatis)</label>
                                                    <input id="teknisi_name" type="text" class="form-input w-full" style="background: rgb(223, 221, 221)" readonly />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium mb-1">Bulan/Tahun Potongan</label>
                                                    <input name="period" id="period_input" type="month" class="form-input w-full" />
                                                    <p class="text-xs text-slate-400 mt-1">Pilih bulan dan tahun (input type="month").</p>
                                                </div>
                                            </div>
                                            <div class="text-center px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                                <button type="button" class="btn-sm border-slate-200 text-slate-600" @click="modalOpen = false">Batal</button>
                                                <button class="btn-sm bg-indigo-500 text-white">Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="px-4 py-2 rounded-sm text-sm bg-amber-100 border border-amber-200 text-amber-600">
            <div class="flex w-full justify-between items-start">
                <div class="flex">
                    <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                    </svg>
                    <div class="font-medium">menu transaksi Pengembalian Dana ini untuk perhitungan transaksi yang masih garansi apabila ada pemotongan ke teknisi yang sudah mendapatkan bonus transaksi tersebut.</div>
                </div>
            </div>
        </div>

        {{-- Table and bulk actions --}}
        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Pengembalian Dana</h2>
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span> item yang dipilih</div>
                            <button class="btn bg-white border-slate-200 text-emerald-500 mr-2" id="btn-approve-selected">Setujui</button>
                            <button class="btn bg-white border-slate-200 text-rose-500" id="btn-delete-selected">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto px-5 pb-5">
                <table id="refund-table" class="table-auto w-full display">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                                <th class="text-center px-2 py-3 w-px">
                                    <input id="parent-checkbox" class="form-checkbox" type="checkbox" />
                                </th>
                            @else
                                <th class="hidden"></th>
                            @endif
                            <th class="text-center px-2 py-3">No</th>
                            <th class="text-center px-2 py-3">Nomor Servis</th>
                            <th class="text-center px-2 py-3">Nominal Potongan Teknisi</th>
                            <th class="text-center px-2 py-3">Pengembalian Biaya</th>
                            <th class="text-center px-2 py-3">Teknisi</th>
                            <th class="text-center px-2 py-3">Bulan/Tahun</th>
                            <th class="text-center px-2 py-3">Status</th>
                            @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                                <th class="text-center px-2 py-3">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200 text-center">
                        </tbody>
                </table>
            </div>
        </div>
    </div>
</x-toko-layout>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Init Select2
        $('.select2').select2({ width: '100%' });

        // Init DataTables
        var table = $('#refund-table').DataTable({
            processing: false,
            serverSide: false,
            ajax: "{{ route('refund.index') }}",
            columns: [
                @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                @else
                { data: null, visible: false },
                @endif
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'nomor_servis', name: 'ServiceTransaction.nomor_servis', className: 'text-center' },
                { data: 'nominal', name: 'nominal', className: 'text-center' },
                { data: 'nominal_servis', name: 'nominal_servis', className: 'text-center' },
                { data: 'teknisi_name', name: 'teknisi.name', className: 'text-center' },
                { data: 'period', name: 'period', className: 'text-center' },
                { data: 'is_approve', name: 'is_approve', orderable: false, searchable: false, className: 'text-center' },
                @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                @endif
            ],
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
                // Re-init bulk delete UI logic after table redraw
                updateBulkDeleteUI();
            }
        });

        // --- Logic Bulk Delete (jQuery menggantikan AlpineJS untuk integrasi DataTables) ---

        // 1. Check All
        $('#parent-checkbox').on('change', function() {
            var isChecked = $(this).is(':checked');
            $('.table-item').prop('checked', isChecked);
            updateBulkDeleteUI();
        });

        // 2. Individual Check
        $('#refund-table').on('change', '.table-item', function() {
            var allChecked = $('.table-item').length === $('.table-item:checked').length;
            $('#parent-checkbox').prop('checked', allChecked);
            updateBulkDeleteUI();
        });

        // 3. Update UI Counter
        function updateBulkDeleteUI() {
            var checkedCount = $('.table-item:checked').length;
            if (checkedCount > 0) {
                $('.table-items-action').removeClass('hidden');
                $('.table-items-count').text(checkedCount);
            } else {
                $('.table-items-action').addClass('hidden');
            }
        }

        // 4. Action Delete Selected
        $('#btn-delete-selected').on('click', function() {
            var selectedIds = [];
            $('.table-item:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                alert('Tidak ada item yang dipilih.');
                return;
            }

            if (!confirm('Yakin ingin menghapus data terpilih?')) return;

            $.ajax({
                url: '{{ route("refund.deleteSelected") }}',
                method: 'POST',
                data: {
                    selectedIds: selectedIds,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    alert(res.message);
                    table.ajax.reload(); // Reload DataTables
                    $('#parent-checkbox').prop('checked', false);
                    updateBulkDeleteUI();
                },
                error: function(err) {
                    console.error(err);
                    alert('Gagal menghapus data.');
                }
            });
        });

        // 5. Action Approve Selected
        $('#btn-approve-selected').on('click', async function() {
            var selectedIds = [];
            $('.table-item:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                alert('Tidak ada item yang dipilih.');
                return;
            }

            var tgl_disetujui = await promptTanggalDisetujui();
            if (!tgl_disetujui) return;

            $.ajax({
                url: '{{ route("refund.approveSelected") }}',
                method: 'POST',
                data: {
                    selectedIds: selectedIds,
                    tgl_disetujui: tgl_disetujui,
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    alert(res.message);
                    table.ajax.reload(); // Reload DataTables
                    $('#parent-checkbox').prop('checked', false);
                    updateBulkDeleteUI();
                },
                error: function(err) {
                    console.error(err);
                    alert('Gagal menyetujui data.');
                }
            });
        });

        // --- Auto Fill Teknisi Logic ---
        const teknisiNameInput = document.getElementById('teknisi_name');
        const NominalInput = document.getElementById('nominal_input');
        const NominalInputServis = document.getElementById('nominal_input_servis');

        function formatRupiahInput(el) {
            let value = el.value || '';
            value = value.toString().replace(/\D/g, '');
            el.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        $('#servis_select').on('select2:select change', function () {
            const id = $(this).val();
            teknisiNameInput.value = '';
            NominalInput.value = '';
            NominalInputServis.value = '';

            if (!id) return;

            let url = '{{ url('refund/service') }}/' + id;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    teknisiNameInput.value = data.teknisi_name ?? '';
                    NominalInput.value = data.nominal ?? 0;
                    NominalInputServis.value = data.nominal_servis ?? 0;

                    // Format tampilan
                    formatRupiahInput(NominalInput);
                    formatRupiahInput(NominalInputServis);
                })
                .catch(err => console.error(err));
        });
    });
</script>
