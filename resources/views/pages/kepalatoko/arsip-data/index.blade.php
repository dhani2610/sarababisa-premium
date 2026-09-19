@section('title')
    Arsip Data
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold flex items-center gap-2">
                    <svg style="width: 24px; height: 24px; min-width: 24px;" class="text-indigo-600 fill-current shrink-0" viewBox="0 0 16 16">
                        <path d="M2 1a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V2a1 1 0 00-1-1H2zm1 2h10v2H3V3zm0 4h10v2H3V7zm0 4h10v2H3v-2z"/>
                    </svg>
                    <span>Arsip Data</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Daftar seluruh file backup data (<code>.sql</code>) yang tersimpan di server VPS. Data ini dapat diunduh atau dipulihkan (restore) kapan saja.
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <a href="{{ route('sistem') }}" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-700 text-xs px-3 py-2 shadow-sm inline-flex items-center gap-1.5">
                    <svg style="width: 13px; height: 13px; min-width: 13px;" class="text-slate-400 fill-current shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H3.414l4.293-4.293a1 1 0 00-1.414-1.414l-6 6a1 1 0 000 1.414l6 6a1 1 0 001.414-1.414L3.414 9H15a1 1 0 000-2z" />
                    </svg>
                    <span>Pengaturan Toko</span>
                </a>
            </div>
        </div>

        <!-- 1-Row Responsive Filter Bar -->
        <div class="bg-white shadow-sm rounded-xl p-3 mb-6 border border-slate-200">
            <form id="arsip-filter-form" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 8px;">
                <div style="flex: 1; min-width: 180px;">
                    <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; display: block; letter-spacing: 0.025em;">Filter Modul</label>
                    <select id="filter_module" name="module" style="font-size: 12px !important; padding: 4px 8px !important; height: 32px !important; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; box-sizing: border-box;">
                        <option value="">-- Semua Modul --</option>
                        @foreach($modules as $key => $mod)
                            <option value="{{ $key }}">{{ $mod['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="width: 150px;">
                    <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; display: block; letter-spacing: 0.025em;">Dari Tanggal</label>
                    <input type="date" id="filter_start_date" name="start_date" style="font-size: 12px !important; padding: 4px 8px !important; height: 32px !important; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; box-sizing: border-box;">
                </div>

                <div style="width: 150px;">
                    <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; display: block; letter-spacing: 0.025em;">Sampai Tanggal</label>
                    <input type="date" id="filter_end_date" name="end_date" style="font-size: 12px !important; padding: 4px 8px !important; height: 32px !important; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; box-sizing: border-box;">
                </div>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <button type="submit" style="background-color: #4f46e5 !important; color: #ffffff !important; font-size: 12px !important; font-weight: 600; padding: 0 14px !important; height: 32px !important; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                        <svg style="width: 12px; height: 12px; min-width: 12px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                            <path d="M7 14A7 7 0 117 0a7 7 0 010 14zm0-2a5 5 0 100-10 5 5 0 000 10z"/>
                            <path d="M11.707 11.707a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414z"/>
                        </svg>
                        <span>Filter</span>
                    </button>
                    <button type="button" id="btn-reset-arsip" style="background-color: #f1f5f9 !important; color: #475569 !important; font-size: 12px !important; padding: 0 10px !important; height: 32px !important; border-radius: 6px; border: 1px solid #e2e8f0; cursor: pointer;" title="Reset Filter">
                        ✕
                    </button>
                </div>
            </form>
        </div>

        <!-- Yajra DataTables Card -->
        <div class="bg-white shadow-sm rounded-xl border border-slate-200 p-4">
            <div class="overflow-x-auto">
                <table id="arsip-table" class="table-auto w-full text-xs text-left" style="width:100%">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-3 py-2.5 text-center" style="width: 40px">No</th>
                            <th class="px-3 py-2.5">Tanggal Backup</th>
                            <th class="px-3 py-2.5">Modul</th>
                            <th class="px-3 py-2.5">Periode Data</th>
                            <th class="px-3 py-2.5 text-right">Total Data</th>
                            <th class="px-3 py-2.5 text-center">Ukuran File</th>
                            <th class="px-3 py-2.5">Dibuat Oleh</th>
                            <th class="px-3 py-2.5 text-center" style="width: 140px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
        </div>

    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <link rel="stylesheet" href="{{ asset('css/sweetalert2-custom.css') }}?v={{ time() }}">
        <style>
            .dataTables_wrapper .dataTables_length select {
                font-size: 12px;
                padding: 3px 24px 3px 8px;
                border-radius: 6px;
                border: 1px solid #cbd5e1;
            }
            .dataTables_wrapper .dataTables_filter input {
                font-size: 12px;
                padding: 4px 8px;
                border-radius: 6px;
                border: 1px solid #cbd5e1;
                margin-left: 6px;
            }
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                font-size: 12px;
                margin-top: 12px;
            }
            .dataTables_wrapper .dataTables_paginate .paginate_button.current {
                background: #4f46e5 !important;
                color: #ffffff !important;
                border: 1px solid #4f46e5 !important;
                border-radius: 6px;
            }
            table.dataTable.no-footer {
                border-bottom: 1px solid #e2e8f0;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {
                var table = $('#arsip-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("arsip-data.data") }}',
                        data: function(d) {
                            d.module = $('#filter_module').val();
                            d.start_date = $('#filter_start_date').val();
                            d.end_date = $('#filter_end_date').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                        { data: 'created_at_formatted', name: 'created_at' },
                        { data: 'module_formatted', name: 'module_name' },
                        { data: 'periode', name: 'start_date' },
                        { data: 'record_count_formatted', name: 'record_count', className: 'text-right' },
                        { data: 'file_size_formatted', name: 'file_size', className: 'text-center' },
                        { data: 'created_by_formatted', name: 'created_by' },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
                    ],
                    order: [[1, 'desc']],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                    }
                });

                $('#arsip-filter-form').on('submit', function(e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#btn-reset-arsip').on('click', function() {
                    $('#filter_module').val('');
                    $('#filter_start_date').val('');
                    $('#filter_end_date').val('');
                    table.draw();
                });
            });

            function confirmDeleteArchive(e, form) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus Arsip Backup?',
                    text: 'File arsip SQL ini akan dihapus permanen dari server VPS.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus File',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#64748b'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
                return false;
            }

            @if(session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    confirmButtonColor: '#4f46e5'
                });
            @endif

            @if(session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Perhatian',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#e11d48'
                });
            @endif
        </script>
    @endpush
</x-toko-layout>
