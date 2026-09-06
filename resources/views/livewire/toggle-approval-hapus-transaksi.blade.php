<div x-data="{ approval_hapus_transaksi: @entangle('approval_hapus_transaksi') }">
    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Approval Hapus Transaksi</h3>
    <div class="text-sm">Jika aktif, Teknisi, Sales, dan Admin yang ingin menghapus transaksi Servis atau POS harus meminta persetujuan Kepala Toko terlebih dahulu.</div>
    <div class="flex items-center mt-3">
        <div class="form-switch">
            <input type="checkbox" id="toggleApprovalHapusTransaksi" class="sr-only" wire:model="approval_hapus_transaksi" wire:change="saveSetting" />
            <label class="bg-slate-400" for="toggleApprovalHapusTransaksi">
                <span class="bg-white shadow-sm" aria-hidden="true"></span>
                <span class="sr-only">Approval Hapus Transaksi</span>
            </label>
        </div>
        <div class="text-sm text-slate-400 italic ml-2" x-text="approval_hapus_transaksi ? 'Aktif (Harus Izin Kepala Toko)' : 'Tidak Aktif (Bisa Hapus Langsung)'"></div>
    </div>
</div>
