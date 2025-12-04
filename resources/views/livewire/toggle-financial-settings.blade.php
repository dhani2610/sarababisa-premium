<div class="space-y-6">
    <section>
        <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Pengaturan Akses</h3>
        <div class="text-sm text-slate-500">
            Mengatur akses profit, modal, dan bonus Servis dan Penjualan Produk untuk Admin/Teknisi/Sales.
        </div>
    </section>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
            <h4 class="text-md font-bold text-slate-700 mb-4 border-b pb-2">Transaksi Servis</h4>

            <div class="flex items-center justify-between mb-4">
                <div class="text-sm text-slate-600">Tampilkan Profit Servis</div>
                <div class="flex items-center">
                    <div class="form-switch">
                        <input type="checkbox" id="is_profit" class="sr-only" wire:model="is_profit" />
                        <label class="bg-slate-400" for="is_profit">
                            <span class="bg-white shadow-sm" aria-hidden="true"></span>
                            <span class="sr-only">Switch</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mb-4">
                <div class="text-sm text-slate-600">Tampilkan Modal Servis</div>
                <div class="flex items-center">
                    <div class="form-switch">
                        <input type="checkbox" id="is_modal" class="sr-only" wire:model="is_modal" />
                        <label class="bg-slate-400" for="is_modal">
                            <span class="bg-white shadow-sm" aria-hidden="true"></span>
                            <span class="sr-only">Switch</span>
                        </label>
                    </div>
                </div>
            </div>

             <div class="flex items-center justify-between">
                <div class="text-sm text-slate-600">Tampilkan Bonus Servis</div>
                <div class="flex items-center">
                    <div class="form-switch">
                        <input type="checkbox" id="is_bonus" class="sr-only" wire:model="is_bonus" />
                        <label class="bg-slate-400" for="is_bonus">
                            <span class="bg-white shadow-sm" aria-hidden="true"></span>
                            <span class="sr-only">Switch</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
            <h4 class="text-md font-bold text-slate-700 mb-4 border-b pb-2">Transaksi Produk</h4>

            <div class="flex items-center justify-between mb-4">
                <div class="text-sm text-slate-600">Tampilkan Profit Produk</div>
                <div class="flex items-center">
                    <div class="form-switch">
                        <input type="checkbox" id="is_profit_produk" class="sr-only" wire:model="is_profit_produk" />
                        <label class="bg-slate-400" for="is_profit_produk">
                            <span class="bg-white shadow-sm" aria-hidden="true"></span>
                            <span class="sr-only">Switch</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mb-4">
                <div class="text-sm text-slate-600">Tampilkan Modal Produk</div>
                <div class="flex items-center">
                    <div class="form-switch">
                        <input type="checkbox" id="is_modal_produk" class="sr-only" wire:model="is_modal_produk" />
                        <label class="bg-slate-400" for="is_modal_produk">
                            <span class="bg-white shadow-sm" aria-hidden="true"></span>
                            <span class="sr-only">Switch</span>
                        </label>
                    </div>
                </div>
            </div>

             <div class="flex items-center justify-between">
                <div class="text-sm text-slate-600">Tampilkan Bonus Produk</div>
                <div class="flex items-center">
                    <div class="form-switch">
                        <input type="checkbox" id="is_bonus_produk" class="sr-only" wire:model="is_bonus_produk" />
                        <label class="bg-slate-400" for="is_bonus_produk">
                            <span class="bg-white shadow-sm" aria-hidden="true"></span>
                            <span class="sr-only">Switch</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
