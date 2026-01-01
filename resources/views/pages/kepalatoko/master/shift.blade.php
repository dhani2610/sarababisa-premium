@section('title', 'Shift')

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Shift Kerja ✨</h1>
            <div class="flex gap-2">
                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path d="M15 7H9V1a1 1 0 10-2 0v6H1a1 1 0 100 2h6v6a1 1 0 102 0V9h6a1 1 0 100-2z" />
                        </svg>
                        <span class="ml-2">Tambah Shift</span>
                    </button>

                    <!-- Modal tambah -->
                    <div x-show="modalOpen" x-cloak>
                        <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"></div>
                        <div class="fixed inset-0 z-50 overflow-auto flex items-center justify-center p-4">
                            <div class="bg-white rounded shadow-lg w-full max-w-lg">
                                <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                    <div class="font-semibold text-slate-800">Tambah Shift</div>
                                    <button class="text-slate-400" @click="modalOpen = false">✕</button>
                                </div>

                                <form action="{{ route('shift.store') }}" method="post">
                                    @csrf
                                    <div class="px-5 py-4 space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium" for="worker_id">Relasi Data
                                                Karyawan</label>
                                            <select id="worker_id" name="worker_id"
                                                class="form-select text-sm py-1 w-full" required>
                                                <option selected value="">Pilih Karyawan</option>
                                                @foreach ($workers as $worker)
                                                    <option value="{{ $worker->id }}">{{ $worker->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Nama Shift</label>
                                            <input name="nama_shift" class="form-input w-full" required>
                                        </div>
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Jam Masuk</label>
                                                <input name="jam_masuk" type="time" class="form-input w-full"
                                                    required>
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Jam Pulang</label>
                                                <input name="jam_pulang" type="time" class="form-input w-full"
                                                    required>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Nominal Gaji</label>
                                            <input name="nominal_gaji" type="text" class="form-input w-full sapator"
                                                required>
                                        </div>
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Potongan Terlambat</label>
                                                <input name="potongan_terlambat" type="text"
                                                    class="form-input w-full sapator" required>
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Potongan Tidak
                                                    Masuk (Alfa)</label>
                                                <input name="potongan_tidak_masuk" type="text"
                                                    class="form-input w-full sapator" required>
                                            </div>
                                        </div>
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Potongan Tidak
                                                    Masuk (Izin)</label>
                                                <input name="potongan_izin" type="text"
                                                    class="form-input w-full sapator" required>
                                            </div>
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Potongan Tidak
                                                    Masuk (Sakit)</label>
                                                <input name="potongan_sakit" type="text"
                                                    class="form-input w-full sapator" required>
                                            </div>
                                        </div>
                                        <div class="flex gap-3">
                                            <div class="w-1/2">
                                                <label class="block text-sm font-medium mb-1">Potongan Tidak
                                                    Masuk (Cuti)</label>
                                                <input name="potongan_cuti" type="text"
                                                    class="form-input w-full sapator" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right px-5 py-4 border-t border-slate-200">
                                        <button type="button" class="btn-sm border-slate-200 text-slate-600"
                                            @click="modalOpen = false">Batal</button>
                                        <button class="btn-sm bg-indigo-500 text-white">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- end modal -->
                </div>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8" x-data="handleSelect()">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Daftar Shift</h2>
                <div class="table-items-action hidden">
                    <div class="flex items-center">
                        <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span> item
                            dipilih</div>
                        <button class="btn bg-white border-slate-200 text-rose-500"
                            @click="deleteSelected">Hapus</button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead
                        class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="text-center px-2 py-3 w-px"><input id="parent-checkbox" class="form-checkbox"
                                    type="checkbox" @click="toggleAll"></th>
                            <th class="text-center px-2 py-3">Nama Karyawan</th>
                            <th class="text-center px-2 py-3">Nama Shift</th>
                            <th class="text-center px-2 py-3">Jam Masuk</th>
                            <th class="text-center px-2 py-3">Jam Pulang</th>
                            <th class="text-center px-2 py-3">Nominal Gaji</th>
                            <th class="text-center px-2 py-3">Potongan Terlambat</th>
                            <th class="text-center px-2 py-3">Potongan Tidak Masuk (Alfa)</th>
                            <th class="text-center px-2 py-3">Potongan Tidak Masuk (Izin)</th>
                            <th class="text-center px-2 py-3">Potongan Tidak Masuk (Cuti)</th>
                            <th class="text-center px-2 py-3">Potongan Tidak Masuk (Sakit)</th>
                            <th class="text-center px-2 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @foreach ($shifts as $shift)
                            <tr>
                                <td class="text-center px-2 py-3"><input class="table-item form-checkbox"
                                        type="checkbox" value="{{ $shift->id }}" @click="uncheckParent"></td>
                                <td class="text-center px-2 py-3">{{ $shift->worker->name  ?? '-'}}</td>
                                <td class="text-center px-2 py-3">{{ $shift->nama_shift }}</td>
                                <td class="text-center px-2 py-3">{{ $shift->jam_masuk }}</td>
                                <td class="text-center px-2 py-3">{{ $shift->jam_pulang }}</td>
                                <td class="text-center px-2 py-3">Rp
                                    {{ number_format($shift->nominal_gaji, 0, ',', '.') }}</td>
                                <td class="text-center px-2 py-3">Rp
                                    {{ number_format($shift->potongan_terlambat, 0, ',', '.') }}</td>
                                <td class="text-center px-2 py-3">Rp
                                    {{ number_format($shift->potongan_tidak_masuk, 0, ',', '.') }}</td>
                                <td class="text-center px-2 py-3">Rp
                                    {{ number_format($shift->potongan_izin, 0, ',', '.') }}</td>
                                <td class="text-center px-2 py-3">Rp
                                    {{ number_format($shift->potongan_cuti, 0, ',', '.') }}</td>
                                <td class="text-center px-2 py-3">Rp
                                    {{ number_format($shift->potongan_sakit, 0, ',', '.') }}</td>
                                <td class="text-center px-2 py-3">
                                    <div class="flex space-x-2 justify-center">
                                        <button type="button" class="text-indigo-500"
                                            @click="openEditModal({
                                            id: '{{ $shift->id }}',
                                            worker_id: '{{ $shift->worker_id }}',
                                            nama_shift: '{{ $shift->nama_shift }}',
                                            jam_masuk: '{{ $shift->jam_masuk }}',
                                            jam_pulang: '{{ $shift->jam_pulang }}',
                                            nominal_gaji: '{{ number_format($shift->nominal_gaji, 0, ',', '.') }}',
                                            potongan_terlambat: '{{ number_format($shift->potongan_terlambat, 0, ',', '.') }}',
                                            potongan_tidak_masuk: '{{ number_format($shift->potongan_tidak_masuk, 0, ',', '.') }}',
                                            potongan_sakit: '{{ number_format($shift->potongan_sakit, 0, ',', '.') }}',
                                            potongan_izin: '{{ number_format($shift->potongan_izin, 0, ',', '.') }}',
                                            potongan_cuti: '{{ number_format($shift->potongan_cuti, 0, ',', '.') }}'
                                        })">
                                            Edit </button>

                                        <form action="{{ route('shift.destroy', $shift->id) }}" method="post"
                                            onsubmit="return confirm('Yakin ingin hapus?')">
                                            @csrf @method('delete')
                                            <button class="text-rose-500">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Modal Edit -->
            <div x-show="editModalOpen" x-cloak>
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"></div>
                <div class="fixed inset-0 z-50 overflow-auto flex items-center justify-center p-4">
                    <div class="bg-white rounded shadow-lg w-full max-w-lg">
                        <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Edit Shift</div>
                            <button class="text-slate-400" @click="editModalOpen = false">✕</button>
                        </div>

                        <form :action="'shift/' + editForm.id" method="post">
                            @csrf
                            @method('PUT')
                            <div class="px-5 py-4 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium" for="worker_id">Relasi Data
                                        Karyawan</label>
                                    <select id="worker_id" name="worker_id"
                                        class="form-select text-sm py-1 w-full" required>
                                        <option selected value="">Pilih Karyawan</option>
                                        @foreach ($workers as $worker)
                                            <option value="{{ $worker->id }}">{{ $worker->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Nama Shift</label>
                                    <input name="nama_shift" x-model="editForm.nama_shift" class="form-input w-full"
                                        required>
                                </div>
                                <div class="flex gap-3">
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium mb-1">Jam Masuk</label>
                                        <input name="jam_masuk" type="time" x-model="editForm.jam_masuk"
                                            class="form-input w-full" required>
                                    </div>
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium mb-1">Jam Pulang</label>
                                        <input name="jam_pulang" type="time" x-model="editForm.jam_pulang"
                                            class="form-input w-full" required>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Nominal Gaji</label>
                                    <input name="nominal_gaji" type="text" x-model="editForm.nominal_gaji"
                                        class="form-input w-full sapator" required>
                                </div>
                                {{-- <div class="flex gap-3">
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium mb-1">Potongan Terlambat</label>
                                        <input name="potongan_terlambat" type="text"
                                            x-model="editForm.potongan_terlambat" class="form-input w-full sapator"
                                            required>
                                    </div>
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium mb-1">Potongan Tidak Masuk</label>
                                        <input name="potongan_tidak_masuk" type="text"
                                            x-model="editForm.potongan_tidak_masuk" class="form-input w-full sapator"
                                            required>
                                    </div>
                                </div> --}}
                                 <div class="flex gap-3">
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium mb-1">Potongan Terlambat</label>
                                        <input name="potongan_terlambat" type="text"
                                            x-model="editForm.potongan_terlambat" class="form-input w-full sapator" required>
                                    </div>
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium mb-1">Potongan Tidak
                                            Masuk (Alfa)</label>
                                        <input name="potongan_tidak_masuk" type="text"
                                            x-model="editForm.potongan_tidak_masuk" class="form-input w-full sapator" required>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium mb-1">Potongan Tidak
                                            Masuk (Izin)</label>
                                        <input name="potongan_izin" type="text"
                                            x-model="editForm.potongan_izin" class="form-input w-full sapator" required>
                                    </div>
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium mb-1">Potongan Tidak
                                            Masuk (Sakit)</label>
                                        <input name="potongan_sakit" type="text"
                                            x-model="editForm.potongan_sakit" class="form-input w-full sapator" required>
                                    </div>
                                </div>
                                <div class="flex gap-3">
                                    <div class="w-1/2">
                                        <label class="block text-sm font-medium mb-1">Potongan Tidak
                                            Masuk (Cuti)</label>
                                        <input name="potongan_cuti" type="text"
                                            x-model="editForm.potongan_cuti" class="form-input w-full sapator" required>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right px-5 py-4 border-t border-slate-200">
                                <button type="button" class="btn-sm border-slate-200 text-slate-600"
                                    @click="editModalOpen = false">Batal</button>
                                <button class="btn-sm bg-indigo-500 text-white">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- end modal edit -->

            <div class="p-4">{{ $shifts->links() }}</div>
        </div>
    </div>


    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('handleSelect', () => ({
                selectall: false,
                toggleAll() {
                    this.selectall = !this.selectall;
                    document.querySelectorAll('input.table-item').forEach(cb => cb.checked = this
                        .selectall);
                    this.selectAction();
                },
                uncheckParent() {
                    this.selectall = false;
                    document.getElementById('parent-checkbox').checked = false;
                    this.selectAction();
                },
                selectAction() {
                    const checked = document.querySelectorAll('input.table-item:checked');
                    document.querySelector('.table-items-count').textContent = checked.length;
                    document.querySelector('.table-items-action').classList.toggle('hidden', checked
                        .length === 0);
                },
                deleteSelected() {
                    const ids = [...document.querySelectorAll('input.table-item:checked')].map(cb => cb
                        .value);
                    if (ids.length === 0) return alert('Tidak ada item dipilih.');
                    if (!confirm('Yakin ingin hapus data terpilih?')) return;
                    fetch('{{ route('shift.deleteSelected') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                selectedIds: ids
                            })
                        })
                        .then(r => r.json())
                        .then(r => {
                            alert(r.message);
                            location.reload();
                        })
                        .catch(() => alert('Gagal hapus data.'));
                }
            }))
        });
    </script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('handleSelect', () => ({
                // fungsi yang sudah ada
                selectall: false,
                toggleAll() {
                    this.selectall = !this.selectall;
                    document.querySelectorAll('input.table-item').forEach(cb => cb.checked = this
                        .selectall);
                    this.selectAction();
                },
                uncheckParent() {
                    this.selectall = false;
                    document.getElementById('parent-checkbox').checked = false;
                    this.selectAction();
                },
                selectAction() {
                    const checked = document.querySelectorAll('input.table-item:checked');
                    document.querySelector('.table-items-count').textContent = checked.length;
                    document.querySelector('.table-items-action').classList.toggle('hidden', checked
                        .length === 0);
                },
                deleteSelected() {
                    const ids = [...document.querySelectorAll('input.table-item:checked')].map(cb => cb
                        .value);
                    if (ids.length === 0) return alert('Tidak ada item dipilih.');
                    if (!confirm('Yakin ingin hapus data terpilih?')) return;
                    fetch('{{ route('shift.deleteSelected') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                selectedIds: ids
                            })
                        })
                        .then(r => r.json())
                        .then(r => {
                            alert(r.message);
                            location.reload();
                        })
                        .catch(() => alert('Gagal hapus data.'));
                },

                // ✅ Fitur edit modal
                editModalOpen: false,
                editForm: {
                    id: null,
                    nama_shift: '',
                    jam_masuk: '',
                    jam_pulang: '',
                    nominal_gaji: '',
                    potongan_terlambat: '',
                    potongan_tidak_masuk: ''
                },
                openEditModal(data) {
                    this.editForm = {
                        ...data
                    };
                    this.editModalOpen = true;
                }
            }));
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sapators = document.querySelectorAll('.sapator');

            sapators.forEach(input => {
                input.addEventListener('input', function(e) {
                    // hapus semua karakter non digit
                    let value = this.value.replace(/\D/g, '');
                    // tambahkan titik setiap 3 angka
                    this.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                });
            });

            // sebelum submit form, hapus semua titik
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function() {
                    sapators.forEach(input => {
                        input.value = input.value.replace(/\./g, '');
                    });
                });
            });
        });
    </script>

</x-toko-layout>
