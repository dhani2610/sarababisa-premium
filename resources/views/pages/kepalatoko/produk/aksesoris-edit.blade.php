@section('title')
    Edit Item Produk
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Item Produk ✨</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Search form -->
                <x-search-form placeholder="Masukkan nama produk" />

                <!-- Create invoice button -->
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="hidden xs:block ml-2">Tambah Aksesoris</span>
                </button>

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
                            <div class="font-semibold text-slate-800">Edit Aksesoris</div>
                            <a href="{{ route('aksesoris.index') }}" class="text-slate-400 hover:text-slate-500">
                                <div class="sr-only">Close</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Modal content -->
                    <form action="{{ route('aksesoris.update', $item->id) }}" method="post">
                        @method('PUT')
                        @csrf
                        <input type="hidden" name="categories_id" value="{{ $item->categories_id }}">
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="sub_categories_id">Sub Kategori <span class="text-rose-500">*</span></label>
                                    <select id="sub_categories_id" name="sub_categories_id" class="form-select text-sm py-1 w-full" style="width: 100%">
                                        <option selected value="{{ $item->subCategory->id }}">{{ $item->subCategory->name }}</option>
                                        @foreach ($accessories as $accessory)
                                            <option value="{{ $accessory->id }}">{{ $accessory->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="product_name">Nama Produk</label>
                                    <input id="product_name" name="product_name" class="form-input w-full px-2 py-1" type="text" value="{{ $item->product_name }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="model_series_id">Model Seri <span class="text-rose-500">*</span></label>
                                    <select id="model_series_id" name="model_series_id" class="form-select text-sm py-1 w-full selectjs1" style="width: 100%">
                                        <option selected value="{{ $item->model->id }}">{{ $item->model->name }}</option>
                                        @foreach ($model_series as $model)
                                            <option value="{{ $model->id }}">{{ $model->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="keterangan">Keterangan Produk</label>
                                    <input id="keterangan" name="keterangan" class="form-input w-full px-2 py-1" type="text" value="{{ $item->keterangan }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="product_code">Kode Produk</label>
                                    <input id="product_code" name="product_code" class="form-input w-full px-2 py-1" type="text" value="{{ $item->product_code }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="stok">Stok</label>
                                    <input id="stok" name="stok" class="form-input w-full px-2 py-1" type="number" value="{{ $item->stok }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="stok_minimal">Stok Minimal</label>
                                    <input id="stok_minimal" name="stok_minimal" class="form-input w-full px-2 py-1" type="number" value="{{ $item->stok_minimal }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="harga_modal">Harga Modal</label>
                                    <div class="relative">
                                        <input id="harga_modal" name="harga_modal" class="form-input w-full pl-10 px-2 py-1 input-currency" type="text" value="{{ $item->harga_modal }}"/>
                                        <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                            <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="harga_jual_toko">Harga Jual Toko<span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <input id="harga_jual_toko" name="harga_jual_toko" class="form-input w-full pl-10 px-2 py-1 input-currency" type="text" value="{{ $item->harga_jual_toko }}" required/>
                                        <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                            <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="harga_jual">Harga Jual Pelanggan</label>
                                    <div class="relative">
                                        <input id="harga_jual" name="harga_jual" class="form-input w-full pl-10 px-2 py-1 input-currency" type="text" value="{{ $item->harga_jual }}"/>
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
                                                    <input type="radio" name="ppn" value="" class="form-radio"
                                                    @if ($item->ppn === null)
                                                checked
                                            @endif/>
                                                    <span class="text-sm ml-2">Tidak</span>
                                                </label>
                                                <!-- End -->
                                            </div>
                                            <div class="m-3">
                                                <!-- Start -->
                                                <label class="flex items-center">
                                                    <input type="radio" name="ppn" value="{{ $toko->ppn }}" class="form-radio"
                                                    @if ($item->ppn != null)
                                                        checked
                                                    @endif/>
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
                                        <option selected value="{{ $item->garansi }}">{{ $item->garansi }}</option>
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
                                <a href="{{ route('aksesoris.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
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

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                Alpine.data('form', () => ({
                    isManual: false,
                }));
            });
        </script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('.selectjs1').select2();
            });
        </script>
    @endpush
</x-toko-layout>
