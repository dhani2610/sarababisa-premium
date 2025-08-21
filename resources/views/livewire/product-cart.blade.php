<div>
    <x-validation-errors class="mb-4" :errors="$errors" />

    <div>
        <div wire:loading.flex class="absolute top-0 left-0 w-full h-full bg-white bg-opacity-75 z-50">
            <div class="m-auto">
                <x-loading />
            </div>
        </div>
        <x-table>
            <x-slot name="thead">
                <x-table.th>Produk</x-table.th>
                <x-table.th>Harga</x-table.th>
                <x-table.th>Stok</x-table.th>
                <x-table.th>Jumlah</x-table.th>
                <x-table.th>Sub Total</x-table.th>
                <x-table.th>Aksi</x-table.th>
            </x-slot>
            <x-table.tbody>
                @if ($cart_items->isNotEmpty())
                    @foreach ($cart_items as $cart_item)
                        <x-table.tr>
                            <x-table.td>
                                {{ $cart_item->name }} @if ($cart_item->options->code != null)
                                    ({{ $cart_item->options->code }})
                                @endif
                                <div x-data="{ modalDiskon: @entangle('discountModal') }">
                                    <!-- Button trigger Discount Modal -->
                                    <button type="button" @click.prevent="modalDiskon = true" aria-controls="modal-discount"
                                        class="border border-red-500 text-red-500 hover:text-reg-800">
                                        <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-percentage"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M7 7m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M6 18l12 -12" /></svg>
                                    </button>
                                    
                                    <!-- Modal backdrop -->
                                    <div
                                        class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                                        x-show="modalDiskon"
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
                                        x-show="modalDiskon"
                                        x-transition:enter="transition ease-in-out duration-200"
                                        x-transition:enter-start="opacity-0 translate-y-4"
                                        x-transition:enter-end="opacity-100 translate-y-0"
                                        x-transition:leave="transition ease-in-out duration-200"
                                        x-transition:leave-start="opacity-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 translate-y-4"
                                        x-cloak
                                    >
                                        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalDiskon = false" @keydown.escape.window="modalDiskon = false">
                                            <form wire:submit.prevent="productDiscount('{{ $cart_item->rowId }}', '{{ $cart_item->id }}')" method="post">
                                                <div class="px-5 py-4">
                                                    <div class="text-center text-xl">
                                                        {{ $cart_item->name }}
                                                        @if ($cart_item->options->code != null)
                                                        <div class="text-xs inline-flex font-medium bg-emerald-100 dark:bg-emerald-400/30 text-emerald-600 dark:text-emerald-400 rounded-full text-center px-2.5 py-1">
                                                            {{ $cart_item->options->code }}
                                                        </div>
                                                        @endif
                                                    </div>
                                                    <x-validation-errors class="mb-4" :errors="$errors" />
                                                    <div class="grid grid-cols-2 gap-4 my-4">
                                                        <div>
                                                            <label>Tipe Diskon</label>
                                                            <select wire:model="discount_type.{{ $cart_item->id }}"
                                                                class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"
                                                                required>
                                                                <option value="fixed">Fixed</option>
                                                                <option value="percentage">Percentage</option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label>Nilai Diskon</label>
                                                            <x-input wire:model.defer="item_discount.{{ $cart_item->id }}" type="text"
                                                            value="{{ $item_discount[$cart_item->id] }}" class="mt-1"/>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="w-full btn bg-rose-500 hover:bg-rose-600 text-white">Terapkan Diskon</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            </x-table.td>

                            <x-table.td>
                                Rp. {{ number_format($cart_item->options->unit_price) }}
                                {{-- @include('livewire.includes.product-cart-price') --}}
                            </x-table.td>

                            <x-table.td>
                                <span
                                    class="badge badge-info">
                                    {{ $cart_item->options->stock }}
                                </span>
                            </x-table.td>

                            <x-table.td>
                                @include('livewire.includes.product-cart-quantity')
                            </x-table.td>

                            <x-table.td>
                                Rp. {{ number_format($cart_item->options->sub_total) }}
                            </x-table.td>

                            <x-table.td>
                                <a href="#" wire:click.prevent="removeItem('{{ $cart_item->rowId }}')">
                                    <button class="text-rose-400 hover:text-rose-500 rounded-full">
                                        <span class="sr-only">Delete</span>
                                        <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                            <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                            <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                        </svg>
                                    </button>
                                </a>
                            </x-table.td>
                        </x-table.tr>
                    @endforeach
                @else
                    <x-table.tr>
                        <x-table.td colspan="8" class="text-center">
                            <span class="text-red-500">
                                Silahkan cari dan pilih produk!
                            </span>
                        </x-table.td>
                    </x-table.tr>
                @endif
            </x-table.tbody>
        </x-table>
    </div>
    <div class="flex flex-wrap md:justify-end mb-2">
        <div class="w-full">
            <div class="w-full py-2">
                <x-table-responsive>
                    @if ($toko->is_tax == 1)
                    <x-table.tr>
                        <x-table.th>{{ __('Pajak') }} ({{ $global_tax }}%)</x-table.th>
                        <x-table.td>(+) Rp. {{ number_format(Cart::instance($cart_instance)->tax()) }}</x-table.td>
                    </x-table.tr>
                    @endif
                    {{-- <x-table.tr>
                        <x-table.th>{{ __('Diskon') }} ({{ $global_discount }}%)</x-table.th>
                        <x-table.td>(-) Rp. {{ number_format(Cart::instance($cart_instance)->discount()) }}</x-table.td>
                    </x-table.tr> --}}
                    <x-table.tr>
                        <x-table.th>Jumlah Total</x-table.th>
                        @php
                            $total_with_shipping = Cart::instance($cart_instance)->total();
                        @endphp
                        <x-table.td>
                            (=) Rp. {{ number_format($total_with_shipping) }}
                        </x-table.td>
                    </x-table.tr>
                </x-table-responsive>
            </div>
        </div>
    </div>

    <input type="hidden" name="total_amount" value="{{ $total_with_shipping }}">

    <div class="flex flex-wrap my-2">
        @if ($toko->is_tax == 1)
        <div class="w-full md:w-1/3 mb-4 md:mb-0">
            <div class="mb-4">
                <label for="tax_percentage">{{ __('Order Tax (%)') }}</label>
                <x-input wire:model.lazy="global_tax" name="tax_percentage" value="{{ $global_tax }}" />
            </div>
        </div>
        @endif
        {{-- <div class="w-full md:w-1/3 mb-4 md:mb-0">
            <div class="mb-4">
                <label for="discount_percentage">{{ __('Diskon Global (%)') }}</label>
                <x-input wire:model.lazy="global_discount" name="discount_percentage" value="{{ $global_discount }}" />
            </div>
        </div> --}}
    </div>
</div>
