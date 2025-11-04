<div>
    <div x-data="{
        modalOpen: false,
        editModal: false,
        editData: {},
        openEdit(data) {
            this.editData = data;
            this.editModal = true;
        }
    }">
        <style>
            [x-cloak] {
                display: none !important;
            }

            .select2-container {
                z-index: 9999 !important;
            }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

        <!-- Statistik -->
        <div class="space-y-4 mb-6">
            <!-- Filter Bulan -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Statistik Izin</h2>
                </div>
                <div>
                    <input type="month" wire:model="filter_bulan" class="form-input border-slate-300 rounded-md">
                </div>
            </div>

            <!-- Tabs tipe -->
            <div class="flex space-x-3 border-b border-slate-200 mb-2">
                @foreach (['izin', 'sakit', 'alfa'] as $tipe)
                    <button wire:click="$set('filter_tipe', '{{ $tipe }}')"
                        class="py-2 px-4 text-sm font-medium border-b-2 transition-all duration-200
                    {{ $filter_tipe === $tipe ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        {{ ucfirst($tipe) }}
                    </button>
                @endforeach
                <button wire:click="$set('filter_tipe', null)"
                    class="py-2 px-4 text-sm font-medium border-b-2 transition-all duration-200
                {{ $filter_tipe === null ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                    Semua
                </button>
            </div>

            <!-- Baris 1: Hari Ini -->
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-purple-100 text-purple-600 rounded-full">
                        <i class="fas fa-calendar-day text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">
                            {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                        </div>
                    </div>
                </div>

                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-green-100 text-green-600 rounded-full"><i class="fas fa-user-check text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Izin Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['hariIni']['izin'] }} Orang</div>
                    </div>
                </div>

                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-yellow-100 text-yellow-600 rounded-full"><i class="fas fa-user-md text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Sakit Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['hariIni']['sakit'] }} Orang</div>
                    </div>
                </div>

                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-red-100 text-red-600 rounded-full"><i class="fas fa-user-times text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Alfa Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['hariIni']['alfa'] }} Orang</div>
                    </div>
                </div>

                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-users text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Total Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['hariIni']['total'] }} Orang</div>
                    </div>
                </div>
            </div>

            <!-- Baris 2: Bulan Ini -->
            <div class="flex flex-wrap gap-4">
                <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-purple-100 text-purple-600 rounded-full">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Bulan Ini</h3>
                        <div class="text-lg font-bold text-slate-800">
                            {{ \Carbon\Carbon::parse($filter_bulan)->translatedFormat('F Y') }}
                        </div>
                    </div>
                </div>

                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-green-100 text-green-600 rounded-full"><i class="fas fa-user-check text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Izin Bulan Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['bulanIni']['izin'] }} Orang</div>
                    </div>
                </div>

                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-yellow-100 text-yellow-600 rounded-full"><i class="fas fa-user-md text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Sakit Bulan Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['bulanIni']['sakit'] }} Orang</div>
                    </div>
                </div>

                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-red-100 text-red-600 rounded-full"><i class="fas fa-user-times text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Alfa Bulan Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['bulanIni']['alfa'] }} Orang</div>
                    </div>
                </div>

                <div class="flex-1 bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-users text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Total Bulan Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $stats['bulanIni']['total'] }} Orang</div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Izin ✨</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <x-search-form placeholder="Cari berdasarkan nama user" />

                <!-- Tombol Tambah -->
                <button type="button" class="btn bg-indigo-500 hover:bg-indigo-600 text-white"
                    @click="modalOpen = true">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path
                            d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Izin</span>
                </button>
            </div>
        </div>

        <!-- Modal Tambah -->
        <div x-show="modalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @keydown.escape.window="modalOpen = false" @click.self="modalOpen = false" x-transition.opacity>
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6" x-transition.scale>
                <h2 class="text-xl font-semibold mb-4">Tambah Izin</h2>
                <form action="{{ route('master-izin.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Karyawan</label>
                            <select name="user_id" class="form-select w-full" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tipe</label>
                            <select name="tipe" class="form-select w-full" required>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alfa">Alfa</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal Dibuat</label>
                            <input type="date" name="tanggal" class="form-input w-full" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Dari Tanggal</label>
                                <input type="date" name="tanggal_mulai" class="form-input w-full" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Sampai Tanggal</label>
                                <input type="date" name="tanggal_selesai" class="form-input w-full" required>
                            </div>
                        </div>
                        @if (Auth::user()->role == 'Kepala Toko')
                        <div>
                            <label class="block text-sm font-medium mb-1">Nominal Potongan <small style="color:red">*jika tidak di isi maka akan otomatis dari pengaturan sistem</small> </label>
                            <input type="text" name="nominal_potongan" class="form-input w-full sapator">
                        </div>
                        @else
                        <input type="hidden" name="nominal_potongan" value="0">
                        @endif
                        <div>
                            <label class="block text-sm font-medium mb-1">Keterangan</label>
                            <textarea name="keterangan" class="form-input w-full" rows="2"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Upload Dokumen (Opsional)</label>
                            <input type="file" name="dokumen" class="form-input w-full" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end space-x-2">
                        <button type="button" class="btn-sm border-slate-200 text-slate-600"
                            @click="modalOpen = false">Batal</button>
                        <button type="submit"
                            class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8" x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Izin
                    <span class="text-slate-400 font-medium">{{ $count }}</span>
                </h2>
            </div>

             <div class="table-items-action hidden flex items-center gap-2">
                    <div class="text-sm text-slate-500">
                    <span class="table-items-count ml-2">0</span> data terpilih
                </div>
                <button type="button" class="btn bg-rose-500 hover:bg-rose-600 text-white btn-sm"
                    @click="deleteSelected">Hapus Terpilih</button>
                </div>

            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead
                        class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 text-center w-px">
                                <label class="inline-flex">
                                    <span class="sr-only">Select all</span>
                                    <input id="parent-checkbox" type="checkbox" class="form-checkbox table-parent"
                                        @click="toggleAll">
                                </label>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">No</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">Nama Karyawan
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">Tipe</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">Tanggal Dibuat</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">Periode</th>
                            @if (Auth::user()->role == 'Kepala Toko')
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">Nominal</th>
                            @endif
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">Keterangan
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">Dokumen
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @php $i = 1; @endphp
                        @foreach ($izins as $izin)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 text-center">
                                    <input type="checkbox" value="{{ $izin->id }}"
                                        class="form-checkbox table-item" @click="uncheckParent">
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">
                                    {{ $i++ }}</td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">
                                    {{ $izin->user->name }}</td>
                                <td class="px-2 capitalize">{{ $izin->tipe }}</td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">
                                    {{ \Carbon\Carbon::parse($izin->tanggal)->format('d/m/Y') }}</td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">
                                    {{ \Carbon\Carbon::parse($izin->tanggal_mulai)->format('d/m/Y') }} -
                                    {{ \Carbon\Carbon::parse($izin->tanggal_selesai)->format('d/m/Y') }}
                                </td>
                                @if (Auth::user()->role == 'Kepala Toko')
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">Rp
                                    {{ number_format($izin->nominal_potongan, 0, ',', '.') }}</td>
                                @endif
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">
                                    {{ $izin->keterangan }}</td>
                                <td class="text-center">
                                    @if ($izin->dokumen)
                                        <a href="{{ asset($izin->dokumen) }}" target="_blank" class="text-indigo-600 hover:underline">
                                            Lihat Dokumen
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">
                                    <div class="flex space-x-1">
                                        <button type="button" class="text-indigo-500 hover:text-indigo-700"
                                            @click="openEdit({
                                            id: '{{ $izin->id }}',
                                            user_id: '{{ $izin->user_id }}',
                                            tipe: '{{ $izin->tipe }}',
                                            tanggal: '{{ $izin->tanggal }}',
                                            nominal_potongan: '{{ $izin->nominal_potongan }}',
                                            keterangan: '{{ $izin->keterangan }}',
                                            tanggal_mulai: '{{ $izin->tanggal_mulai }}',
                                            tanggal_selesai: '{{ $izin->tanggal_selesai }}'
                                        })">Edit</button>
                                        <form action="{{ route('master-izin.destroy', $izin->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin hapus?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="text-rose-500 hover:text-rose-700">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Edit -->
        <div x-show="editModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @keydown.escape.window="editModal = false" @click.self="editModal = false" x-transition.opacity>
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6" x-transition.scale>
                <h2 class="text-xl font-semibold mb-4">Edit Izin</h2>
                <form :action="'/master/master-izin/' + editData.id" method="POST" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium mb-1">Karyawan</label>
                            <select name="user_id" class="form-select w-full" x-model="editData.user_id" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tipe</label>
                            <select name="tipe" class="form-select w-full" x-model="editData.tipe" required>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alfa">Alfa</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Tanggal Dibuat</label>
                            <input type="date" name="tanggal" class="form-input w-full"
                                x-model="editData.tanggal" required>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1">Dari Tanggal</label>
                                <input type="date" x-model="editData.tanggal_mulai" name="tanggal_mulai" class="form-input w-full" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Sampai Tanggal</label>
                                <input type="date" x-model="editData.tanggal_selesai" name="tanggal_selesai" class="form-input w-full" required>
                            </div>
                        </div>
                        @if (Auth::user()->role == 'Kepala Toko')
                        <div>
                            <label class="block text-sm font-medium mb-1">Nominal Potongan<small style="color:red">*jika tidak di isi maka akan otomatis dari pengaturan sistem</small></label>
                            <input type="text" name="nominal_potongan" class="form-input w-full sapator"
                                x-model="editData.nominal_potongan">
                        </div>
                        @else
                        <input type="hidden" name="nominal_potongan" x-model="editData.nominal_potongan">
                        @endif
                        <div>
                            <label class="block text-sm font-medium mb-1">Keterangan</label>
                            <textarea name="keterangan" class="form-input w-full" rows="2" x-model="editData.keterangan"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Upload Dokumen (Opsional)</label>
                            <input type="file" name="dokumen" class="form-input w-full" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end space-x-2">
                        <button type="button" class="btn-sm border-slate-200 text-slate-600"
                            @click="editModal = false">Batal</button>
                        <button type="submit"
                            class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Nominal formatter
        document.querySelectorAll('.sapator').forEach(el => {
            el.addEventListener('input', e => {
                let value = e.target.value.replace(/\D/g, '');
                e.target.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            });
        });

        // Checkbox mass delete
        document.addEventListener('alpine:init', () => {
            Alpine.data('handleSelect', () => ({
                selectall: false,
                toggleAll() {
                    this.selectall = !this.selectall;
                    document.querySelectorAll('input.table-item').forEach(el => el.checked = this
                        .selectall);
                    this.selectAction();
                },
                uncheckParent() {
                    this.selectall = false;
                    document.getElementById('parent-checkbox').checked = false;
                    this.selectAction();
                },
                selectAction() {
                    const selected = document.querySelectorAll('input.table-item:checked');
                    document.querySelector('.table-items-count').innerHTML = selected.length;
                    document.querySelector('.table-items-action').classList.toggle('hidden', selected
                        .length === 0);
                },
                deleteSelected() {
                    const selected = [...document.querySelectorAll('input.table-item:checked')].map(
                        cb => cb.value);
                    fetch('{{ route('master-izin.deleteSelected') }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            selectedIds: selected
                        }),
                    }).then(r => r.json()).then(data => {
                        alert(data.message);
                        window.location.reload();
                    });
                },
            }))
        })
    </script>
</div>
