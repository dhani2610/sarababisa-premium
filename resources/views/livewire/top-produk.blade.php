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
            <x-search-form placeholder="Masukkan nama produk" />

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
                    }else{
                        $route = route('top-produk');
                    }
                @endphp
                 <li class="m-1">
                    <a href="{{ $route }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-indigo-500 text-white duration-150 ease-in-out">Semua</button>
                    </a>
                </li>
                @foreach ($categories as $cat)
                <li class="m-1">
                    <a href="{{ $route }}?id={{ $cat->id }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-indigo-500 text-white duration-150 ease-in-out">{{ $cat->category_name }}</button>
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <!-- Right side -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Print button -->
           
            <div>
                <select wire:model="paginate" id="" class="form-select">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
    </div>


    <!-- Ranking Produk -->
    <div class="bg-white shadow-lg rounded-lg mt-5">
        <div class="px-5 py-4 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800">Top Produk Terlaris</h2>
        </div>
        <div class="divide-y divide-slate-200">
            @php
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
            @endforeach
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
    <div class="mt-8">
        {{ $topProducts->links() }}
    </div>
</div>