@section('title','Edit Refund')

<x-toko-layout>
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Edit Refund</h1>

    <form action="{{ route('refund.update', $item->id) }}" method="post">
        @csrf
        @method('put')

        <div class="space-y-4 bg-white p-4 rounded shadow">
            <div>
                <label class="block text-sm font-medium mb-1">Nomor Servis</label>
                <select id="servis_select_edit" name="servis_transaction_id" class="form-select w-full" required>
                    <option value="">-- Pilih Nomor Servis --</option>
                    @foreach($servis as $s)
                        <option value="{{ $s->id }}" @if($s->id == $item->servis_transaction_id) selected @endif>#{{ $s->nomor_servis }}</option>
                    @endforeach
                </select>
            </div>


            <div>
                <label class="block text-sm font-medium mb-1">Nominal Potongan Bonus Teknisi<span
                        class="text-rose-500">*</span></label>
                <input name="nominal" type="number" id="nominal_input" value="{{ $item->nominal }}"
                    class="form-input w-full disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" readonly style="background: rgb(223, 221, 221)" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Pengembalian Biaya<span
                        class="text-rose-500">*</span></label>
                <input name="nominal_servis" type="text" id="nominal_input_servis" value="{{ $item->nominal_servis }}"
                    class="form-input w-full input-currency disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:cursor-not-allowed" readonly style="background: rgb(223, 221, 221)" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Teknisi</label>
                <input id="teknisi_name_edit" type="text" class="form-input w-full" style="background: rgb(223, 221, 221)" readonly value="{{ optional($item->teknisi)->name }}" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Bulan/Tahun Potongan</label>
                <input name="period" id="period_input_edit" type="month" class="form-input w-full" value="{{ $item->period ? $item->period->format('Y-m') : '' }}" />
            </div>

            <div class="flex justify-end space-x-2">
                <a href="{{ route('refund.index') }}" class="btn-sm border-slate-200 text-slate-600">Batal</a>
                <button class="btn-sm bg-indigo-500 text-white">Simpan</button>
            </div>
        </div>
    </form>
</div>
</x-toko-layout>

@push('scripts')
<script>
function formatRupiahInput(el) {
    let value = el.value || '';

    // hapus semua selain angka
    value = value.replace(/\D/g, '');

    // format pakai titik
    el.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

/* ON INPUT (REAL TIME) */
document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('input-currency')) return;
    formatRupiahInput(e.target);
});

/* ON LOAD (FORM EDIT) */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.input-currency').forEach(function (el) {
        formatRupiahInput(el);
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const servisSelect = document.getElementById('servis_select_edit');
    const teknisiInput = document.getElementById('teknisi_name_edit');
    const NominalInputServis = document.getElementById('nominal_input_servis');
    const NominalInput = document.getElementById('nominal_input');

    if (servisSelect) {
        servisSelect.addEventListener('change', function () {
            const id = this.value;
            teknisiInput.value = '';
            if (!id) return;

            fetch('{{ url("refund/service") }}/' + id)
                .then(res => res.json())
                .then(data => {
                    teknisiInput.value = data.teknisi_name ?? '';
                    NominalInput.value = data.nominal ?? 0;
                    console.log(data.nominal);

                    formatRupiahInput(NominalInput);

                    NominalInputServis.value = data.nominal_servis ?? 0;
                    formatRupiahInput(NominalInputServis);

                }).catch(err => console.error(err));
        });
    }
});

</script>
