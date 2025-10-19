<div x-data>
    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Integrasi Telegram</h3>
    <div class="text-sm mb-3">Masukkan Token Bot, Chat Group ID, dan jam laporan harian.</div>
    <div class="text-sm mb-3">Lihat vidio tutorial <a href="{{ asset('vidio/Pengaturan Toko.mp4') }}" target="_blank" rel="noopener noreferrer" style="color: blue">Klik disini</a> </div>

    <div class="space-y-4">
        <!-- Token Bot -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Token Bot</label>
            <input type="text" wire:model.defer="token_bot" class="form-input w-full" placeholder="Masukkan Token Bot Telegram...">
        </div>

        <!-- Chat Group ID -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Chat Group ID</label>
            <input type="text" wire:model.defer="chat_id" class="form-input w-full" placeholder="Masukkan Chat Group ID...">
        </div>

        <!-- Jam Kirim Otomatis -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Jam Kirim Laporan Harian</label>
            <input type="time" wire:model.defer="report_time" class="form-input w-full">
            <p class="text-xs text-slate-500 mt-1">Kosongkan jika tidak ingin laporan otomatis.</p>
        </div>

        <!-- Tombol Simpan -->
        <div class="flex justify-end">
            <button wire:click="saveSetting" class="btn bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg">
                Simpan
            </button>
        </div>
    </div>
</div>
