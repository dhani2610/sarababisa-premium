<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Pembelian Produk ✨</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            <!-- Search form -->
            <x-search-form placeholder="No.Referensi/Nama Produk/Nama Supplier" />

            <!-- Create invoice button -->
            <a href="{{ route('purchase.create') }}" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                </svg>
                <span class="hidden xs:block ml-2">Tambah Pembelian</span>
            </a>

        </div>

    </div>

    <!-- More actions -->
    <div class="sm:flex sm:justify-between sm:items-center mb-5">
        <!-- Left side -->
        <div class="mb-4 sm:mb-0">
            <ul class="flex flex-wrap -m-1">
                <li class="m-1">
                    <a href="{{ route('purchase.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-indigo-500 text-white duration-150 ease-in-out">Pembelian <span class="ml-1 text-indigo-200">{{ $purchases_count }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('retur.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Retur <span class="ml-1 text-slate-400">{{ $returs_count }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('tukar-tambah.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Tukar Tambah <span class="ml-1 text-slate-400">{{ $trade_ins_count }}</span></button>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Right side -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
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

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                {{-- Left side --}}
                <h2 class="font-semibold text-slate-800">Semua Data Pembelian <span class="text-slate-400 font-medium">{{ $purchases_count }}</span></h2>
                {{-- Right side --}}
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span> item yang dipilih</div>
                            <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" @click="deleteSelected">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <!-- Table header -->
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox" @click="toggleAll" />
                                    </label>
                                </div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Tanggal</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">No. Referensi</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama Supplier</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama Produk</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Keterangan</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Kuantitas Produk</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Total Harga</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Aksi</div>
                            </th>
                        </tr>
                    </thead>
                    <!-- Table body -->
                    <tbody class="text-sm divide-y divide-slate-200">
                        <!-- Row -->
                        @php
                            $i = 1
                        @endphp
                        @foreach($purchases as $item)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="flex items-center">
                                        <label class="inline-flex">
                                            <span class="sr-only">Select</span>
                                            <input class="table-item form-checkbox" type="checkbox" value="{{ $item->id }}" @click="uncheckParent" />
                                        </label>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->reference_number }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">
                                        {{ $item->suppliers_name }}

                                        {{-- @if ($item->supplier != null)
                                            {{ $item->supplier->name }}
                                        @else
                                            -
                                        @endif --}}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    @if ($item->product != null)
                                        <div class="font-medium">{{ $item->product->product_name }}</div>
                                    @else
                                        <div class="font-medium text-red-600">Data produk sudah dihapus</div>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->keterangan }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->quantity }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">Rp. {{ number_format($item->total_price) }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="space-x-1 flex">
                                        <!-- Start -->
                                        <div x-data="{ modalOpen: false }">
                                            <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="modalOpen = true" aria-controls="danger-modal">
                                                <span class="sr-only">Delete</span>
                                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                    <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                                    <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
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
                                                id="danger-modal"
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
                                                    <div class="p-5 flex space-x-4">
                                                        <!-- Icon -->
                                                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                                                            <svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16">
                                                                <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                                                            </svg>
                                                        </div>
                                                        <!-- Content -->
                                                        <div>
                                                            <!-- Modal header -->
                                                            <div class="mb-2">
                                                                <div class="text-lg font-semibold text-slate-800">Apakah anda sudah yakin ?</div>
                                                            </div>
                                                            <!-- Modal content -->
                                                            <div class="text-sm mb-10">
                                                                <div class="space-y-2">
                                                                    <p>Jika sudah terhapus, maka stok produk akan dikembalikan <br> ke jumlah sebelumnya.</p>
                                                                </div>
                                                            </div>
                                                            <!-- Modal footer -->
                                                            <div class="flex flex-wrap justify-end space-x-2">
                                                                <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                                                <form action="{{ route('purchase.destroy', $item->id) }}" method="post">
                                                                    @method('delete')
                                                                    @csrf
                                                                    <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End -->
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
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
                    fetch('/purchases/delete', {
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
        {{ $purchases->links() }}
    </div>
</div>
