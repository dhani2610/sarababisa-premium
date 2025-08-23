<div>
    <div class="w-full px-2" dir="ltr">
        <x-validation-errors class="mb-4" :errors="$errors" />

        <div class="flex gap-4">
            <div class="w-full relative inline-flex">
                <select id="customer_id" name="customer_id" wire:model="customer_id" class="form-select text-sm block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">
                    <option value="">Pilih Pelanggan</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->nama }} ({{ $customer->nomor_hp }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <livewire:product-cart :cartInstance="'sale'" />

        <button
            class="w-full inline-flex items-center px-4 py-2 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest disabled:opacity-25 transition ease-in-out duration-150 bg-indigo-500 hover:bg-indigo-600"
            type="submit" wire:click="proceed">
            Proses Pembayaran
        </button>

    </div>

    <div x-data="{ modalOpen: @entangle('checkoutModal') }">
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
        
        <div
            id="checkout-modal"
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
                <div class="px-5 py-4">
                    <div class="text-center text-xl mb-5">
                        Pembayaran
                    </div>
                    <form id="checkout-form" wire:submit.prevent="store">
                        <input type="hidden" wire:model="order_date" name="order_date" value="{{ \Carbon\Carbon::today()->locale('id')->translatedFormat('d F Y') }}">
                        <div class="space-y-3">
                            <div class="w-full px-2">
                                <label class="block text-sm font-medium" for="users_id">Sales <span class="text-rose-500">*</span></label>
                                <select wire:model="users_id" name="users_id" id="users_id" required
                                    class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1">
                                    <option value="">Pilih Sales</option>
                                    @foreach ($users as $sales)
                                        <option value="{{ $sales->id }}">{{ $sales->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full px-2">
                                <label class="block text-sm font-medium" for="payment_method">Metode Pembayaran <span class="text-rose-500">*</span></label>
                                <small class="mb-1">Jika jumlah pembayaran kurang dari jumlah total maka pilih Kredit</small>
                                <select wire:model="payment_method" id="payment_method" name="payment_method" required
                                    class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1">
                                    <option value="">Pilih Metode Pembayaran</option>
                                    <option value="Tunai">Tunai</option>
                                    <option value="Transfer">Transfer</option>
                                    <option value="Kredit">Kredit</option>
                                    <option value="Tunai & Transfer">Tunai & Transfer</option>
                                </select>
                            </div>
                            <div class="w-full px-2">
                                <label class="block text-sm font-medium mb-1" for="total_amount">Jumlah Total <span class="text-rose-500">*</span></label>
                                <input id="total_amount" type="number" wire:model="total_amount"
                                    class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"
                                    name="total_amount" readonly required>
                            </div>
                            <div class="w-full px-2">
                                <label class="block text-sm font-medium mb-1" for="paid_amount">Jumlah Pembayaran <span class="text-rose-500">*</span></label>
                                <input id="paid_amount" type="number" wire:model="paid_amount"
                                    class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"
                                    name="paid_amount" required>
                            </div>
                            @if ($payment_method === 'Kredit')
                                <div class="w-full px-2">
                                    <div class="flex flex-wrap items-center -m-3">
                                        <div class="m-3">
                                            <!-- Start -->
                                            <label class="flex items-center">
                                                <input wire:model="tunai" name="tunai" type="checkbox" class="form-checkbox" required/>
                                                <span class="text-sm ml-2">Tunai</span>
                                            </label>
                                            <!-- End -->
                                        </div>

                                        <div class="m-3">
                                            <!-- Start -->
                                            <label class="flex items-center">
                                                <input wire:model="transfer" name="transfer" type="checkbox" class="form-checkbox" />
                                                <span class="text-sm ml-2">Transfer</span>
                                            </label>
                                            <!-- End -->
                                        </div>
                                    </div>
                                    <label class="block text-sm font-medium" for="tempo">Waktu Tempo <span class="text-rose-500">*</span></label>
                                    <select wire:model="tempo" id="tempo" name="tempo" required
                                        class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1">
                                        <option value="">Pilih Waktu Tempo</option>
                                        <option value="1">Tempo 1 Hari</option>
                                        <option value="2">Tempo 2 Hari</option>
                                        <option value="3">Tempo 3 Hari</option>
                                        <option value="4">Tempo 4 Hari</option>
                                        <option value="5">Tempo 5 Hari</option>
                                        <option value="6">Tempo 6 Hari</option>
                                        <option value="7">Tempo 1 Minggu</option>
                                        <option value="14">Tempo 2 Minggu</option>
                                        <option value="21">Tempo 3 Minggu</option>
                                        <option value="30">Tempo 1 Bulan</option>
                                        <option value="60">Tempo 2 Bulan</option>
                                        <option value="90">Tempo 3 Bulan</option>
                                    </select>
                                </div>
                            @endif
                            @if ($payment_method === 'Tunai & Transfer')
                                <label class="block text-sm font-medium px-2 text-indigo-500">Silahkan isi hanya pada salah satu input saja: Tunai / Transfer</label>
                                <div class="flex flex-row">
                                    <div class="w-1/2 mb-3 md:mb-0 px-2">
                                        <label class="block text-sm font-medium mb-1">Tunai</label>
                                        <input type="number" wire:model="tunai"
                                        class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"
                                        name="tunai">
                                    </div>
                                    <div class="w-1/2 mb-3 md:mb-0 px-2">
                                        <label class="block text-sm font-medium mb-1">Transfer</label>
                                        <input type="number" wire:model="transfer"
                                        class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"
                                        name="transfer">
                                    </div>
                                </div>
                            @endif
                            <div class="mb-4 w-full px-2">
                                <label class="block text-sm font-medium mb-1" for="note" :value="Note">Catatan</label>
                                <textarea name="note" id="note" rows="5" wire:model="note"
                                    class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"></textarea>
                            </div>
                            <div class="w-full px-2">
                                <x-table-responsive>
                                    <x-table.tr>
                                        <x-table.th>
                                            Total Produk
                                        </x-table.th>
                                        <x-table.td>
                                            <span class="badge badge-success">
                                                {{ Cart::instance($cart_instance)->count() }} item
                                            </span>
                                        </x-table.td>
                                    </x-table.tr>
                                    <x-table.tr>
                                        <x-table.th>
                                            Sub Total
                                        </x-table.th>
                                        <x-table.td>
                                            Rp. {{ number_format(Cart::instance($cart_instance)->subtotal()) }}
                                        </x-table.td>
                                    </x-table.tr>
                                    @if ($toko->is_tax === 1)
                                        <x-table.tr>
                                            <x-table.th>
                                                {{ __('PPN') }} ({{ $toko->ppn }}%)
                                            </x-table.th>
                                            <x-table.td>
                                                (+) Rp. {{ number_format(Cart::instance($cart_instance)->tax()) }}
                                            </x-table.td>
                                        </x-table.tr>
                                    @endif
                                    {{-- <x-table.tr>
                                        <x-table.th>
                                            {{ __('Diskon') }} ({{ $global_discount }}%)
                                        </x-table.th>
                                        <x-table.td>
                                            (-) Rp. {{ number_format(Cart::instance($cart_instance)->discount()) }}
                                        </x-table.td>
                                    </x-table.tr> --}}
                                    <x-table.tr>
                                        <x-table.th>
                                            Total
                                        </x-table.th>
                                        <x-table.td>
                                            (=) Rp. {{ number_format($total_amount) }}
                                        </x-table.td>
                                    </x-table.tr>
                                </x-table-responsive>
                                <div class="float-right py-4">
                                    <button type="submit" x-on:click="modalOpen = false" class="btn border-slate-200 hover:border-slate-300 text-slate-600 mr-2">
                                        Batal
                                    </button>
                                    <button type="submit" wire:loading.attr="disabled" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                                        Selesaikan Transaksi
                                    </button>
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
    document.addEventListener('livewire:load', function () {
        $('#customer_id').select2();
        $('#customer_id').on('change', function (e) {
            var data = $('#customer_id').select2("val");
            @this.set('customer_id', data);
        });
    });

    document.addEventListener('livewire:update', function () {
        $('#customer_id').select2();
    });
</script>