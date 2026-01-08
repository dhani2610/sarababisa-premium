@section('title')
    Riwayat Aktivitas Servis
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Riwayat Aktivitas Servis ✨</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end">

                <!-- Start Delete-->
                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-rose-500 hover:bg-rose-600 text-white" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M4 7l16 0" />
                        <path d="M10 11l0 6" />
                        <path d="M14 11l0 6" />
                        <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                        <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                        </svg>
                        <span class="hidden xs:block ml-2">Bersihkan Semua Riwayat</span>
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
                        id="danger-modal"
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
                            <div class="p-5 flex space-x-4">
                                <!-- Icon -->
                                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-rose-100">
                                    <svg class="w-4 h-4 shrink-0 fill-current text-rose-500" viewBox="0 0 16 16">
                                        <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 12c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1zm1-3H7V4h2v5z" />
                                    </svg>
                                </div>
                                <!-- Content -->
                                <div>
                                    <!-- Modal header -->
                                    <div class="mb-2">
                                        <div class="text-lg font-semibold text-slate-800">Apakah anda sudah yakin ?</div>
                                    </div>
                                    <!-- Modal content -->
                                    <div class="text-sm mb-10">
                                        <div class="space-y-2">
                                            <p>Jika sudah terhapus, maka tidak bisa dikembalikan lagi.</p>
                                        </div>
                                    </div>
                                    <!-- Modal footer -->
                                    <div class="flex flex-wrap justify-end space-x-2">
                                        <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                        <form action="{{ route('log-servis-destroy', ['model' => 'ServiceTransaction']) }}" method="post">
                                            @method('post')
                                            @csrf
                                            <button class="btn-sm bg-rose-500 hover:bg-rose-600 text-white">Ya, Hapus</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- End Delete-->

            </div>

        </div>

        <!-- Table -->
        <x-log-table />

        <!-- Pagination -->
        {{-- <div class="mt-8">
            {{$activities->links()}}
        </div> --}}

    </div>
</x-toko-layout>
