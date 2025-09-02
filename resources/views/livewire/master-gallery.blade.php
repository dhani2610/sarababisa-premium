<div>
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Gallery 📷</h1>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <x-search-form placeholder="Cari foto berdasarkan judul" />

            <!-- Button Tambah -->
            <div x-data="{ modalOpen: false }">
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                    + Tambah Foto
                </button>

                <!-- Modal backdrop -->
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                     x-show="modalOpen"
                     x-transition
                     aria-hidden="true"
                     x-cloak></div>

                <!-- Modal dialog -->
                <div id="tambah-modal"
                     class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                     role="dialog"
                     aria-modal="true"
                     x-show="modalOpen"
                     x-transition
                     x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                         @click.outside="modalOpen = false"
                         @keydown.escape.window="modalOpen = false">

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
                                    <label class="block text-sm font-medium mb-1" for="title">Judul <span class="text-rose-500">*</span></label>
                                    <input id="title" name="title" class="form-input w-full" type="text" required />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="foto">Foto <span class="text-rose-500">*</span></label>
                                    <input id="foto" name="foto" class="form-input w-full" type="file" accept="image/*" required />
                                </div>
                            </div>
                            <!-- Modal footer -->
                            <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                <button type="button" class="btn-sm border-slate-200" @click="modalOpen = false">Batal</button>
                                <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5">
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-2 py-3 text-center" width="10%">No</th>
                        <th class="px-2 py-3 text-center" width="30%">Preview</th>
                        <th class="px-2 py-3 text-center" width="20%">Judul</th>
                        <th class="px-2 py-3 text-center" width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-200">
                    @php $i = 1; @endphp
                    @foreach($galleries as $item)
                        <tr>
                            <td class="px-2 py-3 text-center">{{ $i++ }}</td>
                            <td class="px-2 py-3 text-center">
                                <center>
                                    <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->title }}" style="max-width: 200px" class=" rounded">
                                </center>
                            </td>
                            <td class="px-2 py-3 text-center">{{ $item->title }}</td>
                            <td class="px-2 py-3 text-center">
                                <form action="{{ route('master-gallery.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus foto ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $galleries->links() }}
    </div>
</div>
