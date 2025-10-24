@section('title', 'Refund')

<x-toko-layout>
    <div class=" px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Refund ✨</h1>
            </div>
         
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
            <div class="relative inline-flex" x-data="{ modalOpen: false }">
                <button
                    class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500 hover:text-slate-600 mb-2 md:mb-0"
                    @click.prevent="modalOpen = true" aria-controls="tambah-modal"
                >
                    <span class="sr-only">Print</span><wbr>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <rect x="7" y="13" width="10" height="8" rx="2" />
                    </svg>
                </button>
                <!-- Modal backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-out duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    aria-hidden="true"
                    x-cloak
                ></div>
                <!-- Modal dialog -->
                <div
                    id="tambah-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog"
                    aria-modal="true"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-in-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4"
                    x-cloak
                >
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Atur Pencetakan</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Modal content -->
                        <form action="{{ route('refund.cetak') }}" method="get" target="_blank">

                            @csrf
                            <div class="px-5 py-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Mulai tanggal <span class="text-rose-500">*</span></label>
                                        <input id="start_date" name="start_date" class="form-input w-full py-2" type="date" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Sampai tanggal <span class="text-rose-500">*</span></label>
                                        <input id="end_date" name="end_date" class="form-input w-full py-2" type="date" required />
                                    </div>
                                </div>
                            </div>
                            <!-- Modal footer -->
                            <div class="px-5 py-4 border-t border-slate-200">
                                <div class="flex flex-wrap justify-end space-x-2">
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Cetak</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                    <div x-data="{ modalOpen: false }">
                        <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white"
                            @click.prevent="modalOpen = true">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                                <path
                                    d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                            </svg>
                            <span class="hidden xs:block ml-2">Tambah Refund</span>
                        </button>

                        {{-- Modal Create --}}
                        <div x-show="modalOpen" x-cloak>
                            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"></div>
                            <div class="fixed inset-0 z-50 overflow-auto flex items-center justify-center p-4">
                                <div class="bg-white rounded shadow-lg w-full max-w-lg">
                                    <div class=" px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                        <div class="font-semibold text-slate-800">Tambah Refund</div>
                                        <button class="text-slate-400" @click="modalOpen = false">✕</button>
                                    </div>

                                    <form action="{{ route('refund.store') }}" method="post">
                                        @csrf
                                        <div class=" px-5 py-4 space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Nomor Servis <span
                                                        class="text-rose-500">*</span></label>
                                                <select id="servis_select" name="servis_transaction_id"
                                                    class="form-select w-full" required>
                                                    <option value="">-- Pilih Nomor Servis --</option>
                                                    @foreach ($servis as $s)
                                                        <option value="{{ $s->id }}">#{{ $s->nomor_servis }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium mb-1">Nominal <span
                                                        class="text-rose-500">*</span></label>
                                                <input name="nominal" type="number" id="nominal_input"
                                                    class="form-input w-full" required />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium mb-1">Teknisi (otomatis)</label>
                                                <input id="teknisi_name" type="text" class="form-input w-full"
                                                    readonly />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium mb-1">Bulan/Tahun
                                                    Potongan</label>
                                                <input name="period" id="period_input" type="month"
                                                    class="form-input w-full" />
                                                <p class="text-xs text-slate-400 mt-1">Pilih bulan dan tahun (input
                                                    type="month").</p>
                                            </div>
                                        </div>

                                        <div
                                            class="text-center px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                            <button type="button" class="btn-sm border-slate-200 text-slate-600"
                                                @click="modalOpen = false">Batal</button>
                                            <button class="btn-sm bg-indigo-500 text-white">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        {{-- end modal --}}
                    </div>
                </div>
            @endif
            
        </div>
        </div>

        {{-- Table and bulk actions --}}
        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <div x-data="handleSelect()">
                <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                    <h2 class="font-semibold text-slate-800">Semua Refund <span
                            class="text-slate-400 font-medium">{{ $refunds->total() }}</span></h2>
                    <div class="relative inline-flex">
                        <div class="table-items-action hidden">
                            <div class="flex items-center">
                                <div class="text-sm italic mr-2 whitespace-nowrap"><span
                                        class="table-items-count"></span> item yang dipilih</div>
                                <button class="btn bg-white border-slate-200 text-rose-500"
                                    @click="deleteSelected">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <thead
                            class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                            <tr>
                                    @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                                <th class="text-center px-2 py-3 w-px">
                                    <input id="parent-checkbox" class="form-checkbox" type="checkbox"
                                        @click="toggleAll" />
                                </th>
                                @endif
                                <th class="text-center px-2 py-3">No</th>
                                <th class="text-center px-2 py-3">Nomor Servis</th>
                                <th class="text-center px-2 py-3">Nominal</th>
                                <th class="text-center px-2 py-3">Teknisi</th>
                                <th class="text-center px-2 py-3">Bulan/Tahun</th>
                                @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                                    <th class="text-center px-2 py-3">Aksi</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody class="text-sm divide-y divide-slate-200">
                            @php $i = ($refunds->currentPage()-1)*$refunds->perPage()+1; @endphp
                            @foreach ($refunds as $r)
                                {{-- @dd($r) --}}
                                <tr>
                                    @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                                    <td class="text-center px-2 py-3">
                                        <input class="table-item form-checkbox" type="checkbox"
                                            value="{{ $r->id }}" @click="uncheckParent" />
                                    </td>
                                    @endif
                                    <td class="text-center px-2 py-3">{{ $i++ }}</td>
                                    <td class="text-center px-2 py-3">#{{ $r->ServiceTransaction->nomor_servis }}</td>
                                    <td class="text-center px-2 py-3">Rp {{ number_format($r->nominal, 2, ',', '.') }}</td>
                                    <td class="text-center px-2 py-3">{{ optional($r->teknisi)->name }}</td>
                                    <td class="text-center px-2 py-3">{{ $r->period ? $r->period->format('F Y') : '-' }}
                                    </td>

                                    @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                                        <td class="text-center px-2 py-3">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('refund.edit', $r->id) }}"
                                                    class="text-slate-400 hover:text-slate-500">
                                                    Edit
                                                </a>
                                                <form action="{{ route('refund.destroy', $r->id) }}" method="post"
                                                    onsubmit="return confirm('Yakin ingin hapus?')">
                                                    @csrf
                                                    @method('delete')
                                                    <button class="text-rose-500">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

                <div class="p-4">
                    {{ $refunds->links() }}
                </div>
            </div>
        </div>
    </div>
