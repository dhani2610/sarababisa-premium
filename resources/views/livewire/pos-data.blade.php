<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Point of Sales (POS) ✨</h1>
        </div>

    </div>

    @php
        $allcart = Cart::content();
    @endphp

    <div class="grid grid-cols-12 gap-6">
        <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-7 bg-white shadow-lg rounded-sm border border-slate-200">
            <header class="px-5 py-4 border-b border-slate-100"><h2 class="font-semibold text-slate-800">Keranjang</h2></header>
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <!-- Table header -->
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Jumlah</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Harga</div>
                            </th>
                            @if ($toko->is_tax === 1)
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">PPN</div>
                                </th>
                            @endif
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Sub Total</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Aksi</div>
                            </th>
                        </tr>
                    </thead>

                    <!-- Table body -->
                    <tbody class="text-sm divide-y divide-slate-200">
                        @foreach ($allcart as $cart)
                        @php
                            $ppn = ($cart->price * $cart->qty / 100) * $cart->options->ppn;
                        @endphp
                            <!-- Row -->
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $cart->name }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="space-x-1 flex">
                                        <form action="{{ url('/produk/cart-update/'.$cart->rowId) }}" method="post">
                                            @csrf
                                            <input type="number" name="qty" value="{{ $cart->qty }}" min="1" class="text-sm font-medium" style="width: 60px;">
                                            <button type="submit" class="text-rose-500 hover:text-rose-600 rounded-full">
                                                <span class="sr-only">Update</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-refresh" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#6f32be" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" />
                                                    <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ number_format($cart->price) }}</div>
                                </td>
                                @if ($toko->is_tax === 1)
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">{{ number_format($ppn) }}</div>
                                    </td>
                                @endif
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ number_format($cart->price * $cart->qty + $ppn) }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="space-x-1 flex">
                                        <a href="{{ url('/produk/cart-remove/'.$cart->rowId) }}">
                                            <button class="text-rose-400 hover:text-rose-500 rounded-full">
                                                <span class="sr-only">Delete</span>
                                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                    <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                                    <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                                </svg>
                                            </button>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @php
                // Menghitung total pajak hanya untuk item yang dikenakan pajak
                $totalTax = 0;
                foreach ($allcart as $item) {
                    if ($item->options->ppn > 0) {
                        $totalTax += ($item->price * $item->qty * ($item->options->ppn / 100));
                    }
                }
            @endphp

            <div class="bg-slate-50 p-5 shadow-lg">
                <div class="text-slate-500 font-semibold mb-2">Ringkasan Penjualan</div>
                <!-- Order details -->
                <ul class="mb-4">
                    <li class="text-sm w-full flex justify-between py-3 border-b border-slate-200">
                        <div>Jumlah Barang</div>
                        <div class="font-medium text-slate-800">{{ Cart::count() }}</div>
                    </li>
                    <li class="text-sm w-full flex justify-between py-3 border-b border-slate-200">
                        <div>Total</div>
                        <div class="font-medium text-emerald-600">Rp. {{ number_format(Cart::total() + $totalTax) }}</div>
                    </li>
                </ul>
                <div x-data="{ modalOpen: false }">
                    <button class="btn w-full bg-rose-500 hover:bg-rose-600 text-white mb-4" @click.prevent="modalOpen = true" aria-controls="modal-discount">
                        Atur Diskon
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
                        id="modal-discount"
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
                            <form action="{{ route('produk.applyDiscount') }}" method="post">
                                @csrf
                                <div class="px-5 py-4">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="discount">Jumlah Diskon</label>
                                            <div class="relative">
                                                <input id="discount" name="discount" class="form-input w-full pl-10 px-2 py-1" type="number" max="100" placeholder="Masukkan persen diskon dari 1-100"/>
                                                <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                    <span class="text-sm text-slate-400 font-medium px-3">%</span>
                                                </div>
                                            </div>
                                            <div class="text-xs mt-1">Diskon akan diterapkan pada semua item keranjang</div>
                                        </div>
                                        <!-- Modal footer -->
                                        <button type="submit" class="w-full btn bg-rose-500 hover:bg-rose-600 text-white">Terapkan Diskon</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <form action="{{ url('/produk/complete-order') }}" method="post">
                    @csrf
                    <input type="hidden" name="order_date" value="{{ \Carbon\Carbon::today()->locale('id')->translatedFormat('d F Y') }}">
                    <input type="hidden" name="total_products" value="{{ Cart::count() }}">
                    <input type="hidden" name="sub_total" value="{{ Cart::total() + $totalTax }}">
                    <input type="hidden" name="tgl_disetujui" value="<?php echo date('Y/m/d') ?>"/>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1" for="customers_id">Nama Pelanggan <span class="text-rose-500">*</span></label>
                        <select name="customers_id" class="form-select text-sm py-1 w-full" id="selectjs1" required style="width: 100%">
                            <option selected value="">Pilih Pelanggan</option>
                            @foreach ($customers as $item)
                                <option value="{{ $item->id }}">{{ $item->nama }} {{ $item->nomor_hp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4 space-y-3">
                        <!-- Create invoice button -->
                        <a href="{{ route('pelanggan.index') }}" class="btn w-full bg-emerald-500 hover:bg-emerald-600 text-white">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                            </svg>
                            <span class="hidden xs:block ml-2">Tambah Pelanggan Baru</span>
                        </a>
                        <!-- Create invoice button -->
                        <div x-data="{ modalOpen: false }">
                            <button class="btn w-full bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                                Proses Pembayaran
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
                                    <!-- Modal content -->
                                    <div class="text-center my-3">
                                        <h6>Total Harga</h6>
                                        <p>Rp. {{ number_format(Cart::total() + $totalTax) }}</p>
                                    </div>
                                    <div class="px-5 py-4">
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="users_id">Sales <span class="text-rose-500">*</span></label>
                                                <select id="users_id" name="users_id" class="form-select text-sm py-2 w-full">
                                                    @foreach ($sales as $item)
                                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="payment_method">Metode Pembayaran <span class="text-rose-500">*</span></label>
                                                <select id="payment_method" name="payment_method" class="form-select text-sm py-2 w-full">
                                                    <option value="Tunai">Tunai</option>
                                                    <option value="Transfer">Transfer</option>
                                                    <option value="Kredit">Kredit</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="pay">Jumlah Pembayaran <span class="text-rose-500">*</span></label>
                                                <div class="relative">
                                                    <input id="pay" name="pay" class="form-input w-full pl-10 px-2 py-1" type="number"/>
                                                    <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                        <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Modal footer -->
                                            <button class="w-full btn bg-indigo-500 hover:bg-indigo-600 text-white">Selesaikan Transaksi</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-5">
            <div class="sm:flex sm:justify-between sm:items-center mb-5">
                <div class="mb-0">
                    <select wire:model="paginate" id="" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div class="mt-2 md:mt-0">
                    <x-search-form placeholder="Masukkan nama produk" />
                </div>
            </div>
            <div class="bg-white shadow-lg rounded-sm border border-slate-200">
                <header class="px-5 py-4">
                    <h2 class="font-semibold text-slate-800">Semua Produk <span class="text-slate-400 font-medium">{{ $products_count }}</span></h2>
                </header>
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <!-- Table header -->
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">No.</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Nama Produk</div>
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
                            @foreach($products as $item)
                            <form action="{{ url('/produk/add-cart') }}" method="post">
                                @csrf
                                <input type="hidden" name="id" value="{{ $item->id }}">
                                <input type="hidden" name="name" value="{{ $item->product_name }}">
                                <input type="hidden" name="modal" value="{{ $item->harga_modal }}">
                                <input type="hidden" name="harga_asli" value="{{ $item->harga_jual }}">
                                <input type="hidden" name="qty" value="1">
                                <input type="hidden" name="price" value="{{ $item->harga_jual }}">
                                <input type="hidden" name="garansi" value="{{ $item->garansi }}">
                                <input type="hidden" name="garansi_imei" value="{{ $item->garansi_imei }}">
                                <input type="hidden" name="ppn" value="{{ $item->ppn }}">

                                <tr>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">{{ $i++ }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">
                                            @if ($item->product_code != null)
                                                ({{ $item->product_code }}) {{ $item->product_name }} ({{ $item->stok }} item)
                                            @elseif ($item->capacities_id != null)
                                                {{ $item->product_name }} {{ $item->kondisi }} {{ $item->warna }} {{ $item->ram }} {{ $item->capacity->name }} {{ $item->keterangan }} (IMEI {{ $item->nomor_seri }}) ({{ $item->stok }} item)
                                            @else
                                                {{ $item->product_name }} {{ $item->keterangan }} ({{ $item->stok }} item)
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="space-x-1 flex">
                                            <button type="submit" class="text-slate-400 hover:text-slate-500 rounded-full">
                                                <span class="sr-only">Masukkan keranjang</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-shopping-cart-plus" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                                    <path d="M17 17h-11v-14h-2" />
                                                    <path d="M6 5l6 .429m7.138 6.573l-.143 1h-13" />
                                                    <path d="M15 6h6m-3 -3v6" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </form>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Pagination -->
            <div class="mt-8">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</div>
