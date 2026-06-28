<div>
    <div class="sm:flex sm:justify-between sm:items-center mb-3">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Target Teknisi✨</h1>
        </div>

        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <x-search-form placeholder="Cari berdasarkan nama" />

            <div x-data="{ modalOpen: false }">
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Target</span>
                </button>
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>

                <div id="tambah-modal" class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Tambah Hasil Target</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <form action="{{ route('target-teknisi.store') }}" method="post" x-data="{ tipe: 'item' }">
                            @csrf
                            <div class="px-5 py-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="users_id">Teknisi <span class="text-rose-500">*</span></label>
                                        <select id="users_id" name="users_id" class="form-select text-sm py-1 w-full" required>
                                            <option selected value="">Pilih Teknisi</option>
                                            @foreach ($teknisi as $tkn)
                                                <option value="{{ $tkn->id }}">{{ $tkn->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="tipe_target">Tipe Target <span class="text-rose-500">*</span></label>
                                        <select id="tipe_target" name="tipe_target" x-model="tipe" class="form-select text-sm py-1 w-full" required>
                                            <option value="item">Berdasarkan Item</option>
                                            <option value="nominal">Berdasarkan Nominal</option>
                                        </select>
                                    </div>

                                    <div x-show="tipe === 'item'">
                                        <label class="block text-sm font-medium mb-1" for="item">Jumlah Item <span class="text-rose-500">*</span></label>
                                        <input id="item" name="item" class="form-input w-full px-2 py-1" type="number" x-bind:required="tipe === 'item'" />
                                    </div>

                                    <div x-show="tipe === 'nominal'" x-cloak>
                                        <label class="block text-sm font-medium mb-1" for="nominal">Target Nominal (Rp) <span class="text-rose-500">*</span></label>
                                        <input id="nominal" name="nominal" class="form-input w-full px-2 py-1" type="text" onkeyup="formatRibuan(this)" x-bind:required="tipe === 'nominal'" />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="bonus_nominal">Bonus Pencapaian (Rp) <span class="text-rose-500">*</span></label>
                                        <input id="bonus_nominal" name="bonus_nominal" class="form-input w-full px-2 py-1" type="text" onkeyup="formatRibuan(this)" required />
                                    </div>
                                    </div>
                            </div>
                            <div class="px-5 py-4 border-t border-slate-200">
                                <div class="flex flex-wrap justify-end space-x-2">
                                    <button type="button" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                    <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="sm:flex sm:justify-between sm:items-center">
        <select wire:model="paginate" id="" class="form-select">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Target <span class="text-slate-400 font-medium">{{ $targets_count ?? 0 }}</span></h2>
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span> item yang dipilih</div>
                            <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" @click="deleteSelected">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox" @click="toggleAll" />
                                    </label>
                                </div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">No.</div></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Tanggal</div></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Teknisi</div></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Target</div></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Bonus</div></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-semibold text-left">Aksi</div></th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @php $i = 1 @endphp
                        @foreach($targets as $item)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="flex items-center">
                                        <label class="inline-flex">
                                            <span class="sr-only">Select</span>
                                            <input class="table-item form-checkbox" type="checkbox" value="{{ $item->id }}" @click="uncheckParent" />
                                        </label>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-medium">{{ $i++ }}</div></td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap"><div class="font-medium">{{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d F Y') }}</div></td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    @if ($item->user && $item->user->exists())
                                        <div class="font-medium">{{ $item->user->name }}</div>
                                    @else
                                        <div class="font-medium text-rose-600">Data akun telah dihapus</div>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">
                                        @if($item->tipe == 'nominal')
                                            Rp {{ number_format($item->nominal, 0, ',', '.') }}
                                        @else
                                            {{ $item->item }} item
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium text-emerald-600">
                                        Rp {{ number_format($item->bonus_nominal, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="space-x-1 flex">
                                        <a href="{{ route('target-teknisi.edit', $item->id) }}">
                                            <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                    <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                                </svg>
                                            </button>
                                        </a>
                                        </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function formatRibuan(angka) {
            let number_string = angka.value.replace(/[^,\d]/g, '').toString(),
                split         = number_string.split(','),
                sisa          = split[0].length % 3,
                rupiah        = split[0].substr(0, sisa),
                ribuan        = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            angka.value = rupiah;
        }

        // ... kode alpine JS kamu yang lain
    </script>
</div>
