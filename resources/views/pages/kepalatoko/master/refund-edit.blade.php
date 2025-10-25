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
                <label class="block text-sm font-medium mb-1">Nominal</label>
                <input name="nominal" type="number" class="form-input w-full" value="{{ $item->nominal }}" required />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Teknisi</label>
                <input id="teknisi_name_edit" type="text" class="form-input w-full" readonly value="{{ optional($item->teknisi)->name }}" />
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
document.addEventListener('DOMContentLoaded', function () {
    const servisSelect = document.getElementById('servis_select_edit');
    const teknisiInput = document.getElementById('teknisi_name_edit');

    if (servisSelect) {
        servisSelect.addEventListener('change', function () {
            const id = this.value;
            teknisiInput.value = '';
            if (!id) return;

            fetch('{{ url("kepalatoko/refund/service") }}/' + id)
                .then(res => res.json())
                .then(data => {
                    teknisiInput.value = data.teknisi_name ?? '';
                }).catch(err => console.error(err));
        });
    }
});
</script>
