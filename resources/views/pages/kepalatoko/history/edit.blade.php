@section('title', 'Edit History Garansi')

<x-admin-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-5">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Edit History Garansi ✨</h1>
        </div>

        <div x-data="{ modalOpen: true }">
            <!-- backdrop -->
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                x-show="modalOpen" x-transition aria-hidden="true" x-cloak></div>

            <!-- modal -->
            <div id="edit-modal"
                class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                role="dialog" aria-modal="true" x-show="modalOpen" x-transition x-cloak>
                <div class="bg-white rounded shadow-lg overflow-auto max-w-2xl w-full max-h-full">

                    <form action="{{ route('history-garansi.update', $item->id) }}" method="POST" id="formEditGaransi">
                        @method('PUT')
                        @csrf

                        <div class="px-5 py-4 space-y-4">
                            {{-- error --}}
                            @if ($errors->any())
                                <div class="px-4 py-2 rounded-sm text-sm bg-rose-500 text-white">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Tanggal -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Tanggal</label>
                                <input type="date" name="date" class="form-input w-full"
                                    value="{{ $item->date }}" required>
                            </div>

                            <!-- Nomor Service -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Nomor Service</label>
                                <select name="service_id" class="form-select select2 w-full" required>
                                    @foreach ($serviceTransactions as $st)
                                        <option value="{{ $st->id }}"
                                            {{ $st->id == $item->service_id ? 'selected' : '' }}
                                            data-teknisi="{{ $st->user->name ?? '' }}"
                                            data-expired="{{ $st->exp_garansi }}">
                                            {{ $st->nomor_servis }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-slate-500">Teknisi sebelumnya: <span id="prev_teknisi"></span></small><br>
                                <small class="text-slate-500">Exp Garansi: <span id="exp_garansi"></span></small>
                            </div>

                            <!-- Penerima -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Penerima</label>
                                <select name="penerima_id" class="form-select select2 w-full" required>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}"
                                            {{ $u->id == $item->penerima_id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Teknisi -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Teknisi</label>
                                <select name="teknisi_id" class="form-select select2 w-full" required>
                                    @foreach ($users as $u)
                                        <option value="{{ $u->id }}"
                                            {{ $u->id == $item->teknisi_id ? 'selected' : '' }}>
                                            {{ $u->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tindakan -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Tindakan</label>
                                @php $tindakan_selected = json_decode($item->tindakan, true) ?? []; @endphp
                                <select name="tindakan[]" class="form-select select2 w-full" multiple>
                                    @foreach ($serviceActions as $action)
                                        <option value="{{ $action->id }}"
                                            {{ in_array($action->id, $tindakan_selected) ? 'selected' : '' }}>
                                            {{ $action->nama_tindakan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sparepart Dynamic -->
                            <div id="sparepart_wrapper">
                                <label class="block text-sm font-medium mb-1">Sparepart</label>
                                <button type="button" id="addRow" class="btn-sm bg-indigo-500 text-white">
                                    + Tambah Sparepart
                                </button>
                                <div class="mt-2" id="rowContainer">
                                    @php $spareparts = json_decode($item->sparepart, true) ?? []; @endphp
                                    @foreach ($spareparts as $sp)
                                        <div class="flex gap-2 mb-2 sparepart-row">
                                            <select name="sparepart[{{ $loop->index }}][id]" class="form-select select2 w-full">
                                                <option value="">-- Pilih Sparepart --</option>
                                                @foreach ($products as $prd)
                                                    <option value="{{ $prd->id }}"
                                                        {{ $prd->id == $sp['id'] ? 'selected' : '' }}
                                                        data-harga="{{ $prd->harga_modal }}">
                                                        {{ $prd->product_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="number" name="sparepart[{{ $loop->index }}][qty]" class="form-input qty w-24"
                                                value="{{ $sp['qty'] ?? 1 }}" min="1">
                                            <input type="number" name="sparepart[{{ $loop->index }}][harga]" class="form-input harga w-32"
                                                value="{{ $sp['harga'] ?? 0 }}" readonly>
                                            <button type="button" class="btn-sm bg-rose-500 text-white removeRow">X</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Total Biaya -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Total Biaya</label>
                                <input type="number" name="total_biaya" id="total_biaya" class="form-input w-full"
                                    value="{{ $item->total_biaya }}" readonly>
                            </div>

                            <!-- Catatan -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Catatan</label>
                                <textarea name="catatan" class="form-input w-full">{{ $item->catatan }}</textarea>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium mb-1">Status</label>
                                <select name="status" class="form-select w-full">
                                    <option value="1" {{ $item->status == 1 ? 'selected' : '' }}>Diproses</option>
                                    <option value="2" {{ $item->status == 2 ? 'selected' : '' }}>Selesai</option>
                                    <option value="3" {{ $item->status == 3 ? 'selected' : '' }}>Dibatalkan</option>
                                </select>
                            </div>
                        </div>

                        <!-- footer -->
                        <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                            <a href="{{ route('history-garansi.index') }}" class="btn-sm border-slate-200 text-slate-600">Batal</a>
                            <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>

    <!-- select2 + jquery -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            function hitungTotal() {
                let total = 0;
                $('#rowContainer .sparepart-row').each(function () {
                    let qty = parseInt($(this).find('.qty').val()) || 0;
                    let harga = parseInt($(this).find('.harga').val()) || 0;
                    total += qty * harga;
                });
                $('#total_biaya').val(total);
            }

            // inisialisasi select2
            $('.select2').select2({ width: '100%', dropdownParent: $('#edit-modal') });

            // tambah row sparepart
            $('#addRow').on('click', function () {
                let index = $('#rowContainer .sparepart-row').length;
                let newRow = `
                    <div class="flex gap-2 mb-2 sparepart-row">
                        <select name="sparepart[${index}][id]" class="form-select select2 w-full">
                            <option value="">-- Pilih Sparepart --</option>
                            @foreach ($products as $prd)
                                <option value="{{ $prd->id }}" data-harga="{{ $prd->harga }}">{{ $prd->name }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="sparepart[${index}][qty]" class="form-input qty w-24" value="1" min="1">
                        <input type="number" name="sparepart[${index}][harga]" class="form-input harga w-32" value="0" readonly>
                        <button type="button" class="btn-sm bg-rose-500 text-white removeRow">X</button>
                    </div>`;
                $('#rowContainer').append(newRow);
                $('#rowContainer .select2').last().select2({ width: '100%', dropdownParent: $('#edit-modal') });
            });

            // hapus row sparepart
            $(document).on('click', '.removeRow', function () {
                $(this).closest('.sparepart-row').remove();
                hitungTotal();
            });

            // update harga saat sparepart dipilih
            $(document).on('change', '.sparepart-row select', function () {
                let harga = $(this).find(':selected').data('harga') || 0;
                $(this).closest('.sparepart-row').find('.harga').val(harga);
                hitungTotal();
            });

            // update total saat qty berubah
            $(document).on('input', '.qty', function () {
                hitungTotal();
            });

            // jalankan awal
            hitungTotal();
        });
    </script>
</x-admin-layout>
