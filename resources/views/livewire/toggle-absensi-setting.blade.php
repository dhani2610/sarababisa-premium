<div>
    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Pengaturan Absensi</h3>
    <div class="text-sm mb-3">
        Aktifkan sistem absensi dan atur jam masuk serta jam pulang karyawan.
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
            <div>
                <label for="jam_masuk" class="block text-sm font-medium text-slate-700">Jam Masuk</label>
                <input type="time" id="jam_masuk" wire:model.defer="jam_masuk"
                    class="mt-1 block w-40 rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>

            <div>
                <label for="jam_pulang" class="block text-sm font-medium text-slate-700">Jam Pulang</label>
                <input type="time" id="jam_pulang" wire:model.defer="jam_pulang"
                    class="mt-1 block w-40 rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
            </div>
        </div>
    @endif

    <div class="flex justify-end mt-5">
        <button wire:click="saveSetting" class="btn bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg">
            Simpan
        </button>
    </div>
</div>
