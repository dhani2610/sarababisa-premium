<div x-data="{ is_edit_transaksi: @entangle('is_edit_transaksi') }">
    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Akses Edit Transaksi</h3>
    <div class="text-sm">Dengan mengaktifkan ini maka akun admin/teknisi/sales tidak bisa mengedit transaksi yang sudah lewat tanggal transaksi.</div>
    <div class="flex items-center mt-3">
        <div class="form-switch">
            <input type="checkbox" id="toggleEditTransaksi" class="sr-only" wire:model="is_edit_transaksi"
    wire:change="saveSetting"
 />
            <label class="bg-slate-400" for="toggleEditTransaksi">
                <span class="bg-white shadow-sm" aria-hidden="true"></span>
                <span class="sr-only">Informasi Bonus</span>
            </label>
        </div>
        <div class="text-sm text-slate-400 italic ml-2" x-text="is_edit_transaksi ? 'Aktif' : 'Tidak Aktif'"></div>
    </div>
</div>