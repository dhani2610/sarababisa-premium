<div>
    <div class="sm:flex sm:justify-between sm:items-center mt-5">
        <select wire:model="paginate" id="" class="form-select mb-2 md:mb-0">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>

        <!-- Search form -->
        <x-search-form placeholder="Masukkan nama produk" />
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <header class="px-5 py-4">
            <h2 class="font-semibold text-slate-800">Jumlah Produk Terjual <span class="text-slate-400 font-medium">{{ $jumlah }}</span></h2>
        </header>
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <!-- Table header -->
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
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
                            <div class="font-semibold text-left">Kategori</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Jumlah</div>
                        </th>
                        @if ($toko->is_bonus_produk === 1)
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Bonus</div>
                        </th>
                        @endif
                    </tr>
                </thead>
                <!-- Table body -->
                <tbody class="text-sm divide-y divide-slate-200">
                    <!-- Row -->
                    @foreach($product_transactions as $item)
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if ($item->user)
                                    <div class="font-medium">{{ $item->user->name }}</div>
                                @else
                                    <div class="font-medium text-red-600">Akun sudah dihapus</div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if ($item->product != null)
                                    <div class="font-medium">
                                        @if ($item->product->categories_id == 1)
                                            {{ $item->product->product_name }} {{ $item->product->kondisi }} {{ $item->product->warna }} {{ $item->product->ram }} / @if ($item->product->capacity != null)
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
                                @if ($item->product != null)
                                    <div class="font-medium">{{ $item->product->category_name }}</div>
                                @else
                                    <div class="font-medium text-red-600">Data kategori sudah dihapus</div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ $item->quantity }}</div>
                            </td>
                            @if ($toko->is_bonus_produk === 1)
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">
                                    @php
                                        $bonus = $item->profit/100;
                                        $bonus *= $item->user->persen;
                                    @endphp
                                    Rp. {{ number_format($bonus) }}
                                </div>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
    <!-- Pagination -->
    <div class="mt-8">
        {{ $product_transactions->links() }}
    </div>

</div>
