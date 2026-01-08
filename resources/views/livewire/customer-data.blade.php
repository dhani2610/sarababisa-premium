<div>
    <style>
        .ts-wrapper.multi.has-items .ts-control {
            max-height: 200px !important;
            /* Atur sesuai kebutuhan */
            overflow-y: auto !important;
        }
        .dataTables_wrapper .dataTables_length {
            float: left;
            margin-left: 3%;
        }
        div.dataTables_wrapper div.dataTables_length select {
            width: 41%!important;
            display: inline-block;
        }
        div.dataTables_wrapper div.dataTables_filter {
            text-align: right;
            margin-right: 2%!important;
        }
    </style>

    @include('layouts.messages')
    <!-- Page Headers -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <!-- Start Broadcast -->

        <!-- End Broadcast -->

        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Daftar Pelanggan ✨</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">


            <div x-data="{ modalOpen: false }" class="">
                <button class="btn bg-green-500 hover:bg-green-600 text-white" @click.prevent="modalOpen = true">
                    <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none">
                        <path d="M21 15a2 2 0 01-2 2H8l-5 4V5a2 2 0 012-2h14a2 2 0 012 2v10z" stroke="currentColor"
                            stroke-width="2" />
                    </svg>
                    Broadcast WA
                </button>

                <!-- Modal backdrop -->
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:leave="transition ease-in duration-100" aria-hidden="true" x-cloak>
                </div>

                <!-- Modal -->
                @php
                    $fonteeToken = getStoreSettingByCabang()->fonnte ?? null;
                @endphp
                @if (empty($fonteeToken))

                    <div class="fixed inset-0 z-50 overflow-y-auto  flex items-center justify-center px-4 sm:px-6"
                        x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200"
                        x-transition:leave="transition ease-in-out duration-200" x-cloak>
                        <div class="bg-white rounded shadow-lg max-w-5xl w-full overflow-y-auto max-h-screen"
                            @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">

                            <button @click="modalOpen = false"
                                class="absolute top-3 right-3 text-gray-600 hover:text-red-600 text-2xl font-bold focus:outline-none" style="background: red;padding: 3px 10px 4px 10px;-radius: 50%!important;color:white!important">
                                &times;
                            </button>
                            <h2 class="text-2xl font-semibold mb-6 text-center">Pilih Paket Broadcast</h2>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6">
                                    <!-- Paket Free -->
                                    <div class="border rounded-lg p-5 shadow hover:shadow-md transition">
                                        <div class="flex justify-center mb-4">
                                            <div
                                                class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                                🗨️</div>
                                        </div>
                                        <h3 class="text-center font-bold text-lg mb-4">Free</h3>
                                        <ul class="space-y-1 text-sm mb-4">
                                            <li>✅ 1.000 pesan/bulan</li>
                                        </ul>
                                        <a href="https://wa.me/62811801799?text=Saya ingin berlangganan broadcast paket Free (Rp 0)"
                                             target="_blank" style="background: green;"
                                            class="block text-center text-white py-2 rounded hover:bg-indigo-800">
                                            Rp 0
                                        </a>
                                    </div>

                                    <!-- Paket Lite -->
                                    <div class="border rounded-lg p-5 shadow hover:shadow-md transition">
                                        <div class="flex justify-center mb-4">
                                            <div
                                                class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                                💬</div>
                                        </div>
                                        <h3 class="text-center font-bold text-lg mb-4">Lite</h3>
                                        <ul class="space-y-1 text-sm mb-4">
                                            <li>✅ 1.000 pesan/bulan</li>
                                        </ul>
                                        <a href="https://wa.me/62811801799?text=Saya ingin berlangganan broadcast paket Lite (Rp 25.000)"
                                             target="_blank" style="background: green;"
                                            class="block text-center text-white py-2 rounded hover:bg-indigo-800">
                                            Rp 25.000
                                        </a>
                                    </div>

                                    <!-- Paket Regular -->
                                    <div class="border rounded-lg p-5 shadow hover:shadow-md transition">
                                        <div class="flex justify-center mb-4">
                                            <div
                                                class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                                                📢</div>
                                        </div>
                                        <h3 class="text-center font-bold text-lg mb-4">Regular</h3>
                                        <ul class="space-y-1 text-sm mb-4">
                                            <li>✅ 10.000 pesan/bulan</li>
                                        </ul>
                                        <a href="https://wa.me/62811801799?text=Saya ingin berlangganan broadcast paket Regular (Rp 66.000)"
                                             target="_blank" style="background: green;"
                                            class="block text-center text-white py-2 rounded hover:bg-indigo-800">
                                            Rp 66.000
                                        </a>
                                    </div>
                                </div>
                        </div>
                    </div>
                @else
                    {{-- Modal Form Broadcast jika TOKEN_FONNTE tersedia --}}
                    <div class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center px-4 sm:px-6"
                        x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200"
                        x-transition:leave="transition ease-in-out duration-200" x-cloak>
                        <div class="bg-white rounded shadow-lg max-w-lg w-full" @click.outside="modalOpen = false"
                            @keydown.escape.window="modalOpen = false">
                            <form action="{{ route('pelanggan.broadcast') }}" method="POST">
                                @csrf
                                <div class="px-5 py-4 border-b">
                                    <h2 class="font-semibold text-slate-800">Broadcast WhatsApp</h2>
                                </div>
                                <div class="px-5 py-4 space-y-4">
                                    <textarea name="message" class="form-input w-full" rows="4" placeholder="Isi pesan WhatsApp" required></textarea>

                                    <div>
                                        <label class="flex items-center space-x-2">
                                            <input type="checkbox" id="select-all-customers" class="form-checkbox">
                                            <span class="text-sm">Pilih Semua Pelanggan</span>
                                        </label>
                                    </div>
                                    <div id="broadcast-select">
                                        <select name="customers[]" id="broadcast" class="form-select js-select2 w-full"
                                            multiple="multiple" required>
                                            @foreach (\App\Models\Customer::all() as $cust)
                                                <option value="{{ $cust->nomor_hp }}">{{ $cust->nama }} -
                                                    {{ $cust->nomor_hp }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="px-5 py-4 border-t flex justify-end space-x-2">
                                    <button type="button" class="btn border" @click="modalOpen = false">Batal</button>
                                    <button type="submit"
                                        class="btn bg-green-500 hover:bg-green-600 text-white">Kirim</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>


            <!-- Search form -->
            {{-- <x-search-form placeholder="Masukkan nama pelanggan" /> --}}

            <!-- Create invoice button -->
            <div x-data="{ modalOpen: false }">
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true"
                    aria-controls="tambah-modal">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path
                            d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Pelanggan Baru</span>
                </button>
                <!-- Modal backdrop -->
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true"
                    x-cloak></div>
                <!-- Modal dialog -->
                <div id="tambah-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog" aria-modal="true" x-show="modalOpen"
                    x-transition:enter="transition ease-in-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                        @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Tambah Pelanggan Baru</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path
                                            d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Modal content -->
                        <livewire:customer-create></livewire:customer-create>
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

        <!-- Right side -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <!-- Start Export Excel -->


            <a href="{{ route('pelanggan-export') }}" class="hidden lg:block">
                <button class="btn bg-white border-blue-200 hover:border-blue-300 text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-export"
                        width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2563eb"
                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                        <path d="M11.5 21h-4.5a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v5m-5 6h7m-3 -3l3 3l-3 3" />
                    </svg>
                    <span class="hidden xs:block ml-2">Ekspor Data</span>
                </button>
            </a>
            <!-- End Export Excel-->

            <!-- Start Import Excel -->
            <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>

            <div x-data="{ modalOpen: false }" class="hidden lg:block">
                <button class="btn bg-white border-emerald-200 hover:border-emerald-300 text-emerald-700"
                    @click.prevent="modalOpen = true" aria-controls="import-modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M5 13v-8a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2h-5.5m-9.5 -2h7m-3 -3l3 3l-3 3"/></svg>
                    Impor Data JS
                </button>

                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>

                <div id="import-modal" class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full" @click.outside="if(!processing) modalOpen = false">

                        <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Impor Data (Client Side Processing)</div>
                            <button class="text-slate-400 hover:text-slate-500" @click="if(!processing) modalOpen = false" :class="{'opacity-50 cursor-not-allowed': processing}">
                                <svg class="w-4 h-4 fill-current"><path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" /></svg>
                            </button>
                        </div>

                        <div class="px-5 pt-4 pb-1">
                            <div class="text-sm space-y-3">
                                <p>Pilih file Excel. Sistem akan membaca dan mengupload data secara bertahap (per 200 baris) agar tidak timeout.</p>

                                <input type="file" id="file_js_import" accept=".xlsx, .xls, .csv" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">

                                <div id="import-log" class="hidden p-2 bg-gray-50 rounded text-xs text-gray-600 font-mono h-24 overflow-y-auto border"></div>

                                <div id="js-progress-wrapper" class="hidden mt-4">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-xs font-medium text-indigo-700" id="status-text">Mempersiapkan...</span>
                                        <span class="text-xs font-medium text-indigo-700" id="js-progress-percent">0%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-4">
                                        <div id="js-progress-bar" class="bg-indigo-600 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
                                    </div>
                                    <p class="text-xs text-red-500 mt-1 italic font-bold">⚠️ JANGAN TUTUP HALAMAN INI SAMPAI SELESAI 100%</p>
                                </div>
                            </div>
                        </div>

                        <div class="px-5 py-4 flex justify-end space-x-2">
                            <a href="{{ asset('storage/assets/format_pelanggan.xlsx') }}" class="btn-sm bg-orange-500 text-white" :class="{'hidden': processing}">Download Format</a>

                            <button type="button" id="btn-js-process" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">
                                Mulai Proses Import
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- End Import Excel-->
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                {{-- Left side --}}
                <h2 class="font-semibold text-slate-800">Semua Pelanggan <span
                        class="text-slate-400 font-medium">{{ $customers_count }}</span></h2>
                {{-- Right side --}}
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
            <!-- Table -->
            <div class="overflow-x-auto">
                <table id="pelanggan-table" class="table-auto w-full">
                    <!-- Table header -->
                    <thead
                        class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox"
                                            @click="toggleAll" />
                                    </label>
                                </div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">No.</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Kategori</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">HP</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Alamat</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Aksi</div>
                            </th>
                        </tr>
                    </thead>
                    <!-- Table body -->
                    <tbody class="text-sm divide-y divide-slate-200">
                        {{-- <!-- Row -->
                        <?php $no = 0; ?>
                        @foreach ($customers as $customer)
                            <?php $no++; ?>
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="flex items-center">
                                        <label class="inline-flex">
                                            <span class="sr-only">Select</span>
                                            <input class="table-item form-checkbox" type="checkbox"
                                                value="{{ $customer->id }}" @click="uncheckParent" />
                                        </label>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $no }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $customer->nama }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $customer->kategori }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $customer->nomor_hp }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $customer->alamat }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="space-x-1 flex">
                                        <a href="{{ route('pelanggan.edit', $customer->id) }}">
                                            <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                                <span class="sr-only">Edit</span>
                                                <svg class="w-8 h-8 fill-current" viewBox="0 0 32 32">
                                                    <path
                                                        d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                                </svg>
                                            </button>
                                        </a>
                                        <!-- Start -->
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
                                                    @click.outside="modalOpen = false"
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
                                                                    action="{{ route('pelanggan.destroy', $customer->id) }}"
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
                                        <!-- End -->
                                    </div>
                                </td>
                            </tr>
                        @endforeach --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>


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
                    fetch('/customers/delete', {
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

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <!-- JS Native -->
    <script>
        // Aktifkan Tom Select
        new TomSelect("#broadcast", {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
    </script>

    <script>
        const checkbox = document.getElementById('select-all-customers');
        const select = document.getElementById('broadcast-select');

        checkbox.addEventListener('change', function() {
            if (this.checked) {
                select.style.display = 'none';
                select.required = false;
            } else {
                select.style.display = 'block';
                select.required = true;
            }
        });

        // Optional: hide select awal kalau checkbox udah dicentang pas load
        window.addEventListener('DOMContentLoaded', () => {
            if (checkbox.checked) {
                select.style.display = 'none';
                select.required = false;
            }
        });
    </script>


    <!-- Pagination -->
    {{-- <div class="mt-8">
        {{ $customers->links() }}
    </div> --}}

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#pelanggan-table').DataTable({
                processing: false,
                serverSide: false, // Mengaktifkan Server Side Pagination & Search
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    {
                        data: null,
                        sortable: false,
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'nama', name: 'nama' },
                    { data: 'kategori', name: 'kategori' },
                    { data: 'nomor_hp', name: 'nomor_hp' },
                    { data: 'alamat', name: 'alamat' },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                ],
                order: [[1, 'asc']], // Default order by Nama
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                },
                drawCallback: function() {
                    // Re-attach listeners after table redraw (pagination/search)
                    attachCheckboxHandlers();
                }
            });


            let batchSize = 100;
            let offset = 0;
            let loading = false;

            function loadBatch() {
                if (loading) return;
                loading = true;

                $.ajax({
                    url: '{{ route('pelanggan.data') }}?offset=' + offset + '&limit=' + batchSize,
                    success: function(response) {
                        if (response.data.length > 0) {
                            table.rows.add(response.data).draw(false); // tambahkan data batch
                            offset += batchSize;
                            loading = false;
                            setTimeout(loadBatch, 100); // lanjut batch berikutnya
                        } else {
                            console.log('Semua data sudah dimuat');
                        }
                    }
                });
            }

            // mulai load pertama
            loadBatch();

            // --- Logic Bulk Action Checkbox ---
            function attachCheckboxHandlers() {
                // Reset parent checkbox
                $('#parent-checkbox').prop('checked', false);
                toggleBulkAction();

                // Handler Select All
                $('#parent-checkbox').off('click').on('click', function() {
                    var checked = $(this).is(':checked');
                    $('input.table-item').prop('checked', checked);
                    toggleBulkAction();
                });

                // Handler Individual Checkbox
                $('#pelanggan-table').off('change', '.table-item').on('change', '.table-item', function() {
                    var all = $('input.table-item').length;
                    var checked = $('input.table-item:checked').length;
                    $('#parent-checkbox').prop('checked', all === checked && all > 0);
                    toggleBulkAction();
                });
            }

            function toggleBulkAction() {
                var checkedCount = $('input.table-item:checked').length;
                $('.table-items-count').text(checkedCount);
                if (checkedCount > 0) {
                    $('.table-items-action').removeClass('hidden');
                } else {
                    $('.table-items-action').addClass('hidden');
                }
            }

            // Fungsi Global untuk delete selected (agar bisa dipanggil onclick html)
            window.deleteSelected = function() {
                var selectedIds = $('input.table-item:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) return alert('Pilih data terlebih dahulu.');

                if (!confirm('Apakah anda yakin ingin menghapus ' + selectedIds.length + ' data pelanggan ini?')) return;

                $.ajax({
                    url: "/customers/delete",
                    method: 'POST',
                    data: {
                        ids: selectedIds,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        alert(response.message);
                        table.ajax.reload(); // Reload tabel otomatis
                        $('.table-items-action').addClass('hidden'); // Sembunyikan bulk action
                    },
                    error: function(xhr) {
                        alert('Gagal menghapus data.');
                        console.error(xhr);
                    }
                });
            };
        });

        // Script tambahan untuk Tom Select Broadcast (dari kode lama anda)
        document.addEventListener('DOMContentLoaded', function() {
            if(document.getElementById("broadcast")){
                 new TomSelect("#broadcast", {
                    create: false,
                    sortField: { field: "text", direction: "asc" }
                });
            }

            const checkboxAll = document.getElementById('select-all-customers');
            const selectBroad = document.getElementById('broadcast-select');
            if(checkboxAll && selectBroad){
                checkboxAll.addEventListener('change', function() {
                    if (this.checked) {
                        selectBroad.style.display = 'none';
                        const selectEl = selectBroad.querySelector('select');
                        if(selectEl) selectEl.required = false;
                    } else {
                        selectBroad.style.display = 'block';
                         const selectEl = selectBroad.querySelector('select');
                        if(selectEl) selectEl.required = true;
                    }
                });
            }
        });
    </script>

