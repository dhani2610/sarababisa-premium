@section('title')
    Riwayat Aktivitas Penjualan
@endsection

<x-toko-layout>
    <style>
        .dataTables_length{
            padding: 15px;
        }
    </style>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Riwayat Aktivitas Penjualan </h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end">

                {{-- Pastikan route delete untuk penjualan sudah dibuat di controller, jika belum bisa di-comment dulu --}}

                <div x-data="{ modalOpen: false }">
                    <a href="{{ route('log-penjualan-destroy')  }}" class="btn bg-rose-500 hover:bg-rose-600 text-white" >
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M4 7l16 0" />
                            <path d="M10 11l0 6" />
                            <path d="M14 11l0 6" />
                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                        </svg>
                        <span class="hidden xs:block ml-2">Bersihkan Log Penjualan</span>
                    </a>
                    </div>

                </div>

        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <header class="px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Riwayat Transaksi</h2>
            </header>

            <div class="overflow-x-auto">
                <table id="log-penjualan-table" class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">No.</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Waktu</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">No. Invoice</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Pelanggan</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Pembuat</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama Barang</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Sebelum</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Sesudah</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        </tbody>
                </table>
            </div>
        </div>

        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

        <style>
            .dataTables_wrapper .dataTables_length select {
                padding-right: 30px;
                width: auto;
            }
            table.dataTable tbody td {
                vertical-align: top; /* Agar teks panjang rapi di atas */
            }
            /* Styling list barang */
            ul.list-disc {
                margin-left: 1rem;
            }
        </style>

        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script>
            $(document).ready(function() {
                var table = $('#log-penjualan-table').DataTable({
                    processing: false, // Ubah ke false agar loading terlihat
                    serverSide: false, // Ubah ke false karena controller pakai DataTables::of($query)
                    ajax: "{{ route('log-penjualan.data') }}", // Pastikan route ini benar
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'nomor_invoice', name: 'subject.invoice_no' }, // Mapping ke subject invoice
                        { data: 'pelanggan', name: 'subject.nama_pelanggan' }, // Mapping ke subject pelanggan
                        { data: 'pembuat', name: 'causer.name' }, // Mapping ke user
                        { data: 'nama_barang', name: 'nama_barang', orderable: false, searchable: false },
                        { data: 'sebelum', name: 'sebelum', orderable: false, searchable: false },
                        { data: 'sesudah', name: 'sesudah', orderable: false, searchable: false },
                    ],
                    order: [
                        [1, 'desc'] // Default urut waktu terbaru (Kolom index 1)
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                    },
                });
            });
        </script>

    </div>
</x-toko-layout>
