  <div x-data="{ modalOpen: false }">
      <button class="btn ml-3 mt-3 bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true"
          aria-controls="tambah-modal">
          <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
              <path
                  d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
          </svg>
          <span class="hidden xs:block ml-2">Tambah Produk</span>
      </button>
      <!-- Modal backdrop -->
      <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
          x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
          x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>
      <!-- Modal dialog -->
      <div id="tambah-modal"
          class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog"
          aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200"
          x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
          x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0"
          x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
          <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">
              <!-- Modal header -->
              <div class="px-5 py-3 border-b border-slate-200">
                  <div class="flex justify-between items-center">
                      <div class="font-semibold text-slate-800">Tambah Produk</div>
                      <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                          <div class="sr-only">Close</div>
                          <svg class="w-4 h-4 fill-current">
                              <path
                                  d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                          </svg>
                      </button>
                  </div>
              </div>
              <!-- Modal content -->
              <div x-data="{ tab: '1' }" class="px-5 py-4">
                  <!-- Tabs buttons -->
                  <div class="flex flex-wrap items-center -m-3 mb-0">
                      <div class="m-3">
                          <!-- Start -->
                          <label class="flex items-center">
                              <input type="radio" name="radio-buttons" class="form-radio" checked
                                  @click="tab = '1'" />
                              <span class="text-sm ml-2">Handphone</span>
                          </label>
                          <!-- End -->
                      </div>
                      <div class="m-3">
                          <!-- Start -->
                          <label class="flex items-center">
                              <input type="radio" name="radio-buttons" class="form-radio" @click="tab = '2'" />
                              <span class="text-sm ml-2">Sparepart</span>
                          </label>
                          <!-- End -->
                      </div>
                      <div class="m-3">
                          <!-- Start -->
                          <label class="flex items-center">
                              <input type="radio" name="radio-buttons" class="form-radio" @click="tab = '3'" />
                              <span class="text-sm ml-2">Aksesoris</span>
                          </label>
                          <!-- End -->
                      </div>
                      <div class="m-3">
                          <!-- Start -->
                          <label class="flex items-center">
                              <input type="radio" name="radio-buttons" class="form-radio" @click="tab = '4'" />
                              <span class="text-sm ml-2">Tool</span>
                          </label>
                          <!-- End -->
                      </div>
                  </div>
                  <div x-show="tab === '1'">
                      <form action="{{ route('handphone.store') }}" method="post">
                          @csrf
                          <input type="hidden" name="categories_id" value="1">
                          <div class="space-y-3">
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="brands_id">Merek <span
                                          class="text-rose-500">*</span></label>
                                  <select id="brands_id" name="brands_id"
                                      class="form-select text-sm py-1 w-full selectjs1" style="width: 100%" required>
                                      <option selected value="">Pilih Merek</option>
                                      @foreach ($brands as $brand)
                                          <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="model_series_id">Model Seri <span
                                          class="text-rose-500">*</span></label>
                                  <select id="model_series_id" name="model_series_id"
                                      class="form-select text-sm py-1 w-full selectjs2" style="width: 100%" required>
                                      <option selected value="">Pilih Model Seri</option>
                                      @foreach ($model_series as $model)
                                          <option value="{{ $model->id }}">{{ $model->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="ram">RAM <span
                                          class="text-rose-500">*</span></label>
                                  <select id="ram" name="ram" class="form-select text-sm py-1 w-full"
                                      required>
                                      <option selected value="">Pilih RAM</option>
                                      <option value="2 GB">2 GB</option>
                                      <option value="3 GB">3 GB</option>
                                      <option value="4 GB">4 GB</option>
                                      <option value="6 GB">6 GB</option>
                                      <option value="8 GB">8 GB</option>
                                      <option value="12 GB">12 GB</option>
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="capacities_id">Memori <span
                                          class="text-rose-500">*</span></label>
                                  <select id="capacities_id" name="capacities_id"
                                      class="form-select text-sm py-1 w-full" required>
                                      <option selected value="">Pilih Memori</option>
                                      @foreach ($capacities as $capacity)
                                          <option value="{{ $capacity->id }}">{{ $capacity->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="warna">Warna <span
                                          class="text-rose-500">*</span></label>
                                  <select id="warna" name="warna" class="form-select text-sm py-1 w-full"
                                      required>
                                      <option selected value="">Pilih Warna</option>
                                      @foreach ($colors as $item)
                                          <option value="{{ $item->name }}">{{ $item->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="kondisi">Kondisi <span
                                          class="text-rose-500">*</span></label>
                                  <select id="kondisi" name="kondisi" class="form-select text-sm py-1 w-full"
                                      required>
                                      <option selected value="">Pilih Kondisi</option>
                                      <option value="NEW">NEW</option>
                                      <option value="SECOND">SECOND</option>
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="nomor_seri">IMEI/SN</label>
                                  <input id="nomor_seri" name="nomor_seri" class="form-input w-full px-2 py-1"
                                      type="text" required />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="product_code">Kode Produk</label>
                                  <input id="product_code" name="product_code" class="form-input w-full px-2 py-1"
                                      type="text" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="keterangan">Keterangan
                                      Produk</label>
                                  <input id="keterangan" name="keterangan" class="form-input w-full px-2 py-1"
                                      type="text" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="stok">Stok <span
                                          class="text-rose-500">*</span></label>
                                  <input id="stok" name="stok" class="form-input w-full px-2 py-1"
                                      type="number" value="1" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="stok_minimal">Stok Minimal
                                      <small>(Sebagai pengingat untuk menambah stok produk)</small></label>
                                  <input id="stok_minimal" name="stok_minimal" class="form-input w-full px-2 py-1"
                                      type="number" placeholder="Abaikan jika produk tidak memerlukan pengingat" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="harga_modal">Harga Modal <span
                                          class="text-rose-500">*</span></label>
                                  <div class="relative">
                                      <input id="harga_modal" name="harga_modal"
                                          class="form-input w-full pl-10 px-2 py-1" type="number" required />
                                      <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                          <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                      </div>
                                  </div>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="harga_jual">Harga Jual <span
                                          class="text-rose-500">*</span></label>
                                  <div class="relative">
                                      <input id="harga_jual" name="harga_jual"
                                          class="form-input w-full pl-10 px-2 py-1" type="number" required />
                                      <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                          <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                      </div>
                                  </div>
                              </div>
                              @if ($toko->is_tax === 1)
                                  <div>
                                      <label class="block text-sm font-medium mb-1" for="ppn">Apakah produk
                                          dikenakan pajak?</label>
                                      <div class="flex flex-wrap items-center -m-3">
                                          <div class="m-3">
                                              <!-- Start -->
                                              <label class="flex items-center">
                                                  <input type="radio" name="ppn" value=""
                                                      class="form-radio" checked x-on:click="showDetails = true" />
                                                  <span class="text-sm ml-2">Tidak</span>
                                              </label>
                                              <!-- End -->
                                          </div>
                                          <div class="m-3">
                                              <!-- Start -->
                                              <label class="flex items-center">
                                                  <input type="radio" name="ppn" value="{{ $toko->ppn }}"
                                                      class="form-radio" x-on:click="showDetails = false" />
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
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="garansi_imei">
                                      Garansi IMEI <small>(Abaikan jika bukan produk iPhone)</small>
                                  </label>
                                  <select id="garansi_imei" name="garansi_imei"
                                      class="form-select text-sm py-1 w-full">
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
                          <!-- Modal footer -->
                          <div class="py-4 border-t border-slate-200">
                              <div class="flex flex-wrap justify-end space-x-2">
                                  <a href="{{ route('item.index') }}"
                                      class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                      Batal
                                  </a>
                                  <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                              </div>
                          </div>
                      </form>
                  </div>
                  <div x-show="tab === '2'">
                      <form action="{{ route('sparepart.store') }}" method="post">
                          @csrf
                          <input type="hidden" name="categories_id" value="2">
                          <div class="space-y-3">
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="sub_categories_id">Sub Kategori
                                      Produk <span class="text-rose-500">*</span></label>
                                  <select id="sub_categories_id" name="sub_categories_id"
                                      class="form-select text-sm w-full" required>
                                      <option selected value="">Pilih Sub Kategori</option>
                                      @foreach ($spareparts as $sparepart)
                                          <option value="{{ $sparepart->id }}">{{ $sparepart->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="product_name">Nama Produk <span
                                          class="text-rose-500">*</span></label>
                                  <input id="product_name" name="product_name" class="form-input w-full px-2 py-1"
                                      type="text" required />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="selectjs3">Model Seri <span
                                          class="text-rose-500">*</span></label>
                                  <select id="selectjs3" name="model_series_id"
                                      class="form-select text-sm py-1 w-full" style="width: 100%" required>
                                      <option selected value="">Pilih Model Seri</option>
                                      @foreach ($model_series as $model)
                                          <option value="{{ $model->id }}">{{ $model->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="product_code">Kode Produk</label>
                                  <input id="product_code" name="product_code" class="form-input w-full px-2 py-1"
                                      type="text" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="keterangan">Keterangan
                                      Produk</label>
                                  <input id="keterangan" name="keterangan" class="form-input w-full px-2 py-1"
                                      type="text" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="stok">Stok <span
                                          class="text-rose-500">*</span></label>
                                  <input id="stok" name="stok" class="form-input w-full px-2 py-1"
                                      type="number" value="1" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="stok_minimal">Stok Minimal
                                      <small>(Sebagai pengingat untuk menambah stok produk)</small></label>
                                  <input id="stok_minimal" name="stok_minimal" class="form-input w-full px-2 py-1"
                                      type="number" placeholder="Abaikan jika produk tidak memerlukan pengingat" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="harga_modal">Harga Modal <span
                                          class="text-rose-500">*</span></label>
                                  <div class="relative">
                                      <input id="harga_modal" name="harga_modal"
                                          class="form-input w-full pl-10 px-2 py-1" type="number" required />
                                      <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                          <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                      </div>
                                  </div>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="harga_jual">Harga Jual <span
                                          class="text-rose-500">*</span></label>
                                  <div class="relative">
                                      <input id="harga_jual" name="harga_jual"
                                          class="form-input w-full pl-10 px-2 py-1" type="number" required />
                                      <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                          <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                      </div>
                                  </div>
                              </div>
                              @if ($toko->is_tax === 1)
                                  <div>
                                      <label class="block text-sm font-medium mb-1" for="ppn">Apakah produk
                                          dikenakan pajak?</label>
                                      <div class="flex flex-wrap items-center -m-3">
                                          <div class="m-3">
                                              <!-- Start -->
                                              <label class="flex items-center">
                                                  <input type="radio" name="ppn" value=""
                                                      class="form-radio" checked x-on:click="showDetails = true" />
                                                  <span class="text-sm ml-2">Tidak</span>
                                              </label>
                                              <!-- End -->
                                          </div>
                                          <div class="m-3">
                                              <!-- Start -->
                                              <label class="flex items-center">
                                                  <input type="radio" name="ppn" value="{{ $toko->ppn }}"
                                                      class="form-radio" x-on:click="showDetails = false" />
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
                          <!-- Modal footer -->
                          <div class="py-4 border-t border-slate-200">
                              <div class="flex flex-wrap justify-end space-x-2">
                                  <a href="{{ route('item.index') }}"
                                      class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                      Batal
                                  </a>
                                  <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                              </div>
                          </div>
                      </form>
                  </div>
                  <div x-show="tab === '3'">
                      <form action="{{ route('aksesoris.store') }}" method="post">
                          @csrf
                          <input type="hidden" name="categories_id" value="3">
                          <div class="space-y-3">
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="sub_categories_id">Sub Kategori
                                      Produk <span class="text-rose-500">*</span></label>
                                  <select id="sub_categories_id" name="sub_categories_id"
                                      class="form-select text-sm w-full" required>
                                      <option value="">Pilih Sub Kategori</option>
                                      @foreach ($accessories as $accessory)
                                          <option value="{{ $accessory->id }}">{{ $accessory->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="product_name">Nama Produk <span
                                          class="text-rose-500">*</span></label>
                                  <input id="product_name" name="product_name" class="form-input w-full px-2 py-1"
                                      type="text" required />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="selectjs4">Model Seri <span
                                          class="text-rose-500">*</span></label>
                                  <select id="selectjs4" name="model_series_id"
                                      class="form-select text-sm py-1 w-full" style="width: 100%" required>
                                      <option selected value="">Pilih Model Seri</option>
                                      @foreach ($model_series as $model)
                                          <option value="{{ $model->id }}">{{ $model->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="product_code">Kode Produk</label>
                                  <input id="product_code" name="product_code" class="form-input w-full px-2 py-1"
                                      type="text" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="keterangan">Keterangan
                                      Produk</label>
                                  <input id="keterangan" name="keterangan" class="form-input w-full px-2 py-1"
                                      type="text" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="stok">Stok <span
                                          class="text-rose-500">*</span></label>
                                  <input id="stok" name="stok" class="form-input w-full px-2 py-1"
                                      type="number" value="1" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="stok_minimal">Stok Minimal
                                      <small>(Sebagai pengingat untuk menambah stok produk)</small></label>
                                  <input id="stok_minimal" name="stok_minimal" class="form-input w-full px-2 py-1"
                                      type="number" placeholder="Abaikan jika produk tidak memerlukan pengingat" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="harga_modal">Harga Modal <span
                                          class="text-rose-500">*</span></label>
                                  <div class="relative">
                                      <input id="harga_modal" name="harga_modal"
                                          class="form-input w-full pl-10 px-2 py-1" type="number" required />
                                      <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                          <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                      </div>
                                  </div>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="harga_jual">Harga Jual <span
                                          class="text-rose-500">*</span></label>
                                  <div class="relative">
                                      <input id="harga_jual" name="harga_jual"
                                          class="form-input w-full pl-10 px-2 py-1" type="number" required />
                                      <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                          <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                      </div>
                                  </div>
                              </div>
                              @if ($toko->is_tax === 1)
                                  <div>
                                      <label class="block text-sm font-medium mb-1" for="ppn">Apakah produk
                                          dikenakan pajak?</label>
                                      <div class="flex flex-wrap items-center -m-3">
                                          <div class="m-3">
                                              <!-- Start -->
                                              <label class="flex items-center">
                                                  <input type="radio" name="ppn" value=""
                                                      class="form-radio" checked x-on:click="showDetails = true" />
                                                  <span class="text-sm ml-2">Tidak</span>
                                              </label>
                                              <!-- End -->
                                          </div>
                                          <div class="m-3">
                                              <!-- Start -->
                                              <label class="flex items-center">
                                                  <input type="radio" name="ppn" value="{{ $toko->ppn }}"
                                                      class="form-radio" x-on:click="showDetails = false" />
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
                          <!-- Modal footer -->
                          <div class="py-4 border-t border-slate-200">
                              <div class="flex flex-wrap justify-end space-x-2">
                                  <a href="{{ route('item.index') }}"
                                      class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                      Batal
                                  </a>
                                  <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                              </div>
                          </div>
                      </form>
                  </div>
                  <div x-show="tab === '4'">
                      <form action="{{ route('tool.store') }}" method="post">
                          @csrf
                          <input type="hidden" name="categories_id" value="4">
                          <div class="space-y-3">
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="sub_categories_id">Sub Kategori
                                      Produk <span class="text-rose-500">*</span></label>
                                  <select id="sub_categories_id" name="sub_categories_id"
                                      class="form-select text-sm w-full" required>
                                      <option value="">Pilih Sub Kategori</option>
                                      @foreach ($tools as $tool)
                                          <option value="{{ $tool->id }}">{{ $tool->name }}</option>
                                      @endforeach
                                  </select>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="product_name">Nama Produk <span
                                          class="text-rose-500">*</span></label>
                                  <input id="product_name" name="product_name" class="form-input w-full px-2 py-1"
                                      type="text" required />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="product_code">Kode Produk</label>
                                  <input id="product_code" name="product_code" class="form-input w-full px-2 py-1"
                                      type="text" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="keterangan">Keterangan
                                      Produk</label>
                                  <input id="keterangan" name="keterangan" class="form-input w-full px-2 py-1"
                                      type="text" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="stok">Stok <span
                                          class="text-rose-500">*</span></label>
                                  <input id="stok" name="stok" class="form-input w-full px-2 py-1"
                                      type="number" value="1" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="stok_minimal">Stok Minimal
                                      <small>(Sebagai pengingat untuk menambah stok produk)</small></label>
                                  <input id="stok_minimal" name="stok_minimal" class="form-input w-full px-2 py-1"
                                      type="number" placeholder="Abaikan jika produk tidak memerlukan pengingat" />
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="harga_modal">Harga Modal <span
                                          class="text-rose-500">*</span></label>
                                  <div class="relative">
                                      <input id="harga_modal" name="harga_modal"
                                          class="form-input w-full pl-10 px-2 py-1" type="number" required />
                                      <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                          <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                      </div>
                                  </div>
                              </div>
                              <div>
                                  <label class="block text-sm font-medium mb-1" for="harga_jual">Harga Jual <span
                                          class="text-rose-500">*</span></label>
                                  <div class="relative">
                                      <input id="harga_jual" name="harga_jual"
                                          class="form-input w-full pl-10 px-2 py-1" type="number" required />
                                      <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                          <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                                      </div>
                                  </div>
                              </div>
                              @if ($toko->is_tax === 1)
                                  <div>
                                      <label class="block text-sm font-medium mb-1" for="ppn">Apakah produk
                                          dikenakan pajak?</label>
                                      <div class="flex flex-wrap items-center -m-3">
                                          <div class="m-3">
                                              <!-- Start -->
                                              <label class="flex items-center">
                                                  <input type="radio" name="ppn" value=""
                                                      class="form-radio" checked x-on:click="showDetails = true" />
                                                  <span class="text-sm ml-2">Tidak</span>
                                              </label>
                                              <!-- End -->
                                          </div>
                                          <div class="m-3">
                                              <!-- Start -->
                                              <label class="flex items-center">
                                                  <input type="radio" name="ppn" value="{{ $toko->ppn }}"
                                                      class="form-radio" x-on:click="showDetails = false" />
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
                          <!-- Modal footer -->
                          <div class="py-4 border-t border-slate-200">
                              <div class="flex flex-wrap justify-end space-x-2">
                                  <a href="{{ route('item.index') }}"
                                      class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
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
