@section('title', 'Transfer Stok')

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        {{-- Header --}}
        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Transfer Stok ✨</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max gap-2">

                {{-- Button Cetak --}}
                <div x-data="{ modalOpen: false }">
                    <button
                        class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500 hover:text-slate-600"
                        @click="modalOpen = true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2">
                            <path d="M17 17h2a2 2 0 0 0 2-2v-4a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v4a2 2 0 0 0 2 2h2"/>
                            <path d="M17 9V5a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4"/>
                            <rect x="7" y="13" width="10" height="8" rx="2"/>
                        </svg>
                    </button>

                    {{-- Modal Cetak --}}
                    <div x-show="modalOpen" x-cloak>
                        <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50"></div>
                        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                            <div class="bg-white rounded shadow-lg w-full max-w-md">
                                <div class="px-5 py-3 border-b flex justify-between">
                                    <div class="font-semibold">Cetak Laporan</div>
                                    <button @click="modalOpen = false">✕</button>
                                </div>

                                <form action="{{ route('transfer-stok.cetak') }}" method="get" target="_blank">
                                    <div class="px-5 py-4 space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium">Mulai Tanggal</label>
                                            <input type="date" name="start_date" class="form-input w-full" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium">Sampai Tanggal</label>
                                            <input type="date" name="end_date" class="form-input w-full" required />
                                        </div>
                                    </div>

                                    <div class="px-5 py-4 border-t flex justify-end">
                                        <button class="btn-sm bg-indigo-500 text-white">Cetak</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Button Tambah --}}
                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white"
                        @click="modalOpen = true">
                        + Tambah Transfer
                    </button>

                    {{-- Modal Tambah --}}
                    <div x-show="modalOpen" x-cloak>
                        <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50"></div>
                        <div class="fixed inset-0 flex items-center justify-center z-50 p-4">
                            <div class="bg-white rounded shadow-lg w-full max-w-lg">

                                <div class="px-5 py-3 border-b flex justify-between">
                                    <div class="font-semibold">Tambah Transfer Stok</div>
                                    <button @click="modalOpen = false">✕</button>
                                </div>

                                <form action="{{ route('transfer-stok.store') }}" method="post">
                                    @csrf
                                    <div class="px-5 py-4  gap-3">

                                        <div class="mb-3" >
                                            <label>Dari Cabang</label>
                                            <select id="dari_cabang_id" name="dari_cabang_id" class="form-select w-full" required>
                                                <option value="">-- Pilih Cabang --</option>
                                                @foreach(\App\Models\Cabang::orderBy('nama_cabang')->get() as $c)
                                                    <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3" >
                                            <label>Ke Cabang</label>
                                            <select id="ke_cabang_id" name="ke_cabang_id" class="form-select w-full" required>
                                                <option value="">-- Pilih Cabang --</option>
                                                @foreach(\App\Models\Cabang::orderBy('nama_cabang')->get() as $c)
                                                    <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3" >
                                            <label>Produk Asal</label>
                                            <select id="dari_produk_id" name="dari_produk_id" class="form-select w-full" required>
                                                <option value="">-- Pilih Produk --</option>
                                                {{-- options akan terisi via AJAX --}}
                                            </select>
                                        </div>

                                        <div class="mb-3" >
                                            <label>Produk Tujuan (opsional)</label>
                                            <select id="ke_produk_id" name="ke_produk_id" class="form-select w-full">
                                                <option value="">-- Opsional --</option>
                                                {{-- options via AJAX berdasarkan ke_cabang_id --}}
                                            </select>
                                        </div>

                                        <div class="mb-3" >
                                            <label>Jumlah Stok</label>
                                            <input type="number" name="stok" id="stok_input" class="form-input w-full" required>
                                            <small id="stok_warning" class="text-rose-500 hidden">Stok melebihi stok asal!</small>
                                        </div>

                                        <div class="mb-3" >
                                            <label>Tanggal</label>
                                            <input type="date" name="tanggal" class="form-input w-full" required value="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>

                                    <div class="px-5 py-4 border-t flex justify-end gap-2">
                                        <button type="button" class="btn-sm border" @click="modalOpen=false">Batal</button>
                                        <button class="btn-sm bg-indigo-500 text-white">Simpan</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8" x-data="handleSelect()">
            <div class="px-5 py-4 flex justify-between items-center">
                <h2 class="font-semibold text-slate-800">
                    Data Transfer <span class="text-slate-400">{{ $transfers->total() }}</span>
                </h2>

                <div class="table-items-action hidden">
                    <div class="flex items-center">
                        <span class="table-items-count mr-2"></span> dipilih
                        <button @click="deleteSelected" class="btn bg-white border-slate-200 text-rose-500 ml-2">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="text-center px-2 py-3">
                                <input id="parent-checkbox" type="checkbox" @click="toggleAll">
                            </th>
                            <th class="text-center px-2 py-3">No</th>
                            <th class="text-center px-2 py-3">Tanggal</th>
                            <th class="text-center px-2 py-3">Dari Cabang</th>
                            <th class="text-center px-2 py-3">Produk Asal</th>
                            <th class="text-center px-2 py-3">Ke Cabang</th>
                            <th class="text-center px-2 py-3">Produk Tujuan</th>
                            <th class="text-center px-2 py-3">Stok</th>
                            <th class="text-center px-2 py-3">Status</th>
                            <th class="text-center px-2 py-3">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="text-sm divide-y">

                        @php $i = ($transfers->currentPage()-1)*$transfers->perPage()+1; @endphp

                        @foreach($transfers as $t)
                        <tr class="hover:bg-slate-50">

                            {{-- Checkbox --}}
                            <td class="text-center px-3 py-3">
                                <input class="table-item" type="checkbox" value="{{ $t->id }}" @click="uncheckParent">
                            </td>

                            {{-- No --}}
                            <td class="text-center px-3 py-3 font-medium">
                                {{ $i++ }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="text-center px-3 py-3">
                                {{ \Carbon\Carbon::parse($t->tanggal)->format('d-m-Y') }}
                            </td>

                            {{-- Dari Cabang --}}
                            <td class="px-3 py-3 text-left">
                                <div class="font-semibold text-slate-800">{{ optional($t->dariCabang)->nama_cabang }}</div>
                            </td>
                            <td class="px-3 py-3 text-left">
                                <div class=" text-slate-500">Produk : {{ optional($t->dariProduk)->product_name }}</div>
                            </td>

                            {{-- Ke Cabang --}}
                            <td class="px-3 py-3 text-left">
                                <div class="font-semibold text-slate-800">{{ optional($t->keCabang)->nama_cabang }}</div>
                            </td>
                            {{-- Ke Cabang --}}
                            <td class="px-3 py-3 text-left">
                                <div class=" text-slate-500">Produk : {{ optional($t->keProduk)->product_name }}</div>
                            </td>

                            {{-- Stok --}}
                            <td class="text-center px-3 py-3 font-semibold">
                                {{ $t->stok }}
                            </td>

                            {{-- Status --}}
                            <td class="text-center px-3 py-3">
                                @if ($t->status == 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-amber-100 text-amber-700">
                                        Menunggu Persetujuan
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-700">
                                        Disetujui
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="text-center px-3 py-3 space-x-1">

                                {{-- APPROVE --}}
                                @if ($t->status == 0 && Auth::user()->role == 'Kepala Toko')
                                <form action="{{ route('transfer-stok.approve', $t->id) }}"
                                    method="POST"
                                    class="inline-block">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 rounded-lg bg-green-500 text-white text-xs hover:bg-green-600 transition flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve
                                    </button>
                                </form>
                                @endif

                                {{-- DELETE --}}
                                <form action="{{ route('transfer-stok.destroy', $t->id) }}"
                                    method="POST"
                                    class="inline-block"
                                    onsubmit="return confirm('Yakin hapus transfer ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        class="px-3 py-1.5 rounded-lg bg-rose-500 text-white text-xs hover:bg-rose-600 transition flex items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Hapus
                                    </button>
                                </form>

                            </td>

                        </tr>
                        @endforeach

                    </tbody>

                </table>
            </div>

            <div class="p-4">
                {{ $transfers->links() }}
            </div>
        </div>
    </div>
    <script>
document.addEventListener('alpine:init', () => {
    Alpine.data('handleSelect', () => ({
        selectall: false,
        selectAction() {
            const box = document.querySelector('.table-items-action');
            const checked = document.querySelectorAll('input.table-item:checked');
            document.querySelector('.table-items-count').innerHTML = checked.length;
            checked.length ? box.classList.remove('hidden') : box.classList.add('hidden');
        },
        toggleAll() {
            const checkboxes = document.querySelectorAll('input.table-item');
            this.selectall = !this.selectall;
            checkboxes.forEach(cb => cb.checked = this.selectall);
            this.selectAction();
        },
        uncheckParent() {
            document.getElementById('parent-checkbox').checked = false;
            this.selectall = false;
            this.selectAction();
        },
        deleteSelected() {
            const ids = [...document.querySelectorAll('input.table-item:checked')].map(cb=>cb.value);
            if(!ids.length) return alert('Tidak ada dipilih');

            if(!confirm('Hapus data terpilih?')) return;

            fetch("{{ route('transfer-stok.deleteSelected') }}",{
                method:'POST',
                headers:{
                    'Content-Type':'application/json',
                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                },
                body: JSON.stringify({selectedIds: ids})
            }).then(r=>r.json()).then(res=>{
                alert(res.message);
                location.reload();
            });
        }
    }))
})
</script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Script Kamu -->
<script>
$(function() {

    function loadProducts(cabangId, $targetSelect, placeholder = '-- Pilih Produk --') {
        $targetSelect.prop('disabled', true).html(`<option>${placeholder}</option>`);
        if (!cabangId) {
            $targetSelect.prop('disabled', false);
            return;
        }
        $.get('/api/products-by-cabang/' + cabangId)
            .done(function (data) {
                let opts = `<option value="">${placeholder}</option>`;
                data.forEach(p => {
                    opts += `<option value="${p.id}" data-stok="${p.stok}">${p.product_name} (stok: ${p.stok})</option>`;
                });
                $targetSelect.html(opts).prop('disabled', false).trigger('change');
            })
            .fail(function(){
                $targetSelect.html(`<option value="">Gagal mengambil produk</option>`).prop('disabled', false);
            });
    }

    // saat pilih dari_cabang, muat produk asal
    $('#dari_cabang_id').on('change', function(){
        const id = $(this).val();
        loadProducts(id, $('#dari_produk_id'), '-- Pilih Produk Asal --');
    });

    // saat pilih ke_cabang, muat produk tujuan
    $('#ke_cabang_id').on('change', function(){
        const id = $(this).val();
        loadProducts(id, $('#ke_produk_id'), '-- Pilih Produk Tujuan (opsional) --');
    });

    // validasi jumlah stok
    $(document).on('change', '#dari_produk_id', function(){
        const stok = $(this).find(':selected').data('stok') || 0;
        $('#stok_input').attr('max', stok);
        if (parseInt($('#stok_input').val() || 0) > stok) {
            $('#stok_warning').removeClass('hidden');
        } else {
            $('#stok_warning').addClass('hidden');
        }
    });

    $(document).on('input', '#stok_input', function(){
        const stokSel = $('#dari_produk_id').find(':selected').data('stok') || 0;
        if (parseInt($(this).val() || 0) > stokSel) {
            $('#stok_warning').removeClass('hidden');
        } else {
            $('#stok_warning').addClass('hidden');
        }
    });

    // Init Select2
    $('#dari_produk_id, #ke_produk_id').select2({
        width: '100%'      // biar select2 mengikuti parent (tidak over)
    });

});
</script>


</x-toko-layout>
