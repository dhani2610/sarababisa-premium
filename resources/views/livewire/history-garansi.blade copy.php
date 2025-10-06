<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">History Garansi Service ✨</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            <!-- Search form -->
            <x-search-form placeholder="Cari berdasarkan nama Nomor Service" />

            <!-- Create invoice button -->
            <div x-data="{ modalOpen: false }">
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                    + Tambah History Garansi
                </button>

                <!-- Modal backdrop -->
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                    x-transition aria-hidden="true" x-cloak></div>

                <!-- Modal dialog -->
                <div id="tambah-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog" aria-modal="true" x-show="modalOpen" x-transition x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-2xl w-full max-h-full"
                        @click.outside="if(!$event.target.closest('.select2-container')) modalOpen = false"
                        @keydown.escape.window="modalOpen = false">

                        <form action="{{ route('history-garansi.store') }}" method="POST" id="formGaransi">
                            @csrf
                            <div class="px-5 py-4 space-y-4">


                                <!-- Penerima -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Tanggal <span
                                            class="text-rose-500">*</span></label>
                                    <input type="date" name="date" id="date" class="form-input w-full"
                                        required>
                                </div>

                                <!-- Pilih Service -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Nomor Service <span
                                            class="text-rose-500">*</span></label>
                                    <select name="service_id" id="service_id" class="form-select select2 w-full"
                                        required>
                                        <option value="">-- Pilih Nomor Service --</option>
                                        @foreach ($serviceTransactions as $st)
                                            <option value="{{ $st->id }}"
                                                data-teknisi="{{ $st->user->name ?? '' }}"
                                                data-expired="{{ $st->exp_garansi }}">
                                                {{ $st->nomor_servis }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-slate-500">Teknisi sebelumnya: <span
                                            id="prev_teknisi"></span></small><br>
                                    <small class="text-slate-500">Exp Garansi: <span id="exp_garansi"></span></small>
                                </div>

                                <!-- Penerima -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Penerima <span
                                            class="text-rose-500">*</span></label>
                                    <select name="penerima_id" class="form-select w-full select2" required>
                                        <option value="">-- Pilih Penerima --</option>
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Teknisi -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Teknisi<span
                                            class="text-rose-500">*</span></label>
                                    <select name="teknisi_id" class="form-select w-full select2" required>
                                        <option value="">-- Pilih Teknisi --</option>
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tindakan -->
                                <div class="mb-3">
                                    <label for="tindakan">Tindakan</label>
                                    <select id="tindakan" name="tindakan[]" class="form-control select2" multiple>
                                        @foreach ($serviceActions as $action)
                                            <option value="{{ $action->id }}">{{ $action->nama_tindakan }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="manualTindakanCheckbox">
                                    <label class="form-check-label" for="manualTindakanCheckbox">
                                        Tambah Tindakan Manual
                                    </label>
                                </div>

                                <!-- Dynamic input manual tindakan -->
                                <div id="manualTindakanContainer" style="display: none;">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Tindakan Manual</th>
                                                <th><button type="button" id="addManualRow"
                                                        class="btn btn-sm btn-success bg-danger">+</button></th>
                                            </tr>
                                        </thead>
                                        <tbody id="manualTindakanBody"></tbody>
                                    </table>
                                </div>

                                <!-- Checkbox sebelum sparepart -->
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="useSparepartCheckbox">
                                    <label class="form-check-label" for="useSparepartCheckbox">
                                        Apakah menggunakan stok sparepart toko?
                                    </label>
                                </div>
                                <!-- Sparepart Dynamic -->
                                <div id="sparepart_wrapper" style="display:none;">
                                    <label class="block text-sm font-medium mb-1">Sparepart</label>
                                    <button type="button" id="addSparepartRow" class="btn-sm bg-indigo-500 text-white">
                                        + Tambah Sparepart
                                    </button>
                                    <div class="mt-2" id="rowContainer"></div>
                                </div>

                                <!-- Total Biaya -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Total Biaya<span
                                            class="text-rose-500">*</span></label>
                                    <input type="number" name="total_biaya" id="total_biaya" value="0" class="form-input w-full">
                                </div>

                                <!-- Catatan -->
                                <div>
                                    <label class="block text-sm font-medium mb-1">Catatan<span
                                            class="text-rose-500">*</span></label>
                                    <textarea name="catatan" class="form-input w-full" required></textarea>
                                </div>

                            </div>
                            <div class="px-5 py-4 border-t flex justify-end space-x-2">
                                <button type="button" class="btn-sm border-slate-200"
                                    @click="modalOpen=false">Batal</button>
                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


        </div>

    </div>

    <!-- More actions -->
    <div class="sm:flex sm:justify-between sm:items-center mb-5">
        <!-- Left side -->
        <div class="mb-0">
            <select wire:model="paginate" id="" class="form-select">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
    </div>

    @if ($errors->any())
        <div x-show="open" x-data="{ open: true }">
            <div class="px-4 py-2 rounded-sm text-sm bg-rose-500 text-white">
                <div class="flex w-full justify-between items-start">
                    <div class="flex">
                        <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                            <path
                                d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                        </svg>
                        @foreach ($errors->all() as $error)
                            <div class="font-medium">{{ $error }}</div>
                        @endforeach
                    </div>
                    <button class="opacity-70 hover:opacity-80 ml-3 mt-[3px]" @click="open = false">
                        <div class="sr-only">Close</div>
                        <svg class="w-4 h-4 fill-current">
                            <path
                                d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                {{-- Left side --}}
                <h2 class="font-semibold text-slate-800">Semua History Service <span
                        class="text-slate-400 font-medium">{{ $count }}</span></h2>
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span>
                                item yang dipilih</div>
                            <button
                                class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600"
                                @click="deleteSelected">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
            {{-- @dd($data); --}}
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead
                        class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 py-3">No.</th>
                            <th class="px-2 py-3">Tanggal</th>
                            <th class="px-2 py-3">Nomor Servis</th>
                            <th class="px-2 py-3">Customer</th>
                            <th class="px-2 py-3">Penerima</th>
                            <th class="px-2 py-3">Teknisi</th>
                            <th class="px-2 py-3">Tindakan</th>
                            <th class="px-2 py-3">Sparepart</th>
                            <th class="px-2 py-3">Total Biaya</th>
                            <th class="px-2 py-3">Catatan</th>
                            <th class="px-2 py-3">Status</th>
                            <th class="px-2 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @php $i = 1; @endphp
                        @foreach ($data as $item)
                            <tr>
                                <td class="px-2 py-3">{{ $i++ }}</td>
                                <td class="px-2 py-3">{{ $item->date }}</td>
                                <td class="px-2 py-3">{{ $item->service->nomor_servis ?? $item->service_id }}</td>
                                <td class="px-2 py-3">{{ $item->service->customer->nama ?? '-' }}</td>
                                <td class="px-2 py-3">{{ $item->penerima->name ?? '-' }}</td>
                                <td class="px-2 py-3">{{ $item->teknisi->name ?? '-' }}</td>

                                {{-- Tindakan --}}
                                <td class="px-2 py-3">
                                    @php
                                        $tindakans = json_decode($item->tindakan, true) ?? [];
                                    @endphp

                                    <ul class="list-disc ml-4">
                                        @foreach ($tindakans as $t)
                                            @php
                                                $action = \App\Models\ServiceAction::find($t);
                                            @endphp
                                            <li>{{ $action ? $action->nama_tindakan : $t }}</li>
                                        @endforeach
                                    </ul>
                                </td>

                                {{-- Sparepart --}}
                                <td class="px-2 py-3">
                                    @php $spareparts = json_decode($item->sparepart, true); @endphp
                                    @if ($spareparts)
                                        <ul class="list-disc ml-4">
                                            @foreach ($spareparts as $sp)
                                                @php $prd = \App\Models\Product::find($sp['id']); @endphp
                                                <li>
                                                    {{ $prd->product_name ?? 'Produk ID ' . $sp['id'] }}
                                                    (x{{ $sp['qty'] }})
                                                    - Rp{{ number_format($sp['harga'], 0, ',', '.') }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="px-2 py-3">Rp{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                                <td class="px-2 py-3">{{ $item->catatan }}</td>
                                <td class="px-2 py-3">
                                    <button
                                        class="toggle-status px-2 py-1 rounded 
            {{ $item->status == 1 ? 'bg-yellow-100 text-yellow-700' : ($item->status == 2 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}"
                                        data-id="{{ $item->id }}">
                                        @if ($item->status == 1)
                                            Diproses
                                        @elseif ($item->status == 2)
                                            Selesai
                                        @elseif ($item->status == 3)
                                            Dibatalkan
                                        @endif
                                    </button>
                                </td>


                                {{-- Aksi (popup hapus tetap) --}}
                                <td class="px-2 py-3">
                                    <div class="flex space-x-2">
                                        {{-- <a href="{{ route('history-garansi.edit', $item->id) }}">
                                            <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                                <span class="sr-only">Edit</span>
                                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                    <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                                </svg>
                                            </button>
                                        </a> --}}
                                        <div x-data="{ modalOpen: false }">
                                            <button class="text-rose-500 hover:text-rose-600 rounded-full"
                                                @click.prevent="modalOpen = true" aria-controls="danger-modal">
                                                <span class="sr-only">Delete</span>
                                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                    <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                                                    <path
                                                        d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
                                                </svg>
                                            </button>
                                            <!-- Modal backdrop -->
                                            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                                                x-show="modalOpen"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="transition ease-out duration-100"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>
                                            <!-- Modal dialog -->
                                            <div id="danger-modal"
                                                class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                                role="dialog" aria-modal="true" x-show="modalOpen"
                                                x-transition:enter="transition ease-in-out duration-200"
                                                x-transition:enter-start="opacity-0 translate-y-4"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                x-transition:leave="transition ease-in-out duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0"
                                                x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                                                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                                                    @keydown.escape.window="modalOpen = false">
                                                    <div class="p-5 flex space-x-4">
                                                        <!-- Icon -->
                                                        <div
                                                            class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                                                            <svg class="w-4 h-4 shrink-0 fill-current text-rose-500"
                                                                viewBox="0 0 16 16">
                                                                <path
                                                                    d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                                                            </svg>
                                                        </div>
                                                        <!-- Content -->
                                                        <div>
                                                            <!-- Modal header -->
                                                            <div class="mb-2">
                                                                <div class="text-lg font-semibold text-slate-800">
                                                                    Apakah anda sudah yakin ?</div>
                                                            </div>
                                                            <!-- Modal content -->
                                                            <div class="text-sm mb-10">
                                                                <div class="space-y-2">
                                                                    <p>Jika sudah terhapus, maka tidak bisa dikembalikan
                                                                        lagi.</p>
                                                                </div>
                                                            </div>
                                                            <!-- Modal footer -->
                                                            <div class="flex flex-wrap justify-end space-x-2">
                                                                <button
                                                                    class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600"
                                                                    @click="modalOpen = false">Batal</button>
                                                                <form
                                                                    action="{{ route('history-garansi.destroy', $item->id) }}"
                                                                    method="post">
                                                                    @method('delete')
                                                                    @csrf
                                                                    <button
                                                                        class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya,
                                                                        Hapus</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>


            </div>
        </div>
    </div>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // aktifkan select2
            $('.select2').select2({
                width: '100%', // biar full width
                dropdownParent: $('#tambah-modal') // penting supaya muncul di dalam modal
            });

            // update teknisi + garansi saat service_id berubah
            $('#service_id').on('change', function() {
                let selected = $(this).find(':selected');
                $('#prev_teknisi').text(selected.data('teknisi'));
                $('#exp_garansi').text(selected.data('expired'));
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const checkbox = document.getElementById("manualTindakanCheckbox");
            const container = document.getElementById("manualTindakanContainer");
            const body = document.getElementById("manualTindakanBody");
            const addManualBtn = document.getElementById("addManualRow");

            checkbox.addEventListener("change", function() {
                container.style.display = this.checked ? "block" : "none";
            });

            addManualBtn.addEventListener("click", function() {
                let row = document.createElement("tr");
                row.innerHTML = `
            <td><input type="text" name="tindakan[]" class="form-control" placeholder="Tindakan manual"></td>
            <td><button type="button" class="btn btn-sm btn-danger removeRow">x</button></td>
        `;
                body.appendChild(row);
            });

            body.addEventListener("click", function(e) {
                if (e.target.classList.contains("removeRow")) {
                    e.target.closest("tr").remove();
                }
            });
        });
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('handleSelect', () => ({
                selectall: false,
                selectAction() {
                    countEl = document.querySelector('.table-items-action');
                    if (!countEl) return;
                    checkboxes = document.querySelectorAll('input.table-item:checked');
                    document.querySelector('.table-items-count').innerHTML = checkboxes.length;
                    if (checkboxes.length > 0) {
                        countEl.classList.remove('hidden');
                    } else {
                        countEl.classList.add('hidden');
                    }
                },
                toggleAll() {
                    this.selectall = !this.selectall;
                    checkboxes = document.querySelectorAll('input.table-item');
                    [...checkboxes].map((el) => {
                        el.checked = this.selectall;
                    });
                    this.selectAction();
                },
                uncheckParent() {
                    this.selectall = false;
                    document.getElementById('parent-checkbox').checked = false;
                    this.selectAction();
                },
                deleteSelected() {
                    const checkboxes = document.querySelectorAll('input.table-item:checked');
                    const selectedIds = [...checkboxes].map((checkbox) => checkbox.value);

                    // Kirim permintaan penghapusan ke server
                    fetch('/colors/delete', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            body: JSON.stringify({
                                selectedIds
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            // Refresh halaman atau lakukan tindakan lain setelah penghapusan
                            window.location.reload();
                        })
                        .catch(error => {
                            console.error('Gagal menghapus data:', error);
                        });
                },
            }))
        })
    </script>

    <script>
        $(document).on('click', '.toggle-status', function() {
            let id = $(this).data('id');
            let btn = $(this);

            $.ajax({
                url: `/history-garansi/${id}/toggle-status`,
                method: "PATCH",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.success) {
                        btn.text(res.label);

                        btn.removeClass(
                            'bg-yellow-100 text-yellow-700 bg-green-100 text-green-700 bg-red-100 text-red-700'
                        );

                        if (res.status == 1) {
                            btn.addClass('bg-yellow-100 text-yellow-700');
                        } else if (res.status == 2) {
                            btn.addClass('bg-green-100 text-green-700');
                        } else {
                            btn.addClass('bg-red-100 text-red-700');
                        }
                    }
                },
                error: function() {
                    alert('Gagal update status!');
                }
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let products = @json($products);
            let rowId = 0;

            document.getElementById("addSparepartRow").addEventListener("click", function() {
                rowId++;
                let container = document.getElementById("rowContainer");

                let div = document.createElement("div");
                    div.classList.add(
                        "grid", 
                        "grid-cols-1",   // default 1 kolom (mobile)
                        "md:grid-cols-4", // di desktop jadi 4 kolom
                        "gap-2", 
                        "items-center", 
                        "mb-2"
                    );

                    div.innerHTML = `
                        <select name="sparepart[${rowId}][id]" 
                                class="form-select sparepartSelect select2 w-full" required>
                            <option value="">-- Pilih Sparepart --</option>
                            ${products.map(p => `<option value="${p.id}" data-harga="${p.harga_modal}">${p.product_name}</option>`).join("")}
                        </select>

                        <input type="number" name="sparepart[${rowId}][harga]" 
                            class="form-input harga w-full" placeholder="Harga" required>

                        <input type="number" name="sparepart[${rowId}][qty]" 
                            class="form-input qty w-full" placeholder="Qty" value="1" min="1" required>

                        <button type="button" 
                                class="btn-sm bg-rose-500 text-white w-full md:w-auto removeRow">
                            ✕
                        </button>
                    `;

                container.appendChild(div);

                // update harga otomatis
                div.querySelector(".sparepartSelect").addEventListener("change", function() {
                    let harga = this.options[this.selectedIndex].dataset.harga || 0;
                    div.querySelector(".harga").value = harga;
                    calculateTotal();
                });

                div.querySelector(".harga").addEventListener("input", calculateTotal);
                div.querySelector(".qty").addEventListener("input", calculateTotal);

                div.querySelector(".removeRow").addEventListener("click", function() {
                    div.remove();
                    calculateTotal();
                });
            });

            function calculateTotal() {
                let total = 0;
                document.querySelectorAll("#rowContainer > div").forEach(row => {
                    let harga = parseFloat(row.querySelector(".harga").value || 0);
                    let qty = parseInt(row.querySelector(".qty").value || 0);
                    total += harga * qty;
                });
                document.getElementById("total_biaya").value = total;
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            const sparepartCheckbox = document.getElementById("useSparepartCheckbox");
            const sparepartWrapper = document.getElementById("sparepart_wrapper");
            const rowContainer = document.getElementById("rowContainer");

            sparepartCheckbox.addEventListener("change", function() {
                if (this.checked) {
                    sparepartWrapper.style.display = "block";
                    // semua input sparepart wajib diisi (required)
                    rowContainer.querySelectorAll("select, input").forEach(el => el.required = true);
                } else {
                    sparepartWrapper.style.display = "none";
                    // reset value dan hilangkan semua row sparepart
                    rowContainer.innerHTML = "";
                    // hilangkan required
                    rowContainer.querySelectorAll("select, input").forEach(el => el.required = false);
                    // reset total biaya sparepart (biar ga ikut ngitung)
                    calculateTotal();
                }
            });

            // fungsi hitung total (sama kayak sebelumnya)
            function calculateTotal() {
                let total = 0;
                document.querySelectorAll("#rowContainer > div").forEach(row => {
                    let harga = parseFloat(row.querySelector(".harga")?.value || 0);
                    let qty = parseInt(row.querySelector(".qty")?.value || 0);
                    total += harga * qty;
                });
                document.getElementById("total_biaya").value = total;
            }
        });
    </script>


    <!-- Pagination -->
    <div class="mt-8">
        {{ $data->links() }}
    </div>
</div>
