@section('title')
    Detail Transaksi Produk
@endsection

<x-sales-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
     
        <!-- Billing Information -->
        <div class="mb-6">
            <div class="text-slate-800 font-semibold mb-4">Data Pelanggan</div>
            <form>
                <div class="space-y-4">
                    <!-- 1st row -->
                    <div class="md:flex space-y-4 md:space-y-0 md:space-x-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-1" for="card-name">Nama</label>
                            <input id="card-name" class="form-input w-full" type="text" value="{{ $order->customer->nama }}" disabled/>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium mb-1" for="card-surname">Nomor HP</label>
                            <input id="card-surname" class="form-input w-full" type="text" value="{{ $order->customer->nomor_hp }}" disabled/>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="max-w-sm mx-auto lg:max-w-none">
            <div class="space-y-6">

                <!-- Order Details -->
                <div>
                    <div class="text-slate-800 font-semibold mb-2">Detail Transaksi</div>
                    <!-- Cart items -->
                    <ul>
                        <!-- Cart item -->
                        @foreach ($orderItem as $item)
                            <li class="flex items-center py-3 border-b border-slate-200">
                                <div class="grow">
                                    <h4 class="text-sm font-medium text-slate-800 leading-tight">
                                        @if ($item->product->categories_id == 1)
                                            {{ $item->product->product_name }} {{ $item->product->kondisi }} {{ $item->product->warna }} {{ $item->product->ram }}/{{ $item->product->capacity->name }} {{ $item->product->keterangan }} (IMEI {{ $item->product->nomor_seri }})
                                        @else
                                            {{ $item->product->product_name }} {{ $item->product->keterangan }}
                                        @endif
                                        (Rp. {{ number_format($item->price) }} x {{ $item->quantity }}
                                        @if ($item->quantity == 1)
                                            pc
                                        @else
                                            pcs
                                        @endif)
                                    </h4>
                                </div>
                                <div class="text-sm font-medium text-slate-800 ml-6">
                                    @if ($item->ppn > 0)
                                        <span class="text-xs text-blue-500">(+PPN Rp. {{ number_format($item->ppn) }})</span>    
                                    @endif
                                     Rp. {{ number_format($item->price * $item->quantity + $item->ppn) }}
                                </div>
                            </li>
                        @endforeach
                        <li class="flex items-center justify-between py-3 border-b border-slate-200">
                            <div class="text-sm">Total</div>
                            <div class="text-sm font-medium text-emerald-600 ml-2">
                                 Rp. {{ number_format($total) }}
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Payment Details -->
                <div>
                    <div class="text-slate-800 font-semibold mb-4">Detail Pembayaran</div>
                    <div class="text-sm rounded border-3 border-indigo-300 p-3 space-y-3">
                        @if ($order->due == 0)
                            <div class="flex items-center justify-between space-x-2">
                                <!-- CC details -->
                                <div class="font-semibold">{{ $order->payment_method }}</div>
                                <!-- Expiry -->
                                <div class="text-blue-700 font-semibold ml-2">Rp. {{ number_format($order->pay) }}</div>
                            </div>
                        @else
                            <div class="flex items-center justify-between space-x-2">
                                <!-- CC details -->
                                <div class="font-semibold">{{ $order->payment_method }}</div>
                                <!-- Expiry -->
                                <div class="text-blue-700 font-semibold ml-2">Rp. {{ number_format($order->pay) }}</div>
                            </div>
                            <div class="flex items-center justify-between space-x-2">
                                <!-- CC details -->
                                <div class="font-semibold">Sisa Pembayaran</div>
                                <!-- Expiry -->
                                <div class="font-semibold text-rose-700 ml-2">Rp. {{ number_format($order->due) }}</div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 space-y-2">
                    <!-- Start Printer -->
                    <div x-data="{ modalOpen: false }">
                        <button
                            @click.prevent="modalOpen = true"
                            aria-controls="basic-modal"
                            class="btn w-full bg-indigo-500 hover:bg-indigo-600 text-white"
                        >
                        Cetak Invoice
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
                                id="basic-modal"
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
                                <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                                    <!-- Modal header -->
                                    <div class="px-5 py-3 border-b border-slate-200">
                                        <div class="flex justify-between items-center">
                                            <div class="font-semibold text-slate-800">Pilih Jenis Printer</div>
                                            <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                                <div class="sr-only">Close</div>
                                                <svg class="w-4 h-4 fill-current">
                                                    <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <!-- Modal content -->
                                    <div class="px-5 pt-4 pb-1">
                                        <div class="text-sm">
                                            <div class="space-y-2">
                                                <p>Silahkan pilih printer untuk cetak Nota Tanda Terima Servis.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Modal footer -->
                                    <div class="px-5 py-4">
                                        <div class="flex flex-wrap justify-end space-x-2">
                                            <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                            <a href="{{ route('sales-cetak-termal-produk', $order->id) }}"  target="_blank">
                                                <button class="btn-sm bg-orange-500 hover:bg-orange-600 text-white">
                                                    <span class="mr-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                        <rect x="7" y="13" width="10" height="8" rx="2" />
                                                        </svg>
                                                    </span>
                                                    Printer Termal
                                                </button>
                                            </a>
                                            <a href="{{ route('sales-lunas-cetak-inkjet', $order->id) }}"  target="_blank">
                                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">
                                                    <span class="mr-1">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                        <rect x="7" y="13" width="10" height="8" rx="2" />
                                                        </svg>
                                                    </span>
                                                    Printer Inkjet
                                                </button>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>                                            
                    </div>
                    {{-- <div class="text-xs text-slate-500 italic text-center">Should you ever change your mind, we offer a 14-day, no-questions-asked refund policy.</div> --}}
                </div>
            </div>
        </div>
    </div>
</x-sales-layout>
