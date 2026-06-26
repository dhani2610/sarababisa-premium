@section('title')
    Edit Transaksi Produk
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Transaksi Produk ✨</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Search form -->
                <x-search-form placeholder="Masukkan nama pelanggan" />

            </div>

        </div>
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
                id="edit-modal"
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
                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">
                    <!-- Modal header -->
                    <div class="px-5 py-3 border-b border-slate-200">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Edit Transaksi Produk</div>
                            <a href="{{ route('transaksi-produk.index') }}" class="text-slate-400 hover:text-slate-500">
                                <div class="sr-only">Close</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Modal content -->
                    <div x-data="{ tab: '1' }" class="px-5 py-4">
                        <!-- Tabs buttons -->
                        <div class="flex flex-wrap items-center -m-3 mb-0">
                            <div class="m-3">
                                <!-- Start -->
                                <label class="flex items-center">
                                    <input type="radio" name="radio-buttons" class="form-radio" checked @click="tab = '1'"/>
                                    <span class="text-sm ml-2">Data Utama</span>
                                </label>
                                <!-- End -->
                            </div>
                            <div class="m-3">
                                <!-- Start -->
                                <label class="flex items-center">
                                    <input type="radio" name="radio-buttons" class="form-radio" @click="tab = '2'"/>
                                    <span class="text-sm ml-2">Data Detail</span>
                                </label>
                                <!-- End -->
                            </div>
                        </div>
                        <form action="{{ route('transaksi-produk.update', $item->id) }}" method="post">
                            @method('PUT')
                            @csrf
                            {{-- Form Utama --}}
                            <div x-show="tab === '1'">
                                <div class="space-y-3 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="created_at">Tgl. Transaksi </label>
                                        <input id="created_at" name="created_at" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}"/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="customers_id">Nama Pelanggan </label>
                                        <select id="customers_id" name="customers_id" class="form-select text-sm py-1 w-full" >
                                            <option selected value="{{ $item->customer->id }}">{{ $item->customer->nama }}</option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="payment_method">Metode Pembayaran</label>
                                        <select id="payment_method" name="payment_method" class="form-select text-sm py-1 w-full" >
                                            <option value="Tunai" {{ $item->payment_method == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                                            <option value="Transfer" {{ $item->payment_method == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                                            @foreach (getMetodePembayaran() as $mp)
                                            <option value="{{ $mp->nama }}" {{ $item->payment_method == $mp->nama ? 'selected' : '' }}>{{  $mp->nama  }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1"
                                            for="tipe_status_pembayaran">Status Pembayaran</label>
                                        <select wire:model.defer="tipe_status_pembayaran" id="tipe_status_pembayaran" name="tipe_status_pembayaran"
                                            class="form-select text-sm py-1 w-full">
                                            <option value="0" {{ $item->tipe_status_pembayaran == 0 ? 'selected' : '' }}>Belum Lunas</option>
                                            <option value="1" {{ $item->tipe_status_pembayaran == 1 ? 'selected' : '' }}>Lunas</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="users_id">Sales </label>
                                        <select id="users_id" name="users_id" class="form-select text-sm py-1 w-full" >
                                            <option selected value="{{ $item->user->id }}">{{ $item->user->name }}</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="pay">Pembayaran</label>
                                        <input id="pay" name="pay" class="form-input w-full px-2 py-1 input-currency" type="text" value="{{ $item->pay }}"/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="due">Hutang</label>
                                        <input id="due" name="due" class="form-input w-full px-2 py-1 input-currency" type="text" value="{{ $item->due }}"/>
                                    </div>
                                </div>
                                <!-- Modal footer -->
                                <div class="py-4 border-t border-slate-200">
                                    <div class="flex flex-wrap justify-end space-x-2">
                                        <a href="{{ route('transaksi-produk.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                            Batal
                                        </a>
                                        <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                    </div>
                                </div>
                            </div>
                            {{-- Form Detail --}}
                            <div x-show="tab === '2'">
                                @foreach ($item->detailOrders as $orderDetail)
                                    <input type="hidden" name="order_details[{{ $orderDetail->id }}][persen_sales]" value="{{ $orderDetail->persen_sales }}">
                                    <input type="hidden" name="order_details[{{ $orderDetail->id }}][persen_admin]" value="{{ $orderDetail->persen_admin }}">
                                    <div>
                                        <label class="block text-sm font-semibold mb-1 text-indigo-600" for="modal">{{ $orderDetail->product_name }}</label>
                                    </div>
                                    <div class="space-y-3 mb-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="modal">Total Modal</label>
                                            <input id="modal" name="order_details[{{ $orderDetail->id }}][modal]" class="form-input w-full px-2 py-1 input-currency" type="text" value="{{ $orderDetail->modal }}"/>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium mb-1" for="total">Total Harga Jual</label>
                                            <input id="total" name="order_details[{{ $orderDetail->id }}][total]" class="form-input w-full px-2 py-1 input-currency" type="text" value="{{ $orderDetail->total }}"/>
                                        </div>
                                    </div>
                                @endforeach
                                <!-- Modal footer -->
                                <div class="py-4 border-t border-slate-200">
                                    <div class="flex flex-wrap justify-end space-x-2">
                                        <a href="{{ route('transaksi-produk.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                            Batal
                                        </a>
                                        <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        function formatRupiahInput(el) {
            let value = el.value || '';

            // hapus semua selain angka
            value = value.replace(/\D/g, '');

            // format pakai titik
            el.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        /* ON INPUT (REAL TIME) */
        document.addEventListener('input', function (e) {
            if (!e.target.classList.contains('input-currency')) return;
            formatRupiahInput(e.target);
        });

        /* ON LOAD (FORM EDIT) */
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.input-currency').forEach(function (el) {
                formatRupiahInput(el);
            });
        });
    </script>
</x-toko-layout>
