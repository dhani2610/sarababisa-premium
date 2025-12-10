<section class="bg-slate-50 p-4 rounded-lg border border-slate-200">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-md font-bold text-slate-700">Pembatasan Jam Operasional</h3>
            <div class="text-sm text-slate-500">
                Jika aktif, sistem akan mengunci aksi input data di luar jam kerja.
            </div>
        </div>

        <div class="flex items-center">
            <div class="form-switch">
                <input type="checkbox" id="is_close_toggle" class="sr-only" wire:model="is_close" />
                <label class="bg-slate-400" for="is_close_toggle">
                    <span class="bg-white shadow-sm" aria-hidden="true"></span>
                    <span class="sr-only">Switch</span>
                </label>
            </div>
            <div class="text-sm text-slate-400 italic ml-2">
                {{ $is_close ? 'Aktif' : 'Non-Aktif' }}
            </div>
        </div>
    </div>

    <div x-data="{ open: @entangle('is_close') }"
         x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 border-t pt-4 border-slate-200">

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="time_open">Jam Buka Toko</label>
            <input id="time_open" class="form-input w-full border-slate-300" type="time" wire:model.lazy="time_open_toko" />
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1" for="time_close">Jam Tutup Toko</label>
            <input id="time_close" class="form-input w-full border-slate-300" type="time" wire:model.lazy="time_close_toko" />
        </div>

    </div>
</section>
