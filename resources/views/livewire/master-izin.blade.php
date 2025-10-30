<div>
    <style>
        .select2-container {
            z-index: 9999 !important;
        }
    </style>

    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Master Izin ✨</h1>
        </div>

        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <x-search-form placeholder="Cari berdasarkan nama user atau keterangan" wire:model="search" />

            <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" wire:click="create">
                <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                    <path
                        d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                </svg>
                <span class="hidden xs:block ml-2">Tambah Izin</span>
            </button>
        </div>
    </div>

    <!-- messages -->
    @if (session()->has('message'))
        <div class="px-4 py-2 rounded-sm text-sm bg-green-500 text-white mb-4">
            {{ session('message') }}
        </div>
    @endif

    <!-- Controls -->
    <div class="sm:flex sm:justify-between sm:items-center mb-5">
        <div class="mb-0">
            <select wire:model="paginate" class="form-select">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>

        <div class="flex items-center gap-2">
            <button class="btn bg-rose-500 hover:bg-rose-600 text-white" wire:click="deleteSelected"
                onclick="return confirm('Hapus data terpilih?')">
                Hapus Terpilih
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div class="px-5 py-4">
            <h2 class="font-semibold text-slate-800">Semua Izin <span
                    class="text-slate-400 font-medium">{{ $count }}</span></h2>
        </div>

        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead
                    class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-2 py-3 w-px">
                            <input type="checkbox" wire:model="selectAll" />
                        </th>
                        <th class="px-2 py-3">No.</th>
                        <th class="px-2 py-3">User</th>
                        <th class="px-2 py-3">Tipe</th>
                        <th class="px-2 py-3">Tanggal</th>
                        <th class="px-2 py-3">Potongan (Rp)</th>
                        <th class="px-2 py-3">Keterangan</th>
                        <th class="px-2 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                    @php $i = ($izins->currentPage()-1) * $izins->perPage() + 1; @endphp
                    @foreach ($izins as $item)
                        <tr>
                            <td class="px-2 py-3 w-px">
                                <input type="checkbox" value="{{ $item->id }}" wire:model="selected" />
                            </td>
                            <td class="px-2 py-3">{{ $i++ }}</td>
                            <td class="px-2 py-3">{{ $item->user?->name }}</td>
                            <td class="px-2 py-3">{{ ucfirst($item->tipe) }}</td>
                            <td class="px-2 py-3">{{ $item->tanggal->format('Y-m-d') }}</td>
                            <td class="px-2 py-3">{{ number_format($item->nominal_potongan, 0, ',', '.') }}</td>
                            <td class="px-2 py-3">{{ $item->keterangan }}</td>
                            <td class="px-2 py-3">
                                <div class="flex gap-2">
                                    <button class="text-slate-400 hover:text-slate-600"
                                        wire:click="edit({{ $item->id }})">Edit</button>
                                    <button class="text-rose-500 hover:text-rose-600"
                                        wire:click="confirmDelete({{ $item->id }})">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if ($izins->isEmpty())
                        <tr>
                            <td colspan="8" class="px-2 py-3 text-center">Tidak ada data</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="p-4">
            {{ $izins->links() }}
        </div>
    </div>

    <!-- Modal Create -->
    <div x-data x-cloak>
        <div id="modal-create" class="hidden">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-40"></div>
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded shadow-lg w-full max-w-lg">
                    <div class="px-5 py-3 border-b">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Tambah Izin</div>
                            <button @click="$dispatch('close-modal-create')"
                                class="text-slate-400 hover:text-slate-500">×</button>
                        </div>
                    </div>

                    <div class="p-5">
                        <form wire:submit.prevent="store">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">User <span
                                            class="text-rose-500">*</span></label>
                                    <select id="userSelectEdit" wire:model="user_id" class="form-select w-full">
                                        <option value="">-- Pilih user --</option>
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <span class="text-rose-500 text-sm">{{ $message }}</span>
                                    @enderror

                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Tipe</label>
                                    <select wire:model="tipe" class="form-select w-full">
                                        <option value="izin">Izin</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="alfa">Alfa</option>
                                    </select>
                                    @error('tipe')
                                        <span class="text-rose-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Tanggal <span
                                            class="text-rose-500">*</span></label>
                                    <input type="date" wire:model="tanggal" class="form-input w-full" />
                                    @error('tanggal')
                                        <span class="text-rose-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Nominal Potongan (Rp)</label>
                                    <input type="text" id="nominal_create" wire:model.defer="nominal_potongan"
                                        class="form-input w-full" oninput="formatRupiahInput(this)" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Keterangan</label>
                                    <textarea wire:model="keterangan" class="form-textarea w-full"></textarea>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <button type="button" class="btn-sm border-slate-200"
                                    @click="$dispatch('close-modal-create')">Batal</button>
                                <button type="submit" class="btn-sm bg-indigo-500 text-white">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit (sama layout, but bind to update) -->
    <div x-data x-cloak>
        <div id="modal-edit" class="hidden">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-40"></div>
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded shadow-lg w-full max-w-lg">
                    <div class="px-5 py-3 border-b">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Edit Izin</div>
                            <button @click="$dispatch('close-modal-edit')"
                                class="text-slate-400 hover:text-slate-500">×</button>
                        </div>
                    </div>

                    <div class="p-5">
                        <form wire:submit.prevent="update">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1">User <span
                                            class="text-rose-500">*</span></label>
                                    <select wire:model="user_id" class="form-select w-full">
                                        <option value="">-- Pilih user --</option>
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Tipe</label>
                                    <select wire:model="tipe" class="form-select w-full">
                                        <option value="izin">Izin</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="alfa">Alfa</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Tanggal <span
                                            class="text-rose-500">*</span></label>
                                    <input type="date" wire:model="tanggal" class="form-input w-full" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Nominal Potongan (Rp)</label>
                                    <input type="text" id="nominal_edit" wire:model.defer="nominal_potongan"
                                        class="form-input w-full" oninput="formatRupiahInput(this)" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1">Keterangan</label>
                                    <textarea wire:model="keterangan" class="form-textarea w-full"></textarea>
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end gap-2">
                                <button type="button" class="btn-sm border-slate-200"
                                    @click="$dispatch('close-modal-edit')">Batal</button>
                                <button type="submit" class="btn-sm bg-indigo-500 text-white">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Delete (simple confirm via browser event) -->
    <script>
        function formatRupiahInput(el) {
            // preserve caret roughly; simple formatter: remove non-digit, add thousand separators
            let v = el.value;
            let digits = v.replace(/\D/g, '');
            if (digits === '') {
                el.value = '0';
                return;
            }
            el.value = new Intl.NumberFormat('id-ID').format(parseInt(digits));
            // update Livewire model if available (dispatch input event)
            el.dispatchEvent(new Event('input', {
                bubbles: true
            }));
        }

        window.addEventListener('openModalCreate', () => {
            document.getElementById('modal-create').classList.remove('hidden');
        });
        window.addEventListener('closeModalCreate', () => {
            document.getElementById('modal-create').classList.add('hidden');
        });

        window.addEventListener('openModalEdit', () => {
            document.getElementById('modal-edit').classList.remove('hidden');
        });
        window.addEventListener('closeModalEdit', () => {
            document.getElementById('modal-edit').classList.add('hidden');
        });

        window.addEventListener('openConfirmDelete', event => {
            if (confirm('Yakin ingin menghapus data ini?')) {
                @this.call('delete', event.detail.id);
            }
        });

        // close via dispatched custom events from x buttons inside modal
        document.addEventListener('close-modal-create', () => {
            document.getElementById('modal-create').classList.add('hidden');
        });
        document.addEventListener('close-modal-edit', () => {
            document.getElementById('modal-edit').classList.add('hidden');
        });
    </script>
   <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
document.addEventListener('livewire:load', function() {
    initSelect2();

    // Re-init tiap kali Livewire re-render
    Livewire.hook('message.processed', (message, component) => {
        initSelect2();
    });
});

function initSelect2() {
    // handle dua modal sekaligus (create & edit)
    const selects = ['#userSelect', '#userSelectEdit'];

    selects.forEach(id => {
        const el = $(id);
        if (el.length && !el.data('select2')) {
            el.select2({
                dropdownParent: el.closest('[id^="modal-"]'), // pastikan dropdown muncul di dalam modal yg sama
                placeholder: "-- Pilih user --",
                width: '100%',
            });

            // Sinkron ke Livewire
            el.on('change', function() {
                Livewire.emit('setUserId', $(this).val());
            });

            // 🚫 FIX: cegah klik backdrop modal dari event Select2
            el.on('select2:opening select2:closing', function(e) {
                const modal = $(this).closest('[id^="modal-"]');
                modal.css('pointer-events', 'none');
                setTimeout(() => modal.css('pointer-events', 'auto'), 200);
            });
        }
    });
}
</script>


</div>
