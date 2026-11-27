<div>
    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Pengaturan Absensi & Potongan</h3>
    <div class="text-sm mb-3 text-slate-600">
        Aktifkan sistem absensi, tentukan jam kerja, hari wajib absen, serta nominal potongan jika tidak absen (masuk & pulang).
    </div>

    <div class="flex items-center mt-3">
        <div class="form-switch">
            <input type="checkbox" id="toggleAbsensi" class="sr-only" wire:model="active_setting_absensi" />
            <label class="bg-slate-400" for="toggleAbsensi">
                <span class="bg-white shadow-sm" aria-hidden="true"></span>
                <span class="sr-only">Aktifkan Absensi</span>
            </label>
        </div>
        <div class="text-sm text-slate-400 italic ml-2">
            {{ $active_setting_absensi ? 'Aktif' : 'Tidak Aktif' }}
        </div>
    </div>

    @if ($active_setting_absensi)
        <div class="mt-4 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="jam_masuk" class="block text-sm font-medium text-slate-700">Jam Masuk</label>
                    <input type="time" id="jam_masuk" wire:model.defer="jam_masuk"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>

                <div>
                    <label for="jam_pulang" class="block text-sm font-medium text-slate-700">Jam Pulang</label>
                    <input type="time" id="jam_pulang" wire:model.defer="jam_pulang"
                        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
            </div>

            <!-- Hari Wajib Absen -->
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Hari Wajib Absen</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-2">
                    @foreach ($list_hari as $hari)
                        <label class="flex items-center space-x-2 bg-slate-50 border border-slate-200 rounded p-2 text-sm cursor-pointer hover:bg-indigo-50">
                            <input type="checkbox" value="{{ $hari }}" wire:model.defer="hari_absen" class="form-checkbox text-indigo-600 rounded">
                            <span class="text-slate-800 text-xs font-medium">{{ $hari }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="text-xs text-slate-500 mt-1">Centang hari-hari yang mewajibkan karyawan untuk absen masuk & pulang.</div>
            </div>

            <!-- Nominal Potongan Jika Tidak Absen -->
            <div>
                <label for="nominal_potongan_tidak_absen" class="block text-sm font-medium text-slate-700">Nominal Potongan 1 Hari Tidak Absen (Masuk & Pulang)</label>
                <div class="relative mt-1 rounded-md shadow-sm max-w-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-gray-500 sm:text-sm">Rp</span>
                    </div>
                    <input type="number" id="nominal_potongan_tidak_absen" wire:model.defer="nominal_potongan_tidak_absen"
                        placeholder="Contoh: 50000"
                        class="block w-full rounded-md border-slate-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                </div>
                <div class="text-xs text-slate-500 mt-1">Potongan ini akan dikalikan dengan jumlah hari tidak absen di slip gaji karyawan.</div>
            </div>
        </div>
    @endif

    <div class="flex justify-end mt-5">
        <button wire:click="saveSetting" class="btn bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg">
            Simpan
        </button>
    </div>
</div>
