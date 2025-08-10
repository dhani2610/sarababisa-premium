@section('title')
    Transaksi Servis
@endsection

<x-teknisi-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
     
        <!-- Table -->

        <livewire:teknisi-proses-data></livewire:teknisi-proses-data>

    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $('#selectjs1').select2();
                $('.selectjs2').select2();
                $('#selectjs3').select2();
                $('.selectjs4').select2();
                $('#selectjs5').select2();
                $('#selectjs6').select2();
            });
        </script>
        <script type="text/javascript">
            $(function(){
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
        <script type="text/javascript">
            $(function(){
                $(document).on('change','#merek',function(){
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
                            $('#model').html(html);
                        }
                    })
                });
            });
        </script>
        <script>
            $(document).on('change', '.pilih-tindakan select', function() {
                var serviceActionId = $(this).val();
                const myEl = $(this)
                if (serviceActionId) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-action/' + serviceActionId,
                        dataType: 'json',
                        success: function(data) {
                            const curBiaya = $('#biaya').val() || 0;
                            const curModal = $('#total_modal_sparepart').val() || 0;
                            const prevModal = myEl.parent().parent().parent().find(
                                '[name="prev_modal"]:first');
                            const prevBiaya = myEl.parent().parent().parent().find(
                                '[name="prev_biaya"]:first');

                            $('#biaya').val((parseInt(curBiaya) - parseInt(prevBiaya.val()) + parseInt(data
                                .biaya)).toString());
                            $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal
                                    .val()) + parseInt(data
                                    .modal_sparepart))
                                .toString());
                            prevModal.val(data.modal_sparepart)
                            prevBiaya.val(data.biaya)
                            myEl.parent().parent().parent().find('[name="modal_sparepart"]:first').val(data
                                .modal_sparepart)
                            myEl.parent().parent().parent().find('[name="biaya_servis"]:first').val(data
                                .biaya)
                        }
                    });
                } else {
                    $('#biaya').val('');
                    $('#modal_sparepart').val('');
                }
            });

            $(document).on('change', '.modal_sparepart', function(e) {
                const myEl = $(this);
                const curModal = $('#total_modal_sparepart').val() || 0;
                const prevModal = myEl.parent().parent().parent().find('[name="prev_modal"]:first');
                $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal.val()) + parseInt(myEl.val()))
                    .toString());
                prevModal.val(myEl.val())
            })

            $(document).on('change', '.biaya_servis', function(e) {
                const myEl = $(this);
                const curModal = $('#biaya').val() || 0;
                const prevModal = myEl.parent().parent().parent().find('[name="prev_biaya"]:first');
                $('#biaya').val((parseInt(curModal) - parseInt(prevModal.val()) + parseInt(myEl.val()))
                    .toString());
                prevModal.val(myEl.val())
            })

            $(document).on('change', '.selectAction2', function() {
                var productId = $(this).val();
                if (productId) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-sparepart/' + productId,
                        dataType: 'json',
                        success: function(data) {
                            const prev = $('#modal_sparepart').val() || 0;
                            $('#modal_sparepart').val((parseInt(prev) + parseInt(data.modal_sparepart))
                                .toString());
                        }
                    });
                } else {
                    $('#modal_sparepart').val('');
                }
            });

            $(document).ready(function() {

                let pilihTindakanEl = `<div x-data="{ showInputManual: false }" class="tindakan-servis">
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-sm font-medium">
                        Tindakan Servis
                        <span class="text-rose-500">*</span>
                    </label>
                    <label class="flex items-center">
                        <input type="checkbox" class="form-checkbox"
                            x-on:click="showInputManual = true" />
                        <span class="text-sm ml-2">Isi Manual</span>
                    </label>
                </div>
                <div class="flex flex-col pilih-tindakan">
                    <select class="selectAction" name="service_actions_id[]"
                        class="form-select text-sm py-1 w-full">
                        <option selected value="">Pilih Tindakan</option>
                        @foreach (App\Models\ServiceAction::all() as $action)
                            <option value="{{ $action->id }}">
                                {{ $action->nama_tindakan }}</option>
                        @endforeach
                    </select>
                </div>
                <div x-show="showInputManual" class="mt-2">
                    <input class="form-input w-full px-2 py-1" type="text"
                        name="tindakan_servis[]" />
                </div>
                <input type="hidden" name="prev_modal" value="0">
                <input type="hidden" name="prev_biaya" value="0">
            </div>`
                let konfirSparepartEl = `<div x-data="{ showDetails: false }" class="konfirmasi-stok border-b-2 pb-4">
                    <label class="block text-sm font-medium mb-1" for="modal_sparepart">Apakah
                        menggunakan stok sparepart toko?</label>
                    <div class="flex flex-wrap items-center -m-3">
                        <div class="m-3">
                            <!-- Start -->
                            <label class="flex items-center">
                                <input type="radio" name="radio-buttons" class="form-radio"
                                    checked x-on:click="showDetails = false" />
                                <span class="text-sm ml-2">Tidak</span>
                            </label>
                            <!-- End -->
                        </div>
                        <div class="m-3">
                            <!-- Start -->
                            <label class="flex items-center">
                                <input type="radio" name="radio-buttons" class="form-radio"
                                    x-on:click="showDetails = true" />
                                <span class="text-sm ml-2">Ya</span>
                            </label>
                            <!-- End -->
                        </div>
                    </div>
                    <div x-show="showDetails" class="mt-3">
                        <label class="block text-sm font-medium mb-1"
                            for="products_id">Sparepart Toko yg Digunakan</label>
                        <select class="selectAction2" name="products_id[]"
                            class="form-select text-sm py-1 w-full" style="width: 100%;">
                            <option selected value="">Pilih Sparepart</option>
                            @foreach (App\Models\Product::where('stok', '>', 0)->get() as $item)
                                <option value="{{ $item->id }}">{{ $item->product_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="showDetails" class="mt-3">
                        <label class="block text-sm font-medium mb-1" for="sales_id">Sales
                            Sparepart</label>
                        <select id="sales_id" name="sales_id[]"
                            class="form-select text-sm py-1 w-full">
                            <option selected value="1">Tidak ada Sales</option>
                            @foreach (App\Models\User::where('role', 'Sales')->get() as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1"
                            for="garansi">Garansi</label>
                        <select name="garansi[]" class="form-select text-sm py-1 w-full">
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
                    <div class="mt-3">
                        <label class="block text-sm font-medium mb-1"
                            for="modal_sparepart">Modal Sparepart <span
                                class="text-rose-500">*</span></label>
                        <input class="form-input modal_sparepart w-full px-2 py-1" type="number" value="0"
                            name="modal_sparepart[]" required />
                    </div>
                    <div class="mt-3">
                        <label class="block text-sm font-medium mb-1" for="biaya_servis">Biaya
                            Servis <span class="text-rose-500">*</span></label>
                        <input class="form-input w-full px-2 py-1 biaya_servis" type="number"
                            name="biaya_servis[]" required />
                    </div>
                </div>`

                $(document).on('change', '.pilih-tindakan select', function() {
                var serviceActionId = $(this).val();
                const myEl = $(this)
                if (serviceActionId) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-action/' + serviceActionId,
                        dataType: 'json',
                        success: function(data) {
                            const curBiaya = $('#biaya').val() || 0;
                            const curModal = $('#total_modal_sparepart').val() || 0;
                            const prevModal = myEl.parent().parent().parent().find(
                                '[name="prev_modal"]:first');
                            const prevBiaya = myEl.parent().parent().parent().find(
                                '[name="prev_biaya"]:first');

                            $('#biaya').val((parseInt(curBiaya) - parseInt(prevBiaya.val()) + parseInt(data
                                .biaya)).toString());
                            $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal
                                    .val()) + parseInt(data
                                    .modal_sparepart))
                                .toString());
                            prevModal.val(data.modal_sparepart)
                            prevBiaya.val(data.biaya)
                            myEl.parent().parent().parent().find('[name="modal_sparepart[]"]:first').val(
                                data
                                .modal_sparepart)
                            myEl.parent().parent().parent().find('[name="biaya_servis[]"]:first').val(data
                                .biaya)
                        }
                    });
                } else {
                    $('#biaya').val('');
                    $('#modal_sparepart').val('');
                }
            });

            $(document).on('change', '.modal_sparepart', function(e) {
                const myEl = $(this);
                const curModal = $('#total_modal_sparepart').val() || 0;
                const prevModal = myEl.parent().parent().parent().find('[name="prev_modal"]:first');
                $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal.val()) + parseInt(myEl.val()))
                    .toString());
                prevModal.val(myEl.val())
            })

            $(document).on('change', '.biaya_servis', function(e) {
                const myEl = $(this);
                const curModal = $('#biaya').val() || 0;
                const prevModal = myEl.parent().parent().parent().find('[name="prev_biaya"]:first');
                $('#biaya').val((parseInt(curModal) - parseInt(prevModal.val()) + parseInt(myEl.val()))
                    .toString());
                prevModal.val(myEl.val())
            })

            $(document).on('change', '.selectAction2', function() {
                var productId = $(this).val();
                if (productId) {
                    $.ajax({
                        type: 'GET',
                        url: '/get-sparepart/' + productId,
                        dataType: 'json',
                        success: function(data) {
                            const prev = $('#modal_sparepart').val() || 0;
                            $('#modal_sparepart').val((parseInt(prev) + parseInt(data.modal_sparepart))
                                .toString());
                        }
                    });
                } else {
                    $('#modal_sparepart').val('');
                }
            });

            function getRandomName() {
                return 'radio_' + Math.random().toString(36).substr(2, 9);
            }

            $('#tambah-servis').click(function() {
                const parent = $("<div></div>")
                $(pilihTindakanEl).appendTo(parent)
                const cloned = $(konfirSparepartEl)
                let newName = getRandomName();

                cloned.find('input[type="radio"]').each(function() {
                    $(this).attr('name', newName);
                });
                cloned.appendTo(parent)
                parent.appendTo('#servis-lain')
                $('.selectAction').select2();
                $('.selectAction2').select2();
            })
            });
        </script>
    @endpush
</x-teknisi-layout>
