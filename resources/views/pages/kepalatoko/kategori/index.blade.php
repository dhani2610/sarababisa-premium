@section('title')
    Kategori Produk
@endsection

<x-toko-layout>
     <style>
        .dataTables_wrapper .dataTables_length select{
            width: 68px!important;
        }
    </style>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Kategori Produk ✨</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <div x-data="{ modalOpen: false }">
                    {{-- <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="ml-2">Tambah Kategori</span>
                    </button> --}}

                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-cloak></div>
                    <div class="fixed inset-0 z-50 flex items-center justify-center px-4" x-show="modalOpen" x-cloak>
                        <div class="bg-white rounded shadow-lg w-full max-w-md" @click.outside="modalOpen = false">
                            <form method="POST" action="{{ route('kategori.store') }}">
                                @csrf
                                <div class="px-5 py-4 border-b">
                                    <h2 class="font-semibold text-slate-800">Tambah Kategori</h2>
                                </div>
                                <div class="px-5 py-4">
                                    <label class="block text-sm font-medium mb-1" for="category_name">Nama Kategori</label>
                                    <input type="text" name="category_name" id="category_name" class="form-input w-full" required>
                                    <label class="block text-sm font-medium mb-1 mt-2" for="show_portal">Show Portal</label>
                                    <select name="show_portal" id="" class="form-input w-full">
                                        <option value="1">Active</option>
                                        <option value="0">Non Active</option>
                                    </select>
                                </div>
                                <div class="px-5 py-4 border-t flex justify-end gap-2">
                                    <button type="button" class="btn-sm border" @click="modalOpen = false">Batal</button>
                                    <button type="submit" class="btn-sm bg-indigo-500 text-white">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <div class="p-5">
                <div class="overflow-x-auto">
                    <table id="kategoriTable" class="table-auto w-full">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">ID</th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">Nama Kategori</th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">Show Portal</th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-toko-layout>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        $('#kategoriTable').DataTable({
            // Sesuai request: search biasa gaperlu ke backend -> Client Side processing
            serverSide: false,
            processing: false,
            ajax: "{{ route('kategori.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'category_name', name: 'category_name' },
                { data: 'show_portal', name: 'show_portal' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "Lanjut",
                    previous: "Kembali"
                }
            }
        });
    });
</script>