</x-toko-layout>

{{-- Scripts: handle auto teknisi and bulk delete --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('handleSelect', () => ({
            selectall: false,
            selectAction() {
                const countEl = document.querySelector('.table-items-action');
                if (!countEl) return;
                const checkboxes = document.querySelectorAll('input.table-item:checked');
                document.querySelector('.table-items-count').innerHTML = checkboxes.length;
                if (checkboxes.length > 0) {
                    countEl.classList.remove('hidden');
                } else {
                    countEl.classList.add('hidden');
                }
            },
            toggleAll() {
                this.selectall = !this.selectall;
                const checkboxes = document.querySelectorAll('input.table-item');
                [...checkboxes].map((el) => el.checked = this.selectall);
                this.selectAction();
            },
            uncheckParent() {
                this.selectall = false;
                document.getElementById('parent-checkbox').checked = false;
                this.selectAction();
            },
            deleteSelected() {
                const checkboxes = document.querySelectorAll('input.table-item:checked');
                const selectedIds = [...checkboxes].map(cb => cb.value);
                if (selectedIds.length === 0) return alert('Tidak ada item yang dipilih.');

                if (!confirm('Yakin ingin menghapus data terpilih?')) return;

                fetch('{{ route('refund.deleteSelected') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            selectedIds
                        })
                    })
                    .then(res => res.json())
                    .then(res => {
                        alert(res.message);
                        location.reload();
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Gagal menghapus data.');
                    });
            }
        }))
    });

    // auto fill teknisi ketika servis dipilih (create modal)
    document.addEventListener('DOMContentLoaded', function() {
        const servisSelect = document.getElementById('servis_select');
        const teknisiNameInput = document.getElementById('teknisi_name');
        const NominalInput = document.getElementById('nominal_input');

        if (servisSelect) {
            servisSelect.addEventListener('change', function() {
                const id = this.value;
                teknisiNameInput.value = '';
                if (!id) return;

                fetch('{{ url('refund/service') }}/' + id)
                    .then(res => res.json())
                    .then(data => {
                        teknisiNameInput.value = data.teknisi_name ?? '';
                        NominalInput.value = data.nominal ?? 0;
                    })
                    .catch(err => {
                        console.error(err);
                    });
            });
        }
    });
</script>
