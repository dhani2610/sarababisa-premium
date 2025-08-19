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


                        <!-- Wrapper Alpine -->
            <div x-data="{ modalTambahStok: false }">

                <!-- Tombol Tambah Stok -->
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white"
                        @click="modalTambahStok = true"
                        aria-controls="tambah-stok-modal">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1-1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Stok</span>
                </button>

                <!-- Modal Tambah Stok -->
                <div x-show="modalTambahStok"
                    x-cloak
                    class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                        <h2 class="text-lg font-bold mb-4">Scan Barcode Produk</h2>
                        <input type="text"
                            wire:model="barcode"
                            id="barcode_input"
                            placeholder="Scan Barcode di sini..."
                            autofocus
                            class="form-input w-full rounded-md border-gray-300 shadow-sm
                                focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
                        <div class="flex justify-end mt-4">
                            <button class="px-4 py-2 bg-gray-300 rounded-lg mr-2"
                                    @click="modalTambahStok = false">Tutup</button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Create invoice button -->
            <div x-data="{ modalOpen: false }">
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Sparepart</span>
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
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Tambah Produk</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Modal content -->
                        <form action="{{ route('admin-sparepart.store') }}" method="post">
                            @csrf
                            <input type="hidden" name="categories_id" value="2">
                            <div class="px-5 py-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="sub_categories_id">Sub Kategori Produk <span class="text-rose-500">*</span></label>
                                        <select id="sub_categories_id" name="sub_categories_id" class="form-select text-sm w-full" required>
                                            <option selected value="">Pilih Sub Kategori</option>
                                            @foreach ($spareparts as $sparepart)
                                                <option value="{{ $sparepart->id }}">{{ $sparepart->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="product_name">Nama Produk <span class="text-rose-500">*</span></label>
                                        <input id="product_name" name="product_name" class="form-input w-full px-2 py-1" type="text" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="model_series_id">Model Seri <span class="text-rose-500">*</span></label>
                                        <select id="model_series_id" name="model_series_id" class="form-select text-sm py-1 w-full selectjs1" style="width: 100%" required>
                                            <option selected value="">Pilih Model Seri</option>
                                            @foreach ($model_series as $model)
                                                <option value="{{ $model->id }}">{{ $model->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="keterangan">Keterangan Produk</label>
                                        <input id="keterangan" name="keterangan" class="form-input w-full px-2 py-1" type="text" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="product_code">Kode Produk</label>
                                        <input id="product_code" name="product_code" class="form-input w-full px-2 py-1" type="text" />
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium mb-1" for="stok">Stok <span class="text-rose-500">*</span></label>
                                        <input id="stok" name="stok" class="form-input w-full px-2 py-1" type="number" value="1"/>
                                    </div>
                                    <div class="mt-3">
                                        <label class="block text-sm font-medium mb-1" for="stok_minimal">Stok Minimal <small>(Sebagai pengingat untuk menambah stok produk)</small></label>
                                        <input id="stok_minimal" name="stok_minimal" class="form-input w-full px-2 py-1" type="number" placeholder="Abaikan jika produk tidak memerlukan pengingat"/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="harga_modal">Harga Modal <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <input id="harga_modal" name="harga_modal" class="form-input w-full pl-10 px-2 py-1" type="number" required/>
                                            <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="harga_jual">Harga Jual <span class="text-rose-500">*</span></label>
                                        <div class="relative">
                                            <input id="harga_jual" name="harga_jual" class="form-input w-full pl-10 px-2 py-1" type="number" required/>
                                            <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                                <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($toko->is_tax === 1)
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="ppn">Apakah produk dikenakan pajak?</label>
                                            <div class="flex flex-wrap items-center -m-3">
                                                <div class="m-3">
                                                    <!-- Start -->
                                                    <label class="flex items-center">
                                                        <input type="radio" name="ppn" value="" class="form-radio" checked x-on:click="showDetails = true"/>
                                                        <span class="text-sm ml-2">Tidak</span>
                                                    </label>
                                                    <!-- End -->
                                                </div>
                                                <div class="m-3">
                                                    <!-- Start -->
                                                    <label class="flex items-center">
                                                        <input type="radio" name="ppn" value="{{ $toko->ppn }}" class="form-radio" x-on:click="showDetails = false"/>
                                                        <span class="text-sm ml-2">Ya</span>
                                                    </label>
                                                    <!-- End -->
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="garansi">Garansi Produk</label>
                                        <select id="garansi" name="garansi" class="form-select text-sm py-1 w-full">
                                            <option value="">Tidak Ada</option>
                                            <option value="1">1 Hari</option>
                                            <option value="2">2 Hari</option>
                                            <option value="3">3 Hari</option>
                                            <option value="4">4 Hari</option>
                                            <option value="5">5 Hari</option>
                                            <option value="6">6 Hari</option>
                                            <option value="7">1 Minggu</option>
                                            <option value="14">2 Minggu</option>
                                            <option value="21">3 Minggu</option>
                                            <option value="30">1 Bulan</option>
                                            <option value="60">2 Bulan</option>
                                            <option value="90">3 Bulan</option>
                                            <option value="120">4 Bulan</option>
                                            <option value="150">5 Bulan</option>
                                            <option value="180">6 Bulan</option>
                                            <option value="210">7 Bulan</option>
                                            <option value="240">8 Bulan</option>
                                            <option value="270">9 Bulan</option>
                                            <option value="300">10 Bulan</option>
                                            <option value="330">11 Bulan</option>
                                            <option value="365">1 Tahun</option>
                                            <option value="730">2 Tahun</option>
                                            <option value="1095">3 Tahun</option>
                                            <option value="1460">4 Tahun</option>
                                            <option value="1825">5 Tahun</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Modal footer -->
                            <div class="px-5 py-4 border-t border-slate-200">
                                <div class="flex flex-wrap justify-end space-x-2">
                                    <a href="{{ route('admin-sparepart.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                        Batal
                                    </a>
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>

    </div>

    <!-- More actions -->
    <div class="sm:flex sm:justify-between sm:items-center mb-5">
        <!-- Left side -->
        <div class="mb-4 sm:mb-0">
            <ul class="flex flex-wrap -m-1">
                <li class="m-1">
                    <a href="{{ route('admin-item.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Semua</button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('admin-handphone.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Handphone</button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('admin-sparepart.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-indigo-500 text-white duration-150 ease-in-out">Sparepart</button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('admin-aksesoris.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Aksesoris</button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('admin-tool.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Tool</button>
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
            <!-- Start Export Excel -->
            <a href="{{ route('admin-ekspor-sparepart') }}" class="hidden lg:block">
                <button class="btn bg-white border-blue-200 hover:border-blue-300 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-export" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2563eb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                    <path d="M11.5 21h-4.5a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v5m-5 6h7m-3 -3l3 3l-3 3" />
                    </svg>
                    <span class="hidden xs:block ml-2">Ekspor Data</span>
                </button>
            </a>
            <!-- End Export Excel-->

            <!-- Start Import Excel -->
            <div x-data="{ modalOpen: false }" class="hidden lg:block">
                <button class="btn bg-white border-emerald-200 hover:border-emerald-300 text-emerald-700" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-import" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="#047857" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                    <path d="M5 13v-8a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5.5m-9.5 -2h7m-3 -3l3 3l-3 3" />
                    </svg>
                    <span class="hidden xs:block ml-2">Impor Data</span>
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
                            <form action="{{ route('admin-impor-sparepart') }}" method="post" enctype="multipart/form-data">
                            @csrf
                                <!-- Modal header -->
                                <div class="px-5 py-3 border-b border-slate-200">
                                    <div class="flex justify-between items-center">
                                        <div class="font-semibold text-slate-800">Impor Data Produk</div>
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
                                            <p>Silahkan download terlebih dahulu formatnya, kemudian isi datanya dan upload.</p>
                                                <input type="file" name="file" id="file" class="btn-sm bg-slate-100 w-full" required>
                                        </div>
                                    </div>
                                </div>
                                <!-- Modal footer -->
                                <div class="px-5 py-4">
                                    <div class="flex flex-wrap justify-end space-x-2">
                                        <a href="{{ asset('storage/assets/format_sparepart.xlsx') }}" class="btn-sm bg-orange-500 hover:bg-orange-600 text-white">
                                            <span class="mr-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-download" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                <line x1="12" y1="11" x2="12" y2="17" />
                                                <polyline points="9 14 12 17 15 14" />
                                                </svg>
                                            </span>
                                            Download Format
                                        </a>
                                        <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">
                                            <span class="mr-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-upload" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                <line x1="12" y1="11" x2="12" y2="17" />
                                                <polyline points="9 14 12 11 15 14" />
                                                </svg>
                                            </span>
                                            Upload File
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>                                            
            </div>
            <!-- End Import Excel-->
        </div>
    </div>

    @if ($errors->any())
        <div x-show="open" x-data="{ open: true }">
            <div class="px-4 py-2 rounded-sm text-sm bg-rose-500 text-white">
                <div class="flex w-full justify-between items-start">
                    <div class="flex">
                        <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                            <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                        </svg>
                        @foreach ($errors->all() as $error)
                            <div class="font-medium">{{ $error }}</div>
                        @endforeach
                    </div>
                    <button class="opacity-70 hover:opacity-80 ml-3 mt-[3px]" @click="open = false">
                        <div class="sr-only">Close</div>
                        <svg class="w-4 h-4 fill-current">
                            <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-lg rounded-lg mt-5">
        <div class="px-5 py-4 border-b border-slate-200">
            <h2 class="font-semibold text-slate-800">Top Produk Terjual</h2>
        </div>
        <div class="divide-y divide-slate-200">
            @php $rank = 1; @endphp
            @foreach($topProducts as $itemtop)
                <div class="flex justify-between items-center px-5 py-3">
                    <div class="flex items-center space-x-3">
                        <span class="text-lg font-bold text-slate-500">{{ $rank++ }}.</span>
                        <span class="font-medium text-slate-800">{{ $itemtop->product_name }}</span>
                    </div>
                    <div class="text-right">
                        <p class="text-sm">Terjual: <span class="font-semibold">{{ $itemtop->total_terjual }}</span></p>
                        <p class="text-sm text-emerald-600">Rp {{ number_format($itemtop->omzet, 0, ',', '.') }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                {{-- Left side --}}
                <h2 class="font-semibold text-slate-800">Produk Sparepart <span class="text-slate-400 font-medium">{{ $spareparts_count }}</span></h2>
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
                                <div class="font-semibold text-left">No.</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Kategori Produk</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama Produk</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Kode Produk</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Model Seri</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Stok</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Harga Jual</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Keterangan</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Garansi Produk</div>
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
                                    <div class="font-medium">{{ $i++ }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->subCategory->name }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->product_name }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">
                                        @if ($item->product_code != null)
                                            {{ $item->product_code }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->model->name }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->stok }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">Rp. {{ number_format($item->harga_jual) }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->keterangan }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">
                                        @if ($item->garansi != null)
                                            {{ $item->garansi }} hari
                                        @else
                                            -
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="space-x-1 flex">
                                        <a href="{{ route('download-barcode', $item->id) }}">
                                            <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                    <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                    <rect x="7" y="13" width="10" height="8" rx="2" />
                                                </svg>
                                            </button>
                                        </a>
                                        @if ((int) ($toko->is_edit_produk ?? 0) == 1)
                                            <a href="{{ route('admin-sparepart.edit', $item->id) }}">
                                                <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                                    <span class="sr-only">Edit</span>
                                                    <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                        <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                                    </svg>
                                                </button>
                                            </a>
                                            <!-- Start -->
                                            <div x-data="{ showDelete: false, deleteId: null }" x-show = "showDelete" x-on:open-delete.window="showDelete = true; deleteId = $event.detail.id" x-on:close-delete.window = "showDelete = false" x-on:keydown.escape.window = "showDelete = false" class="fixed z-50 inset-0">
                                                <!-- Modal backdrop -->
                                                <div x-on:click="showDelete = false" class="fixed inset-0 bg-slate-900 bg-opacity-40" x-cloak></div>
                                                <!-- Modal dialog -->
                                                <div
                                                    id="danger-modal"
                                                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                                    role="dialog"
                                                    aria-modal="true"
                                                    x-show="showDelete"
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
                                                                        <p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi.</p>
                                                                    </div>
                                                                </div>
                                                                <!-- Modal footer -->
                                                                <div class="flex flex-wrap justify-end space-x-2">
                                                                    <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" x-on:click="$dispatch('close-delete')">Batal</button>
                                                                    <form x-bind:action="'{{ route('admin-sparepart.destroy', '') }}/' + deleteId" method="post">
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
                                            
                                            <button x-data x-on:click="$dispatch('open-delete', { id: {{ $item->id }} })" class="text-rose-500 hover:text-rose-600 rounded-full">
                                                <span class="sr-only">Delete</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <line x1="4" y1="7" x2="20" y2="7" />
                                                    <line x1="10" y1="11" x2="10" y2="17" />
                                                    <line x1="14" y1="11" x2="14" y2="17" />
                                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                </svg>
                                            </button>
                                        @else
                                        -
                                        @endif
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
        {{ $products->links() }}
    </div>
</div>