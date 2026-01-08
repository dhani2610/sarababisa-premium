@section('title', 'Rincian Invest')

    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto"
        id="main-area"
        x-data="{
            modalOpen: false,
            editModalOpen: false,
            editData: {
                id: '',
                id_investor: '',
                tanggal: '',
                tipe: '',
                nominal: '',
                keterangan: '',
                bukti_url: ''
            }
        }"
        @open-edit-modal.window="editData = $event.detail; editModalOpen = true">

        <style>
            [x-cloak] { display: none !important; }
            .dataTables_wrapper .dataTables_length select { padding-right: 30px; }
            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

            /* CSS Bukti Modal & Img */
            .bukti-img {
                width: 80px; height: 80px; border-radius: 8px; object-fit: cover; cursor: pointer; transition: transform 0.2s ease;
            }
            .bukti-img:hover { transform: scale(1.05); }
            .bukti-modal {
                display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%;
                background-color: rgba(0, 0, 0, 0.9); justify-content: center; align-items: center;
            }
            .bukti-modal-content { max-width: 90%; max-height: 90%; border-radius: 10px; box-shadow: 0 0 25px rgba(255, 255, 255, 0.4); object-fit: contain; }
            .close-btn { position: absolute; top: 15px; right: 25px; color: white; font-size: 40px; font-weight: bold; cursor: pointer; transition: color 0.2s; }
            .close-btn:hover { color: red; }
        </style>

        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Rincian Invest 📊</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">



                @if (Auth::user()->role != 'Investor')
                    <div>
                        <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                            + Tambah Rincian
                        </button>

                        <div x-show="modalOpen" x-cloak>
                            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"></div>
                            <div class="fixed inset-0 z-50 overflow-auto flex items-center justify-center p-4">
                                <div class="bg-white rounded shadow-lg w-full max-w-lg">
                                    <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                        <div class="font-semibold text-slate-800">Tambah Rincian Invest</div>
                                        <button class="text-slate-400" @click="modalOpen = false">✕</button>
                                    </div>
                                    <form action="{{ route('rincian-invest.store') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="px-5 py-4 space-y-3">
                                            @if (Auth::user()->role != 'Investor')
                                                <div>
                                                    <label class="block text-sm font-medium mb-1">Pilih Investor <span class="text-rose-500">*</span></label>
                                                    <select name="id_investor" class="form-select w-full" required>
                                                        <option value="">Pilih Investor</option>
                                                        @foreach ($investors as $investor)
                                                            <option value="{{ $investor->id }}">{{ $investor->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Tanggal <span class="text-rose-500">*</span></label>
                                                <input type="date" name="tanggal" class="form-input w-full" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Tipe <span class="text-rose-500">*</span></label>
                                                <select name="tipe" class="form-select w-full" required>
                                                    <option value="">Pilih tipe</option>
                                                    <option value="1">Masuk</option>
                                                    <option value="2">Pembagian</option>
                                                    <option value="3">Lain-lain</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Nominal <span class="text-rose-500">*</span></label>
                                                <input type="text" name="nominal" class="form-input w-full format-nominal" required>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Upload Bukti Transfer <span class="text-rose-500">*</span></label>
                                                <input type="file" name="upload_bukti_tf" class="form-input w-full" accept="image/*" required onchange="checkFileSize(this)">
                                                <p id="file_alert" class="text-red-500 text-xs mt-1 hidden">Ukuran file maksimal 1 MB!</p>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Keterangan <span class="text-rose-500">*</span></label>
                                                <textarea name="keterangan" class="form-input w-full" rows="3" required></textarea>
                                            </div>
                                        </div>
                                        <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                            <button type="button" class="btn-sm border-slate-200" @click="modalOpen = false">Batal</button>
                                            <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5">
            <div class="w-full">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mt-4 border-b border-slate-200 pb-2 px-5">
                    <div class="flex overflow-x-auto gap-2 w-full lg:w-auto pb-2 lg:pb-0 no-scrollbar">
                        <button onclick="filterTipe(0)" id="tab-0" class="tab-btn whitespace-nowrap px-4 py-2 text-sm font-semibold border-b-2 transition-all duration-150 flex-shrink-0 border-indigo-500 text-indigo-600">
                            Semua
                        </button>
                        <button onclick="filterTipe(1)" id="tab-1" class="tab-btn whitespace-nowrap px-4 py-2 text-sm font-semibold border-b-2 transition-all duration-150 flex-shrink-0 border-transparent text-slate-500 hover:text-slate-700">
                            Masuk (Rp{{ number_format($totals['masuk'], 0, ',', '.') }})
                        </button>
                        <button onclick="filterTipe(2)" id="tab-2" class="tab-btn whitespace-nowrap px-4 py-2 text-sm font-semibold border-b-2 transition-all duration-150 flex-shrink-0 border-transparent text-slate-500 hover:text-slate-700">
                            Pembagian (Rp{{ number_format($totals['pembagian'], 0, ',', '.') }})
                        </button>
                        <button onclick="filterTipe(3)" id="tab-3" class="tab-btn whitespace-nowrap px-4 py-2 text-sm font-semibold border-b-2 transition-all duration-150 flex-shrink-0 border-transparent text-slate-500 hover:text-slate-700">
                            Lain-lain (Rp{{ number_format($totals['lain'], 0, ',', '.') }})
                        </button>
                    </div>

                    @if (Auth::user()->role != 'Investor')
                        <div class="w-full lg:w-auto flex items-center gap-2">
                            <label class="text-sm font-medium whitespace-nowrap hidden sm:block">Filter Investor:</label>
                            <select id="filter-investor" onchange="reloadTable()" class="form-select text-sm w-full lg:w-auto">
                                <option value="0">Semua Investor</option>
                                @foreach ($investors as $investor)
                                    <option value="{{ $investor->id }}">{{ $investor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                    <h2 class="font-semibold text-slate-800">Daftar Data</h2>
                    <div class="table-items-action hidden flex items-center">
                        <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count">0</span> item dipilih</div>
                        <button class="btn bg-white border-slate-200 text-rose-500" onclick="deleteSelected()">Hapus</button>
                    </div>
                </div>

                <div class="overflow-x-auto px-5 pb-5">
                    <table id="rincian-table" class="table-auto w-full text-sm">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                            <tr>
                                @if (Auth::user()->role != 'Investor')
                                    <th class="px-2 py-3 text-center w-px">
                                        <input type="checkbox" id="parent-checkbox" class="form-checkbox">
                                    </th>
                                @else
                                    <th class="hidden"></th>
                                @endif
                                <th class="px-2 py-3 text-center">No</th>
                                <th class="px-2 py-3 text-center">Investor</th>
                                <th class="px-2 py-3 text-center">Tanggal</th>
                                <th class="px-2 py-3 text-center">Tipe</th>
                                <th class="px-2 py-3 text-center">Nominal</th>
                                <th class="px-2 py-3 text-center">Bukti Transfer</th>
                                <th class="px-2 py-3 text-center">Keterangan</th>
                                <th class="px-2 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-center"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div x-show="editModalOpen" x-cloak>
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"></div>
            <div class="fixed inset-0 z-50 overflow-auto flex items-center justify-center p-4">
                <div class="bg-white rounded shadow-lg w-full max-w-lg">
                    <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                        <div class="font-semibold text-slate-800">Edit Rincian</div>
                        <button class="text-slate-400" @click="editModalOpen = false">✕</button>
                    </div>

                    <form :action="'{{ url('rincian-invest') }}/' + editData.id" method="POST" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="px-5 py-4 space-y-3 text-left">
                            @if (Auth::user()->role != 'Investor')
                                <div>
                                    <label class="block text-sm font-medium mb-1">Pilih Investor <span class="text-rose-500">*</span></label>
                                    <select name="id_investor" class="form-select w-full" x-model="editData.id_investor" required>
                                        <option value="">Pilih Investor</option>
                                        @foreach ($investors as $investor)
                                            <option value="{{ $investor->id }}">{{ $investor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div>
                                <label class="block text-sm font-medium mb-1">Tanggal</label>
                                <input type="date" name="tanggal" class="form-input w-full" x-model="editData.tanggal" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Tipe</label>
                                <select name="tipe" class="form-select w-full" x-model="editData.tipe" required>
                                    <option value="1">Masuk</option>
                                    <option value="2">Pembagian</option>
                                    <option value="3">Lain-lain</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Nominal</label>
                                <input type="text" name="nominal" class="form-input w-full format-nominal" x-model="editData.nominal" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Ganti Bukti Transfer</label>
                                <input type="file" name="upload_bukti_tf" class="form-input w-full" accept="image/*">
                                <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak diganti</p>
                                <template x-if="editData.bukti_url">
                                    <img :src="editData.bukti_url" class="w-20 h-20 mt-2 rounded border">
                                </template>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Keterangan</label>
                                <textarea name="keterangan" class="form-input w-full" rows="3" x-model="editData.keterangan" required></textarea>
                            </div>
                        </div>
                        <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                            <button type="button" class="btn-sm border-slate-200 text-slate-600" @click="editModalOpen = false">Batal</button>
                            <button type="submit" class="btn-sm bg-indigo-500 text-white">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        </div>

    <div id="buktiModal" class="bukti-modal" onclick="closeBukti()">
        <span class="close-btn">&times;</span>
        <img class="bukti-modal-content" id="buktiModalImg">
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script>
            // --- GLOBAL VARIABLES & FUNCTIONS ---
            var currentTipe = 0;

            function filterTipe(tipe) {
                currentTipe = tipe;
                // Update styling tab
                $('.tab-btn').removeClass('border-indigo-500 text-indigo-600').addClass('border-transparent text-slate-500');
                $('#tab-' + tipe).removeClass('border-transparent text-slate-500').addClass('border-indigo-500 text-indigo-600');

                reloadTable();
            }

            function reloadTable() {
                $('#rincian-table').DataTable().draw();
            }

            function openEditModal(data) {
                window.dispatchEvent(new CustomEvent('open-edit-modal', { detail: data }));
            }

            // Fungsi Bukti Gambar
            function showBukti(src) {
                document.getElementById('buktiModalImg').src = src;
                document.getElementById('buktiModal').style.display = 'flex';
            }
            function closeBukti() {
                document.getElementById('buktiModal').style.display = 'none';
            }

            // Fungsi Bulk Delete
            function deleteSelected() {
                var selectedIds = [];
                $('.table-item:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) return alert('Tidak ada item dipilih.');
                if (!confirm('Yakin hapus ' + selectedIds.length + ' data terpilih?')) return;

                fetch("{{ route('rincian-invest.bulkDelete') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ selectedIds: selectedIds })
                })
                .then(res => res.json())
                .then(res => {
                    if(res.success) {
                        alert('Data berhasil dihapus');
                        reloadTable();
                        $('#parent-checkbox').prop('checked', false);
                        updateBulkUI();
                    } else {
                        alert('Gagal menghapus data');
                    }
                })
                .catch(err => alert('Terjadi kesalahan'));
            }

            function updateBulkUI() {
                var count = $('.table-item:checked').length;
                $('.table-items-count').text(count);
                if(count > 0) {
                    $('.table-items-action').removeClass('hidden');
                } else {
                    $('.table-items-action').addClass('hidden');
                }
            }

            function checkFileSize(input) {
                const file = input.files[0];
                const alertEl = document.getElementById('file_alert');
                if (file && file.size > 1024 * 1024) {
                    alertEl.classList.remove('hidden');
                    input.value = '';
                } else {
                    alertEl.classList.add('hidden');
                }
            }

            // --- DOCUMENT READY ---
            $(document).ready(function() {
                var table = $('#rincian-table').DataTable({
                    processing: false,
                    serverSide: true, // Gunakan server-side untuk performa
                    ajax: {
                        url: "{{ route('rincian-invest.index') }}",
                        data: function(d) {
                            d.filter_tipe = currentTipe;
                            d.filter_investor = $('#filter-investor').val();
                        }
                    },
                    columns: [
                        @if (Auth::user()->role != 'Investor')
                            {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center'},
                        @else
                            {data: null, visible: false},
                        @endif
                        {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'investor_name', name: 'investor.name', className: 'text-center'},
                        {data: 'tanggal', name: 'tanggal', className: 'text-center'},
                        {data: 'tipe', name: 'tipe', className: 'text-center'},
                        {data: 'nominal', name: 'nominal', className: 'text-center'},
                        {data: 'upload_bukti_tf', name: 'upload_bukti_tf', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'keterangan', name: 'keterangan', className: 'text-center'},
                        {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
                    ],
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
                        $('#parent-checkbox').prop('checked', false);
                        updateBulkUI();
                        $('.table-item').off('change').on('change', function() {
                            updateBulkUI();
                        });
                    }
                });

                // Custom Search Logic
                $('#custom-search').on('keyup', function() {
                    table.search(this.value).draw();
                });

                // Parent Checkbox
                $('#parent-checkbox').change(function() {
                    $('.table-item').prop('checked', this.checked);
                    updateBulkUI();
                });

                // Input Format Nominal (Ribuan)
                $(document).on('input', '.format-nominal', function() {
                    let value = this.value.replace(/\D/g, "");
                    if (value.length > 15) return;
                    this.value = value ? value.replace(/\B(?=(\d{3})+(?!\d))/g, ".") : "";
                });
            });
        </script>
    @endpush
