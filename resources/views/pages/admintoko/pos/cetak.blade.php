@section('title')
    Point of Sales (POS)
@endsection

<x-admin-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-3">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Point of Sales (POS) ✨</h1>
            </div>

        </div>

        <div class="grid grid-cols-12 gap-6">
            <div class="flex flex-col col-span-full sm:col-span-6 bg-white shadow-lg rounded-sm border border-slate-200">
                <livewire:search-product />
            </div>
            <div class="flex flex-col col-span-full sm:col-span-6">
                <livewire:pos.admin-index :cartInstance="'sale'" />
            </div>
        </div>
        <!-- Start -->
        <div x-data="{ modalOpen: true }">
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
                id="announcement-modal"
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
                <div class="bg-white rounded shadow-lg overflow-auto max-w-3xl w-full max-h-full">
                    <div class="p-6">
                        <div class="relative">
                            <!-- Close button -->
                            <a href="{{ route('admin-pos') }}">
                                <button class="absolute top-0 right-0 text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </a>
                            <!-- Modal header -->
                            <div class="mb-6 text-center">
                                <div class="text-lg font-semibold text-slate-800">Cetak Nota Transaksi Penjualan</div>
                            </div>
                            <div class="flex flex-row mb-4">
                                <div class="md:w-1/2 mb-3 md:mb-0">
                                    <h5 class="mb-2 border-bottom pb-2">Informasi Pelanggan:</h5>
                                    <div><strong>{{ $order->nama_pelanggan }}</strong></div>
                                    <div>{{ $order->customer->alamat }}</div>
                                    <div>Nomor HP: {{ $order->customer->nomor_hp }}</div>
                                </div>

                                <div class="md:w-1/2 mb-3 md:mb-0">
                                    <h5 class="mb-2 border-bottom pb-2">Informasi Transaksi:</h5>
                                    <div>Invoice:
                                        <strong>{{ $order->invoice_no }}</strong>
                                    </div>
                                    <div>Tanggal:
                                        {{ $order->order_date }}
                                    </div>
                                    <div>
                                        Metode Pembayaran : {{ $order->payment_method }}
                                    </div>
                                </div>
                            </div>
                            <div class="my-4">
                                <table style="margin:10px 0">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Jumlah</th>
                                            <th>Harga</th>
                                            <th>Sub Total</th>
                                            @if ($toko->is_tax === 1)
                                                <th>PPN {{ $toko->ppn }}%</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($order != null)
                                            @foreach ($order->detailOrders as $item)
                                                <tr>
                                                    <td>
                                                        {{ $item->product_name }}
                                                    </td>
                                                    <td>
                                                        {{ $item->quantity }}
                                                    </td>
                                                    <td>
                                                        Rp. {{ number_format($item->price) }}
                                                    </td>
                                                    <td>
                                                        Rp. {{ number_format($item->sub_total) }}
                                                    </td>
                                                    @if ($toko->is_tax === 1)
                                                        <td>Rp. {{ number_format($item->ppn) }}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="w-full mb-4">
                                <table class="table">
                                    <tbody>
                                        {{-- @if (settings()->show_order_tax == true)
                                            <tr>
                                                <td class="left"><strong>{{ __('Discount') }}
                                                        ({{ $sale?->discount_percentage }}%)</strong></td>
                                                <td class="right">
                                                    {{ format_currency($sale?->discount_amount) }}
                                                </td>
                                            </tr>
                                        @endif --}}
                                        <tr>
                                            <td class="left"><strong>Jumlah Total</strong>
                                            </td>
                                            <td class="right">
                                                <strong>Rp. {{ number_format($order->sub_total) }}</strong>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- Modal content -->
                            <div class="text-center">
                                <!-- CTAs -->
                                <div class="flex justify-center space-x-2">
                                    <a href="{{ route('admin-cetak-termal-produk', $order->id) }}" target="__blank">
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
                                    <a href="{{ route('admin-lunas-cetak-inkjet', $order->id) }}" target="__blank">
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
            </div>                                            
        </div>
        <!-- End -->
    </div>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            padding-top: 4px;
        }

        thead th {
            background-color: #2980b9;
            color: #ffffff;
            text-align: left;
            border: none;
            padding: 8px;
        }

        tbody tr:nth-child(odd) {
            background-color: #f2f2f2;
        }

        td {
            border: none;
            padding: 8px;
            text-align: left;
        }
    </style>
</x-admin-layout>