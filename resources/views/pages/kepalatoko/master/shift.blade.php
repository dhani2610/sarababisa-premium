@section('title', 'Shift')

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto"
        id="main-area"
        x-data="{
            modalOpen: false,
            editModalOpen: false,
            editForm: {},
            // State untuk menampung jam (default 1 baris)
            createSlots: [{ masuk: '', pulang: '' }],
            editSlots: [],

            // Fungsi Tambah Baris
            addSlot(mode) {
                if (mode === 'create') {
                    this.createSlots.push({ masuk: '', pulang: '' });
                } else {
                    this.editSlots.push({ masuk: '', pulang: '' });
                }
            },

            // Fungsi Hapus Baris
            removeSlot(mode, index) {
                if (mode === 'create') {
                    if (this.createSlots.length > 1) this.createSlots.splice(index, 1);
                } else {
                    if (this.editSlots.length > 1) this.editSlots.splice(index, 1);
                }
            },

            // Reset Form Tambah saat modal dibuka
            resetCreate() {
                this.createSlots = [{ masuk: '', pulang: '' }];
                this.modalOpen = true;
            }
        }"
        @open-edit-modal.window="
            editForm = $event.detail;

            // LOGIC MAPPING DATA ARRAY DARI CONTROLLER KE ALPINE
            // Kita pastikan data masuk sebagai array
            let rawMasuk = $event.detail.jam_masuk;
            let rawPulang = $event.detail.jam_pulang;

            // Reset edit slots
            editSlots = [];

            // Jika data array (format baru), kita loop
            if (Array.isArray(rawMasuk)) {
                rawMasuk.forEach((m, i) => {
                    editSlots.push({
                        masuk: m,
                        pulang: rawPulang[i] || ''
                    });
                });
            } else {
                // Fallback jika data lama (string), masukkan sebagai 1 baris
                editSlots.push({ masuk: rawMasuk, pulang: rawPulang });
            }

            editModalOpen = true;
        ">

        <style>
            [x-cloak] { display: none !important; }
            .dataTables_wrapper .dataTables_length select { padding-right: 30px; }
        </style>

        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Shift Kerja ✨</h1>
            <div class="flex gap-2">
                <div>
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="resetCreate()">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path d="M15 7H9V1a1 1 0 10-2 0v6H1a1 1 0 100 2h6v6a1 1 0 102 0V9h6a1 1 0 100-2z" />
                        </svg>
                        <span class="ml-2">Tambah Shift</span>
                    </button>

                    <div x-show="modalOpen" x-cloak>
                        <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"></div>
                        <div class="fixed inset-0 z-50 overflow-auto flex items-center justify-center p-4">
                            <div class="bg-white rounded shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
                                <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                    <div class="font-semibold text-slate-800">Tambah Shift</div>
                                    <button class="text-slate-400" @click="modalOpen = false">✕</button>
                                </div>

                                <form action="{{ route('shift.store') }}" method="post">
                                    @csrf
                                    <div class="px-5 py-4 space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium" for="worker_id">Relasi Data Karyawan</label>
                                            <select id="worker_id" name="worker_id" class="form-select text-sm py-1 w-full" required>
                                                <option selected value="">Pilih Karyawan</option>
                                                @foreach ($workers as $worker)
                                                    <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Nama Shift</label>
                                            <input name="nama_shift" class="form-input w-full" required>
                                        </div>

                                        <div class="bg-slate-50 p-3 rounded border border-slate-200">
                                            <label class="block text-sm font-medium mb-2 text-slate-600">Jadwal Jam Kerja</label>

                                            <template x-for="(slot, index) in createSlots" :key="index">
                                                <div class="flex gap-2 mb-2 items-end">
                                                    <div class="w-1/2">
                                                        <label class="block text-xs font-medium mb-1 text-slate-500">Masuk</label>
                                                        <input x-model="slot.masuk" name="jam_masuk[]" type="time" class="form-input w-full" required>
                                                    </div>
                                                    <div class="w-1/2">
                                                        <label class="block text-xs font-medium mb-1 text-slate-500">Pulang</label>
                                                        <input x-model="slot.pulang" name="jam_pulang[]" type="time" class="form-input w-full" required>
                                                    </div>
                                                    <button type="button" @click="removeSlot('create', index)" class="text-rose-500 hover:bg-rose-50 p-2 rounded mb-[2px]" x-show="createSlots.length > 1">
                                                        ✕
                                                    </button>
                                                </div>
                                            </template>

                                            <button type="button" @click="addSlot('create')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium mt-1 flex items-center">
                                                <span>+ Tambah Jam</span>
                                            </button>
                                        </div>
                                        <input name="nominal_gaji" type="hidden" class="form-input w-full sapator">

                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Potongan Terlambat</label>
                                                <input name="potongan_terlambat" type="text" class="form-input w-full sapator" required>
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Potongan Alfa</label>
                                                <input name="potongan_tidak_masuk" type="text" class="form-input w-full sapator" required>
                                            </div>
                                        </div>
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Potongan Izin</label>
                                                <input name="potongan_izin" type="text" class="form-input w-full sapator" required>
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Potongan Sakit</label>
                                                <input name="potongan_sakit" type="text" class="form-input w-full sapator" required>
                                            </div>
                                        </div>
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Potongan Cuti</label>
                                                <input name="potongan_cuti" type="text" class="form-input w-full sapator" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right px-5 py-4 border-t border-slate-200">
                                        <button type="button" class="btn-sm border-slate-200 text-slate-600" @click="modalOpen = false">Batal</button>
                                        <button class="btn-sm bg-indigo-500 text-white">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-5 rounded-r">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Panduan Pengaturan Shift</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>
                                Jika hanya memiliki 1 shift, maka hanya isi 1 jam kerja saja.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Daftar Shift</h2>
                <div class="table-items-action hidden flex items-center">
                    <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count">0</span> item dipilih</div>
                    <button class="btn bg-white border-slate-200 text-rose-500" onclick="deleteSelected()">Hapus</button>
                </div>
            </div>

            <div class="overflow-x-auto px-5 pb-5">
                <table id="shift-table" class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="text-center px-2 py-3 w-px">
                                <input id="parent-checkbox" class="form-checkbox" type="checkbox">
                            </th>
                            <th class="text-center px-2 py-3">Nama Karyawan</th>
                            <th class="text-center px-2 py-3">Nama Shift</th>
                            <th class="text-center px-2 py-3">Jadwal Shift</th>
                            <th class="text-center px-2 py-3">Potongan Terlambat</th>
                            <th class="text-center px-2 py-3">Potongan Alfa</th>
                            <th class="text-center px-2 py-3">Potongan Izin</th>
                            <th class="text-center px-2 py-3">Potongan Cuti</th>
                            <th class="text-center px-2 py-3">Potongan Sakit</th>
                            <th class="text-center px-2 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                    </tbody>
                </table>
            </div>
        </div>

        <div x-show="editModalOpen" x-cloak>
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"></div>
            <div class="fixed inset-0 z-50 overflow-auto flex items-center justify-center p-4">
                <div class="bg-white rounded shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                        <div class="font-semibold text-slate-800">Edit Shift</div>
                        <button class="text-slate-400" @click="editModalOpen = false">✕</button>
                    </div>

                    <form :action="'shift/' + editForm.id" method="post">
                        @csrf
                        @method('PUT')
                        <div class="px-5 py-4 space-y-3">
                            <div>
                                <label class="block text-sm font-medium" for="worker_id">Relasi Data Karyawan</label>
                                <select id="worker_id" name="worker_id" class="form-select text-sm py-1 w-full" x-model="editForm.worker_id" required>
                                    <option selected value="">Pilih Karyawan</option>
                                    @foreach ($workers as $worker)
                                        <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Nama Shift</label>
                                <input name="nama_shift" x-model="editForm.nama_shift" class="form-input w-full" required>
                            </div>

                            <div class="bg-slate-50 p-3 rounded border border-slate-200">
                                <label class="block text-sm font-medium mb-2 text-slate-600">Jadwal Jam Kerja</label>

                                <template x-for="(slot, index) in editSlots" :key="index">
                                    <div class="flex gap-2 mb-2 items-end">
                                        <div class="w-1/2">
                                            <label class="block text-xs font-medium mb-1 text-slate-500">Masuk</label>
                                            <input x-model="slot.masuk" name="jam_masuk[]" type="time" class="form-input w-full" required>
                                        </div>
                                        <div class="w-1/2">
                                            <label class="block text-xs font-medium mb-1 text-slate-500">Pulang</label>
                                            <input x-model="slot.pulang" name="jam_pulang[]" type="time" class="form-input w-full" required>
                                        </div>
                                        <button type="button" @click="removeSlot('edit', index)" class="text-rose-500 hover:bg-rose-50 p-2 rounded mb-[2px]" x-show="editSlots.length > 1">
                                            ✕
                                        </button>
                                    </div>
                                </template>

                                <button type="button" @click="addSlot('edit')" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium mt-1 flex items-center">
                                    <span>+ Tambah Jam</span>
                                </button>
                            </div>
                            <input name="nominal_gaji" type="text" x-model="editForm.nominal_gaji" class="form-input w-full sapator" required>
                            {{-- <div>
                                <label class="block text-sm font-medium mb-1">Gaji Pokok</label>
                                <input name="nominal_gaji" type="text" x-model="editForm.nominal_gaji" class="form-input w-full sapator" required>
                            </div> --}}
                            <div class="flex gap-3">
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium mb-1">Potongan Terlambat</label>
                                    <input name="potongan_terlambat" type="text" x-model="editForm.potongan_terlambat" class="form-input w-full sapator" required>
                                </div>
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium mb-1">Potongan Alfa</label>
                                    <input name="potongan_tidak_masuk" type="text" x-model="editForm.potongan_tidak_masuk" class="form-input w-full sapator" required>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium mb-1">Potongan Izin</label>
                                    <input name="potongan_izin" type="text" x-model="editForm.potongan_izin" class="form-input w-full sapator" required>
                                </div>
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium mb-1">Potongan Sakit</label>
                                    <input name="potongan_sakit" type="text" x-model="editForm.potongan_sakit" class="form-input w-full sapator" required>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="w-1/2">
                                    <label class="block text-sm font-medium mb-1">Potongan Cuti</label>
                                    <input name="potongan_cuti" type="text" x-model="editForm.potongan_cuti" class="form-input w-full sapator" required>
                                </div>
                            </div>
                        </div>
                        <div class="text-right px-5 py-4 border-t border-slate-200">
                            <button type="button" class="btn-sm border-slate-200 text-slate-600" @click="editModalOpen = false">Batal</button>
                            <button class="btn-sm bg-indigo-500 text-white">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script>
            // --- FUNGSI GLOBAL ---

            // 1. Trigger Edit Modal (dipanggil dari tombol edit di DataTables)
            function openEditModal(data) {
                // Dispatch event ke window agar AlpineJS menangkapnya
                window.dispatchEvent(new CustomEvent('open-edit-modal', { detail: data }));
            }

            // 2. Fungsi Bulk Delete (TETAP SAMA)
            function deleteSelected() {
                var selectedIds = [];
                $('.table-item:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    alert('Tidak ada item dipilih.');
                    return;
                }

                if (!confirm('Yakin ingin hapus ' + selectedIds.length + ' data terpilih?')) return;

                fetch('{{ route('shift.deleteSelected') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ selectedIds: selectedIds })
                })
                .then(r => r.json())
                .then(r => {
                    alert(r.message);
                    $('#shift-table').DataTable().ajax.reload();
                    $('#parent-checkbox').prop('checked', false);
                    updateBulkUI();
                })
                .catch(() => alert('Gagal hapus data.'));
            }

            // 3. Update UI
            function updateBulkUI() {
                var count = $('.table-item:checked').length;
                $('.table-items-count').text(count);
                if(count > 0) {
                    $('.table-items-action').removeClass('hidden');
                } else {
                    $('.table-items-action').addClass('hidden');
                }
            }

            // --- DOCUMENT READY ---
            $(document).ready(function() {
                var table = $('#shift-table').DataTable({
                    processing: false,
                    serverSide: false,
                    ajax: "{{ route('shift.index') }}",
                    columns: [
                        {data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center'},
                        {data: 'worker_name', name: 'worker.name', className: 'text-center'},
                        {data: 'nama_shift', name: 'nama_shift', className: 'text-center'},
                        // Perhatikan: Kolom ini sekarang mengambil 'jadwal_shift' yang dikirim controller (HTML list)
                        {data: 'jadwal_shift', name: 'jadwal_shift', className: 'text-left'},
                        // {data: 'nominal_gaji', name: 'nominal_gaji', className: 'text-center'},
                        {data: 'potongan_terlambat', name: 'potongan_terlambat', className: 'text-center'},
                        {data: 'potongan_tidak_masuk', name: 'potongan_tidak_masuk', className: 'text-center'},
                        {data: 'potongan_izin', name: 'potongan_izin', className: 'text-center'},
                        {data: 'potongan_cuti', name: 'potongan_cuti', className: 'text-center'},
                        {data: 'potongan_sakit', name: 'potongan_sakit', className: 'text-center'},
                        {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
                    ],
                     language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                    },
                    drawCallback: function() {
                        $('#parent-checkbox').prop('checked', false);
                        updateBulkUI();
                        $('.table-item').off('change').on('change', function() {
                            updateBulkUI();
                        });
                    }
                });

                $('#parent-checkbox').change(function() {
                    var checked = this.checked;
                    $('.table-item').prop('checked', checked);
                    updateBulkUI();
                });

                // Sapator Logic
                const sapators = document.querySelectorAll('.sapator');
                sapators.forEach(input => {
                    input.addEventListener('input', function(e) {
                        let value = this.value.replace(/\D/g, '');
                        this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    });
                });

                document.querySelectorAll('form').forEach(form => {
                    form.addEventListener('submit', function() {
                        sapators.forEach(input => {
                            input.value = input.value.replace(/\./g, '');
                        });
                    });
                });
            });
        </script>
    @endpush
</x-toko-layout>
