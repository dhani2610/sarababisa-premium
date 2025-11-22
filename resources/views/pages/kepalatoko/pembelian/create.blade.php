@section('title')
    Tambah Pembelian Produk
@endsection

<x-toko-layout background="bg-white">
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Tambah Pembelian Produk ✨</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Start -->
                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-slate-500 hover:bg-slate-600 text-white" @click.prevent="modalOpen = true" aria-controls="danger-modal">
                        <span class="sr-only">Exit</span>
                        <svg class="w-4 h-4 fill-current">
                            <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                        </svg>
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
                        id="danger-modal"
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
                                        <div class="text-lg font-semibold text-slate-800">Tinggalkan halaman ini ?</div>
                                    </div>
                                    <!-- Modal content -->
                                    <div class="text-sm mb-10">
                                        <div class="space-y-2">
                                            <p>Jika Anda keluar, inputan Anda tidak akan disimpan.</p>
                                        </div>
                                    </div>
                                    <!-- Modal footer -->
                                    <div class="flex flex-wrap justify-end space-x-2">
                                        <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white" @click="modalOpen = false">Tetap Disini</button>
                                        <a href="{{ route('purchase.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                            Keluar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End -->

            </div>

        </div>

        <div class="space-y-8 mt-8 mb-6">
            <div class="grid gap-5 md:grid-cols-3">
                <div>
                    <!-- Start -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="default">Supplier</label>
                        <select id="suppliers_id" name="suppliers_id" class="form-select text-sm w-full" required>
                            <option value="">Pilih Supplier</option>
                            @foreach ($suppliers as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- End -->
                </div>

                <div>
                    <!-- Start -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="reference_number">No. Referensi</label>
                        <input id="reference_number" name="reference_number" class="form-input w-full" type="text" />
                    </div>
                    <!-- End -->
                </div>

                <div>
                    <!-- Start -->
                    <div>
                        <label class="block text-sm font-medium mb-1" for="date">Tanggal</label>
                        <input id="date" name="date" class="form-input w-full" type="date" value="<?php echo date('Y-m-d'); ?>"/>
                    </div>
                    <!-- End -->
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm font-medium mb-1" for="categories_id">Kategori <span class="text-rose-500">*</span></label>
            <select id="categories_id" name="categories_id" class="form-select text-sm py-1 w-full" required>
                <option selected="">Pilih Kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select id="products_id" name="products_id" class="form-select text-sm w-full selectjs1" required>
                <option value="">Pilih Produk</option>
                @foreach (App\Models\Product::where('cabang_id',getCabangId())->where('stok', '>', 0)->get() as $item)
                    <option value="{{ $item->id }}">{{ $item->product_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-3">
            <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white w-full addeventmore">
                <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                </svg>
                <span class="hidden xs:block ml-2">Masukkan Produk</span>
            </button>
        </div>

        <form method="post" action="{{ route('purchase.store') }}">
            @csrf
            <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
                <header class="px-5 py-4">
                    <h2 class="font-semibold text-slate-800">Data Produk</h2>
                </header>
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <!-- Table header -->
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                            <tr>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Nama Produk</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Kuantitas</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Harga Beli</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Keterangan</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Total</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Aksi</div>
                                </th>
                            </tr>
                        </thead>

                        <tbody id="addRow" class="text-sm divide-y divide-slate-200">

                        </tbody>

                        <!-- Table body -->
                        <tbody class="text-sm divide-y divide-slate-200">
                            <!-- Row -->
                            <tr>
                                <td colspan="4"></td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <input type="text" name="estimated_amount" id="estimated_amount" value="0" class="form-input estimated_amount" readonly style="background-color: #ddd;">
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" id="storeButton" class="btn bg-emerald-500 hover:bg-emerald-600 text-white w-full">
                    Simpan Data Pembelian
                </button>
            </div>
        </form>

    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.7.8/handlebars.min.js" integrity="sha512-E1dSFxg+wsfJ4HKjutk/WaCzK7S2wv1POn1RRPGh8ZK+ag9l244Vqxji3r6wgz9YBf6+vhQEYJZpSjqWFPg9gg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js" integrity="sha512-efUTj3HdSPwWJ9gjfGR71X9cvsrthIA78/Fvd/IN+fttQVy7XWkOAXb295j8B3cmm/kFKVxjiNYzKw9IQJHIuQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script id="document-template" type="text/x-handlebars-template">

            <tr class="delete_add_more_item" id="delete_add_more_item">
                <input type="hidden" name="date[]" value="@{{date}}">
                <input type="hidden" name="reference_number[]" value="@{{reference_number}}">
                <input type="hidden" name="suppliers_id[]" value="@{{suppliers_id}}">
>
                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <div class="font-medium">@{{ product_name }}</div>
                    <input type="hidden" name="products_id[]" value="@{{products_id}}">
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="number" min="1" class="form-input quantity text-right" name="quantity[]" value="">
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="number" class="form-input product_price text-right" name="product_price[]" value="">
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="text" class="form-input" name="keterangan[]">
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <input type="number" class="form-input total_price" name="total_price[]" value="0" readonly>
                </td>

                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                    <button class="text-rose-500 hover:text-rose-600 rounded-full removeeventmore">
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
                </td>

            </tr>

        </script>

        <script type="text/javascript">
            $(document).ready(function(){
                $(document).on("click",".addeventmore", function(){
                    var date = $('#date').val();
                    var reference_number = $('#reference_number').val();
                    var suppliers_id = $('#suppliers_id').val();
                    var products_id = $('#products_id').val();
                    var product_name = $('#products_id').find('option:selected').text();
                    if(date == ''){
                        $.notify("Tanggal wajib diisi" ,  {globalPosition: 'top right', className:'error' });
                        return false;
                        }
                        if(reference_number == ''){
                        $.notify("Nomor Referensi wajib diisi" ,  {globalPosition: 'top right', className:'error' });
                        return false;
                        }
                        if(suppliers_id == ''){
                        $.notify("Supplier wajib diisi" ,  {globalPosition: 'top right', className:'error' });
                        return false;
                        }
                        if(products_id == ''){
                        $.notify("Produk wajib dipilih" ,  {globalPosition: 'top right', className:'error' });
                        return false;
                        }
                        var source = $("#document-template").html();
                        var template = Handlebars.compile(source);
                        var data = {
                            date:date,
                            reference_number:reference_number,
                            suppliers_id:suppliers_id,
                            products_id:products_id,
                            product_name:product_name
                        };
                        var html = template(data);
                        $("#addRow").append(html);
                });

                $(document).on("click",".removeeventmore",function(event){
                    $(this).closest(".delete_add_more_item").remove();
                    totalAmountPrice();
                });

                $(document).on('keyup click','.product_price,.quantity', function(){
                    var product_price = $(this).closest("tr").find("input.product_price").val();
                    var quantity = $(this).closest("tr").find("input.quantity").val();
                    var total = product_price * quantity;
                    $(this).closest("tr").find("input.total_price").val(total);
                    totalAmountPrice();
                });

                function totalAmountPrice(){
                    var sum = 0;
                    $(".total_price").each(function(){
                        var value = $(this).val();
                        if(!isNaN(value) && value.length != 0){
                            sum += parseFloat(value);
                        }
                    });
                    const n = sum;
                    const formatted = n.toLocaleString('id');
                    $('#estimated_amount').val(formatted);
                }
            })
        </script>

        <script type="text/javascript">
            $(document).ready(function() {
                $('.selectjs1').select2();
            });
        </script>

        <script type="text/javascript">
            $(function(){
                $(document).on('change','#categories_id',function(){
                    var categories_id = $(this).val();
                    $.ajax({
                        url:"{{ route('get-product') }}",
                        type: "GET",
                        data:{categories_id:categories_id},
                        success:function(data){
                            var html = '<option value="">Pilih Produk</option>';
                            $.each(data,function(key,v){
                                html += '<option value=" '+v.id+' "> '+v.product_name+'</option>';
                            });
                            $('#products_id').html(html);
                        }
                    })
                });
            });
        </script>
    @endpush
</x-toko-layout>
