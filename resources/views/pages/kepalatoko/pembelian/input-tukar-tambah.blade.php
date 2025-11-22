@section('title')
    Tambah Pembelian Produk
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Tambah Pembelian Produk ✨</h1>
            </div>

        </div>

        <div class="mt-8 bg-white px-5 py-4">
            <form action="{{ route('tukar-tambah.store') }}" method="post">
                @csrf
                <input type="hidden" name="order_date" value="{{ \Carbon\Carbon::today()->locale('id')->translatedFormat('d F Y') }}">
                <div class="font-semibold text-slate-800 mb-3">Form Penjualan</div>
                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium mb-1" for="users_id">Sales <span class="text-rose-500">*</span></label>
                        <select id="users_id" name="users_id" class="form-select text-sm py-1 w-full" required>
                            <option selected="">Pilih Sales</option>
                            @foreach ($sales as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="customers_id">Pelanggan <span class="text-rose-500">*</span></label>
                        <select id="customers_id" name="customers_id" class="form-select text-sm py-1 w-full selectjs1" required style="width: 100%">
                            <option selected="">Pilih Pelanggan</option>
                            @foreach ($customers as $item)
                                <option value="{{ $item->id }}">{{ $item->nama }} {{ $item->nomor_hp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="products_id">Produk yang dijual <span class="text-rose-500">*</span></label>
                        <select id="products_id" name="product_sale_id" class="form-select text-sm py-1 w-full selectjs2" required style="width: 100%">
                            <option selected="">Pilih Produk</option>
                            @foreach (App\Models\Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->get() as $item)
                                <option value="{{ $item->id }}">{{ $item->product_name }} ({{ $item->nomor_seri }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="price">Harga Jual <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input id="price" name="price" class="form-input w-full pl-10 px-2 py-1" type="number" required/>
                            <div class="absolute inset-0 right-auto flex items-center pointer-events-none">
                                <span class="text-sm text-slate-400 font-medium px-3">Rp.</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="payment_method">Metode Pembayaran <span class="text-rose-500">*</span></label>
                        <select id="payment_method" name="payment_method" required class="form-select w-full text-sm py-1">
                            <option value="">Pilih Metode Pembayaran</option>
                            <option value="Tunai">Tunai</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>
                </div>
                <div class="font-semibold text-slate-800 mt-6 mb-3">Form Penukaran</div>
                <div class="grid gap-5 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium mb-1" for="brands_id">Merek <span class="text-rose-500">*</span></label>
                        <select id="brands_id" name="brands_id" class="form-select text-sm py-1 w-full selectjs3" style="width: 100%" required>
                            <option value="">Pilih Merek</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="model_series_id">Model Seri <span class="text-rose-500">*</span></label>
                        <select id="model_series_id" name="model_series_id" class="form-select text-sm py-1 w-full selectjs4" style="width: 100%" required>
                            <option value="">Pilih Model Seri</option>
                            @foreach ($model_series as $model)
                                <option value="{{ $model->id }}">{{ $model->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="ram">RAM <span class="text-rose-500">*</span></label>
                        <select id="ram" name="ram" class="form-select text-sm py-1 w-full" required>
                            <option value="">Pilih RAM</option>
                            <option value="2 GB">2 GB</option>
                            <option value="3 GB">3 GB</option>
                            <option value="4 GB">4 GB</option>
                            <option value="6 GB">6 GB</option>
                            <option value="8 GB">8 GB</option>
                            <option value="12 GB">12 GB</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="capacities_id">Memori <span class="text-rose-500">*</span></label>
                        <select id="capacities_id" name="capacities_id" class="form-select text-sm py-1 w-full" required>
                            <option value="">Pilih Memori</option>
                            @foreach ($capacities as $capacity)
                                <option value="{{ $capacity->id }}">{{ $capacity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="warna">Warna <span class="text-rose-500">*</span></label>
                        <select id="warna" name="warna" class="form-select text-sm py-1 w-full" required>
                            <option value="">Pilih Warna</option>
                            @foreach ($colors as $item)
                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="nomor_seri">IMEI/SN</label>
                        <input id="nomor_seri" name="nomor_seri" class="form-input w-full px-2 py-1" type="text" required/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="keterangan">Keterangan Produk</label>
                        <input id="keterangan" name="keterangan" class="form-input w-full px-2 py-1" type="text" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="product_code">Kode Produk</label>
                        <input id="product_code" name="product_code" class="form-input w-full px-2 py-1" type="text" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="harga_modal">Harga Tukar <span class="text-rose-500">*</span></label>
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
                        <select id="garansi_imei" name="garansi_imei" class="form-select text-sm py-1 w-full">
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
                <div class="flex flex-wrap justify-end space-x-2 mt-6">
                    <a href="{{ route('tukar-tambah.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                        Batal
                    </a>
                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                </div>
            </form>
        </div>

    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                Alpine.data('form', () => ({
                    isManual: false,
                }));
            });
        </script>
        {{-- Selectjs --}}
        <script type="text/javascript">
            $(document).ready(function() {
                $('.selectjs1').select2();
                $('.selectjs2').select2();
                $('.selectjs3').select2();
                $('.selectjs4').select2();
            });
        </script>
        <script>
            $(document).ready(function () {
                $('#products_id').on('change', function () {
                    var productId = $(this).val();
                    if (productId) {
                        $.ajax({
                            type: 'GET',
                            url: '/get-product/' + productId,
                            dataType: 'json',
                            success: function (data) {
                                $('#price').val(data.price);
                            }
                        });
                    } else {
                        $('#price').val('');
                    }
                });

                $(document).on('change','#brands_id',function(){
                    var brands_id = $(this).val();
                    $.ajax({
                        url:"{{ route('get-modelserie') }}",
                        type: "GET",
                        data:{brands_id:brands_id},
                        success:function(data){
                            var html = '<option value="">Pilih Model Seri</option>';
                            $.each(data,function(key,v){
                                html += '<option value=" '+v.id+' "> '+v.name+'</option>';
                            });
                            $('#model_series_id').html(html);
                        }
                    })
                });
            });
        </script>
    @endpush
</x-toko-layout>