<script>
    // Variable global untuk state
    let processing = false;
    let allData = [];
    let totalRows = 0;
    let processedRows = 0;
    const CHUNK_SIZE = 200; // Kirim 200 data per request (aman untuk shared hosting)

    $(document).ready(function() {

        // 1. Tombol Klik Proses
        $('#btn-js-process').on('click', function() {
            const fileInput = document.getElementById('file_js_import');

            if (fileInput.files.length === 0) {
                alert("Pilih file Excel dulu!");
                return;
            }

            // Kunci UI
            processing = true;
            $(this).prop('disabled', true).addClass('opacity-50').text('Sedang Membaca File...');
            $('#js-progress-wrapper').removeClass('hidden');
            $('#import-log').removeClass('hidden').html('<div>Mulai membaca file...</div>');

            // 2. Baca Excel di Browser
            const file = fileInput.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {type: 'array'});

                // Ambil sheet pertama
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];

                // Convert ke JSON
                // raw: false agar tanggal/angka dibaca sesuai format string
                allData = XLSX.utils.sheet_to_json(worksheet, {raw: false});

                totalRows = allData.length;
                processedRows = 0;

                addLog(`Total data ditemukan: ${totalRows} baris.`);

                if (totalRows === 0) {
                    alert("File Excel kosong atau format header salah!");
                    resetUI();
                    return;
                }

                // 3. Mulai Pengiriman Bertahap
                sendChunk(0);
            };

            reader.readAsArrayBuffer(file);
        });

        // Fungsi Rekursif Mengirim Data
        function sendChunk(startIndex) {
            if (startIndex >= totalRows) {
                // SELESAI
                updateProgress(100);
                $('#status-text').text('Selesai!');
                addLog('✅ Semua data berhasil diimport!');
                alert('Import Selesai! Halaman akan dimuat ulang.');
                window.location.reload();
                return;
            }

            // Ambil potongan data (slice)
            const chunk = allData.slice(startIndex, startIndex + CHUNK_SIZE);
            const currentChunkNumber = Math.ceil((startIndex + 1) / CHUNK_SIZE);
            const totalChunks = Math.ceil(totalRows / CHUNK_SIZE);

            $('#status-text').text(`Mengupload bagian ${currentChunkNumber} dari ${totalChunks}...`);

            // Kirim via AJAX
            $.ajax({
                url: "{{ route('pelanggan.import-chunk') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    rows: chunk
                },
                success: function(response) {
                    processedRows += chunk.length;

                    // Update Persentase
                    let percent = Math.round((processedRows / totalRows) * 100);
                    updateProgress(percent);

                    // Log kecil
                    // addLog(`Chunk ${currentChunkNumber} OK. Inserted: ${response.inserted}, Updated: ${response.updated}`);

                    // Lanjut ke potongan berikutnya
                    sendChunk(startIndex + CHUNK_SIZE);
                },
                error: function(xhr) {
                    console.error(xhr);
                    addLog(`❌ Error pada baris ${startIndex} - ${startIndex + CHUNK_SIZE}. Menghentiikan proses.`);
                    alert("Terjadi kesalahan koneksi atau server error. Cek Log.");
                    resetUI();
                }
            });
        }

        function updateProgress(percent) {
            $('#js-progress-bar').css('width', percent + '%');
            $('#js-progress-percent').text(percent + '%');
        }

        function addLog(message) {
            const logDiv = $('#import-log');
            logDiv.append(`<div>${message}</div>`);
            logDiv.scrollTop(logDiv[0].scrollHeight); // Auto scroll ke bawah
        }

        function resetUI() {
            processing = false;
            $('#btn-js-process').prop('disabled', false).removeClass('opacity-50').text('Mulai Proses Import');
        }
    });
</script>
</div>
