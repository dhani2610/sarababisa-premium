<div x-data="{ is_edit_produk: @entangle('is_edit_produk') }">
    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Akses Edit Produk</h3>
    <div class="text-sm">Dengan mengaktifkan ini maka akun admin/teknisi/sales bisa mengedit Produk.</div>
    <div class="flex items-center mt-3">
        <div class="form-switch">
            <input type="checkbox" id="toggleEditProduk" class="sr-only" wire:model="is_edit_produk"
    wire:change="saveSetting"
 />
            <label class="bg-slate-400" for="toggleEditProduk">
                <span class="bg-white shadow-sm" aria-hidden="true"></span>
                <span class="sr-only">Edit Produk</span>
            </label>
        </div>
        <div class="text-sm text-slate-400 italic ml-2" x-text="is_edit_produk ? 'Aktif' : 'Tidak Aktif'"></div>
    </div>
</div>