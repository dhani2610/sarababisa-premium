@section('title')
    Edit Target Teknisi
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Target Teknisi ✨</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <x-search-form placeholder="Cari berdasarkan nama" />

                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="hidden xs:block ml-2">Tambah Target</span>
                </button>

            </div>

        </div>

        <div x-data="{ modalOpen: true, tipeTarget: '{{ $item->tipe ?? 'item' }}' }">
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
            <div
                id="edit-modal"
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
                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">
                    <div class="px-5 py-3 border-b border-slate-200">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Edit Target Teknisi</div>
                            <a href="{{ route('target-teknisi.index') }}" class="text-slate-400 hover:text-slate-500">
                                <div class="sr-only">Close</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <form action="{{ route('target-teknisi.update', $item->id) }}" method="post">
                        @method('PUT')
                        @csrf
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="created_at">Tanggal Target</label>
                                    <input id="created_at" name="created_at" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="users_id">Teknisi</label>
                                    <select id="users_id" name="users_id" class="form-select text-sm py-1 w-full" required>
                                        @if($item->user)
                                            <option selected value="{{ $item->user->id }}">{{ $item->user->name }}</option>
                                        @endif
                                        @foreach ($teknisi as $tkn)
                                            <option value="{{ $tkn->id }}">{{ $tkn->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1" for="tipe_target">Tipe Target <span class="text-rose-500">*</span></label>
                                    <select id="tipe_target" name="tipe" x-model="tipeTarget" class="form-select text-sm py-1 w-full" required>
                                        <option value="item">Berdasarkan Item</option>
                                        <option value="nominal">Berdasarkan Nominal</option>
                                    </select>
                                </div>

                                <div x-show="tipeTarget === 'item'">
                                    <label class="block text-sm font-medium mb-1" for="item">Jumlah Item <span class="text-rose-500">*</span></label>
                                    <input id="item" name="item" class="form-input w-full px-2 py-1" type="number" value="{{ $item->item }}" x-bind:required="tipeTarget === 'item'" />
                                </div>

                                <div x-show="tipeTarget === 'nominal'" x-cloak>
                                    <label class="block text-sm font-medium mb-1" for="nominal">Target Nominal (Rp) <span class="text-rose-500">*</span></label>
                                    <input id="nominal" name="nominal" class="form-input w-full px-2 py-1" type="text" onkeyup="formatRibuan(this)" value="{{ $item->nominal ? number_format($item->nominal, 0, '', '.') : '' }}" x-bind:required="tipeTarget === 'nominal'" />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1" for="bonus_nominal">Bonus Pencapaian (Rp) <span class="text-rose-500">*</span></label>
                                    <input id="bonus_nominal" name="bonus_nominal" class="form-input w-full px-2 py-1" type="text" onkeyup="formatRibuan(this)" value="{{ $item->bonus_nominal ? number_format($item->bonus_nominal, 0, '', '.') : '' }}" required />
                                </div>

                            </div>
                        </div>
                        <div class="px-5 py-4 border-t border-slate-200">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <a href="{{ route('target-teknisi.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                    Batal
                                </a>
                                <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan Perubahan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        function formatRibuan(angka) {
            // Hapus semua karakter selain angka (0-9)
            let number_string = angka.value.replace(/[^0-9]/g, '').toString(),
                sisa          = number_string.length % 3,
                rupiah        = number_string.substr(0, sisa),
                ribuan        = number_string.substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            // Tampilkan kembali hasil format
            angka.value = rupiah;
        }
    </script>
</x-toko-layout>
