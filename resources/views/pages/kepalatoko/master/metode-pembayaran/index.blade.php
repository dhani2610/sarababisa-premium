@section('title')
    Master Metode Pembayaran
@endsection
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/css/dropify.min.css">
    <style>
        .dataTables_wrapper .dataTables_length select { padding-right: 30px; width: auto; }
        .dropify-wrapper .dropify-message p { font-size: 14px; }
    </style>

<x-toko-layout>

<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <div class="sm:flex sm:justify-between sm:items-center mb-5">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Metode Pembayaran</h1>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" onclick="openModalCreate()">
                <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16"><path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" /></svg>
                <span class="hidden xs:block ml-2">Tambah Metode</span>
            </button>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8">
        <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
            <h2 class="font-semibold text-slate-800">Daftar Metode</h2>
            <div class="table-items-action hidden">
                <div class="flex items-center">
                    <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count">0</span> item dipilih</div>
                    <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" onclick="deleteSelected()">Hapus Masal</button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto p-4">
            <table id="metode-table" class="table-auto w-full">
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="flex items-center">
                                <label class="inline-flex">
                                    <span class="sr-only">Select all</span>
                                    <input id="parent-checkbox" class="form-checkbox" type="checkbox" />
                                </label>
                            </div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">ID</th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Nama</th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Foto</th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Tipe</th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200"></tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-metode" class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 hidden transition-opacity" aria-modal="true" role="dialog">
    <div class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center px-4 sm:px-6">
        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">
            <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                <div class="font-semibold text-slate-800" id="modal-title">Tambah Metode</div>
                <button class="text-slate-400 hover:text-slate-500" onclick="closeModal()">&times;</button>
            </div>

            <form id="form-metode" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="metode_id" name="id">
                <div class="px-5 py-4 space-y-4">

                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Metode <span class="text-rose-500">*</span></label>
                        <input id="nama" name="nama" class="form-input w-full px-2 py-1" type="text" required />
                    </div>

                    <div class="flex items-center mt-3">
                        <input id="is_payment_gateway" name="is_payment_gateway" type="checkbox" class="form-checkbox h-5 w-5 text-indigo-600">
                        <label for="is_payment_gateway" class="ml-2 text-sm text-slate-700">Set sebagai Payment Gateway?</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Icon / Logo <span class="text-rose-500">*</span></label>
                        <input type="file" id="foto" name="foto" class="dropify" data-height="100" />
                        <span class="text-xs text-slate-400">Allowed: jpg, png, svg. Max: 2MB</span>
                    </div>

                </div>
                <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                    <button type="button" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white" id="btn-save">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Dropify/0.2.2/js/dropify.min.js"></script>

<script>
    var table;
    var drEvent;

    $(document).ready(function() {
        // Init Dropify
        drEvent = $('.dropify').dropify();

        // Init DataTable
        table = $('#metode-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('metode-pembayaran.data') }}",
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                { data: 'id', name: 'id' },
                { data: 'nama', name: 'nama' },
                { data: 'foto', name: 'foto', orderable: false, searchable: false },
                { data: 'is_payment_gateway', name: 'is_payment_gateway' },
                { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
            ],
            order: [[1, 'desc']],
            drawCallback: function() { attachCheckboxHandlers(); }
        });

        // Submit Form (AJAX)
        $('#form-metode').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var id = $('#metode_id').val();
            var url = id ? "/master/metode-pembayaran/" + id : "{{ route('metode-pembayaran.store') }}";

            // Jika update, Laravel butuh method spoofing untuk FormData
            if(id) {
                formData.append('_method', 'PUT');
                url = "{{ url('master/metode-pembayaran') }}/" + id;
            }

            $('#btn-save').text('Menyimpan...').attr('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    alert(response.message);
                    closeModal();
                    table.ajax.reload();
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';
                    $.each(errors, function(key, value) { errorMessage += value[0] + '\n'; });
                    alert(errorMessage ? errorMessage : 'Terjadi kesalahan sistem.');
                },
                complete: function() {
                    $('#btn-save').text('Simpan').attr('disabled', false);
                }
            });
        });
    });

    // --- Modal Functions ---
    function openModalCreate() {
        $('#form-metode')[0].reset();
        $('#metode_id').val('');
        $('#modal-title').text('Tambah Metode Pembayaran');
        $('#modal-metode').removeClass('hidden');

        // Reset Dropify
        var dr = drEvent.data('dropify');
        dr.resetPreview();
        dr.clearElement();
    }

    function closeModal() {
        $('#modal-metode').addClass('hidden');
    }

    // --- Edit Data ---
    window.editData = function(id) {
        $.get("{{ url('master/metode-pembayaran') }}/" + id + "/edit", function(data) {
            $('#metode_id').val(data.id);
            $('#nama').val(data.nama);
            $('#is_payment_gateway').prop('checked', data.is_payment_gateway == 1);

            // Set Dropify Image Preview
            var dr = drEvent.data('dropify');
            dr.resetPreview();
            dr.clearElement();
            dr.settings.defaultFile = data.foto_url;
            dr.destroy();
            dr.init();

            $('#modal-title').text('Edit Metode Pembayaran');
            $('#modal-metode').removeClass('hidden');
        });
    }

    // --- Delete Single ---
    window.deleteData = function(id) {
        if(!confirm('Yakin ingin menghapus data ini?')) return;
        $.ajax({
            url: "{{ url('master/metode-pembayaran') }}/" + id,
            type: "POST",
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function(res) {
                alert(res.message);
                table.ajax.reload();
            },
            error: function() { alert('Gagal menghapus data.'); }
        });
    }

    // --- Bulk Delete Logic (Sama seperti MasterWarna) ---
    function attachCheckboxHandlers() {
        $('#parent-checkbox').prop('checked', false);
        toggleBulkAction();

        $('#parent-checkbox').off('click').on('click', function() {
            var checked = $(this).is(':checked');
            $('input.table-item').prop('checked', checked);
            toggleBulkAction();
        });

        $('#metode-table').off('change', '.table-item').on('change', '.table-item', function() {
            var all = $('input.table-item').length;
            var checked = $('input.table-item:checked').length;
            $('#parent-checkbox').prop('checked', all === checked && all > 0);
            toggleBulkAction();
        });
    }

    function toggleBulkAction() {
        var checkedCount = $('input.table-item:checked').length;
        $('.table-items-count').text(checkedCount);
        if (checkedCount > 0) $('.table-items-action').removeClass('hidden');
        else $('.table-items-action').addClass('hidden');
    }

    window.deleteSelected = function() {
        var selectedIds = $('input.table-item:checked').map(function() { return $(this).val(); }).get();
        if (selectedIds.length === 0) return alert('Pilih data terlebih dahulu.');
        if (!confirm('Yakin ingin menghapus ' + selectedIds.length + ' data ini?')) return;

        $.ajax({
            url: "{{ route('metode-pembayaran.delete-batch') }}",
            method: 'POST',
            data: { ids: selectedIds, _token: "{{ csrf_token() }}" },
            success: function(response) {
                alert(response.message);
                table.ajax.reload();
                $('.table-items-action').addClass('hidden');
            },
            error: function() { alert('Gagal menghapus data masal.'); }
        });
    };
</script>
</x-toko-layout>

