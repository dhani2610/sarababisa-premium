@section('title')
    Transaksi Produk
@endsection
<x-toko-layout>


    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <style>
            div.dataTables_wrapper div.dataTables_length select {
                width: 47%!important;
                display: inline-block;
            }
            div.dataTables_wrapper{
                padding: 1%!important;
            }
            .select2-container {
                width: 100% !important;
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

                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500"
                        @click.prevent="modalOpen = true">
                        Export Belum Lunas
                    </button>

                    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                        <div class="fixed inset-0 bg-slate-900 bg-opacity-30" @click="modalOpen = false"></div>
                        <div class="bg-white rounded shadow-lg max-w-sm w-full p-5 z-10">
                            <h2 class="font-semibold text-slate-800 mb-4">Pilih Customer</h2>
                            <form action="{{ route('cetak-customer-produk-belum-lunas') }}" method="get"
                                target="_blank">
                                <select name="customers_id" class="form-select selectjs2 w-full mb-4" required>
                                    <option value="">Pilih Pelanggan</option>
                                    @foreach ($customers as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama }} - {{ $item->nomor_hp }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="flex justify-end space-x-2">
                                    <button type="button" class="btn border-slate-200"
                                        @click="modalOpen = false">Batal</button>
                                    <button type="submit" class="btn bg-indigo-500 text-white">Cetak</button>
                                </div>
                            </form>
                        </div>
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="overflow-x-auto p-4">

                        <table id="transaksiTable" class="table-auto w-full text-sm">
                            <thead
                                class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                                <tr>
                                    @if (Auth::user()->role == 'Kepala Toko')
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        <div class="flex items-center">
                                            <label class="inline-flex">
                                                <span class="sr-only">Select all</span>
                                                <input id="parent-checkbox" class="form-checkbox" type="checkbox" @click="toggleAll" />
                                            </label>
                                        </div>
                                    </th>
                                    @endif
                                    <th>No.</th>
                                    <th>Invoice</th>
                                    <th width="30%">Item</th>
                                    <th>Tgl Transaksi</th>
                                    <th>Sales</th>
                                    <th>Pelanggan</th>
                                    <th>Pembayaran</th>
                                    @if (Auth::user()->role != 'Investor')
                                        @if (Auth::user()->role == 'Kepala Toko' || $storeSettings->is_modal_produk == 1)
                                        <th>Modal</th>
                                        @endif
                                    @endif
                                    <th>Total Harga</th>
                                    <th>Jumlah Pembayaran</th>
                                    <th>Sisa Pembayaran</th>
                                    <th>Status Pembayaran</th>
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
                            console.log('klik');

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
                            fetch('/product-transactions/update-lunas', {
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
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

            <script>
                 $(document).ready(function() {
                    $('.selectjs2').select2();
                });
                $(document).ready(function() {
                    let table = $('#transaksiTable').DataTable({
                        processing: false,
                        serverSide: false,
                        // ajax: '{{ route('transaksi-produk.data') }}',
                        columns: [
                            @if (Auth::user()->role == 'Kepala Toko')
                            {
                                data: 'checkbox',
                                orderable: false,
                                searchable: false,
                                render: function(data, type, row) {
                                    return data;
                                }
                            },
                            @endif
                            {
                                data: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'invoice_no'
                            },
                            {
                                data: 'item',
                                render: function(data) {
                                    return data ?? '';
                                }
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
                                @if (Auth::user()->role == 'Kepala Toko' || $storeSettings->is_modal_produk == 1)
                                {
                                    data: 'modal'
                                },
                                 @endif
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
                            {
                                data: 'status_pembayaran',
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
                        order: [],
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                        },
                        columnDefs: [{
                                targets: '_all',
                                defaultContent: ''
                            } // biar ga error kalau data null
                        ]
                    });

                    // --- custom pagination load bertahap ---
                    let batchSize = 20;
                    let offset = 0;
                    let loading = false;

                    function loadBatch() {
                        if (loading) return;
                        loading = true;
                        $.ajax({
                            url: '{{ route('transaksi-produk-belum-lunas.data') }}?offset=' + offset + '&limit=' + batchSize,
                            success: function(response) {
                                if (response.data.length > 0) {
                                    table.rows.add(response.data).draw(false);
                                    offset += batchSize;
                                    loading = false;
                                    // lanjut load batch berikutnya
                                    setTimeout(loadBatch, 100);
                                } else {
                                    console.log('semua data sudah dimuat');
                                }
                            }
                        });
                    }

                    // mulai load pertama
                    loadBatch();

                    $('#parent-checkbox').on('click', function() {
                        const isChecked = $(this).is(':checked');
                        $('.table-item').prop('checked', isChecked);
                        toggleBulkAction();
                    });

                    // Handle individual checkbox
                    $('#transaksiTable').on('change', '.table-item', function() {
                        const allChecked = $('.table-item').length === $('.table-item:checked').length;
                        $('#parent-checkbox').prop('checked', allChecked);
                        toggleBulkAction();
                    });

                    // Fungsi untuk toggle tampilan "x item dipilih"
                    function toggleBulkAction() {
                        const checkedCount = $('.table-item:checked').length;
                        $('.table-items-count').text(checkedCount);

                        if (checkedCount > 0) {
                            $('.table-items-action').removeClass('hidden');
                        } else {
                            $('.table-items-action').addClass('hidden');
                        }
                    }
                });
            </script>

            <!-- Pagination -->
            {{-- <div class="mt-8">
        {{ $orders->links() }}
    </div> --}}
        </div>
    </div>

</x-toko-layout>
