@section('title')
    Transaksi Produk
@endsection
<x-toko-layout>


    <!-- ✅ DataTables core CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- ✅ (Opsional) DataTables Buttons extension CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

    <!-- ✅ (Opsional) Jika kamu pakai Bootstrap -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <style>
            div.dataTables_wrapper div.dataTables_length select {
                width: 47%!important;
                display: inline-block;
            }
            div.dataTables_wrapper{
                padding: 1%!important;
            }
        </style>

        <div>
            <!-- Page header -->
            <div class="sm:flex sm:justify-between sm:items-center mb-3">

                <!-- Left: Title -->
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Transaksi Produk ✨</h1>
                </div>

            </div>

            <!-- More actions -->
            <div class="sm:flex sm:justify-between sm:items-center mb-5">

                <!-- Left side -->
                <div class="mb-4 sm:mb-0">
                    <ul class="flex flex-wrap -m-1">
                        <li class="m-1">
                            <a href="{{ route('transaksi-produk.index') }}">
                                <button
                                    class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-indigo-500 text-white duration-150 ease-in-out">Semua
                                    <span class="ml-1 text-indigo-200">{{ $jumlah_semua }}</span></button>
                            </a>
                        </li>
                        <li class="m-1">
                            <a href="{{ route('transaksi-produk-paid.index') }}">
                                <button
                                    class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Lunas
                                    <span class="ml-1 text-slate-400">{{ $jumlah_lunas }}</span></button>
                            </a>
                        </li>
                        <li class="m-1">
                            <a href="{{ route('transaksi-produk-due.index') }}">
                                <button
                                    class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Belum
                                    Lunas <span class="ml-1 text-slate-400">{{ $jumlah_tidaklunas }}</span></button>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Right side -->
                <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                    <div class="mb-0">
                        <select wire:model="paginate" id="" class="form-select">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="250">250</option>
                            <option value="500">500</option>
                            <option value="1000">1000</option>
                        </select>
                    </div>
                </div>

            </div>

            <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
                <div x-data="handleSelect">
                    <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                        {{-- Left side --}}
                        <h2 class="font-semibold text-slate-800">Semua <span
                                class="text-slate-400 font-medium">{{ $jumlah_semua }}</span></h2>
                        {{-- Right side --}}
                        <div class="relative inline-flex">
                            <div class="table-items-action hidden">
                                <div class="flex items-center">
                                    <div class="text-sm italic mr-2 whitespace-nowrap"><span
                                            class="table-items-count"></span> item yang dipilih</div>
                                    <div class="space-x-1">
                                        <button
                                            class="btn bg-white border-slate-200 hover:border-slate-300 text-blue-500 hover:text-blue-600"
                                            @click="approveSelected">Setujui</button>
                                        <button
                                            class="btn bg-white border-slate-200 hover:border-slate-300 text-gray-900 hover:text-gray-950"
                                            @click="rejectSelected">Tolak</button>
                                        <button
                                            class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600"
                                            @click="deleteSelected">Hapus</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="overflow-x-auto">

                        <table id="transaksiTable" class="table-auto w-full text-sm">
                            <thead
                                class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                                <tr>
                                    <th>No.</th>
                                    <th>Invoice</th>
                                    <th>Tgl Transaksi</th>
                                    <th>Sales</th>
                                    <th>Pelanggan</th>
                                    <th>Pembayaran</th>
                                    @if (Auth::user()->role != 'Investor')
                                        <th>Modal</th>
                                    @endif
                                    <th>Total Harga</th>
                                    <th>Jumlah Pembayaran</th>
                                    <th>Sisa Pembayaran</th>
                                    @if (Auth::user()->role != 'Investor')
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('handleSelect', () => ({
                        selectall: false,
                        selectAction() {
                            countEl = document.querySelector('.table-items-action');
                            if (!countEl) return;
                            checkboxes = document.querySelectorAll('input.table-item:checked');
                            document.querySelector('.table-items-count').innerHTML = checkboxes.length;
                            if (checkboxes.length > 0) {
                                countEl.classList.remove('hidden');
                            } else {
                                countEl.classList.add('hidden');
                            }
                        },
                        toggleAll() {
                            this.selectall = !this.selectall;
                            checkboxes = document.querySelectorAll('input.table-item');
                            [...checkboxes].map((el) => {
                                el.checked = this.selectall;
                            });
                            this.selectAction();
                        },
                        uncheckParent() {
                            this.selectall = false;
                            document.getElementById('parent-checkbox').checked = false;
                            this.selectAction();
                        },
                        deleteSelected() {
                            const checkboxes = document.querySelectorAll('input.table-item:checked');
                            const selectedIds = [...checkboxes].map((checkbox) => checkbox.value);

                            // Kirim permintaan penghapusan ke server
                            fetch('/product-transactions/delete', {
                                    method: 'DELETE',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    },
                                    body: JSON.stringify({
                                        selectedIds
                                    }),
                                })
                                .then(response => response.json())
                                .then(data => {
                                    alert(data.message);
                                    // Refresh halaman atau lakukan tindakan lain setelah penghapusan
                                    window.location.reload();
                                })
                                .catch(error => {
                                    console.error('Gagal menghapus data:', error);
                                });
                        },
                        approveSelected() {
                            const checkboxes = document.querySelectorAll('input.table-item:checked');
                            const selectedIds = [...checkboxes].map((checkbox) => checkbox.value);

                            // Kirim permintaan update ke server
                            fetch('/product-transactions/update', {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    },
                                    body: JSON.stringify({
                                        selectedIds
                                    }),
                                })
                                .then(response => response.json())
                                .then(data => {
                                    alert(data.message);
                                    // Refresh halaman atau lakukan tindakan lain setelah update
                                    window.location.reload();
                                })
                                .catch(error => {
                                    console.error('Gagal memperbarui data:', error);
                                });
                        },
                        rejectSelected() {
                            const checkboxes = document.querySelectorAll('input.table-item:checked');
                            const selectedIds = [...checkboxes].map((checkbox) => checkbox.value);

                            // Kirim permintaan update ke server
                            fetch('/product-transactions/reject', {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    },
                                    body: JSON.stringify({
                                        selectedIds
                                    }),
                                })
                                .then(response => response.json())
                                .then(data => {
                                    alert(data.message);
                                    // Refresh halaman atau lakukan tindakan lain setelah update
                                    window.location.reload();
                                })
                                .catch(error => {
                                    console.error('Gagal memperbarui data:', error);
                                });
                        },
                    }))
                })
            </script>
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>

            <script>
                $(document).ready(function() {
                    $('#transaksiTable').DataTable({
                        processing: true,
                        serverSide: false,
                        ajax: '{{ route('transaksi-produk.data') }}',
                        columns: [{
                                data: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'invoice_no'
                            },
                            {
                                data: 'tgl_transaksi'
                            },
                            {
                                data: 'sales'
                            },
                            {
                                data: 'pelanggan'
                            },
                            {
                                data: 'pembayaran'
                            },
                            @if (Auth::user()->role != 'Investor')
                                {
                                    data: 'modal'
                                },
                            @endif {
                                data: 'sub_total'
                            },
                            {
                                data: 'pay'
                            },
                            {
                                data: 'sisa',
                                render: function(data) {
                                    return data ?? '';
                                }
                            },
                            @if (Auth::user()->role != 'Investor')
                                {
                                    data: 'status',
                                    render: function(data) {
                                        return data ?? '';
                                    }
                                }, {
                                    data: 'aksi',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        return data; // ✅ tampilkan HTML dari server
                                    }
                                }
                            @endif
                        ],
                        order: [
                            [2, 'desc']
                        ],
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                        },
                        columnDefs: [{
                                targets: '_all',
                                defaultContent: ''
                            } // biar ga error kalau data null
                        ]
                    });
                });
            </script>

            <!-- Pagination -->
            {{-- <div class="mt-8">
        {{ $orders->links() }}
    </div> --}}
        </div>
    </div>

</x-toko-layout>
