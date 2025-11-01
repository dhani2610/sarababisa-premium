@section('title')
    Transaksi Servis
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Table -->
        <livewire:proses-data></livewire:proses-data>

    </div>

    @push('styles')
    <style>
        .dataTables_wrapper .dataTables_length {
            float: left;
            padding-left: 2%;
        }
    </style>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/patternlock@2.0.2/dist/patternlock.min.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM="
            crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/patternlock@2.0.2/dist/patternlock.min.js"></script>
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                Alpine.data('form', () => ({
                    isManual: false,
                }));
            });
        </script>

        {{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>


        <script>
            $(document).ready(function() {

                var table = $('#transaksi-servis-table').DataTable({
                    processing: false,
                    serverSide: false,
                    ajax: '{{ route('transaksi-servis.data') }}',
                    columns: [
                        @if (Auth::user()->role == 'Kepala Toko')
                            {
                                data: 'checkbox',
                                name: 'checkbox',
                                orderable: false,
                                searchable: false
                            },
                        @endif {
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'nomor_servis',
                            name: 'nomor_servis'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'penerima',
                            name: 'penerima'
                        },
                        {
                            data: 'pelanggan',
                            name: 'pelanggan',
                            orderable: false,
                            searchable: true
                        },
                        {
                            data: 'hubungi',
                            name: 'hubungi',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'nama_barang',
                            name: 'nama_barang'
                        },
                        {
                            data: 'kelengkapan',
                            name: 'kelengkapan'
                        },
                        {
                            data: 'kerusakan',
                            name: 'kerusakan'
                        },
                        {
                            data: 'qc_masuk',
                            name: 'qc_masuk'
                        },
                        {
                            data: 'uang_muka',
                            name: 'uang_muka'
                        },
                        {
                            data: 'estimasi_biaya',
                            name: 'estimasi_biaya'
                        },
                        {
                            data: 'estimasi_pengerjaan',
                            name: 'estimasi_pengerjaan'
                        },
                        @if (Auth::user()->role != 'Investor')
                            {
                                data: 'status',
                                name: 'status',
                                orderable: false,
                                searchable: true
                            }, {
                                data: 'aksi',
                                name: 'aksi',
                                orderable: false,
                                searchable: false
                            }
                        @endif
                    ],
                    order: [
                        [3, 'desc']
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                    },
                    drawCallback: function() {
                        attachCheckboxHandlers();
                    }
                });

                function attachCheckboxHandlers() {
                    $('#parent-checkbox').off('click').on('click', function() {
                        var checked = $(this).is(':checked');
                        $('input.table-item').prop('checked', checked).trigger('change');
                        toggleBulkAction();
                    });

                    // Un/Check parent when any row checkbox change
                    $('#transaksi-servis-table').off('change', '.table-item').on('change', '.table-item', function() {
                        var all = $('input.table-item').length;
                        var checked = $('input.table-item:checked').length;
                        $('#parent-checkbox').prop('checked', all === checked && all > 0);
                        toggleBulkAction();
                    });

                    toggleBulkAction();
                }

                // Toggle visibility bulk action area
                function toggleBulkAction() {
                    var checkedCount = $('input.table-item:checked').length;
                    $('.table-items-count').text(checkedCount);
                    if (checkedCount > 0) {
                        $('.table-items-action').removeClass('hidden');
                    } else {
                        $('.table-items-action').addClass('hidden');
                    }
                }

                // Bulk action functions (DELETE / APPROVE / REJECT)
                window.deleteSelected = function() {
                    var selectedIds = $('input.table-item:checked').map(function() {
                        return $(this).val();
                    }).get();
                    if (selectedIds.length === 0) return alert('Pilih item terlebih dahulu.');
                    if (!confirm('Yakin hapus data yang dipilih?')) return;
                    fetch('{{ url('/product-transactions/delete') }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            selectedIds: selectedIds
                        })
                    }).then(res => res.json()).then(data => {
                        alert(data.message || 'Sukses dihapus');
                        table.ajax.reload(null, false);
                    }).catch(err => {
                        console.error(err);
                        alert('Gagal menghapus.');
                    });
                };

                window.approveSelected = function() {
                    var selectedIds = $('input.table-item:checked').map(function() {
                        return $(this).val();
                    }).get();
                    if (selectedIds.length === 0) return alert('Pilih item terlebih dahulu.');
                    fetch('{{ url('/product-transactions/update') }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            selectedIds: selectedIds
                        })
                    }).then(res => res.json()).then(data => {
                        alert(data.message || 'Sukses diperbarui');
                        table.ajax.reload(null, false);
                    }).catch(err => {
                        console.error(err);
                        alert('Gagal memperbarui.');
                    });
                };

                window.rejectSelected = function() {
                    var selectedIds = $('input.table-item:checked').map(function() {
                        return $(this).val();
                    }).get();
                    if (selectedIds.length === 0) return alert('Pilih item terlebih dahulu.');
                    fetch('{{ url('/product-transactions/reject') }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            selectedIds: selectedIds
                        })
                    }).then(res => res.json()).then(data => {
                        alert(data.message || 'Sukses diperbarui');
                        table.ajax.reload(null, false);
                    }).catch(err => {
                        console.error(err);
                        alert('Gagal memperbarui.');
                    });
                };

                // fungsi kirimFontee (dipanggil dari kolom hubungi bila ada token)
                window.kirimFontee = function(token, number, message) {
                    // implementasi sederhana: kirim POST ke endpoint fontee di backend (kamu bisa ubah endpointnya)
                    fetch('{{ url('/send-fontee') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            token: token,
                            number: number,
                            message: message
                        })
                    }).then(res => res.json()).then(data => {
                        alert(data.message || 'Pesan terkirim via Fontee');
                    }).catch(err => {
                        console.error(err);
                        alert('Gagal kirim Fontee');
                    });
                };

                // fungsi saveCanvasAjax & resetCanvas: placeholder, sesuaikan implementasi sesuai backend kamu
                window.saveCanvasAjax = function(id) {
                    // ambil pola dari canvas -> polaInput-id, kirim ke server
                    var polaInput = document.getElementById('polaInput-' + id);
                    var pinVal = document.getElementById('pinInput-' + id).value;
                    var polaVal = polaInput ? polaInput.value : '';
                    fetch('/save-pin-pola/' + id, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            pin: pinVal,
                            pola: polaVal
                        })
                    }).then(res => res.json()).then(data => {
                        alert(data.message || 'Tersimpan');
                        // dispatch event untuk menutup modal Livewire jika perlu
                        window.dispatchEvent(new Event('close-pin-modal-' + id));
                    }).catch(err => {
                        console.error(err);
                        alert('Gagal menyimpan.');
                    });
                };

                window.resetCanvas = function(id) {
                    // implementasi reset canvas — jika kamu pakai library signature, panggil reset library-nya
                    var canvas = document.getElementById('sig-canvas-' + id);
                    if (!canvas) return;
                    var ctx = canvas.getContext('2d');
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    var polaInput = document.getElementById('polaInput-' + id);
                    if (polaInput) polaInput.value = '';
                };
            });
        </script>
        {{-- Selectjs --}}
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
        {{-- Model Seri --}}
        <script type="text/javascript">
            $(function() {
                $(document).on('change', '#brands_id', function() {
                    var brands_id = $(this).val();
                    $.ajax({
                        url: "{{ route('get-modelserie') }}",
                        type: "GET",
                        data: {
                            brands_id: brands_id
                        },
                        success: function(data) {
                            var html = '<option value="">Pilih Model Seri</option>';
                            $.each(data, function(key, v) {
                                html += '<option value=" ' + v.id + ' "> ' + v.name +
                                    '</option>';
                            });
                            $('#model_series_id').html(html);
                        }
                    })
                });
            });
        </script>
        <script type="text/javascript">
            $(function() {
                $(document).on('change', '#merek', function() {
                    var brands_id = $(this).val();
                    $.ajax({
                        url: "{{ route('get-modelserie') }}",
                        type: "GET",
                        data: {
                            brands_id: brands_id
                        },
                        success: function(data) {
                            var html = '<option value="">Pilih Model Seri</option>';
                            $.each(data, function(key, v) {
                                html += '<option value=" ' + v.id + ' "> ' + v.name +
                                    '</option>';
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
                            // $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal
                            //         .val()) + parseInt(data
                            //         .modal_sparepart))
                            //     .toString());
                            // prevModal.val(data.modal_sparepart)
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

            // $(document).on('change', '.modal_sparepart', function(e) {
            //     const myEl = $(this);
            //     const curModal = $('#total_modal_sparepart').val() || 0;
            //     const prevModal = myEl.parent().parent().parent().find('[name="prev_modal"]:first');
            //     $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal.val()) + parseInt(myEl.val()))
            //         .toString());
            //     prevModal.val(myEl.val())
            // })

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
                    <button type="button"
                        class="hapus-servis bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1 rounded">
                        Hapus Tindakan
                    </button>
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
                                <option value="{{ $item->id }}" data-harga_modal="{{ $item->harga_modal }}">{{ $item->product_name }}
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

                                $('#biaya').val((parseInt(curBiaya) - parseInt(prevBiaya.val()) +
                                    parseInt(data
                                        .biaya)).toString());
                                $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(
                                        prevModal
                                        .val()) + parseInt(data
                                        .modal_sparepart))
                                    .toString());
                                prevModal.val(data.modal_sparepart)
                                prevBiaya.val(data.biaya)
                                myEl.parent().parent().parent().find(
                                    '[name="modal_sparepart[]"]:first').val(
                                    data
                                    .modal_sparepart)
                                myEl.parent().parent().parent().find(
                                    '[name="biaya_servis[]"]:first').val(data
                                    .biaya)
                            }
                        });
                    } else {
                        $('#biaya').val('');
                        $('#modal_sparepart').val('');
                    }
                });

                // $(document).on('change', '.modal_sparepart', function(e) {
                //     const myEl = $(this);
                //     const curModal = $('#total_modal_sparepart').val() || 0;
                //     const prevModal = myEl.parent().parent().parent().find('[name="prev_modal"]:first');
                //     $('#total_modal_sparepart').val((parseInt(curModal) - parseInt(prevModal.val()) + parseInt(myEl.val()))
                //         .toString());
                //     prevModal.val(myEl.val())
                // })

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
                                $('#modal_sparepart').val((parseInt(prev) + parseInt(data
                                        .modal_sparepart))
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
                $(document).on('click', '.hapus-servis', function() {
                    if (confirm('Yakin ingin menghapus tindakan servis ini?')) {
                        const parent = $(this).closest('.konfirmasi-stok');
                        // Hilangkan nilai biaya dari total global
                        const biayaServis = parseInt(parent.find('.biaya_servis').val()) || 0;
                        const modalSparepart = parseInt(parent.find('.modal_sparepart').val()) || 0;

                        const curBiaya = parseInt($('#biaya').val()) || 0;
                        const curModal = parseInt($('#total_modal_sparepart').val()) || 0;

                        $('#biaya').val(curBiaya - biayaServis);
                        $('#total_modal_sparepart').val(curModal - modalSparepart);

                        // Hapus elemen
                        parent.prev('.tindakan-servis').remove(); // hapus pasangan pilih tindakan
                        parent.remove(); // hapus blok sparepart
                    }
                });
            });
        </script>
    @endpush
</x-toko-layout>
