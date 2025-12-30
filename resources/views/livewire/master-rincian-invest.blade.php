<div>
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Rincian Invest 📊</h1>
        </div>


        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <x-search-form placeholder="Cari berdasarkan keterangan" />




            @if (Auth::user()->role != 'Investor')
                <!-- Button Tambah -->
                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                        + Tambah Rincian
                    </button>

                    <!-- Modal backdrop -->
                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                        x-transition aria-hidden="true" x-cloak></div>

                    <!-- Modal tambah -->
                    <div id="tambah-modal"
                        class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                        role="dialog" aria-modal="true" x-show="modalOpen" x-transition x-cloak>
                        <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                            @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">

                            <!-- Header -->
                            <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Tambah Rincian Invest</div>
                                <button class="text-slate-400 hover:text-slate-500"
                                    @click="modalOpen = false">✕</button>
                            </div>

                            <!-- Form -->
                            <form action="{{ route('rincian-invest.store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="px-5 py-4 space-y-3">
                                    @if (Auth::user()->role != 'Investor')
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Pilih Investor <span
                                                    class="text-rose-500">*</span></label>
                                            <select name="id_investor" class="form-select w-full" required>
                                                <option value="">Pilih Investor</option>
                                                @foreach ($investors as $investor)
                                                    <option value="{{ $investor->id }}">{{ $investor->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Tanggal <span
                                                class="text-rose-500">*</span></label>
                                        <input type="date" name="tanggal" class="form-input w-full" required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Tipe <span
                                                class="text-rose-500">*</span></label>
                                        <select name="tipe" class="form-select w-full" required>
                                            <option value="">Pilih tipe</option>
                                            <option value="1">Masuk</option>
                                            <option value="2">Pembagian</option>
                                            <option value="3">Lain-lain</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Nominal <span
                                                class="text-rose-500">*</span></label>
                                        <input type="text" name="nominal" class="form-input w-full format-nominal"
                                            required>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Upload Bukti Transfer <span
                                                class="text-rose-500">*</span></label>
                                        <input type="file" name="upload_bukti_tf" class="form-input w-full"
                                            accept="image/*" required onchange="checkFileSize(this)">
                                        <p id="file_alert" class="text-red-500 text-xs mt-1 hidden">Ukuran file maksimal
                                            1
                                            MB!</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Keterangan <span
                                                class="text-rose-500">*</span></label>
                                        <textarea name="keterangan" class="form-input w-full" rows="3" required></textarea>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                    <button type="button" class="btn-sm border-slate-200"
                                        @click="modalOpen = false">Batal</button>
                                    <button type="submit"
                                        class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5">
        <div class="row">
            <!-- Filter Tabs -->
            <div class="flex gap-2 mt-4 border-b border-slate-200">
                <button wire:click="setFilter(0)"
                    class="px-4 py-2 text-sm font-semibold border-b-2 transition-all duration-150
        {{ $filterTipe == 0 ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                    Semua
                </button>

                <button wire:click="setFilter(1)"
                    class="px-4 py-2 text-sm font-semibold border-b-2 transition-all duration-150
        {{ $filterTipe == 1 ? 'border-green-500 text-green-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                    Masuk (Rp{{ number_format($totals['masuk'], 0, ',', '.') }})
                </button>

                <button wire:click="setFilter(2)"
                    class="px-4 py-2 text-sm font-semibold border-b-2 transition-all duration-150
        {{ $filterTipe == 2 ? 'border-blue-500 text-blue-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                    Pembagian (Rp{{ number_format($totals['pembagian'], 0, ',', '.') }})
                </button>

                <button wire:click="setFilter(3)"
                    class="px-4 py-2 text-sm font-semibold border-b-2 transition-all duration-150
        {{ $filterTipe == 3 ? 'border-gray-500 text-gray-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                    Lain-lain (Rp{{ number_format($totals['lain'], 0, ',', '.') }})
                </button>

                @if (Auth::user()->role != 'Investor')
                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium">Filter Investor:</label>
                        <select wire:model="filterInvestor" class="form-select text-sm">
                            <option value="0">Semua Investor</option>
                            @foreach ($investors as $investor)
                                <option value="{{ $investor->id }}">{{ $investor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

            </div>

        </div>
        <div class="overflow-x-auto" x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Data <span
                        class="text-slate-400 font-medium">{{ $rincian->total() }}</span></h2>

                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span>
                                item dipilih</div>
                            <button
                                class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600"
                                @click="deleteSelected">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>

            <table-responsive>

            </table-responsive>
           <div class="overflow-x-auto border rounded-sm">
            <table class="w-full text-xs text-left border-collapse">
                <thead
                    class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        @if (Auth::user()->role != 'Investor')
                            <th class="px-2 py-3 text-center w-px">
                                <input type="checkbox" class="form-checkbox" @click="toggleAll">
                            </th>
                        @endif
                        <th class="px-2 py-3 text-center">No</th>
                        <th class="px-2 py-3 text-center">Investor</th>
                        <th class="px-2 py-3 text-center">Tanggal</th>
                        <th class="px-2 py-3 text-center">Tipe</th>
                        <th class="px-2 py-3 text-center">Nominal</th>
                        <th class="px-2 py-3 text-center">Bukti Transfer</th>
                        <th class="px-2 py-3 text-center">Keterangan</th>
                        @if (Auth::user()->role != 'Investor')
                            <th class="px-2 py-3 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                    @php $no = $rincian->firstItem(); @endphp
                    @foreach ($rincian as $item)
                        <tr>
                            @if (Auth::user()->role != 'Investor')
                                <td class="px-2 py-3 text-center">
                                    <input type="checkbox" class="form-checkbox table-item"
                                        value="{{ $item->id }}" @change="toggleItem($event)">
                                </td>
                            @endif
                            <td class="px-2 py-3 text-center">{{ $no++ }}</td>
                            <td class="px-2 py-3 text-center">
                                {{ $item->investor ? $item->investor->name : '-' }}
                            </td>

                            <td class="px-2 py-3 text-center">{{ $item->tanggal->format('d/m/Y') }}</td>
                            @php
                                if ($item->tipe == 1) {
                                    $tipe_color = 'bg-green-100 text-green-700';
                                    $tipe_text = 'Masuk';
                                } elseif ($item->tipe == 2) {
                                    $tipe_color = 'bg-blue-100 text-blue-700';
                                    $tipe_text = 'Pembagian';
                                } else {
                                    $tipe_color = 'bg-gray-100 text-gray-700';
                                    $tipe_text = 'Lain-lain';
                                }
                            @endphp


                            <td class="px-2 py-3 text-center">
                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $tipe_color }}">
                                    {{ $tipe_text }}
                                </span>
                            </td>
                            <td class="px-2 py-3 text-center">Rp{{ number_format($item->nominal, 0, ',', '.') }}</td>
                            <td class="px-2 py-3 text-center">
                                <img src="{{ asset('storage/' . $item->upload_bukti_tf) }}" alt="Bukti Pembayaran"
                                    class="bukti-img" onclick="showBukti(this.src)">
                            </td>
                            <td class="px-2 py-3 text-center">{{ $item->keterangan }}</td>
                            @if (Auth::user()->role != 'Investor')
                                <td class="px-2 py-3 text-center">
                                    <!-- Tombol Edit -->
                                    <div x-data="{ editModalOpen: false }" class="inline-block">
                                        <button class="btn-sm bg-amber-500 hover:bg-amber-600 text-white"
                                            @click.prevent="editModalOpen = true">
                                            Edit
                                        </button>

                                        <!-- Modal backdrop -->
                                        <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                                            x-show="editModalOpen" x-transition aria-hidden="true" x-cloak></div>

                                        <!-- Modal edit -->
                                        <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                            role="dialog" aria-modal="true" x-show="editModalOpen" x-transition
                                            x-cloak>
                                            <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                                                @click.outside="editModalOpen = false"
                                                @keydown.escape.window="editModalOpen = false">
                                                <div
                                                    class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                                    <div class="font-semibold text-slate-800">Edit Rincian</div>
                                                    <button class="text-slate-400 hover:text-slate-500"
                                                        @click="editModalOpen = false">✕</button>
                                                </div>

                                                <form action="{{ route('rincian-invest.update', $item->id) }}"
                                                    method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="px-5 py-4 space-y-3">
                                                        @if (Auth::user()->role != 'Investor')
                                                            <div>
                                                                <label class="block text-sm font-medium mb-1"
                                                                    style="float: left">Pilih
                                                                    Investor <span
                                                                        class="text-rose-500">*</span></label>
                                                                <select name="id_investor" class="form-select w-full"
                                                                    required>
                                                                    <option value="">Pilih Investor</option>
                                                                    @foreach ($investors as $investor)
                                                                        <option value="{{ $investor->id }}"
                                                                            {{ $item->id_investor == $investor->id ? 'selected' : '' }}>
                                                                            {{ $investor->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <label class="block text-sm font-medium mb-1"
                                                                style="float: left">Tanggal</label>
                                                            <input type="date" name="tanggal"
                                                                value="{{ $item->tanggal->format('Y-m-d') }}"
                                                                class="form-input w-full" required>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium mb-1"
                                                                style="float: left">Tipe</label>
                                                            <select name="tipe" class="form-select w-full"
                                                                required>
                                                                <option value="1"
                                                                    {{ $item->tipe == 1 ? 'selected' : '' }}>Masuk
                                                                </option>
                                                                <option value="2"
                                                                    {{ $item->tipe == 2 ? 'selected' : '' }}>Pembagian
                                                                </option>
                                                                <option value="3"
                                                                    {{ $item->tipe == 3 ? 'selected' : '' }}>Lain-lain
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium mb-1"
                                                                style="float: left">Nominal</label>
                                                            <input type="text" name="nominal"
                                                                value="{{ number_format($item->nominal, 0, ',', '.') }}"
                                                                class="form-input w-full format-nominal" required>
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium mb-1"
                                                                style="float: left">Ganti Bukti
                                                                Transfer</label>
                                                            <input type="file" name="upload_bukti_tf"
                                                                class="form-input w-full" accept="image/*">
                                                            <p class="text-xs text-gray-500" style="float: left">
                                                                Kosongkan
                                                                jika tidak diganti</p>
                                                            <img src="{{ asset('storage/' . $item->upload_bukti_tf) }}"
                                                                class="w-20 h-20 mt-2 rounded">
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-medium mb-1"
                                                                style="float: left">Keterangan</label>
                                                            <textarea name="keterangan" class="form-input w-full" rows="3" required>{{ $item->keterangan }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                                        <button type="button" class="btn-sm border-slate-200"
                                                            @click="editModalOpen = false">Batal</button>
                                                        <button type="submit"
                                                            class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Update</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tombol Hapus -->
                                    <form action="{{ route('rincian-invest.destroy', $item->id) }}" method="POST"
                                        class="inline-block" onsubmit="return confirm('Yakin hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Hapus</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-4 px-5">
            {{ $rincian->links() }}
        </div>
    </div>
</div>
<!-- Modal -->
<div id="buktiModal" class="bukti-modal">
    <span class="close-btn" onclick="closeBukti()">&times;</span>
    <img class="bukti-modal-content" id="buktiModalImg">
</div>

<!-- CSS -->
<style>
    .bukti-img {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    .bukti-img:hover {
        transform: scale(1.05);
    }

    /* Modal disembunyikan default */
    .bukti-modal {
        display: none;
        /* <- kuncinya di sini */
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        justify-content: center;
        align-items: center;
    }

    .bukti-modal-content {
        max-width: 90%;
        max-height: 90%;
        border-radius: 10px;
        box-shadow: 0 0 25px rgba(255, 255, 255, 0.4);
        object-fit: contain;
    }

    .close-btn {
        position: absolute;
        top: 15px;
        right: 25px;
        color: white;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.2s;
    }

    .close-btn:hover {
        color: red;
    }
</style>

<!-- JS -->
<script>
    function showBukti(src) {
        const modal = document.getElementById('buktiModal');
        const img = document.getElementById('buktiModalImg');
        img.src = src;
        modal.style.display = 'flex'; // tampilkan modal dengan flex agar di tengah
    }

    function closeBukti() {
        const modal = document.getElementById('buktiModal');
        modal.style.display = 'none';
    }

    // Tutup kalau klik area hitam
    document.getElementById('buktiModal').addEventListener('click', function(e) {
        if (e.target.id === 'buktiModal') {
            closeBukti();
        }
    });
</script>

<script>
    function checkFileSize(input) {
        const file = input.files[0];
        const alertEl = document.getElementById('file_alert');
        if (file && file.size > 1024 * 1024) {
            alertEl.classList.remove('hidden');
            input.value = '';
        } else {
            alertEl.classList.add('hidden');
        }
    }

    document.addEventListener("alpine:init", () => {
        Alpine.data("handleSelect", () => ({
            selected: [],
            toggleAll(e) {
                this.selected = [];
                document.querySelectorAll(".table-item").forEach((el) => {
                    el.checked = e.target.checked;
                    if (el.checked) this.selected.push(el.value);
                });
                this.updateAction();
            },
            toggleItem(e) {
                const id = e.target.value;
                if (e.target.checked) {
                    if (!this.selected.includes(id)) this.selected.push(id);
                } else {
                    this.selected = this.selected.filter(item => item !== id);
                }
                this.updateAction();
            },
            updateAction() {
                const actionBox = document.querySelector(".table-items-action");
                const countBox = document.querySelector(".table-items-count");
                if (this.selected.length > 0) {
                    actionBox.classList.remove("hidden");
                    countBox.innerText = this.selected.length;
                } else {
                    actionBox.classList.add("hidden");
                }
            },
            deleteSelected() {
                if (this.selected.length === 0) return;
                if (!confirm("Yakin hapus data yang dipilih?")) return;

                fetch("{{ route('rincian-invest.bulkDelete') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            selectedIds: this.selected
                        })
                    })
                    .then(res => res.json())
                    .then(() => location.reload());
            }
        }));
    });
</script>
<script>
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('format-nominal')) {
            let input = e.target;
            let value = input.value.replace(/\D/g, ""); // hanya angka

            // Jangan batasi panjang angka
            if (value.length > 15) return; // (opsional: batasi sampai 999 triliun biar aman)

            // Format angka ke ribuan (1.000.000)
            input.value = value ? value.replace(/\B(?=(\d{3})+(?!\d))/g, ".") : "";
        }
    });
</script>
