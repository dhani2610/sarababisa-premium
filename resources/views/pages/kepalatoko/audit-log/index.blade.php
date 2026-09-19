@section('title')
    Audit Log Data
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-6">
            <div>
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold flex items-center gap-2">
                    <svg style="width: 24px; height: 24px; min-width: 24px;" class="text-indigo-600 fill-current shrink-0" viewBox="0 0 16 16">
                        <path d="M8 0L2 2.5v5c0 4.1 2.6 7.9 6 8.5 3.4-.6 6-4.4 6-8.5v-5L8 0zm-1 10.5l-2.5-2.5 1.1-1.1 1.4 1.4 3.4-3.4 1.1 1.1L7 10.5z"/>
                    </svg>
                    <span>Audit Log Aktivitas Data</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Catatan riwayat lengkap siapa yang melakukan <strong>Backup</strong>, <strong>Hapus</strong>, <strong>Restore</strong>, dan <strong>Unduh Data</strong>. Dilengkapi waktu detil (tanggal & jam), modul, periode, jumlah baris data, alamat IP, dan perangkat untuk memantau keamanan sistem.
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <a href="{{ route('arsip-data.index') }}" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-700 text-xs px-3 py-2 shadow-sm inline-flex items-center gap-1.5">
                    <svg style="width: 13px; height: 13px; min-width: 13px;" class="text-indigo-500 fill-current shrink-0" viewBox="0 0 16 16">
                        <path d="M2 1a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V2a1 1 0 00-1-1H2zm1 2h10v2H3V3zm0 4h10v2H3V7zm0 4h10v2H3v-2z"/>
                    </svg>
                    <span>Buka Arsip Data</span>
                </a>
            </div>
        </div>

        <!-- Metric Cards with Professional SVG Icons (NO Emojis) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 20px;">
            <div style="padding: 10px 14px; border-radius: 10px; background: #fff; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                <div>
                    <div style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.025em;">Total Aktivitas</div>
                    <div style="font-size: 18px !important; font-weight: 700; font-family: monospace; color: #1e293b; margin-top: 2px;">{{ number_format($totalAktivitas) }}</div>
                </div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #64748b; flex-shrink: 0;">
                    <svg style="width: 15px; height: 15px; min-width: 15px;" class="fill-current" viewBox="0 0 16 16">
                        <path d="M2 1a1 1 0 00-1 1v12a1 1 0 001 1h12a1 1 0 001-1V2a1 1 0 00-1-1H2zm1 2h10v2H3V3zm0 4h10v2H3V7zm0 4h7v2H3v-2z"/>
                    </svg>
                </div>
            </div>

            <div style="padding: 10px 14px; border-radius: 10px; background: #fff; border: 1px solid #d1fae5; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                <div>
                    <div style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #059669; letter-spacing: 0.025em;">Total Backup</div>
                    <div style="font-size: 18px !important; font-weight: 700; font-family: monospace; color: #047857; margin-top: 2px;">{{ number_format($totalBackup) }}</div>
                </div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #ecfdf5; display: flex; align-items: center; justify-content: center; color: #059669; flex-shrink: 0;">
                    <svg style="width: 15px; height: 15px; min-width: 15px;" class="fill-current" viewBox="0 0 16 16">
                        <path d="M8 12l-4-4h2.5V2h3v6H12L8 12zM2 14v-2h12v2H2z"/>
                    </svg>
                </div>
            </div>

            <div style="padding: 10px 14px; border-radius: 10px; background: #fff; border: 1px solid #ffe4e6; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                <div>
                    <div style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #e11d48; letter-spacing: 0.025em;">Total Hapus</div>
                    <div style="font-size: 18px !important; font-weight: 700; font-family: monospace; color: #be123c; margin-top: 2px;">{{ number_format($totalHapus) }}</div>
                </div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #fff1f2; display: flex; align-items: center; justify-content: center; color: #e11d48; flex-shrink: 0;">
                    <svg style="width: 15px; height: 15px; min-width: 15px;" class="fill-current" viewBox="0 0 16 16">
                        <path d="M5 2V1h6v1h4v2H1V2h4zm1 3h2v8H6V5zm4 0h2v8h-2V5z"/>
                    </svg>
                </div>
            </div>

            <div style="padding: 10px 14px; border-radius: 10px; background: #fff; border: 1px solid #e0e7ff; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                <div>
                    <div style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #4f46e5; letter-spacing: 0.025em;">Total Restore</div>
                    <div style="font-size: 18px !important; font-weight: 700; font-family: monospace; color: #3730a3; margin-top: 2px;">{{ number_format($totalRestore) }}</div>
                </div>
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #eef2ff; display: flex; align-items: center; justify-content: center; color: #4f46e5; flex-shrink: 0;">
                    <svg style="width: 15px; height: 15px; min-width: 15px;" class="fill-current" viewBox="0 0 16 16">
                        <path d="M8 3a5 5 0 104.546 2.914.75.75 0 011.36-.632A6.5 6.5 0 118 1.5v-1l3 2-3 2V3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- 1-Row Responsive Filter Bar -->
        <div class="bg-white shadow-sm rounded-xl p-3 mb-6 border border-slate-200">
            <form id="audit-filter-form" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 8px;">
                <div style="width: 125px;">
                    <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; display: block; letter-spacing: 0.025em;">Aksi</label>
                    <select id="filter_action" name="action" style="font-size: 12px !important; padding: 4px 8px !important; height: 32px !important; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; box-sizing: border-box;">
                        <option value="">-- Semua Aksi --</option>
                        <option value="BACKUP">Backup Data</option>
                        <option value="HAPUS">Hapus Data</option>
                        <option value="RESTORE">Restore Data</option>
                        <option value="DOWNLOAD_ARSIP">Unduh Arsip</option>
                        <option value="DOWNLOAD_LANGSUNG">Unduh Langsung</option>
                        <option value="HAPUS_ARSIP">Hapus Arsip</option>
                    </select>
                </div>

                <div style="width: 160px;">
                    <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; display: block; letter-spacing: 0.025em;">Modul</label>
                    <select id="filter_module" name="module" style="font-size: 12px !important; padding: 4px 8px !important; height: 32px !important; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; box-sizing: border-box;">
                        <option value="">-- Semua Modul --</option>
                        @foreach($modules as $key => $mod)
                            <option value="{{ $key }}">{{ $mod['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="width: 135px;">
                    <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; display: block; letter-spacing: 0.025em;">Dari Tanggal</label>
                    <input type="date" id="filter_date_from" name="date_from" style="font-size: 12px !important; padding: 4px 8px !important; height: 32px !important; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; box-sizing: border-box;">
                </div>

                <div style="width: 135px;">
                    <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; display: block; letter-spacing: 0.025em;">Sampai Tanggal</label>
                    <input type="date" id="filter_date_to" name="date_to" style="font-size: 12px !important; padding: 4px 8px !important; height: 32px !important; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; box-sizing: border-box;">
                </div>

                <div style="flex: 1; min-width: 150px;">
                    <label style="font-size: 10.5px !important; font-weight: 700; text-transform: uppercase; color: #64748b; margin-bottom: 3px; display: block; letter-spacing: 0.025em;">Cari (User / IP / Ket)</label>
                    <input type="text" id="filter_q" name="q" placeholder="Cari nama, IP..." style="font-size: 12px !important; padding: 4px 8px !important; height: 32px !important; border-radius: 6px; border: 1px solid #cbd5e1; width: 100%; box-sizing: border-box;">
                </div>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <button type="submit" style="background-color: #4f46e5 !important; color: #ffffff !important; font-size: 12px !important; font-weight: 600; padding: 0 14px !important; height: 32px !important; border-radius: 6px; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 5px;">
                        <svg style="width: 12px; height: 12px; min-width: 12px;" class="fill-current shrink-0" viewBox="0 0 16 16">
                            <path d="M7 14A7 7 0 117 0a7 7 0 010 14zm0-2a5 5 0 100-10 5 5 0 000 10z"/>
                            <path d="M11.707 11.707a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414z"/>
                        </svg>
                        <span>Filter</span>
                    </button>
                    <button type="button" id="btn-reset-audit" style="background-color: #f1f5f9 !important; color: #475569 !important; font-size: 12px !important; padding: 0 10px !important; height: 32px !important; border-radius: 6px; border: 1px solid #e2e8f0; cursor: pointer;" title="Reset Filter">
                        ✕
                    </button>
                </div>
            </form>
        </div>

        <!-- Yajra DataTables Card -->
        <div class="bg-white shadow-sm rounded-xl border border-slate-200 p-4">
            <div class="overflow-x-auto">
                <table id="audit-table" class="table-auto w-full text-xs text-left" style="width:100%">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-3 py-2.5 text-center" style="width: 35px">No</th>
                            <th class="px-3 py-2.5" style="width: 130px">Waktu</th>
                            <th class="px-3 py-2.5">Pengguna</th>
                            <th class="px-3 py-2.5 text-center" style="width: 80px">Aksi</th>
                            <th class="px-3 py-2.5">Modul</th>
                            <th class="px-3 py-2.5">Periode Data</th>
                            <th class="px-3 py-2.5 text-right">Data</th>
                            <th class="px-3 py-2.5">Detail Keterangan</th>
                            <th class="px-3 py-2.5">IP & Perangkat</th>
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
                var table = $('#audit-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route("audit-log-data.data") }}',
                        data: function(d) {
                            d.action = $('#filter_action').val();
                            d.module = $('#filter_module').val();
                            d.date_from = $('#filter_date_from').val();
                            d.date_to = $('#filter_date_to').val();
                            d.q = $('#filter_q').val();
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                        { data: 'waktu', name: 'created_at' },
                        { data: 'pengguna', name: 'user_name' },
                        { data: 'action', name: 'action', className: 'text-center' },
                        { data: 'module_name', name: 'module_name' },
                        { data: 'periode', name: 'start_date' },
                        { data: 'record_count', name: 'record_count', className: 'text-right' },
                        { data: 'description', name: 'description' },
                        { data: 'ip_perangkat', name: 'ip_address' }
                    ],
                    order: [[1, 'desc']],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                    }
                });

                $('#audit-filter-form').on('submit', function(e) {
                    e.preventDefault();
                    table.draw();
                });

                $('#btn-reset-audit').on('click', function() {
                    $('#filter_action').val('');
                    $('#filter_module').val('');
                    $('#filter_date_from').val('');
                    $('#filter_date_to').val('');
                    $('#filter_q').val('');
                    table.draw();
                });

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
            });
        </script>
    @endpush
</x-toko-layout>
