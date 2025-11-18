<style>
.btn-status {
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
}

/* Menunggu Konfirmasi (Kuning) */
.status-menunggu {
    background-color: #FFF7D1;
    color: #B58105;
}

/* Sudah Selesai (Hijau) */
.status-selesai {
    background-color: #D1FADF;
    color: #027A48;
}

/* Dibatalkan (Merah) */
.status-batal {
    background-color: #FEE2E2;
    color: #B91C1C;
}

</style>
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

            <!-- Print button -->
            @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
            <div class="relative inline-flex" x-data="{ modalOpen: false }">
                <button
                    class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500 hover:text-slate-600 mb-2 md:mb-0"
                    @click.prevent="modalOpen = true" aria-controls="tambah-modal"
                >
                    <span class="sr-only">Print</span><wbr>
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                        <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                        <rect x="7" y="13" width="10" height="8" rx="2" />
                    </svg>
                </button>
                <!-- Modal backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-out duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    aria-hidden="true"
                    x-cloak
                ></div>
                <!-- Modal dialog -->
                <div
                    id="tambah-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog"
                    aria-modal="true"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-in-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4"
                    x-cloak
                >
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Atur Pencetakan History Garansi</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Modal content -->
                        <form action="{{ route('history-garansi.cetak') }}" method="get" target="_blank">

                            @csrf
                            <div class="px-5 py-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Mulai tanggal <span class="text-rose-500">*</span></label>
                                        <input id="start_date" name="start_date" class="form-input w-full py-2" type="date" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Sampai tanggal <span class="text-rose-500">*</span></label>
                                        <input id="end_date" name="end_date" class="form-input w-full py-2" type="date" required />
                                    </div>
                                </div>
                            </div>
                            <!-- Modal footer -->
                            <div class="px-5 py-4 border-t border-slate-200">
                                <div class="flex flex-wrap justify-end space-x-2">
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Cetak</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            <!-- Create invoice button -->
            <div x-data="{ modalOpen: false }">
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                    + Tambah History Garansi
                </button>

                <!-- Modal backdrop -->
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                    x-transition aria-hidden="true" x-cloak></div>

                <!-- Modal dialog -->
                <div id="tambah-modal-data"
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
                                    <select name="service_id" id="service_id" class="form-select select2  w-full"
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
                                    <select name="penerima_id" class="form-select w-full " required>
                                        <option value="">-- Pilih Penerima --</option>
                                        @foreach ($users as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                 <div>
                                    <label class="block text-sm font-medium mb-1">Keluhan <span
                                            class="text-rose-500">*</span></label>
                                    <input type="text" name="keluhan" class="form-input w-full"
                                        required>
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
                            <th class="px-2 py-3 w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox"
                                            @click="toggleAll">
                                    </label>
                                </div>
                            </th>
                            <th class="px-2 py-3">No.</th>
                            <th class="px-2 py-3">Tanggal</th>
                            <th class="px-2 py-3">Tgl Selesai</th>
                            <th class="px-2 py-3">Nomor Servis</th>
                            <th class="px-2 py-3">Customer</th>
                            <th class="px-2 py-3">Penerima</th>
                            <th class="px-2 py-3">Keluhan</th>
                            <th class="px-2 py-3">Teknisi</th>
                            <th class="px-2 py-3">Tindakan</th>
                            <th class="px-2 py-3">Sparepart</th>
                            <th class="px-2 py-3">Total Modal</th>
                            <th class="px-2 py-3">Catatan</th>
                            <th class="px-2 py-3">Status</th>
                            @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                            <th class="px-2 py-3">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @php $i = 1; @endphp
                        @foreach ($data as $item)
                            <tr>
                                <td class="px-2 py-3 w-px">
                                    <div class="flex items-center">
                                        <label class="inline-flex">
                                            <input class="table-item form-checkbox" type="checkbox"
                                                value="{{ $item->id }}" @click="uncheckParent">
                                        </label>
                                    </div>
                                </td>

                                <td class="px-2 py-3">{{ $i++ }}</td>
                                <td class="px-2 py-3">{{ $item->date }}</td>
                                <td class="px-2 py-3">{{ $item->tgl_selesai }}</td>
                                <td class="px-2 py-3">{{ $item->service->nomor_servis ?? $item->service_id }}</td>
                                <td class="px-2 py-3">{{ $item->service->customer->nama ?? '-' }}</td>
                                <td class="px-2 py-3">{{ $item->penerima->name ?? '-' }}</td>
                                <td class="px-2 py-3">{{ $item->keluhan ?? '-' }}</td>
                                <td class="px-2 py-3">{{ $item->teknisi->name ?? '-' }}</td>

                                {{-- Tindakan --}}
                                <td class="px-2 py-3">
                                    @if (!empty($item->tindakan))
                                    @php
                                        $tindakans = json_decode( $item->tindakan, true) ?? [];
                                    @endphp

                                    <ul class="list-disc ml-4">
                                        @foreach ($tindakans as $t)
                                            @php
                                                $action = \App\Models\ServiceAction::find($t['id']);
                                            @endphp
                                            <li>{{ $action ? $action->nama_tindakan : $t['id_manual'] }}
                                                - Rp{{ number_format($t['harga'], 0, ',', '.') }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Sparepart --}}
                                <td class="px-2 py-3">
                                    @if (!empty($item->sparepart))
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
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="px-2 py-3">Rp{{ number_format($item->total_biaya, 0, ',', '.') }}</td>
                                <td class="px-2 py-3">{{ $item->catatan }}</td>
                                <td class="px-2 py-3">
                                    <center>
                                        <button
                                                class="btn-status 
                                                {{ $item->status == 1 ? 'status-menunggu' : ($item->status == 2 ? 'status-selesai' : 'status-batal') }}"
                                                data-id="{{ $item->id }}">
    
                                                @if ($item->status == 1)
                                                    Menunggu Konfirmasi
                                                @elseif ($item->status == 2)
                                                    Sudah Selesai
                                                @elseif ($item->status == 3)
                                                    Dibatalkan
                                                @endif
    
                                            </button>
                                    </center>


                                </td>


                                @if (Auth::user()->role == 'Kepala Toko' || Auth::user()->role == 'Admin Toko')
                                {{-- Aksi (popup hapus tetap) --}}
                                <td class="px-2 py-3">
                                    <div class="space-x-1 flex">
                                        <div class="flex space-x-2">
                                           
                                            <a href="{{ route('history-garansi.edit', $item->id) }}">
                                                <button class="text-slate-400 hover:text-slate-500 rounded-full" title="Ubah">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clipboard-check" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                        <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                                                        <rect x="9" y="3" width="6" height="4" rx="2" />
                                                        <path d="M9 14l2 2l4 -4" />
                                                    </svg>
                                                </button>
                                            </a>
                                        </div>
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
                                        </div>
                                    </div>
                                </td>
                                @endif
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
                dropdownParent: $('#tambah-modal-data') // penting supaya muncul di dalam modal
            });

            // update teknisi + garansi saat service_id berubah
            $('#service_id').on('change', function() {
                let selected = $(this).find(':selected');
                $('#prev_teknisi').text(selected.data('teknisi'));
                $('#exp_garansi').text(selected.data('expired'));
            });
        });
    </script>

  <script>
