<div x-data>
    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Integrasi Telegram</h3>
    <div class="text-sm mb-3">Masukkan Token Bot dan Chat Group ID Telegram untuk notifikasi otomatis.</div>

    <div class="space-y-4">
        <!-- Token Bot -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="token_bot">Token Bot</label>
            <input type="text" id="token_bot" wire:model.defer="token_bot"
                class="form-input w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Masukkan Token Bot Telegram...">
        </div>

        <!-- Chat Group ID -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="chat_id">Chat Group ID</label>
            <input type="text" id="chat_id" wire:model.defer="chat_id"
                class="form-input w-full border border-slate-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="Masukkan Chat Group ID...">
        </div>

        <!-- Tombol Simpan -->
        <div class="flex justify-end">
            <button wire:click="saveSetting"
                class="btn bg-indigo-500 hover:bg-indigo-600 text-white mt-2 px-4 py-2 rounded-lg transition">
                Simpan
            </button>
        </div>
    </div>
</div>
