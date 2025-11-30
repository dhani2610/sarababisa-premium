<div>
    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Informasi Modal</h3>
    <div class="text-sm">Dengan meangktifkan ini maka informasi modal pada akun Admin, Teknisi, Sales akan dimunculkan pada sistem.</div>
    <div class="flex items-center mt-3">
        <div class="form-switch">
            <input type="checkbox" id="toggleModal" class="sr-only" wire:model="modalApplied" />
            <label class="bg-slate-400" for="toggleModal">
                <span class="bg-white shadow-sm" aria-hidden="true"></span>
                <span class="sr-only">Informasi Modal</span>
            </label>
        </div>
        <div class="text-sm text-slate-400 italic ml-2" x-text="modalApplied ? 'Aktif' : 'Tidak Aktif'"></div>
    </div>
</div>