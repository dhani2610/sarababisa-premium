<div x-data>
    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">
        Pengaturan Potongan Izin / Alfa / Sakit
    </h3>
    <div class="text-sm mb-3 text-slate-600">
        Atur nominal potongan gaji untuk masing-masing jenis izin karyawan.
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Potongan Izin -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Potongan Izin</label>
            <input
                type="text"
                wire:model.defer="nominal_potongan_izin"
                class="form-input w-full sapator"
                placeholder="0"
            >
        </div>

        <!-- Potongan Alfa -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Potongan Alfa</label>
            <input
                type="text"
                wire:model.defer="nominal_potongan_alfa"
                class="form-input w-full sapator"
                placeholder="0"
            >
        </div>

        <!-- Potongan Sakit -->
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Potongan Sakit</label>
            <input
                type="text"
                wire:model.defer="nominal_potongan_sakit"
                class="form-input w-full sapator"
                placeholder="0"
            >
        </div>
    </div>

    <div class="flex justify-end mt-5">
        <button
            wire:click="saveSetting"
            class="btn bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg"
        >
            Simpan
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function formatNumber(input) {
        let value = input.value.replace(/\D/g, '');
        input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    document.querySelectorAll('.sapator').forEach(input => {
        input.addEventListener('keyup', () => formatNumber(input));
        formatNumber(input); // format saat load pertama
    });
});
</script>
