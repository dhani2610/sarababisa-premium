    <style>
        .dataTables_wrapper .dataTables_length select{
            width: 68px!important;
        }
    </style>
<div>
    <div class="sm:flex sm:justify-between sm:items-center mt-5">
        {{-- <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <select wire:model="paginate" id="" class="form-select mb-2 md:mb-0">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div> --}}

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Print button -->
            <div class="relative inline-flex" x-data="{ modalOpen: false }">
                <button
                    class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500 hover:text-slate-600 mb-2 md:mb-0"
                    @click.prevent="modalOpen = true" aria-controls="tambah-modal"
                >
                    <span class="sr-only">Print</span><wbr>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <rect x="7" y="13" width="10" height="8" rx="2" />
                    </svg>
                </button>
                <!-- Modal backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-out duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    aria-hidden="true"
                    x-cloak
                ></div>
                <!-- Modal dialog -->
                <div
                    id="tambah-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog"
                    aria-modal="true"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-in-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4"
                    x-cloak
                >
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Atur Pencetakan Laporan</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Modal content -->
                        <form action="{{ route('cetak-laporan-penjualan') }}" method="get"  target="_blank">
                            @csrf
                            <div class="px-5 py-4">
                                <div class="space-y-3">
                                    @if (Auth::user()->role == 'Investor')
                                    <input type="hidden" value="Sudah Disetujui" name="tipe"    >
                                    @else
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Tipe <span class="text-rose-500">*</span></label>
                                        <select name="tipe" class="form-select text-sm py-1 w-full" id="tipe">
                                            <option value="Sudah Disetujui">Sudah Disetujui</option>
                                            <option value="Belum Disetujui">Belum Disetujui</option>
                                        </select>
                                    </div>
                                    @endif
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
                            <!-- Modal footer -->
                            <div class="px-5 py-4 border-t border-slate-200">
                                <div class="flex flex-wrap justify-end space-x-2">
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Cetak</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Search form -->
            {{-- <x-search-form placeholder="Masukkan nama produk" /> --}}
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <header class="px-5 py-4">
            <h2 class="font-semibold text-slate-800">Jumlah Produk Terjual <span class="text-slate-400 font-medium">{{ $jumlah }}</span></h2>
        </header>
        <!-- Table -->
        <div class="overflow-x-auto">
            <table id="laporanPenjualanTable" class="table-auto w-full">
                <!-- Table header -->
                <thead  class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">No</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Tanggal</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Sales</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Nama Produk</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Jumlah</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Modal</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Harga Jual</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Profit Toko</div>
                        </th>
                        @if ($toko->is_tax === 1)
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">PPN</div>
                            </th>
                        @endif
                    </tr>
                </thead>
                <!-- Table body -->
                <tbody class="text-sm divide-y divide-slate-200">
                    <!-- Row -->
                    {{-- @foreach($product_transactions as $item)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</div>
                            </td>
                            @if ($item->user)
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->user->name }}</div>
                                </td>
                            @else
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium text-red-600">Akun sudah dihapus</div>
                                </td>
                            @endif
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if ($item->product != null)
                                    <div class="font-medium">
                                        @if ($item->product->categories_id == 1)
                                            {{ $item->product->product_name }} {{ $item->product->kondisi }} {{ $item->product->warna }} {{ $item->product->ram }}/@if ($item->product->capacity != null)
                                                {{ $item->product->capacity->name }}
                                            @else
                                                -
                                            @endif {{ $item->product->keterangan }} (IMEI {{ $item->product->nomor_seri }})
                                        @else
                                            {{ $item->product->product_name }} {{ $item->product->keterangan }}
                                        @endif
                                    </div>
                                @else
                                    <div class="font-medium text-red-600">Data produk sudah dihapus</div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ $item->quantity }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">Rp. {{ number_format($item->modal) }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">Rp. {{ number_format($item->total) }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">
                                    Rp. {{ number_format($item->profit_toko) }}
                                </div>
                            </td>
                            @if ($toko->is_tax === 1)
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">
                                        Rp. {{ number_format($item->ppn) }}
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach --}}
                </tbody>
            </table>

        </div>
    </div>
    <!-- Pagination -->
    {{-- <div class="mt-8">
        {{ $product_transactions->links() }}
    </div> --}}



    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#laporanPenjualanTable').DataTable({
                processing: false,
                serverSide: false,
                columns: [
                    {
                        data: null,
                        sortable: false,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'sales_name', name: 'user.name' },
                    { data: 'product_display_name', name: 'product_name' }, // Search by product_name column
                    { data: 'quantity', name: 'quantity' },
                    { data: 'modal', name: 'modal' },
                    { data: 'total', name: 'total' },
                    { data: 'profit_toko', name: 'profit_toko' },
                    @if ($toko->is_tax === 1)
                    { data: 'ppn', name: 'ppn' }
                    @endif
                ],
                order: [[0, 'asc']],
                language: {
                    search: "Cari Produk / Sales:",
                    lengthMenu: "Tampilkan _MENU_ data",
                }
            });

            let batchSize = 200;
            let offset = 0;
            let loading = false;

            function loadBatch() {
                if (loading) return;
                loading = true;

                $.ajax({
                    url: '{{ route('laporan-penjualan') }}?offset=' + offset + '&limit=' + batchSize,
                    success: function(response) {
                        if (response.data.length > 0) {
                            table.rows.add(response.data).draw(false); // tambahkan data batch
                            offset += batchSize;
                            loading = false;
                            setTimeout(loadBatch, 200); // lanjut batch berikutnya
                        } else {
                            console.log('Semua data sudah dimuat');
                        }
                    }
                });
            }
            loadBatch();
        });
    </script>

</div>
