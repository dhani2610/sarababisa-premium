@section('title')
    Item Produk
@endsection

<x-toko-layout>
    <style>
        .dataTables_wrapper .dataTables_length {
            float: left;
            margin-left: 3%;
        }
        div.dataTables_wrapper div.dataTables_length select {
            width: 41%!important;
            display: inline-block;
        }
        div.dataTables_wrapper div.dataTables_filter {
            text-align: right;
            margin-right: 2%!important;
        }
    </style>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <livewire:produk-sparepart-data></livewire:produk-sparepart-data>

        <div id="modalUploadFoto" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Upload Foto Produk</h2>

                <form id="formUploadFoto" enctype="multipart/form-data">
                    <input type="hidden" id="upload_product_id" name="id">

                    <div class="mb-4">
                        <div id="imagePreviewContainer" class="mb-3 hidden text-center bg-gray-50 p-2 rounded border border-dashed border-gray-300">
                             <label class="block text-xs font-bold text-gray-500 mb-2 uppercase tracking-wide">Preview Foto</label>
                             <img id="imagePreview" src="" alt="Preview Gambar" class="max-h-48 mx-auto object-contain rounded shadow-sm">
                        </div>

                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih File Baru</label>
                        <input type="file" id="inputFoto" name="foto"
                               class="block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-full file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100"
                               accept=".jpg,.jpeg,.png,.webp" />
                        <p class="text-xs text-gray-400 mt-1">Format: JPG, PNG, WEBP. Maksimal 1MB.</p>
                    </div>

                    <div class="flex justify-end space-x-2 pt-2 border-t">
                        <button type="button" onclick="closeFotoModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition-colors">Batal</button>
                        <button type="submit" id="btnSimpanFoto" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md flex items-center transition-colors">
                            <span id="btnText">Simpan</span>
                            <svg id="btnLoading" class="hidden animate-spin ml-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js" integrity="sha256-JlqSTELeR4TLqP0OG9dxM7yDPqX1ox/HfgiSLBj8+kM="
            crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script>
        var dropifyDr; // Variabel global untuk menyimpan instance Dropify

        $(document).ready(function() {
            // 1. Inisialisasi Dropify Sekali Saja di Awal
            var drEvent = $('.dropify').dropify({
                messages: {
                    'default': 'Drag & drop foto di sini atau klik',
                    'replace': 'Drag & drop atau klik untuk mengganti',
                    'remove':  'Hapus',
                    'error':   'Ooops, ada kesalahan.'
                }
            });

            // Simpan instance dropify ke variabel global
            dropifyDr = drEvent.data('dropify');

            // 2. Inisialisasi DataTable
            var table = $('#produk-table').DataTable({
                processing: false,
                serverSide: false,
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    {
                        data: null,
                        sortable: false,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            // meta.row adalah index baris internal (mulai dari 0)
                            return meta.row + 1;
                        }
                    },
                    { data: 'nama_produk', name: 'product_name' },
                    { data: 'product_code', name: 'product_code' },
                    { data: 'model.name', name: 'model.name' },
                    { data: 'stok', name: 'stok' },
                    @if (Auth::user()->role == 'Kepala Toko' || $toko->is_modal_produk === 1)
                    { data: 'harga_modal', name: 'harga_modal' },
                    @endif
                    { data: 'harga_jual_toko', name: 'harga_jual_toko' },
                    { data: 'harga_jual', name: 'harga_jual' },
                    { data: 'keterangan', name: 'keterangan' },
                    { data: 'garansi', name: 'garansi' },
                    { data: 'is_portal', name: 'is_portal' },
                    @if (Auth::user()->role != 'Teknisi' && Auth::user()->role != 'Sales')
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                    @endif
                ],
                order: [[1, 'asc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                },
                drawCallback: function() { attachCheckboxHandlers(); }
            });


             // --- custom pagination load bertahap ---
            let batchSize = 50;
            let offset = 0;
            let loading = false;

            function loadBatch() {
                if (loading) return;
                loading = true;

                $.ajax({
                    url: '{{ route('produk-item.data') }}?cat=2&offset=' + offset + '&limit=' + batchSize,
                    success: function(response) {
                        if (response.data.length > 0) {
                            table.rows.add(response.data).draw(false); // tambahkan data batch
                            offset += batchSize;
                            loading = false;
                            setTimeout(loadBatch, 100); // lanjut batch berikutnya
                        } else {
                            console.log('Semua data sudah dimuat');
                        }
                    }
                });
            }

            // mulai load pertama
            loadBatch();

           // 1. Fungsi Buka Modal & Tampilkan Preview Lama
                window.openFotoModal = function(id, currentFotoUrl) {
                    $('#upload_product_id').val(id);
                    $('#inputFoto').val(''); // Reset input file

                    var container = $('#imagePreviewContainer');
                    var img = $('#imagePreview');
                    console.log('====================================');
                    console.log(currentFotoUrl);
                    console.log('====================================');

                    if(currentFotoUrl && currentFotoUrl.length > 10) { // Cek basic valid string
                        img.attr('src', currentFotoUrl);
                        container.removeClass('hidden');
                    } else {
                        img.attr('src', '');
                        container.addClass('hidden');
                    }

                    $('#modalUploadFoto').removeClass('hidden');
                }

                // 2. Fungsi Tutup Modal
                window.closeFotoModal = function() {
                    $('#modalUploadFoto').addClass('hidden');
                }

                // 3. Logic Preview Gambar Baru saat user pilih file
                $('#inputFoto').change(function() {
                    var file = this.files[0];
                    if (file) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            $('#imagePreview').attr('src', e.target.result);
                            $('#imagePreviewContainer').removeClass('hidden');
                        }
                        reader.readAsDataURL(file);
                    }
                });

                // 4. Submit Form via AJAX
                $('#formUploadFoto').on('submit', function(e) {
                    e.preventDefault();

                    var formData = new FormData(this);
                    formData.append('_token', '{{ csrf_token() }}');

                    $('#btnText').text('Menyimpan...');
                    $('#btnLoading').removeClass('hidden');
                    $('#btnSimpanFoto').prop('disabled', true);

                    $.ajax({
                        url: "{{ route('produk-item.upload-foto') }}",
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            alert(response.message);
                            closeFotoModal();
                            table.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            try {
                                var err = JSON.parse(xhr.responseText);
                                var msg = err.message;
                                if(err.errors) {
                                    msg = '';
                                    $.each(err.errors, function(key, value) { msg += value + "\n"; });
                                }
                                alert(msg || 'Gagal mengupload foto.');
                            } catch(e) {
                                alert('Terjadi kesalahan server.');
                            }
                        },
                        complete: function() {
                            $('#btnText').text('Simpan');
                            $('#btnLoading').addClass('hidden');
                            $('#btnSimpanFoto').prop('disabled', false);
                            window.location.reload();
                        }
                    });
                });
            // --- FUNGSI LAINNYA ---
            window.openDeleteModal = function(id) {
                window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: id }));
            }

            // Logic Bulk Action
            function attachCheckboxHandlers() {
                $('#parent-checkbox').prop('checked', false);
                toggleBulkAction();

                $('#parent-checkbox').off('click').on('click', function() {
                    var checked = $(this).is(':checked');
                    $('input.table-item').prop('checked', checked);
                    toggleBulkAction();
                });

                $('#produk-table').off('change', '.table-item').on('change', '.table-item', function() {
                    var all = $('input.table-item').length;
                    var checked = $('input.table-item:checked').length;
                    $('#parent-checkbox').prop('checked', all === checked && all > 0);
                    toggleBulkAction();
                });
            }

            function toggleBulkAction() {
                var checkedCount = $('input.table-item:checked').length;
                $('.table-items-count').text(checkedCount);
                if (checkedCount > 0) {
                    $('.table-items-action').removeClass('hidden');
                } else {
                    $('.table-items-action').addClass('hidden');
                }
            }

            window.deleteSelected = function() {
                var selectedIds = $('input.table-item:checked').map(function() { return $(this).val(); }).get();
                if (selectedIds.length === 0) return alert('Pilih data dahulu');
                if (!confirm('Yakin hapus ' + selectedIds.length + ' produk?')) return;

                $.ajax({
                    url: "{{ route('produk-item.delete-batch') }}",
                    method: 'POST',
                    data: { ids: selectedIds, _token: "{{ csrf_token() }}" },
                    success: function(res) { alert(res.message); table.ajax.reload(); $('.table-items-action').addClass('hidden'); },
                    error: function(e) { alert(e.responseJSON.message); }
                });
            };

            window.updatePortalStatus = function(action) {
                var selectedIds = $('input.table-item:checked').map(function() { return $(this).val(); }).get();
                if (selectedIds.length === 0) return alert('Pilih data dahulu');

                $.ajax({
                    url: "{{ route('produk-item.update-portal') }}",
                    method: 'POST',
                    data: { selectedIds: selectedIds, action: action, _token: "{{ csrf_token() }}" },
                    success: function(res) { alert(res.message); table.ajax.reload(); $('.table-items-action').addClass('hidden'); },
                    error: function(e) { alert('Gagal update portal'); }
                });
            };
        });

        // Script Alpine & Select2
        window.addEventListener('DOMContentLoaded', () => {
            Alpine.data('form', () => ({
                isManual: false,
            }));
        });

        $(document).ready(function() {
            $('.selectjs1').select2();
            $('.selectjs2').select2();
            $('#selectjs3').select2();
            $('#selectjs4').select2();
        });

        $(function() {
            $(document).on('change', '#brands_id', function() {
                var brands_id = $(this).val();
                $.ajax({
                    url: "{{ route('get-modelserie') }}",
                    type: "GET",
                    data: { brands_id: brands_id },
                    success: function(data) {
                        var html = '<option value="">Pilih Model Seri</option>';
                        $.each(data, function(key, v) {
                            html += '<option value=" ' + v.id + ' "> ' + v.name + '</option>';
                        });
                        $('#model_series_id').html(html);
                    }
                })
            });
        });
    </script>
    @endpush

</x-toko-layout>
