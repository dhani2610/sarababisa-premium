@section('title')
    Transaksi Servis Sudah Diambil
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Table -->
        <livewire:sudah-diambil-data></livewire:sudah-diambil-data>

    </div>

    @push('styles')
        <style>
            div.dataTables_wrapper div.dataTables_length select {
                width: 47%!important;
                display: inline-block;
            }
            .dataTables_wrapper .dataTables_length {
                float: left;
                padding-left: 2%;
            }
        </style>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM=" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>



        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>


        <script>
            $(document).ready(function() {

                var table = $('#transaksi-servis-table').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: '{{ route('transaksi-servis-sudah-diambil.data') }}',
                    columns: [
                        @if (Auth::user()->role != 'Investor')
                            {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false},
                        @endif
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                        {data: 'nomor_servis', name: 'nomor_servis'},
                        {data: 'created_at', name: 'created_at'},
                        {data: 'penerima', name: 'penerima'},
                        {data: 'pelanggan', name: 'pelanggan'},
                        @if (Auth::user()->role != 'Investor')
                            {data: 'hubungi', name: 'hubungi', orderable: false, searchable: false},
                        @endif
                        {data: 'nama_barang', name: 'nama_barang'},
                        {data: 'kerusakan', name: 'kerusakan'},
                        {data: 'qc_masuk', name: 'qc_masuk'},
                        {data: 'qc_keluar', name: 'qc_keluar'},
                        {data: 'kondisi_servis', name: 'kondisi_servis'},
                        {data: 'tindakan_servis', name: 'tindakan_servis'},
                        {data: 'teknisi', name: 'teknisi'},
                        @if (Auth::user()->role != 'Investor')
                            {data: 'modal_sparepart', name: 'modal_sparepart'},
                        @endif
                        {data: 'biaya', name: 'biaya'},
                        {data: 'diskon', name: 'diskon'},
                        {data: 'cara_pembayaran', name: 'cara_pembayaran'},
                        {data: 'tgl_ambil', name: 'tgl_ambil'},
                        {data: 'pengambil', name: 'pengambil'},
                        {data: 'penyerah', name: 'penyerah'},
                        {data: 'exp_garansi', name: 'exp_garansi'},
                        @if (Auth::user()->role != 'Investor')
                            {data: 'status', name: 'status', orderable: false, searchable: false},
                            {data: 'aksi', name: 'aksi', orderable: false, searchable: false},
                        @endif
                    ],
                    order: [],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                    },
                    drawCallback: function() {
                        attachCheckboxHandlers();
                    }
                });

                // --- custom pagination load bertahap ---
                let batchSize = 100;
                let offset = 0;
                let loading = false;

                function loadBatch() {
                    if (loading) return;
                    loading = true;
                    $.ajax({
                        url: '{{ route('transaksi-servis-sudah-diambil.data') }}?offset=' + offset + '&limit=' + batchSize,
                        success: function(response) {
                            if (response.data.length > 0) {
                                table.rows.add(response.data).draw(false);
                                offset += batchSize;
                                loading = false;
                                // lanjut load batch berikutnya
                                setTimeout(loadBatch, 100);
                            } else {
                                console.log('semua data sudah dimuat');
                            }
                        }
                    });
                }

                // mulai load pertama
                loadBatch();

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
                function kirimFontee(token, phone, message) {
                    fetch('https://api.fonnte.com/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': token
                        },
                        body: JSON.stringify({
                            target: phone,
                            message: decodeURIComponent(message)
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === true || data.success) {
                            alert('✅ Pesan berhasil dikirim ke Pelanggan!');
                        } else {
                            alert('⚠️ Gagal mengirim ke Pelanggan. Coba lagi.');
                        }
                    })
                    .catch(() => alert('❌ Terjadi kesalahan saat mengirim ke Fontee.'));
                }

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

        <script type="text/javascript">
            $(document).ready(function() {
                $('#selectjs1').select2();
                $('.selectjs2').select2();
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
    @endpush
</x-toko-layout>
