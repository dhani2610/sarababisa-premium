<div>
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Merek ✨</h1>
        </div>

        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            <div x-data="{ modalOpen: false }">
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Merek</span>
                </button>

                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:leave="transition ease-in duration-100" x-cloak></div>

                <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false">
                        <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Tambah Merek</div>
                            <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">&times;</button>
                        </div>
                        <form action="{{ route('master-merek.store') }}" method="post">
                            @csrf
                            <div class="px-5 py-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="name">Nama Merek <span class="text-rose-500">*</span></label>
                                        <input id="name" name="name" class="form-input w-full px-2 py-1" type="text" required />
                                    </div>
                                </div>
                            </div>
                            <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                <button type="button" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sm:flex sm:justify-between sm:items-center mb-5">
        <div class="mb-0"></div> <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('merek-export') }}">
                <button class="btn bg-white border-blue-200 hover:border-blue-300 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-export" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2563eb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M11.5 21h-4.5a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v5m-5 6h7m-3 -3l3 3l-3 3" />
                    </svg>
                    <span class="hidden xs:block ml-2">Ekspor Data</span>
                </button>
            </a>

            <div x-data="{ modalOpen: false }">
                <button class="btn bg-white border-emerald-200 hover:border-emerald-300 text-emerald-700" @click.prevent="modalOpen = true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-import" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="#047857" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4" /><path d="M5 13v-8a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5.5m-9.5 -2h7m-3 -3l3 3l-3 3" />
                    </svg>
                    <span class="hidden xs:block ml-2">Impor Data</span>
                </button>
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-cloak></div>
                <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full" @click.outside="modalOpen = false">
                        <form id="form-import-merek">
                            <div class="px-5 py-3 border-b border-slate-200 flex justify-between">
                                <div class="font-semibold text-slate-800">Impor Data Merek (Chunk)</div>
                                <button type="button" class="text-slate-400" @click="modalOpen = false">&times;</button>
                            </div>
                            <div class="px-5 pt-4 pb-4 space-y-2">
                                <p class="text-sm">Silahkan download format, isi, dan upload.</p>
                                <input type="file" id="file_import" class="btn-sm bg-slate-100 w-full" accept=".xlsx, .xls" required>

                                <div id="progress-container" class="hidden mt-4">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm font-medium text-indigo-700">Mengupload...</span>
                                        <span class="text-sm font-medium text-indigo-700" id="progress-text">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                                        <div class="bg-indigo-600 h-2.5 rounded-full" id="progress-bar" style="width: 0%"></div>
                                    </div>
                                    <p id="status-text" class="text-xs text-slate-500 mt-1"></p>
                                </div>
                            </div>
                            <div class="px-5 py-4 border-t flex justify-end space-x-2">
                                <a href="{{ asset('storage/assets/format_merek.xlsx') }}" class="btn-sm bg-orange-500 text-white">Download Format</a>
                                <button type="submit" id="btn-upload" class="btn-sm bg-indigo-500 text-white">Upload File</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-4 px-4 py-2 rounded-sm text-sm bg-rose-500 text-white">
            <div class="flex w-full justify-between items-start">
                <div class="flex">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">

        <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
            <h2 class="font-semibold text-slate-800">Semua Merek <span class="text-slate-400 font-medium">{{ $brands_count }}</span></h2>

            <div class="relative inline-flex">
                <div class="table-items-action hidden">
                    <div class="flex items-center">
                        <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count">0</span> item yang dipilih</div>
                        <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" onclick="deleteSelected()">Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto p-4">
            <table id="merek-table" class="table-auto w-full">
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
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">No Id.</th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Nama Merek</th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                    </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_wrapper .dataTables_length select {
            padding-right: 30px;
            width: auto;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#merek-table').DataTable({
                processing: false,
                serverSide: false,
                ajax: "{{ route('master-merek.data') }}",
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },

                    // Kolom No. Urut (Client Side Calculation) agar tetap urut meski beda page
                    // {
                    //     data: null,
                    //     sortable: false,
                    //     orderable: false,
                    //     searchable: false,
                    //     render: function (data, type, row, meta) {
                    //         return meta.row + meta.settings._iDisplayStart + 1;
                    //     }
                    // },

                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                ],
                order: [[1, 'asc']], // Default urut berdasarkan Nama (index ke-2)
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                },
                drawCallback: function() {
                    attachCheckboxHandlers();
                }
            });

            // Logic Checkbox & Bulk Delete
            function attachCheckboxHandlers() {
                $('#parent-checkbox').prop('checked', false);
                toggleBulkAction();

                $('#parent-checkbox').off('click').on('click', function() {
                    var checked = $(this).is(':checked');
                    $('input.table-item').prop('checked', checked);
                    toggleBulkAction();
                });

                $('#merek-table').off('change', '.table-item').on('change', '.table-item', function() {
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

            // Fungsi Bulk Delete
            window.deleteSelected = function() {
                var selectedIds = $('input.table-item:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) return alert('Pilih data terlebih dahulu.');
                if (!confirm('Yakin ingin menghapus ' + selectedIds.length + ' data ini?')) return;

                $.ajax({
                    url: "{{ route('master-merek.delete-batch') }}",
                    method: 'POST',
                    data: {
                        ids: selectedIds,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        alert(response.message);
                        table.ajax.reload();
                        $('.table-items-action').addClass('hidden');
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus data.';
                        alert(msg);
                    }
                });
            };
        });
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <script>
        $(document).ready(function() {
            // --- LOGIKA IMPORT CHUNK ---
            $('#form-import-merek').on('submit', function(e) {
                e.preventDefault();

                var fileInput = document.getElementById('file_import');
                if (fileInput.files.length === 0) {
                    alert('Pilih file terlebih dahulu!');
                    return;
                }

                var file = fileInput.files[0];
                var reader = new FileReader();

                // Tampilkan Progress UI
                $('#progress-container').removeClass('hidden');
                $('#progress-bar').css('width', '0%');
                $('#progress-text').text('0%');
                $('#status-text').text('Membaca file...');
                $('#btn-upload').prop('disabled', true).text('Memproses...');

                reader.onload = function(e) {
                    var data = new Uint8Array(e.target.result);
                    var workbook = XLSX.read(data, { type: 'array' });
                    var firstSheetName = workbook.SheetNames[0];
                    var worksheet = workbook.Sheets[firstSheetName];

                    // Convert Excel ke JSON
                    var jsonData = XLSX.utils.sheet_to_json(worksheet);

                    if (jsonData.length === 0) {
                        alert('File kosong atau format salah!');
                        resetUploadUI();
                        return;
                    }

                    // Proses Chunking
                    uploadChunks(jsonData);
                };

                reader.readAsArrayBuffer(file);
            });

            async function uploadChunks(data) {
                const chunkSize = 200; // Jumlah baris per request
                const totalChunks = Math.ceil(data.length / chunkSize);
                let totalInserted = 0;
                let totalSkipped = 0;

                for (let i = 0; i < totalChunks; i++) {
                    const start = i * chunkSize;
                    const end = start + chunkSize;
                    const chunk = data.slice(start, end);

                    try {
                        const response = await $.ajax({
                            url: "{{ route('master-merek.import-chunk') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                rows: chunk
                            }
                        });

                        if (response.status === 'success') {
                            totalInserted += response.inserted;
                            totalSkipped += response.skipped;
                        }

                        // Update Progress Bar
                        const percent = Math.round(((i + 1) / totalChunks) * 100);
                        $('#progress-bar').css('width', percent + '%');
                        $('#progress-text').text(percent + '%');
                        $('#status-text').text(`Memproses data ke ${Math.min(end, data.length)} dari ${data.length}...`);

                    } catch (error) {
                        console.error(error);
                        alert('Terjadi kesalahan saat upload chunk ke-' + (i + 1));
                        resetUploadUI();
                        return; // Stop loop jika error
                    }
                }

                // Selesai
                $('#status-text').text('Selesai! ' + totalInserted + ' data masuk, ' + totalSkipped + ' dilewati (duplikat).');
                setTimeout(() => {
                    alert('Import Selesai!\nData Masuk: ' + totalInserted + '\nDuplikat (Skip): ' + totalSkipped);
                    resetUploadUI();
                    $('#file_import').val(''); // Reset input file
                    table.ajax.reload(); // Reload DataTable
                    // Tutup modal secara manual (karena pake x-data alpine, kita trigger click tombol close atau reload page)
                    // window.location.reload();
                }, 500);
            }

            function resetUploadUI() {
                $('#progress-container').addClass('hidden');
                $('#btn-upload').prop('disabled', false).text('Upload File');
            }
        });
    </script>
@endpush
</div>
