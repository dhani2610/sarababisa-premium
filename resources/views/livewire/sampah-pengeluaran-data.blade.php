@section('title')
    Keranjang Sampah Pengeluaran
@endsection
 <style>
        .dataTables_wrapper .dataTables_length select{
            width: 68px!important;
        }
    </style>
        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Keranjang Sampah ✨</h1>
            </div>
        </div>

        <div class="sm:flex sm:justify-between sm:items-center mb-5">
            <div class="mb-4 sm:mb-0">
               <ul class="flex flex-wrap -m-1">
                    <li class="m-1">
                        <a href="{{ route('keranjang-servis') }}">
                            <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Servis <span class="ml-1 text-indigo-200"></span></button>
                        </a>
                    </li>
                    <li class="m-1">
                        <a href="{{ route('keranjang-penjualan') }}">
                            <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Penjualan <span class="ml-1 text-indigo-200"></span></button>
                        </a>
                    </li>
                    <li class="m-1">
                        <a href="{{ route('keranjang-akun') }}">
                            <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Akun <span class="ml-1 text-indigo-200"></span></button>
                        </a>
                    </li>
                    <li class="m-1">
                        <a href="{{ route('keranjang-pelanggan') }}">
                            <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Pelanggan <span class="ml-1 text-indigo-200"></span></button>
                        </a>
                    </li>
                    <li class="m-1">
                        <a href="{{ route('keranjang-produk') }}">
                            <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Produk <span class="ml-1 text-indigo-200"></span></button>
                        </a>
                    </li>
                    <li class="m-1">
                        <a href="{{ route('keranjang-insiden') }}">
                            <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Insiden <span class="ml-1 text-indigo-200"></span></button>
                        </a>
                    </li>
                    <li class="m-1">
                        <a href="{{ route('keranjang-kasbon') }}">
                            <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Kasbon <span class="ml-1 text-indigo-200"></span></button>
                        </a>
                    </li>
                    <li class="m-1">
                        <a href="{{ route('keranjang-pengeluaran') }}">
                            <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-indigo-500 text-white duration-150 ease-in-out">Pengeluaran <span class="ml-1 text-slate-400"></span></button>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-rose-500 hover:bg-rose-600 text-white" @click.prevent="modalOpen = true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current shrink-0" viewBox="0 0 24 24">
                            <path d="M4 7l16 0M10 11l0 6M14 11l0 6M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" stroke="currentColor" stroke-width="1.5" fill="none"/>
                        </svg>
                        <span class="hidden xs:block ml-2">Bersihkan sampah pengeluaran</span>
                    </button>
                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>
                    <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false">
                            <div class="p-5 flex space-x-4">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                                    <svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16"><path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" /></svg>
                                </div>
                                <div>
                                    <div class="text-lg font-semibold text-slate-800 mb-2">Apakah anda sudah yakin?</div>
                                    <div class="text-sm mb-10">Jika sudah terhapus secara permanen, data tidak bisa dikembalikan.</div>
                                    <div class="flex justify-end space-x-2">
                                        <button class="btn-sm border-slate-200 text-slate-600" @click="modalOpen = false">Batal</button>
                                        <form action="{{ route('bersihkan-keranjang-pengeluaran') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn-sm bg-rose-500 text-white">Ya, Hapus Semua</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200">
            <header class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-800">Semua Data Pengeluaran <span class="text-slate-400 font-medium">{{ $items_count }}</span></h2>
            </header>
            <div class="p-3">
                <div class="overflow-x-auto">
                    <table id="expenseTrashTable" class="table-auto w-full">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">No.</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Tanggal Dihapus</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Akun Pembuat</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Item</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Nominal</div></th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-center">Aksi</div></th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            {{-- Data dimuat via AJAX --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#expenseTrashTable').DataTable({
                processing: true,
                serverSide: false, // Gunakan true jika data ribuan
                ajax: "{{ route('keranjang-pengeluaran') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'deleted_at', name: 'deleted_at' },
                    { data: 'user_name', name: 'user_name' },
                    { data: 'name', name: 'name' },
                    { data: 'price', name: 'price' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                language: {
                    search: "Cari data:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    }
                }
            });
        });
    </script>
