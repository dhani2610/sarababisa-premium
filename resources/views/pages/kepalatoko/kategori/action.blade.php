<div class="flex space-x-2">
    <div x-data="{ modalOpen: false }">
        <button class="text-slate-500 hover:text-slate-700" @click.prevent="modalOpen = true">
            <span class="sr-only">Edit</span>
            <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
            </svg>
        </button>

        <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-cloak></div>
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4" x-show="modalOpen" x-cloak>
            <div class="bg-white rounded shadow-lg w-full max-w-md" @click.outside="modalOpen = false">
                <form method="POST" action="{{ route('kategori.update', $row->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="px-5 py-4 border-b">
                        <h2 class="font-semibold text-slate-800">Edit Kategori</h2>
                    </div>
                    <div class="px-5 py-4">
                        <label class="block text-sm font-medium mb-1" for="edit_category_{{ $row->id }}">Nama Kategori</label>
                        <input type="text" name="category_name" id="edit_category_{{ $row->id }}" value="{{ $row->category_name }}" class="form-input w-full" required>

                        <label class="block text-sm font-medium mb-1 mt-2" for="show_portal">Show Portal</label>
                        <select name="show_portal" id="" class="form-input w-full">
                            <option value="1" {{ $row->show_portal == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $row->show_portal == 0 ? 'selected' : '' }}>Non Active</option>
                        </select>
                    </div>
                    <div class="px-5 py-4 border-t flex justify-end gap-2">
                        <button type="button" class="btn-sm border" @click="modalOpen = false">Batal</button>
                        <button type="submit" class="btn-sm bg-indigo-500 text-white">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div x-data="{ modalOpen: false }">
        <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="modalOpen = true" aria-controls="danger-modal">
            <span class="sr-only">Delete</span>
            <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                <path d="M13 15h2v6h-2zM17 15h2v6h-2z" />
                <path d="M20 9c0-.6-.4-1-1-1h-6c-.6 0-1 .4-1 1v2H8v2h1v10c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V13h1v-2h-4V9zm-6 1h4v1h-4v-1zm7 3v9H11v-9h10z" />
            </svg>
        </button>
        <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             aria-hidden="true" x-cloak></div>
        <div id="danger-modal" class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
             role="dialog" aria-modal="true" x-show="modalOpen"
             x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
            <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                <div class="p-5 flex space-x-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                        <svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16">
                            <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                        </svg>
                    </div>
                    <div>
                        <div class="mb-2">
                            <div class="text-lg font-semibold text-slate-800">Apakah anda sudah yakin ?</div>
                        </div>
                        <div class="text-sm mb-10">
                            <div class="space-y-2">
                                <p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi.</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-end space-x-2">
                            <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                            <form action="{{ route('kategori.destroy', $row->id) }}" method="post">
                                @method('delete')
                                @csrf
                                <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
