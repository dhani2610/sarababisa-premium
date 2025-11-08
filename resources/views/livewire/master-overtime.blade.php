<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<div x-data="{
    modalOpen: false,
    editModal: false,
    editData: {},
    // daftar id yang sedang tampil (per halaman)
    pageIds: @json($overtimes->pluck('id')->toArray()),
    selected: [],
    allChecked: false,

    // toggle header checkbox (dipanggil saat header berubah)
    toggleAll() {
        if (this.allChecked) {
            // jika header sekarang checked -> isi selected dengan semua id halaman (string supaya konsisten)
            this.selected = this.pageIds.map(id => String(id));
        } else {
            // jika header unchecked -> kosongkan selected
            this.selected = [];
        }
    },

    // sinkronisasi header checkbox ketika user klik checkbox item satu-per-satu
    syncAllChecked() {
        this.allChecked = (this.selected.length > 0) && (this.selected.length === this.pageIds.length);
    }
}">


    <div class="flex justify-between mb-4">
        <h1 class="text-2xl font-bold text-slate-800">Data Overtime ✨</h1>
        <button @click="modalOpen = true" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
            + Tambah Overtime
        </button>
    </div>
    {{-- <!-- Filter Range & Export -->
    <div class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Dari Tanggal</label>
            <input type="date" wire:model="start_date"
                class="border rounded-md px-3 py-2 text-sm focus:ring focus:ring-indigo-200 focus:border-indigo-400">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Sampai Tanggal</label>
            <input type="date" wire:model="end_date"
                class="border rounded-md px-3 py-2 text-sm focus:ring focus:ring-indigo-200 focus:border-indigo-400">
        </div>
    
        <!-- Tombol filter -->
        <div>
            <button wire:click="applyFilter"
                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold rounded-lg shadow transition">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
        </div>
    
        <!-- Tombol export -->
        <div>
            <button wire:click="exportExcel"
                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white text-sm font-semibold rounded-lg shadow transition">
                <i class="fas fa-file-excel mr-1"></i> Export Excel
            </button>
        </div>
    </div> --}}
    


    <!-- Tombol bulk action -->
    <template x-if="selected.length > 0">
        <div class="flex gap-2 mb-3">
            <form x-bind:action="'{{ route('master-overtime.deleteSelected') }}'" method="POST">
                @csrf @method('DELETE')
                <input type="hidden" name="ids" :value="JSON.stringify(selected)">
                <button type="submit" class="btn-sm bg-red-500 text-white">Hapus (<span
                        x-text="selected.length"></span>)</button>
            </form>

            <form x-bind:action="'{{ route('master-overtime.approveSelected') }}'" method="POST">
                @csrf
                <input type="hidden" name="ids" :value="JSON.stringify(selected)">
                <button type="submit" class="btn-sm bg-green-500 text-white">Approve (<span
                        x-text="selected.length"></span>)</button>
            </form>

            <form x-bind:action="'{{ route('master-overtime.rejectSelected') }}'" method="POST">
                @csrf
                <input type="hidden" name="ids" :value="JSON.stringify(selected)">
                <button type="submit" class="btn-sm bg-yellow-500 text-white">Reject (<span
                        x-text="selected.length"></span>)</button>
            </form>
        </div>
    </template>

    {{-- Statistik --}}
    @if (Auth::user()->role == 'Kepala Toko')
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border rounded-lg shadow p-4">
                <h3 class="text-sm text-slate-500">Overtime Hari Ini</h3>
                <div class="text-xl font-bold">{{ $stats['hariIni']['total'] }} Kali</div>
            </div>
            <div class="bg-white border rounded-lg shadow p-4">
                <h3 class="text-sm text-slate-500">Nominal Hari Ini</h3>
                <div class="text-xl font-bold">Rp {{ number_format($stats['hariIni']['total_nominal'], 0, ',', '.') }}
                </div>
            </div>
            <div class="bg-white border rounded-lg shadow p-4">
                <h3 class="text-sm text-slate-500">Overtime Bulan Ini</h3>
                <div class="text-xl font-bold">{{ $stats['bulanIni']['total'] }} Kali</div>
            </div>
            <div class="bg-white border rounded-lg shadow p-4">
                <h3 class="text-sm text-slate-500">Nominal Bulan Ini</h3>
                <div class="text-xl font-bold">Rp {{ number_format($stats['bulanIni']['total_nominal'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white border rounded-lg shadow overflow-x-auto">
        <table class="table-auto w-full text-sm">
            <thead class="bg-slate-100">
                <tr>
                    @if (Auth::user()->role == 'Kepala Toko')
                        <th class="px-2 py-2 text-center">
                            <input type="checkbox" x-model="allChecked" @change="toggleAll()"
                                title="Select all on this page" />

                        </th>
                    @endif
                    <th class="px-4 py-2">No</th>
                    <th class="px-4 py-2">Nama</th>
                    <th class="px-4 py-2">Tanggal</th>
                    <th class="px-4 py-2">Mulai</th>
                    <th class="px-4 py-2">Selesai</th>
                    @if (Auth::user()->role == 'Kepala Toko')
                        <th class="px-4 py-2">Nominal</th>
                    @endif
                    <th class="px-4 py-2">Keterangan</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($overtimes as $index => $item)
                    <tr class="border-b">
                        @if (Auth::user()->role == 'Kepala Toko')
                            <td class="text-center">
                                <input type="checkbox" class="checkbox-item" :value="'{{ $item->id }}'"
                                    x-model="selected" @change="syncAllChecked()" />

                            </td>
                        @endif
                        <td class="px-4 py-2 text-center">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 text-center">{{ $item->user->name ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">{{ $item->tanggal }}</td>
                        <td class="px-4 py-2 text-center">{{ $item->waktu_start }}</td>
                        <td class="px-4 py-2 text-center">{{ $item->waktu_end }}</td>
                        @if (Auth::user()->role == 'Kepala Toko')
                            <td class="px-4 py-2 text-center">Rp
                                {{ number_format($item->nominal_overtime, 0, ',', '.') }}
                            </td>
                        @endif
                        <td class="px-4 py-2 text-center">{{ $item->keterangan ?? '-' }}</td>
                        <td class="px-4 py-2 text-center">
                            <span
                                class="px-2 py-1 rounded text-xs 
                                {{ $item->status === 'approved'
                                    ? 'bg-green-100 text-green-700'
                                    : ($item->status === 'rejected'
                                        ? 'bg-red-100 text-red-700'
                                        : 'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex justify-center gap-2">
                                @if ($item->status == 'pending' || Auth::user()->role == 'Kepala Toko')
                                    <button @click="editData = {{ $item }}; editModal = true"
                                        class="text-blue-500 hover:underline">Edit</button>
                                    <form action="{{ route('master-overtime.destroy', $item->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus data ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:underline">Delete</button>
                                    </form>
                                @else
                                    -
                                @endif
                                {{-- @if (Auth::user()->role == 'Kepala Toko' && $item->status === 'pending')
                                <form action="{{ route('master-overtime.approve', $item->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:underline">Approve</button>
                                </form>
                                @endif --}}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-3 text-slate-500">Belum ada data lembur</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">{{ $overtimes->links() }}</div>

    {{-- Modal Tambah --}}
    <div x-show="modalOpen" x-cloak
        class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h2 class="text-lg font-semibold mb-4">Tambah Overtime</h2>
            <form method="POST" action="{{ route('master-overtime.store') }}">
                @csrf
                <div class="space-y-3">
                    <div><label>Tanggal</label><input type="date" name="tanggal" class="form-input w-full"
                            required>
                    </div>
                    <div><label>Mulai</label><input type="time" name="waktu_start" class="form-input w-full"
                            required></div>
                    <div><label>Selesai</label><input type="time" name="waktu_end" class="form-input w-full"
                            required></div>
                    <div><label>Keterangan</label>
                        <textarea name="keterangan" class="form-input w-full"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" @click="modalOpen=false" class="btn-sm border-slate-300">Batal</button>
                    <button type="submit" class="btn-sm bg-indigo-500 text-white">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div x-show="editModal" x-cloak
        class="fixed inset-0 bg-black bg-opacity-40 flex justify-center items-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <h2 class="text-lg font-semibold mb-4">Edit Overtime</h2>
            <form x-bind:action="'{{ url('master/master-overtime') }}/' + editData.id" method="POST">
                @csrf @method('PUT')
                <div class="space-y-3">
                    <div><label>Tanggal</label><input type="date" name="tanggal" :value="editData.tanggal"
                            class="form-input w-full" required></div>
                    <div><label>Mulai</label><input type="time" name="waktu_start" :value="editData.waktu_start"
                            class="form-input w-full" required></div>
                    <div><label>Selesai</label><input type="time" name="waktu_end" :value="editData.waktu_end"
                            class="form-input w-full" required></div>
                    <div><label>Keterangan</label>
                        <textarea name="keterangan" class="form-input w-full" x-text="editData.keterangan"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" @click="editModal=false" class="btn-sm border-slate-300">Batal</button>
                    <button type="submit" class="btn-sm bg-indigo-500 text-white">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
