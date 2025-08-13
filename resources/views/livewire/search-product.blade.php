<div class="relative mt-8">
    <div class="mb-3 px-4">
        <div class="mb-2 w-full relative text-gray-600 focus-within:text-gray-400">
            <x-input wire:keydown.escape="resetQuery" wire:model.debounce.500ms="query" type="search"
                minlength="4" placeholder="Cari produk berdasarkan nama atau kode produk" autofocus />
            <div class="absolute right-0 top-0 mt-2 mr-4 text-purple-lighter">
                <button wire:click="resetQuery" type="button">X</button>
            </div>
        </div>
        
        <div class="flex flex-wrap -mx-2 mb-3">
       
            
            <div class="lg:w-1/2 md:w-1/2 sm:w-1/2 px-2 mb-2">
                <label class="block text-sm font-medium mb-1">Scan Barcode</label>
                <input type="text" 
                    id="barcode_input" 
                    wire:model="barcode"
                    placeholder="Scan Barcode di sini..." 
                    autofocus
                    class="form-input w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>
            <div class="lg:w-1/2 md:w-1/2 sm:w-1/2 px-2 mb-2">
                <label class="block text-sm font-medium mb-1">Kategori Produk</label>
                <x-select-list :options="$this->categories" wire:model="categories_id" name="categories_id" id="categories_id" />
            </div>

            <div class="lg:w-1/2 md:w-1/2 sm:w-1/2 px-2">
                <label class="block text-sm font-medium mb-1">Jumlah produk</label>
                <x-label for="showCount" :value="'Product per page'" />
                <select wire:model="showCount"
                    class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1">
                    <option value="9">9</option>
                    <option value="15">15</option>
                    <option value="21">21</option>
                    <option value="30">30</option>
                    <option value="">Semua</option>
                </select>
            </div>

        </div>
    </div>

    
    
    <div class="w-full px-2 mb-4 bg-white">
        <div class="flex flex-wrap w-full">
            <div
                class="w-full grid gap-3 md:grid-cols-2 lg:grid-cols-3 px-2 mt-5 overflow-y-auto">
                @forelse($products as $product)
                    <div wire:click.prevent="selectProduct({{ $product }})"
                        class="select-none cursor-pointer transition-shadow overflow-hidden rounded-2xl bg-white shadow hover:shadow-lg w-full py-8 relative border border-green-400">
                        <div
                            class="inline-block p-1 text-center font-semibold text-xs align-baseline leading-none bg-blue-100 text-blue-600  mb-3 absolute top-0 right-0">
                            Stok: {{ $product->stok }}

                        </div>
                        <div class="pb-3 px-3 text-sm -mt-3">
                            <h6 class="text-md text-center font-semibold my-3 md:mb-0">
                                {{ $product->product_name }} @if ($product->categories_id === 1)
                                    {{ $product->nomor_seri }}
                                @endif
                            </h6>
                            <p class="mb-0 text-center font-bold">
                                 Rp. {{ number_format($product->harga_jual) }}
                            </p>
                        </div>
                        <span
                            class="block p-1 text-center font-semibold text-xs align-baseline leading-none bg-emerald-100 text-emerald-600 absolute bottom-0">
                            {{ $product->product_code }}
                        </span>
                        @if ($product->categories_id === 1)
                            <span
                                class="block p-1 text-center font-semibold text-xs align-baseline leading-none bg-emerald-100 text-emerald-600  absolute bottom-0">
                                {{ $product->kondisi }} {{ $product->warna }} ({{ $product->ram }} /@if($product->capacity != null)
                                    {{ $product->capacity->name }}
                                @else
                                    -
                                @endif)
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full w-full px-2 py-3 mb-4 border rounded">
                        <span class="inline-block align-middle mr-8">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11a1 1 0 11-2 0 1 1 0 012 0zm-1-3a1 1 0 00-1 1v3a1 1 0 102 0v-3a1 1 0 00-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </span>
                        <span class="inline-block align-middle mr-8">
                            Tidak ada produk yang ditemukan
                        </span>
                    </div>
                @endforelse
            </div>
            <div class="my-3 mx-auto">
                @if ($products->count() >= $showCount)
                    <button wire:click.prevent="loadMore" class="btn bg-white border-slate-200 hover:border-slate-300 text-indigo-500" type="button">
                        Tampilkan lebih banyak
                    </button>
                @endif
            </div>
        </div>
    </div>

</div>