document.addEventListener('alpine:init', () => {
    Alpine.data('handleSelect', () => ({
        selected: [],
        deleteUrl: '{{ route("history-garansi.bulkDelete") }}', // kita buat route ini
        toggleAll(e) {
            const checked = e.target.checked;
            this.selected = [];
            document.querySelectorAll('.table-item').forEach(el => {
                el.checked = checked;
                if (checked) this.selected.push(el.value);
            });
            this.toggleAction();
        },
        uncheckParent() {
            const all = document.querySelectorAll('.table-item');
            const selected = Array.from(all).filter(x => x.checked).map(x => x.value);
            this.selected = selected;
            document.getElementById('parent-checkbox').checked = selected.length === all.length;
            this.toggleAction();
        },
        toggleAction() {
            const action = document.querySelector('.table-items-action');
            const countEl = document.querySelector('.table-items-count');
            if (this.selected.length > 0) {
                action.classList.remove('hidden');
                countEl.textContent = this.selected.length;
            } else {
                action.classList.add('hidden');
            }
        },
        deleteSelected() {
            if (this.selected.length === 0) return;

            if (!confirm('Yakin ingin menghapus data terpilih?')) return;

            fetch(this.deleteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: this.selected })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                }
            })
            .catch(err => console.error(err));
        }
    }))
});
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
                    "grid-cols-1", // default 1 kolom (mobile)
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
                div.querySelector(".tindakanHarga").addEventListener("input", calculateTotal);

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
                    console.log(total);

                });
                // ubah ke angka biar gak digabung string
                let total_biaya_tindakan = parseFloat($('#total_biaya_tindakan').val() || 0);

                // hitung total keseluruhan
                let totalKeseluruhan = total + total_biaya_tindakan;

                $('#total_biaya').val(totalKeseluruhan);
                document.getElementById("modal_sparepart").value = total;
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            const sparepartCheckbox = document.getElementById("useSparepartCheckbox");
            const sparepartWrapper = document.getElementById("sparepart_wrapper");
            const modalSparepartWrapper = document.getElementById("modal_sparepart_wrapper");
            const rowContainer = document.getElementById("rowContainer");

            sparepartCheckbox.addEventListener("change", function() {
                if (this.checked) {
                    sparepartWrapper.style.display = "block";
                    modalSparepartWrapper.style.display = "block";
                    // semua input sparepart wajib diisi (required)
                    rowContainer.querySelectorAll("select, input").forEach(el => el.required = true);
                } else {
                    sparepartWrapper.style.display = "none";
                    modalSparepartWrapper.style.display = "none";
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
                document.getElementById("modal_sparepart").value = total;
            }
        });
    </script>
    <script>
        function updateTotalModal() {
            const tindakan = parseFloat(document.getElementById('total_biaya_tindakan').value) || 0;
            document.getElementById('total_biaya').value = tindakan;
        }
        document.addEventListener("DOMContentLoaded", function() {
            let tindakanList = @json($serviceActions); // pastikan kamu kirim $serviceActions dari controller
            let tindakanContainer = document.getElementById("tindakanContainer");
            let totalBiayaInput = document.getElementById("total_biaya_tindakan");
            let totalBiayaFinal = document.getElementById("total_biaya");
            let addTindakanBtn = document.getElementById("addTindakanRow");
            let tindakanRowId = 0;

            addTindakanBtn.addEventListener("click", function() {
                tindakanRowId++;
                let div = document.createElement("div");
                div.classList.add("grid", "grid-cols-1", "md:grid-cols-3", "gap-2", "items-center", "mb-2");
                div.innerHTML = `
                <select name="tindakan[${tindakanRowId}][id]"
                        class="form-select tindakanSelect w-full" required>
                    <option value="">-- Pilih Tindakan --</option>
                    ${tindakanList.map(t => `<option value="${t.id}" data-harga="${t.harga_pelanggan}">${t.nama_tindakan}</option>`).join("")}
                </select>
                <input type="text" name="tindakan[${tindakanRowId}][id_manual]" class="form-input tindakanManual w-full hidden" placeholder="Input manual tindakan">

                 <div class="flex items-center space-x-2">
                    <input type="checkbox" class="form-checkbox toggleManual" id="manual-${tindakanRowId}">
                    <label for="manual-${tindakanRowId}" class="text-sm text-slate-600">Input manual</label>
                </div>

                <input type="number" name="tindakan[${tindakanRowId}][harga]"
                       class="form-input tindakanHarga w-full" placeholder="Harga" value="0" required>
                <button type="button" class="btn-sm bg-rose-500 text-white removeTindakan w-full md:w-auto">✕</button>
            `;

                tindakanContainer.appendChild(div);

                // toggle manual input
                div.querySelector(".toggleManual").addEventListener("change", function() {
                    let manual = div.querySelector(".tindakanManual");
                    let select = div.querySelector(".tindakanSelect");

                    if (this.checked) {
                        select.classList.add("hidden");
                        select.removeAttribute("required");
                        manual.classList.remove("hidden");
                        manual.setAttribute("required", true);
                        $(select).val('').trigger('change');
                    } else {
                        manual.classList.add("hidden");
                        manual.removeAttribute("required");
                        select.classList.remove("hidden");
                        select.setAttribute("required", true);
                        manual.value = '';
                    }
                });

                let select = div.querySelector(".tindakanSelect");
                let hargaInput = div.querySelector(".tindakanHarga");

                div.querySelector(".tindakanHarga").addEventListener("input", calculateTindakanTotal);

                // ketika pilih tindakan
                select.addEventListener("change", function() {
                    let harga = parseFloat(select.options[select.selectedIndex].dataset.harga || 0);
                    hargaInput.value = harga;
                    calculateTindakanTotal();
                });

                // hapus row tindakan
                div.querySelector(".removeTindakan").addEventListener("click", function() {
                    div.remove();
                    calculateTindakanTotal();
                });
            });

            function calculateTindakanTotal() {
                let total = 0;
                document.querySelectorAll("#tindakanContainer .tindakanHarga").forEach(input => {
                    total += parseFloat(input.value || 0);
                });
                totalBiayaInput.value = total;
                calculateFinalTotal();
            }

            function calculateFinalTotal() {
                let totalTindakan = parseFloat(totalBiayaInput.value || 0);
                let modalSparepart = parseFloat(document.getElementById("modal_sparepart").value || 0);
                totalBiayaFinal.value = totalTindakan + modalSparepart;
            }

            // jika modal_sparepart berubah, update total akhir
            document.getElementById("modal_sparepart").addEventListener("input", calculateFinalTotal);
        });
    </script>





    <!-- Pagination -->
    <div class="mt-8">
        {{ $data->links() }}
    </div>
</div>
