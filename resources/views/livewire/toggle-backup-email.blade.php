<div x-data class="pt-6 border-t border-slate-200">
    <div class="flex items-center space-x-2 mb-1">
        <svg class="w-5 h-5 text-indigo-500 fill-current" viewBox="0 0 20 20">
            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
        </svg>
        <h3 class="text-xl leading-snug text-slate-800 font-bold">Email Penerima Backup Database</h3>
    </div>
    <div class="text-sm text-slate-600 mb-3">
        Setiap kali Kepala Toko melakukan backup modul, salinan file data (<code>.sql</code>) akan otomatis dikirimkan ke email ini.
    </div>

    <div class="space-y-3">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Email Penerima</label>
            <div class="flex flex-col sm:flex-row gap-2 max-w-xl">
                <input type="email" wire:model.defer="backup_email" class="form-input flex-1" placeholder="contoh: admin@perusahaan.com">
                <button type="button" wire:click="saveSetting" class="btn bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg shrink-0">
                    Simpan Email
                </button>
            </div>
            @error('backup_email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            <p class="text-xs text-slate-500 mt-1">Kosongkan jika hanya ingin backup langsung di-download tanpa dikirim email.</p>
        </div>
    </div>
</div>
