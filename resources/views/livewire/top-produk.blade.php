<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Item Produk ✨</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            <!-- Search form -->
            {{-- <x-search-form placeholder="Masukkan nama produk" /> --}}

        </div>

    </div>

    <!-- More actions -->
    <div class="sm:flex sm:justify-between sm:items-center mb-5">

        
        <!-- Left side -->
        <div class="mb-4 sm:mb-0">
            <ul class="flex flex-wrap -m-1">
            @php
                if (Auth::user()->role == 'Kepala Toko') {
                    $route = route('top-produk-kepala-toko');
                } else {
                    $route = route('top-produk');
                }
            @endphp

            {{-- Tombol Semua --}}
            <li class="m-1">
                <a href="{{ $route }}">
                    <button
                        class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 shadow-sm duration-150 ease-in-out 
                        {{ empty($categoryId) ? 'bg-indigo-500 text-white' : 'bg-white text-slate-700 border' }}">
                        Semua
                    </button>
                </a>
            </li>

            {{-- Tombol per kategori --}}
            @foreach ($categories as $cat)
                <li class="m-1">
                    <a href="{{ $route }}?id={{ $cat->id }}">
                        <button
                            class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 shadow-sm duration-150 ease-in-out
                            {{ $categoryId == $cat->id ? 'bg-indigo-500 text-white' : 'bg-white text-slate-700 border' }}">
                            {{ $cat->category_name }}
                        </button>
                    </a>
                </li>
            @endforeach
        </ul>

        </div>

        <!-- Right side -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Print button -->
           
            {{-- <div>
                <select wire:model="paginate" id="" class="form-select">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div> --}}
        </div>
    </div>


    <!-- Ranking Produk -->
    <div class="bg-white shadow-lg rounded-lg mt-5">
        <div class="px-5 py-4 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800">Top Produk Terlaris</h2>
        </div>
        <div class="divide-y divide-slate-200">

             <div class="overflow-x-auto">
                <table id="top-produk-list" class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th>#</th>
                        <th>Produk</th>
                        <th>Stat</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

            {{-- @php
                $rank = ($topProducts->currentPage() - 1) * $topProducts->perPage() + 1;
            @endphp

            @foreach($topProducts as $itemtop)
                <div class="flex justify-between items-center px-5 py-3">
                    <div class="flex items-center space-x-3">
                        <span class="text-lg font-bold text-slate-500">{{ $rank++ }}.</span>
                        <span class="font-medium text-slate-800">

                             @if ($itemtop->categories_id == 1)
                                {{ $itemtop->product_name }} {{ $itemtop->kondisi }} {{ $itemtop->warna }} {{ $itemtop->ram }} / @if ($itemtop->capacity != null)
                                    {{ $itemtop->capacity->name }}
                                @else
                                    -
                                @endif (IMEI {{ $itemtop->nomor_seri }})
                            @else
                                {{ $itemtop->product_name }} {{ $itemtop->nomor_seri }}
                            @endif
                        </span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm">Terjual: <span class="font-semibold">{{ $itemtop->total_terjual }}</span></p>
                        <p class="text-sm text-emerald-600">Rp {{ number_format($itemtop->omzet, 0, ',', '.') }}</p>
                    </div>
                </div>
            @endforeach --}}
        </div>
    </div>

    

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_wrapper .dataTables_length select {
            padding-right: 30px;
            width: auto;
        }
    </style>

    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
    $(function () {

        let categoryId = "{{ request('id') }}";

        const table = $('#top-produk-list').DataTable({
            processing: false,
            serverSide: false,
            paging: true,
            searching: true,
            info: false,
            ordering: false,
            ajax: {
                url: "{{ route('top-produk-kepala-toko.data') }}",
                data: function (d) {
                    d.search = $('input[type=search]').val();
                    d.category_id = categoryId;
                }
            },
            pageLength: 10,
            columns: [
                { data: 'DT_RowIndex', searchable:false },
                { data: 'display' },
                { data: 'stat' },
            ],
            
            order: [[1, 'asc']], // Default urut berdasarkan Nama (index ke-2)
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
            rowCallback: function (row, data, index) {
                const rank = data.DT_RowIndex;
                $(row).html(`
                    <td colspan="3">
                        <div class="flex justify-between items-center px-5 py-3">
                            <div class="flex items-center space-x-3">
                                <span class="text-lg font-bold text-slate-500">${rank}.</span>
                                <span class="font-medium text-slate-800">${data.display}</span>
                            </div>
                            <div class="text-right">${data.stat}</div>
                        </div>
                    </td>
                `);
            }

        });

        // 🔍 SEARCH
        $(document).on('input', 'input[type=search]', function () {
            table.ajax.reload();
        });

        // 📄 PAGINATION SELECT
        $('select[wire\\:model="paginate"]').on('change', function () {
            table.page.len($(this).val()).draw();
        });

    });
    </script>

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
                    fetch('/products/delete', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ selectedIds }),
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
            }))
        })    
    </script>

    <!-- Pagination -->
    {{-- <div class="mt-8">
        {{ $topProducts->links() }}
    </div> --}}
</div>