    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

    <style>
        /* Agar modal filepond terlihat rapi */
        .filepond--root {
            font-family: sans-serif;
        }

        .filepond--panel-root {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
        }

        .filepond--drop-label {
            color: #64748b;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>
    @include('layouts.messages2')
    <div>
        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-3">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Servis Belum Lunas (Kredit) ✨</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Search form -->
                {{-- <x-search-form placeholder="Pelanggan/Nomor Servis/Barang/Tindakan/IMEI" /> --}}

                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500"
                        @click.prevent="modalOpen = true">
                        Export Belum Lunas
                    </button>

                    <div x-show="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
                        <div class="fixed inset-0 bg-slate-900 bg-opacity-30" @click="modalOpen = false"></div>
                        <div class="bg-white rounded shadow-lg max-w-sm w-full p-5 z-10">
                            <h2 class="font-semibold text-slate-800 mb-4">Pilih Customer</h2>
                            <form action="{{ route('cetak-servis-customer-belum-lunas') }}" method="get"
                                target="_blank">
                                <select name="customers_id" class="form-select selectjs2 w-full mb-4" required>
                                    <option value="">Pilih Pelanggan</option>
                                    @foreach ($customers as $item)
                                        <option value="{{ $item->id }}">{{ $item->nama }} - {{ $item->nomor_hp }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="flex justify-end space-x-2">
                                    <button type="button" class="btn border-slate-200"
                                        @click="modalOpen = false">Batal</button>
                                    <button type="submit" class="btn bg-indigo-500 text-white">Cetak</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- More actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5">


            <!-- Right side -->
            {{-- <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <!-- Filter button -->
                <div class="relative inline-flex" x-data="{ open: false }">
                    <button
                        class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-500 hover:text-slate-600"
                        aria-haspopup="true"
                        @click.prevent="open = !open"
                        :aria-expanded="open"
                    >
                        <span class="sr-only">Filter</span><wbr>
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 16 16">
                            <path d="M9 15H7a1 1 0 010-2h2a1 1 0 010 2zM11 11H5a1 1 0 010-2h6a1 1 0 010 2zM13 7H3a1 1 0 010-2h10a1 1 0 010 2zM15 3H1a1 1 0 010-2h14a1 1 0 010 2z" />
                        </svg>
                    </button>
                    <div
                        class="origin-top-left z-10 absolute top-full min-w-56 bg-white border border-slate-200 pt-1.5 rounded shadow-lg overflow-hidden mt-1 left-4"
                        @click.outside="open = false"
                        @keydown.escape.window="open = false"
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200 transform"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-out duration-200"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        x-cloak
                    >
                        <div class="text-xs font-semibold text-slate-400 uppercase pt-1.5 pb-2 px-4">Filter</div>
                        <ul class="mb-4">
                            @foreach ($types as $item)
                                <li class="py-1 px-3">
                                    <label class="flex items-center">
                                        <input type="checkbox" class="form-checkbox" wire:model="type" value="{{ $item->id }}"/>
                                        <span class="text-sm font-medium ml-2">{{ $item->name }}</span>
                                    </label>
                                </li>
                            @endforeach
                            <li class="py-1 px-3">
                                <label class="flex items-center">
                                    <input type="checkbox" class="form-checkbox" wire:model="kondisi.0" value="Sudah jadi"/>
                                    <span class="text-sm font-medium ml-2">Sudah jadi</span>
                                </label>
                            </li>
                            <li class="py-1 px-3">
                                <label class="flex items-center">
                                    <input type="checkbox" class="form-checkbox" wire:model="kondisi.1" value="Tidak bisa"/>
                                    <span class="text-sm font-medium ml-2">Tidak bisa</span>
                                </label>
                            </li>
                            <li class="py-1 px-3">
                                <label class="flex items-center">
                                    <input type="checkbox" class="form-checkbox" wire:model="kondisi.2" value="Dibatalkan"/>
                                    <span class="text-sm font-medium ml-2">Dibatalkan</span>
                                </label>
                            </li>
                            <li class="py-1 px-3">
                                <label class="flex items-center">
                                    <input type="checkbox" class="form-checkbox" wire:model="kondisi.3" value="Menunggu konfirmasi"/>
                                    <span class="text-sm font-medium ml-2">Menunggu konfirmasi</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="mb-0">
                    <select wire:model="paginate" id="" class="form-select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="250">250</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                    </select>
                </div>
            </div> --}}

        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <div x-data="handleSelect">
                <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                    {{-- Left side --}}
                    <h2 class="font-semibold text-slate-800">Servis Belum Lunas <span
                            class="text-slate-400 font-medium">{{ $jumlah_belum_lunas }}</span></h2>
                    {{-- Right side --}}
                    <div class="relative inline-flex">
                        <div class="table-items-action hidden">
                            <div class="flex items-center">
                                <div class="text-sm italic mr-2 whitespace-nowrap"><span
                                        class="table-items-count"></span> item yang dipilih</div>
                                <div class="space-x-1">
                                    <button
                                        class="btn bg-white border-slate-200 hover:border-slate-300 text-blue-500 hover:text-blue-600"
                                        @click="approveSelected">Setujui</button>
                                    <button
                                        class="btn bg-white border-slate-200 hover:border-slate-300 text-gray-900 hover:text-gray-950"
                                        @click="rejectSelected">Tolak</button>
                                    <button
                                        class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600"
                                        @click="deleteSelected">Hapus</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table id="transaksi-servis-table" class="table-auto w-full">
                        <!-- Table header -->
                        <thead
                            class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                            <tr>
                                @if (Auth::user()->role != 'Investor')
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        <div class="flex items-center">
                                            <label class="inline-flex">
                                                <span class="sr-only">Select all</span>
                                                <input id="parent-checkbox" class="form-checkbox" type="checkbox"
                                                    @click="toggleAll" />
                                            </label>
                                        </div>
                                    </th>
                                @endif
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">No.</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Nomor Servis</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Tgl Terima</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Penerima</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Pelanggan</div>
                                </th>
                                @if (Auth::user()->role != 'Investor')
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">Hubungi</div>
                                    </th>
                                @endif
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Nama Barang</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Kerusakan</div>
                                </th>
                                {{-- <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Pengecekan Masuk</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Pengecekan Keluar</div>
                                </th> --}}
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Pengecekan Fungsi</div>
                                </th>

                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Kondisi</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Tindakan</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Teknisi</div>
                                </th>
                                @if (Auth::user()->role == 'Kepala Toko' || $storeSetting->is_modal == 1)
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">Modal Sparepart</div>
                                    </th>
                                @endif
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Biaya</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Diskon</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Pembayaran</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Tipe Status Pembayaran</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Tgl Ambil</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Pengambil</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Penyerah</div>
                                </th>
                                <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-left">Masa Garansi</div>
                                </th>
                                @if (Auth::user()->role != 'Investor')
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">Status</div>
                                    </th>
                                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-left">Aksi</div>
                                    </th>
                                @endif
                            </tr>
                        </thead>
                        <!-- Table body -->
                        {{-- <tbody class="text-sm divide-y divide-slate-200">
                            <!-- Row -->
                            @php
                                $i = 1
                            @endphp
                            @foreach ($service_transactions as $transaction)
                                @php
                                    if ($transaction->profit < '0') :
                                        $color = 'text-red-600';
                                    else :
                                        $color = '';
                                    endif;
                                @endphp
                                @php
                                    if ($transaction->kondisi_servis === 'Sudah jadi') :
                                        $status_color = 'bg-emerald-100 text-emerald-600';
                                        $total_color = 'text-emerald-500';
                                    elseif ($transaction->kondisi_servis === 'Menunggu konfirmasi') :
                                        $status_color = 'bg-amber-100 text-amber-600';
                                        $total_color = 'text-amber-500';
                                    elseif ($transaction->kondisi_servis === 'Tidak bisa') :
                                        $status_color = 'bg-rose-100 text-rose-500';
                                        $total_color = 'text-rose-500';
                                    else :
                                        $status_color = 'bg-slate-100 text-slate-500';
                                        $total_color = 'text-slate-500';
                                    endif;
                                @endphp
                                <tr class="{{ $color }}">
                                    @if (Auth::user()->role != 'Investor')
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        <div class="flex items-center">
                                            <label class="inline-flex">
                                                <span class="sr-only">Select</span>
                                                <input class="table-item form-checkbox" type="checkbox" value="{{ $transaction->id }}" @click="uncheckParent" />
                                            </label>
                                        </div>
                                    </td>
                                    @endif
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">{{ $i++ }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if (Auth::user()->role != 'Investor')
                                        <a href="{{ route('transaksi-servis-sudah-diambil.edit', $transaction->id) }}">
                                            <div class="flex items-center text-blue-600">
                                                <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
                                                    <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                                </svg>
                                                <div class="font-medium">{{ $transaction->nomor_servis }}</div>
                                            </div>
                                        </a>
                                        @else
                                        <div class="font-medium">{{ $transaction->nomor_servis }}</div>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if ($transaction->penerima != null)
                                            <div class="font-medium">{{ $transaction->penerima }}</div>
                                        @else
                                            <div></div>
                                        @endif
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if ($transaction->customer)
                                            @if ($transaction->customer->exists())
                                                <div class="font-medium">{{ $transaction->customer->nama }}</div>
                                            @else
                                                <div></div>
                                            @endif
                                        @else
                                            <div></div>
                                        @endif
                                    </td>
                                    @if (Auth::user()->role != 'Investor')
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="flex space-x-1">
                                            @php
                                                if ($transaction->customer != null) {
                                                    $nomor = $transaction->customer->nomor_hp;
                                                    $nomorwa = preg_replace('/^08/', 628, $nomor);
                                                }
                                            @endphp
                                            @if ($transaction->customer != null)
                                                <!-- Start -->
                                                <div
                                                    class="relative"
                                                    x-data="{ open: false }"
                                                    @mouseenter="open = true"
                                                    @mouseleave="open = false"
                                                >
                                                    <a href="https://api.whatsapp.com/send?phone={{$nomorwa}}&text="  target="_blank">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                            <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                                            <path d="M9 10a0.5 .5 0 0 0 1 0v-1a0.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a0.5 .5 0 0 0 0 -1h-1a0.5 .5 0 0 0 0 1" />
                                                        </svg>
                                                    </a>
                                                    <div class="z-10 absolute bottom-full left-1/2 -translate-x-1/2">
                                                        <div
                                                            class="bg-slate-800 p-2 rounded overflow-hidden mb-2"
                                                            x-show="open"
                                                            x-transition:enter="transition ease-out duration-200 transform"
                                                            x-transition:enter-start="opacity-0 translate-y-2"
                                                            x-transition:enter-end="opacity-100 translate-y-0"
                                                            x-transition:leave="transition ease-out duration-200"
                                                            x-transition:leave-start="opacity-100"
                                                            x-transition:leave-end="opacity-0"
                                                            x-cloak
                                                        >
                                                            <div class="text-xs text-slate-
                                                            200 whitespace-nowrap">Kirim pesan melalui Whatsapp</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- End -->

                                                @php
                                                    $fonnteToken = getStoreSettingByCabang()->fonnte ?? null;
                                                    $hasToken = !empty($fonnteToken);

                                                    if ($transaction->customer != null) {
                                                        $nomor = $transaction->customer->nomor_hp;
                                                        $nomorwa = preg_replace('/^08/', 628, $nomor);
                                                    }

                                                    // Pesan mentah untuk Fonnte
                                                    $message = "*Notifikasi Service*\n{$toko->nama_toko}\n\n"
                                                        . "No. Service : {$transaction->nomor_servis}\n"
                                                        . "Nama user : *{$transaction->nama_pelanggan}*\n"
                                                        . "Unit : {$transaction->nama_barang}\n"
                                                        . "Diambil : {$transaction->pengambil}\n"
                                                        . "Tanggal : " . \Carbon\Carbon::parse($transaction->tgl_ambil)->translatedFormat('d F Y (H:i)') . "\n"
                                                        . "Status : {$transaction->kondisi_servis}\n"
                                                        . "Garansi sampai : " . ($transaction->exp_garansi ? \Carbon\Carbon::parse($transaction->exp_garansi)->translatedFormat('d F Y') : 'Tidak ada garansi') . "\n"
                                                        . "Pembayaran : {$transaction->cara_pembayaran}\n\n"
                                                        . "Link tracking : " . env('APP_URL') . "/tracking\n"
                                                        . "Link Nota : " . route('kepalatoko-pengambilan-cetak-inkjet', $transaction->id)  . "\n\n"
                                                        . "Terimakasih";

                                                    // Versi encoded untuk WhatsApp link
                                                    $waMessage = rawurlencode($message);
                                                @endphp

                                                <div class="flex space-x-1">
                                                    @if ($transaction->customer != null)
                                                        <div class="relative" x-data="{ open: false }" @mouseenter="open = true" @mouseleave="open = false">

                                                            @if ($hasToken)
                                                                <!-- ✅ Kirim otomatis via Fonnte -->
                                                                <a href="javascript:void(0)"
                                                                onclick="kirimFontee('{{ $fonnteToken }}', '{{ $nomorwa }}', `{!! str_replace('`', '\`', $message) !!}`)"
                                                                title="Kirim otomatis via Fonnte">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-invoice" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                                        <line x1="9" y1="7" x2="10" y2="7" />
                                                                        <line x1="9" y1="13" x2="15" y2="13" />
                                                                        <line x1="13" y1="17" x2="15" y2="17" />
                                                                    </svg>
                                                                </a>
                                                            @else
                                                                <!-- 💬 Manual via WhatsApp -->
                                                                <a href="https://wa.me/{{ $nomorwa }}/?text={{ $waMessage }}" target="_blank"
                                                                title="Kirim manual via WhatsApp">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-invoice" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                                        <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                                        <line x1="9" y1="7" x2="10" y2="7" />
                                                                        <line x1="9" y1="13" x2="15" y2="13" />
                                                                        <line x1="13" y1="17" x2="15" y2="17" />
                                                                    </svg>
                                                                </a>
                                                            @endif

                                                            <div class="z-10 absolute bottom-full left-1/2 -translate-x-1/2">
                                                                <div class="min-w-56 bg-slate-800 p-2 rounded overflow-hidden mb-2"
                                                                    x-show="open"
                                                                    x-transition:enter="transition ease-out duration-200 transform"
                                                                    x-transition:enter-start="opacity-0 translate-y-2"
                                                                    x-transition:enter-end="opacity-100 translate-y-0"
                                                                    x-transition:leave="transition ease-out duration-200"
                                                                    x-transition:leave-start="opacity-100"
                                                                    x-transition:leave-end="opacity-0"
                                                                    x-cloak>
                                                                    <div class="text-xs text-slate-200">
                                                                        Kirim Nota Pengambilan dan link untuk cek Status Garansi
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>


                                                <!-- End -->
                                            @endif
                                        </div>
                                    </td>
                                    @endif
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">{{ $transaction->nama_barang }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium capitalize">{{ $transaction->kerusakan }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium capitalize">{{ $transaction->qc_masuk }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium capitalize">{{ $transaction->qc_keluar }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 {{$status_color}}">{{ $transaction->kondisi_servis }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">
                                            {{ implode(', ', json_decode($transaction->tindakan_servis) ?? []) }}
                                        </div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if ($transaction->user)
                                            <div class="font-medium">{{ $transaction->user->name }}</div>
                                        @else
                                            <div class="font-medium text-red-600">Akun sudah dihapus</div>
                                        @endif
                                    </td>
                                    @if (Auth::user()->role != 'Investor')
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">Rp. {{ number_format($transaction->modal_sparepart) }}</div>
                                    </td>
                                    @endif
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">Rp. {{ number_format($transaction->biaya) }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">Rp. {{ number_format($transaction->diskon) }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">{{ $transaction->cara_pembayaran }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">{{ \Carbon\Carbon::parse($transaction->tgl_ambil)->format('d/m/Y H:i') }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">{{ $transaction->pengambil }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        <div class="font-medium">{{ $transaction->penyerah }}</div>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if ($transaction->exp_garansi === null)
                                            <div class="font-medium">Tidak Ada</div>
                                        @elseif ($transaction->exp_garansi < \Carbon\Carbon::now())
                                            <div class="font-medium text-red-600">{{ \Carbon\Carbon::parse($transaction->exp_garansi)->format('d/m/Y') }}</div>
                                        @else
                                            <div class="font-medium text-blue-600">{{ \Carbon\Carbon::parse($transaction->exp_garansi)->format('d/m/Y') }}</div>
                                        @endif
                                    </td>
                                @if (Auth::user()->role != 'Investor')

                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                        @if (Auth::user()->role == 'Kepala Toko')
                                        <a href="{{ route('transaksi-servis-approve.edit', $transaction->id) }}">
                                        @else
                                        <a href="#">
                                        @endif
                                            @if ($transaction->is_approve === null)
                                                <div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-amber-500 text-white">Belum Disetujui</div>
                                            @elseif ($transaction->is_approve === 'Setuju')
                                                <div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-blue-500 text-white">Sudah Disetujui</div>
                                            @else
                                                <div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 bg-red-500 text-white">Ditolak</div>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                        <div class="space-x-1 flex">
                                            <!-- Start Printer -->
                                            <div x-data="{ showPrint : false, printId: null }" x-show = "showPrint" x-on:open-print.window="showPrint = true; printId = $event.detail.id" x-on:close-print.window = "showPrint = false" x-on:keydown.escape.window = "showPrint = false" class="fixed z-50 inset-0">
                                                    <!-- Modal backdrop -->
                                                    <div x-on:click="showPrint = false" class="fixed inset-0 bg-slate-900 bg-opacity-40" x-cloak></div>
                                                    <!-- Modal dialog -->
                                                    <div
                                                        class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                                        x-show="showPrint"
                                                        x-cloak
                                                    >
                                                        <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full" x-on:keydown.escape.window="showPrint = false">
                                                            <!-- Modal header -->
                                                            <div class="px-5 py-3 border-b border-slate-200">
                                                                <div class="flex justify-between items-center">
                                                                    <div class="font-semibold text-slate-800">Pilih Jenis Printer</div>
                                                                    <button class="text-slate-400 hover:text-slate-500" x-on:click="$dispatch('close-print')">
                                                                        <div class="sr-only">Close</div>
                                                                        <svg class="w-4 h-4 fill-current">
                                                                            <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <!-- Modal content -->
                                                            <div class="px-5 pt-4 pb-1">
                                                                <div class="text-sm">
                                                                    <div class="space-y-2">
                                                                        <p>Silahkan pilih printer untuk cetak Nota Pengambilan Servis Selesai.</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Modal footer -->
                                                            <div class="px-5 py-4">
                                                                <div class="flex flex-wrap justify-end space-x-2">
                                                                    <a x-bind:href="'{{ route('kepalatoko-nota-pengambilan-termal', '') }}/' + printId"  target="_blank">
                                                                        <button class="btn-sm bg-orange-500 hover:bg-orange-600 text-white">
                                                                            <span class="mr-1">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                                                <rect x="7" y="13" width="10" height="8" rx="2" />
                                                                                </svg>
                                                                            </span>
                                                                            Printer Termal
                                                                        </button>
                                                                    </a>
                                                                    <a x-bind:href="'{{ route('kepalatoko-pengambilan-cetak-inkjet', '') }}/' + printId"  target="_blank">
                                                                        <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">
                                                                            <span class="mr-1">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                                <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                                                <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                                                <rect x="7" y="13" width="10" height="8" rx="2" />
                                                                                </svg>
                                                                            </span>
                                                                            Printer Inkjet
                                                                        </button>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                            </div>
                                            <!-- End Printer-->

                                            <button x-data x-on:click="$dispatch('open-print', { id: {{ $transaction->id }} })">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                    <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                    <rect x="7" y="13" width="10" height="8" rx="2" />
                                                </svg>
                                            </button>

                                            <!-- Start Cancel -->
                                            <div
                                                class="relative"
                                                x-data="{ open: false }"
                                                @mouseenter="open = true"
                                                @mouseleave="open = false"
                                            >
                                                <a href="{{ route('transaksi-servis-sudah-diambil.show', $transaction->id) }}">
                                                    <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                                        <span class="sr-only">Cancel</span>
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-back-up" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#000000" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 14l-4 -4l4 -4" /><path d="M5 10h11a4 4 0 1 1 0 8h-1" /></svg>
                                                    </button>
                                                </a>
                                                <div class="z-10 absolute right-full top-1/2 -translate-y-1/2">
                                                    <div
                                                        class="bg-slate-800 p-2 rounded overflow-hidden mb-2"
                                                        x-show="open"
                                                        x-transition:enter="transition ease-out duration-200 transform"
                                                        x-transition:enter-start="opacity-0 translate-y-2"
                                                        x-transition:enter-end="opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-out duration-200"
                                                        x-transition:leave-start="opacity-100"
                                                        x-transition:leave-end="opacity-0"
                                                        x-cloak
                                                    >
                                                        <div class="text-xs text-slate-200 whitespace-nowrap">Kembalikan status ke bisa diambil</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End Cancel -->

                                            <!-- Start -->
                                            <div x-data="{ showDelete: false, deleteId: null }" x-show = "showDelete" x-on:open-delete.window="showDelete = true; deleteId = $event.detail.id" x-on:close-delete.window = "showDelete = false" x-on:keydown.escape.window = "showDelete = false" class="fixed z-50 inset-0">
                                                <!-- Modal backdrop -->
                                                <div x-on:click="showDelete = false" class="fixed inset-0 bg-slate-900 bg-opacity-40" x-cloak></div>
                                                <!-- Modal dialog -->
                                                <div
                                                    id="danger-modal"
                                                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                                    role="dialog"
                                                    aria-modal="true"
                                                    x-show="showDelete"
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
                                                                    <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" x-on:click="$dispatch('close-delete')">Batal</button>
                                                                    <form x-bind:action="'{{ route('transaksi-servis-sudah-diambil.destroy', '') }}/' + deleteId" method="post">
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
                                            <!-- End -->

                                            <button x-data x-on:click="$dispatch('open-delete', { id: {{ $transaction->id }} })" class="text-rose-500 hover:text-rose-600 rounded-full">
                                                <span class="sr-only">Delete</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ff2825" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <line x1="4" y1="7" x2="20" y2="7" />
                                                    <line x1="10" y1="11" x2="10" y2="17" />
                                                    <line x1="14" y1="11" x2="14" y2="17" />
                                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                @endif
                                </tr>
                            @endforeach
                        </tbody> --}}
                    </table>

                </div>
            </div>
        </div>


        <div id="modal-upload-foto" class="fixed inset-0 z-[100] hidden overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true">

            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

                <div class="fixed inset-0 transition-opacity bg-gray-900 bg-opacity-75" aria-hidden="true"
                    onclick="closeModalFoto()"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div
                    class="inline-block w-full text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl overflow-hidden sm:my-8 sm:align-middle sm:max-w-3xl">

                    <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full mt-3 text-center sm:mt-0 sm:text-left">

                                <div class="flex items-center justify-between pb-2 mb-4 border-b">
                                    <h3 class="text-xl font-bold leading-6 text-gray-900" id="modal-title">
                                        📸 Dokumentasi Foto Servis
                                    </h3>
                                    <button onclick="closeModalFoto()"
                                        class="text-gray-400 hover:text-gray-500 focus:outline-none">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <input type="hidden" id="current-servis-id">

                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div class="p-4 border rounded-lg bg-slate-50 border-slate-200">
                                        <div class="flex items-center mb-2">
                                            <span
                                                class="bg-yellow-100 text-yellow-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">IN</span>
                                            <label class="block text-sm font-bold text-gray-700">Kondisi Masuk</label>
                                        </div>
                                        <small class="text-rose-500">*Klik lagi untuk menambah foto lainnya
                                            (Multi-upload).</small>
                                        <input type="file" class="filepond-masuk" name="file" multiple
                                            data-max-file-size="10MB">
                                    </div>

                                    <div class="p-4 border rounded-lg bg-slate-50 border-slate-200">
                                        <div class="flex items-center mb-2">
                                            <span
                                                class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">OUT</span>
                                            <label class="block text-sm font-bold text-gray-700">Kondisi
                                                Selesai</label>
                                        </div>
                                        <small class="text-rose-500">*Klik lagi untuk menambah foto lainnya
                                            (Multi-upload).</small>
                                        <input type="file" class="filepond-selesai" name="file" multiple
                                            data-max-file-size="10MB">
                                    </div>
                                </div>

                                <div class="mt-4 text-xs italic text-gray-500">
                                    * Foto otomatis tersimpan saat berhasil di-upload. Klik foto untuk memperbesar
                                    (zoom).
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-100 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            onclick="closeModalFoto()">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
        <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
        <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css"
            rel="stylesheet">

        <script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.min.js"></script>
        <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.min.js">
        </script>
        <script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.min.js"></script>
        <script src="https://unpkg.com/filepond-plugin-image-transform/dist/filepond-plugin-image-transform.min.js"></script>
        <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
        <script>
            // 1. Register Plugin
            FilePond.registerPlugin(
                FilePondPluginFileValidateType,
                FilePondPluginImageResize,
                FilePondPluginImageTransform,
                FilePondPluginImagePreview
            );

            let pondMasuk, pondSelesai;
            let viewer;

            document.addEventListener('DOMContentLoaded', function() {

                // Cek apakah library kompresi sudah jalan
                if (typeof imageCompression === 'undefined') {
                    console.error(
                        "ERROR: Library browser-image-compression belum terload! Cek koneksi internet atau script tag."
                    );
                }

                // 2. Config Dasar FilePond
                const baseConfig = {
                    allowMultiple: true,
                    acceptedFileTypes: ['image/jpeg', 'image/png',
                        'image/webp'
                    ], // Batasi tipe file agar transform jalan
                    labelIdle: 'Drag & Drop gambar atau <span class="filepond--label-action">Cari</span>',
                    credits: false,

                    // --- KONFIGURASI RESIZE (DIMENSI) ---
                    allowImageResize: true,
                    imageResizeTargetWidth: 1280,
                    imageResizeTargetHeight: 1280,
                    imageResizeMode: 'contain',
                    imageResizeUpscale: false,

                    // --- KONFIGURASI TRANSFORM (KOMPRESI) ---
                    allowImageTransform: true,
                    imageTransformOutputQuality: 70, // Turunkan sedikit ke 70 agar size lebih kecil
                    imageTransformOutputMimeType: 'image/jpeg', // Paksa convert ke JPEG (lebih kecil dari PNG)

                    // Fix untuk orientasi foto HP (EXIF data)
                    imageTransformOutputStripImageHead: false,

                    // Preview
                    imagePreviewHeight: 150,

                    // Event Zoom Viewer
                    onactivatefile: (file) => {
                        let imageUrl = file.getMetadata('url');
                        if (!imageUrl && file.file) {
                            imageUrl = URL.createObjectURL(file.file);
                        }
                        if (imageUrl) showImagePopup(imageUrl);
                    }
                };

                // 3. Create Instance
                const inputMasuk = document.querySelector('.filepond-masuk');
                const inputSelesai = document.querySelector('.filepond-selesai');

                // Cek element ada atau tidak sebelum create
                if (inputMasuk) pondMasuk = FilePond.create(inputMasuk, baseConfig);
                if (inputSelesai) pondSelesai = FilePond.create(inputSelesai, baseConfig);

                // 4. Event Listener Tombol Modal
                $(document).on('click', '.btn-upload-foto', function() {
                    let id = $(this).data('id');
                    $('#current-servis-id').val(id);
                    $('#modal-upload-foto').removeClass('hidden');

                    if (pondMasuk) pondMasuk.removeFiles();
                    if (pondSelesai) pondSelesai.removeFiles();

                    if (pondMasuk) setupPondServer(pondMasuk, id, 'masuk');
                    if (pondSelesai) setupPondServer(pondSelesai, id, 'selesai');

                    loadExistingImages(id);
                });
            });

            // ... (Fungsi setupPondServer, loadExistingImages, showImagePopup sama seperti sebelumnya) ...
            // ... Copy paste fungsi-fungsi helper di bawah sini ...

            function setupPondServer(pondInstance, id, type) {
                // (Paste kode setupPondServer sebelumnya disini)
                // Pastikan kode server process/revert/remove/load ada disini
                pondInstance.setOptions({
                    server: {
                        process: {
                            url: `/servis/transaksi-servis/${id}/upload-foto`,
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            ondata: (formData) => {
                                formData.append('type', type);
                                return formData;
                            }
                        },
                        revert: {
                            url: `/servis/transaksi-servis/${id}/delete-foto?type=${type}`,
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        },
                        remove: (source, load, error) => {
                            fetch(`/servis/transaksi-servis/${id}/delete-foto?type=${type}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'text/plain'
                                },
                                body: source
                            }).then(() => load()).catch((err) => error('Gagal menghapus'));
                        },
                        load: (source, load, error) => {
                            let myRequest = new Request(`/storage/servis/${source}`);
                            fetch(myRequest).then(res => res.blob()).then(blob => load(blob)).catch(err => error(
                                'Gagal load'));
                        }
                    }
                });
            }

            function loadExistingImages(id) {
                fetch(`/servis/transaksi-servis/${id}/get-foto`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.masuk && pondMasuk) pondMasuk.files = data.masuk;
                        if (data.selesai && pondSelesai) pondSelesai.files = data.selesai;
                    })
                    .catch(err => console.error("Gagal load foto", err));
            }

            function showImagePopup(imageUrl) {
                const image = new Image();
                image.src = imageUrl;
                const viewer = new Viewer(image, {
                    hidden: function() {
                        viewer.destroy();
                    },
                    toolbar: {
                        zoomIn: 1,
                        zoomOut: 1,
                        oneToOne: 1,
                        reset: 1,
                        rotateLeft: 1,
                        rotateRight: 1,
                        flipHorizontal: 1,
                        flipVertical: 1
                    },
                });
                viewer.show();
            }

            function closeModalFoto() {
                $('#modal-upload-foto').addClass('hidden');
            }
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
                        fetch('/services/delete', {
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
                    approveSelected() {
                        const checkboxes = document.querySelectorAll('input.table-item:checked');
                        const selectedIds = [...checkboxes].map((checkbox) => checkbox.value);

                        // Kirim permintaan update ke server
                        fetch('/services/update', {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}', // Tambahkan token CSRF jika menggunakan Laravel
                                },
                                body: JSON.stringify({
                                    selectedIds
                                }),
                            })
                            .then(response => response.json())
                            .then(data => {
                                alert(data.message);
                                // Refresh halaman atau lakukan tindakan lain setelah update
                                window.location.reload();
                            })
                            .catch(error => {
                                console.error('Gagal memperbarui data:', error);
                            });
                    },
                    rejectSelected() {
                        const checkboxes = document.querySelectorAll('input.table-item:checked');
                        const selectedIds = [...checkboxes].map((checkbox) => checkbox.value);

                        // Kirim permintaan update ke server
                        fetch('/services/reject', {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}', // Tambahkan token CSRF jika menggunakan Laravel
                                },
                                body: JSON.stringify({
                                    selectedIds
                                }),
                            })
                            .then(response => response.json())
                            .then(data => {
                                alert(data.message);
                                // Refresh halaman atau lakukan tindakan lain setelah update
                                window.location.reload();
                            })
                            .catch(error => {
                                console.error('Gagal memperbarui data:', error);
                            });
                    },
                }))
            })
        </script>

        <script>
            function kirimFontee(token, phone, message) {
                fetch('https://api.fonnte.com/send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': token
                        },
                        body: JSON.stringify({
                            target: phone,
                            message: decodeURIComponent(message)
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === true || data.success) {
                            alert('✅ Pesan berhasil dikirim ke Pelanggan!');
                        } else {
                            alert('⚠️ Gagal mengirim ke Pelanggan. Coba lagi.');
                        }
                    })
                    .catch(() => alert('❌ Terjadi kesalahan saat mengirim ke Fontee.'));
            }
        </script>

        <!-- Pagination -->
        {{-- <div class="mt-8">
            {{ $service_transactions->links() }}
        </div> --}}
    </div>
