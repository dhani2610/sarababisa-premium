@section('title')
    Edit Karyawan
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Karyawan ✨</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Search form -->
                <x-search-form placeholder="Cari berdasarkan nama karyawan" />

                <!-- Create invoice button -->
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="hidden xs:block ml-2">Tambah Karyawan</span>
                </button>

            </div>

        </div>
        <div x-data="{ modalOpen: true }">
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
                    <!-- Modal header -->
                    <div class="px-5 py-3 border-b border-slate-200">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Edit Karyawan</div>
                            <a href="{{ route('karyawan.index') }}" class="text-slate-400 hover:text-slate-500">
                                <div class="sr-only">Close</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Modal content -->
                    <form action="{{ route('karyawan.update', $item->id) }}" method="post">
                        @method('PUT')
                        @csrf
                        <div class="px-5 py-4">

                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-5 rounded-r">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">Panduan Pengaturan Shift & Gaji</h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li>
                                                <span class="font-semibold">Gaji Pokok</span> terhubung langsung dengan data shift.
                                            </li>
                                            <li>
                                                Untuk karyawan <span class="font-semibold">Rolling Shift</span>, pilih <u>General Shift</u> (Otomatis jam masuk/pulang nya menjadi 24 jam dan untuk potongan dapat di ubah di halaman shift).
                                            </li>
                                            <li>
                                                Untuk karyawan <span class="font-semibold">Shift Tertentu</span>, pilih <u>Satu Shift</u> terlebih dahulu, lalu sesuaikan jam masuk/pulang dan potongan di halaman Shift.
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="name">Nama Karyawan</label>
                                    <input id="name" name="name" class="form-input w-full px-2 py-1" type="text" value="{{ $item->name }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="jabatan">Jabatan</label>
                                    <input id="jabatan" name="jabatan" class="form-input w-full px-2 py-1" type="text" value="{{ $item->jabatan }}" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="status">Status</label>
                                    <select id="status" name="status" class="form-select text-sm py-1 w-full">
                                        <option value="{{ $item->status }}" selected>{{ $item->status }}</option>
                                        <option value="Karyawan Tetap">Karyawan Tetap</option>
                                        <option value="Karyawan Kontrak">Karyawan Kontrak</option>
                                        <option value="Magang">Magang</option>
                                        <option value="Freelancer">Freelancer</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="bulankerja">Bulan Kerja</label>
                                    <input id="bulankerja" name="bulankerja" class="form-input w-full px-2 py-1" type="date" value="{{ \Carbon\Carbon::parse($item->bulankerja)->format('Y-m-d') }}" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="status">Status Shift</label>
                                    <select id="status_shift" name="status_shift" class="form-select text-sm py-1 w-full">
                                        <option value="1" {{  (int)$item->status_aktif ==  1 ? 'selected' : '' }}>Multi Shift</option>
                                        <option value="0" {{  (int)$item->status_aktif ==  0 ? 'selected' : '' }}>Satu Shift</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="gaji">Gaji Pokok</label>
                                    <input id="gaji" name="gaji" class="form-input w-full px-2 py-1 input-currency" type="text" value="{{ $item->gaji }}"/>
                                </div>
                                {{-- <input id="gaji" name="gaji" class="form-input w-full px-2 py-1" type="hidden" value="{{ $item->gaji }}"/> --}}
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="absen">Tunjangan Kehadiran</label>
                                    <input id="absen" name="absen" class="form-input w-full px-2 py-1 input-currency" type="text" value="{{ $item->absen }}"/>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="bpjs">Tunjangan BPJS</label>
                                    <input id="bpjs" name="bpjs" class="form-input w-full px-2 py-1 input-currency" type="text" value="{{ $item->bpjs }}"/>
                                </div>

                            </div>
                        </div>
                        <!-- Modal footer -->
                        <div class="px-5 py-4 border-t border-slate-200">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <a href="{{ route('karyawan.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                    Batal
                                </a>
                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <script>
        function formatRupiahInput(el) {
            let value = el.value || '';

            // hapus semua selain angka
            value = value.replace(/\D/g, '');

            // format pakai titik
            el.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        /* ON INPUT (REAL TIME) */
        document.addEventListener('input', function (e) {
            if (!e.target.classList.contains('input-currency')) return;
            formatRupiahInput(e.target);
        });

        /* ON LOAD (FORM EDIT) */
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.input-currency').forEach(function (el) {
                formatRupiahInput(el);
            });
        });
    </script>
</x-toko-layout>
