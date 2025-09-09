<div>
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Gallery 📷</h1>
        </div>

        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <x-search-form placeholder="Cari foto berdasarkan judul" />
            {{-- Alert sukses / gagal --}}

            <!-- Button Tambah -->
            <div x-data="{ modalOpen: false }">
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                    + Tambah Foto
                </button>

                <!-- Modal backdrop -->
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                    x-transition aria-hidden="true" x-cloak></div>

                <!-- Modal dialog -->
                <div id="tambah-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog" aria-modal="true" x-show="modalOpen" x-transition x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                        @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">

                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Tambah Foto</div>
                            <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">✕</button>
                        </div>

                        <!-- Form -->
                        <form action="{{ route('master-gallery.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="px-5 py-4 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="title">Judul <span
                                            class="text-rose-500">*</span></label>
                                    <input id="title" name="title" class="form-input w-full" type="text"
                                        required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="foto">
                                        Foto <span class="text-rose-500">*</span>
                                    </label>

                                    <input id="foto" name="foto" class="form-input w-full" type="file"
                                        accept="image/*" required onchange="checkFotoSize(this)" />

                                    <p id="foto_alert" class="text-red-500 text-xs mt-1 hidden">
                                        Ukuran foto maksimal 1 MB!
                                    </p>
                                    <p class="text-gray-500 text-xs mt-1">
                                        Maksimal ukuran file: 1 MB
                                    </p>
                                </div>

                                <script>
                                    function checkFotoSize(input) {
                                        const file = input.files[0];
                                        const alertEl = document.getElementById('foto_alert');
                                        if (file && file.size > 1024 * 1024) {
                                            alertEl.classList.remove('hidden');
                                            input.value = ''; // reset input
                                        } else {
                                            alertEl.classList.add('hidden');
                                        }
                                    }
                                </script>

                            </div>
                            <!-- Modal footer -->
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
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5">
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
        <div class="overflow-x-auto">
            <div x-data="handleSelect">
                <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                    <h2 class="font-semibold text-slate-800">Semua Foto <span
                            class="text-slate-400 font-medium">{{ $galleries->total() }}</span></h2>
                    <div class="relative inline-flex">
                        <div class="table-items-action hidden">
                            <div class="flex items-center">
                                <div class="text-sm italic mr-2 whitespace-nowrap"><span
                                        class="table-items-count"></span> item dipilih</div>
                                <button
                                    class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600"
                                    @click="deleteSelected">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full">
                        <thead
                            class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                            <tr>
                                <th class="px-2 py-3 text-center w-px">
                                    <input type="checkbox" class="form-checkbox" @click="toggleAll">
                                </th>
                                <th class="px-2 py-3 text-center">No</th>
                                <th class="px-2 py-3 text-center">Preview</th>
                                <th class="px-2 py-3 text-center">Judul</th>
                                <th class="px-2 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-200">
                            @php $i = 1; @endphp
                            @foreach ($galleries as $item)
                                <tr>
                                    <td class="px-2 py-3 text-center">
                                        <input type="checkbox" class="form-checkbox table-item"
                                            value="{{ $item->id }}" @change="toggleItem($event)">

                                    </td>
                                    <td class="px-2 py-3 text-center">{{ $i++ }}</td>
                                    <td class="px-2 py-3 text-center">
                                        <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->title }}"
                                            style="max-width: 200px" class="rounded">
                                    </td>
                                    <td class="px-2 py-3 text-center">{{ $item->title }}</td>
                                    <td class="px-2 py-3 text-center">
                                        <form action="{{ route('master-gallery.destroy', $item->id) }}" method="POST"
                                            onsubmit="return confirm('Yakin hapus foto ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $galleries->links() }}
    </div>
</div>
<script>
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
                if (!confirm("Yakin hapus foto yang dipilih?")) return;

                fetch("{{ route('master-gallery.deleteSelected') }}", {
                        method: "DELETE",
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
