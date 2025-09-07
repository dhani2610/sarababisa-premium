<div>
    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Penerapan Pajak</h3>
    <div class="text-sm">
        Dengan mengaktifkan penerapan pajak maka menu inputan dan perhitungan pajak akan dimunculkan pada sistem.
    </div>

    <div class="flex items-center mt-3">
        <div class="form-switch">
            <input type="checkbox" id="toggleTax" class="sr-only" wire:model="taxApplied" />
            <label class="bg-slate-400" for="toggleTax">
                <span class="bg-white shadow-sm" aria-hidden="true"></span>
                <span class="sr-only">Penerapan Pajak</span>
            </label>
        </div>
        <div class="text-sm text-slate-400 italic ml-2">
            {{ $taxApplied ? 'Aktif' : 'Tidak Aktif' }}
        </div>
    </div>

    @if ($taxApplied)
        <div class="mt-3">
            <label for="ppn" class="block text-sm font-medium text-slate-700">Persentase PPN (%)</label>
            <input type="number" id="ppn" wire:model="ppn"
                class="mt-1 block w-32 rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="10" />
        </div>
    @endif

</div>
