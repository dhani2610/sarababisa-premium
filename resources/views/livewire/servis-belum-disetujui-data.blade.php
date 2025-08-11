<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Transaksi Servis ✨</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            <!-- Search form -->
            <x-search-form placeholder="Pelanggan/Nomor Servis/Barang/Tindakan" />
            
        </div>

    </div>

    <!-- More actions -->
    <div class="sm:flex sm:justify-between sm:items-center mb-5">
    
        <!-- Left side -->
        <div class="mb-4 sm:mb-0">
            <ul class="flex flex-wrap -m-1">
                <li class="m-1">
                    <a href="{{ route('transaksi-servis.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-white text-slate-500 duration-150 ease-in-out">Proses <span class="ml-1 text-indigo-200">{{ $processes_count }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-bisa-diambil.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-white text-slate-500 duration-150 ease-in-out">Bisa Diambil <span class="ml-1 text-slate-400">{{ $jumlah_bisa_diambil }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-sudah-diambil.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-transparent shadow-sm  bg-white text-slate-500 duration-150 ease-in-out">Sudah Diambil <span class="ml-1 text-slate-400">{{ $jumlah_sudah_diambil }}</span></button>
                    </a>
                </li>
                <li class="m-1">
                    <a href="{{ route('transaksi-servis-belum-disetujui.index') }}">
                        <button class="inline-flex items-center justify-center text-sm font-medium leading-5 rounded-full px-3 py-1 border border-slate-200 hover:border-slate-300 shadow-sm bg-indigo-500 text-white duration-150 ease-in-out">Belum Disetujui <span class="ml-1 text-slate-400">{{ $jumlah_belum_disetujui }}</span></button>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Right side -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
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
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="250">250</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                {{-- Left side --}}
                <h2 class="font-semibold text-slate-800">Belum Disetujui <span class="text-slate-400 font-medium">{{ $jumlah_belum_disetujui }}</span></h2>
                {{-- Right side --}}
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span> item yang dipilih</div>
                            <div class="space-x-1">
                                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-blue-500 hover:text-blue-600" @click="approveSelected">Setujui</button>
                                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-gray-900 hover:text-gray-950" @click="rejectSelected">Tolak</button>
                                <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" @click="deleteSelected">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <!-- Table header -->
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox" @click="toggleAll" />
                                    </label>
                                </div>
                            </th>
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
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Hubungi</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama Barang</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Kerusakan</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Pengecekan Masuk</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Pengecekan Keluar</div>
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
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Modal Sparepart</div>
                            </th>
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
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Status</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Aksi</div>
                            </th>
                        </tr>
                    </thead>
                    <!-- Table body -->
                    <tbody class="text-sm divide-y divide-slate-200">
                        <!-- Row -->
                        @php
                            $i = 1
                        @endphp
                        @foreach($service_transactions as $transaction)                 
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
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="flex items-center">
                                        <label class="inline-flex">
                                            <span class="sr-only">Select</span>
                                            <input class="table-item form-checkbox" type="checkbox" value="{{ $transaction->id }}" @click="uncheckParent" />
                                        </label>
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $i++ }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <a href="{{ route('transaksi-servis-belum-disetujui.edit', $transaction->id) }}">
                                        <div class="flex items-center text-blue-600">
                                            <svg class="w-6 h-6 fill-current" viewBox="0 0 32 32">
                                                <path d="M19.7 8.3c-.4-.4-1-.4-1.4 0l-10 10c-.2.2-.3.4-.3.7v4c0 .6.4 1 1 1h4c.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4l-4-4zM12.6 22H10v-2.6l6-6 2.6 2.6-6 6zm7.4-7.4L17.4 12l1.6-1.6 2.6 2.6-1.6 1.6z" />
                                            </svg>
                                            <div class="font-medium">{{ $transaction->nomor_servis }}</div>
                                        </div>
                                    </a>
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
                                                <a href="https://api.whatsapp.com/send?phone={{$nomorwa}}&text=" target="__blank">
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
                                                        <div class="text-xs text-slate-200 whitespace-nowrap">Kirim pesan melalui Whatsapp</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End -->
                                            
                                            <!-- Start -->
                                            <div
                                                class="relative"
                                                x-data="{ open: false }"
                                                @mouseenter="open = true"
                                                @mouseleave="open = false"
                                            >
                                                @if ($transaction->exp_garansi != null)
                                                    <a href="https://wa.me/{{ $nomorwa }}/?text=*Notifikasi%20|%20{{ $toko->nama_toko }}*%20Barang Servis%20*{{ $transaction->type->name }}%20{{ $transaction->brand->name }}%20{{ $transaction->modelserie->name }}*%20dengan%20No.%20Servis%20*{{ $transaction->nomor_servis }}*%20{{ $transaction->status_servis }}%20dalam%20kondisi%20*{{ $transaction->kondisi_servis }}*%20pada%20tanggal%20{{ \Carbon\Carbon::parse($transaction->tgl_ambil)->translatedFormat('d F Y') }}%20oleh%20*{{ $transaction->pengambil }}*%20dengan%20pembayaran%20*{{ $transaction->cara_pembayaran }}*.%20Garansi%20akan%20berakhir%20pada%20tanggal%20*{{ \Carbon\Carbon::parse($transaction->exp_garansi)->translatedFormat('d F Y') }}*.%20Untuk%20Cek%20Status%20Garansi%20Servis%20Anda,%20silahkan%20buka%20Link%20berikut%20ini%20{{ env('APP_URL') }}/tracking.%20Terima%20Kasih%20atas%20kepercayaan%20Anda%20telah%20melakukan%20Servis%20di%20*{{ $toko->nama_toko }}*." target="__blank">
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
                                                    <a href="https://wa.me/{{ $nomorwa }}/?text=*Notifikasi%20|%20{{ $toko->nama_toko }}*%20Barang Servis%20*{{ $transaction->type->name }}%20{{ $transaction->brand->name }}%20{{ $transaction->modelserie->name }}*%20dengan%20No.%20Servis%20*{{ $transaction->nomor_servis }}*%20{{ $transaction->status_servis }}%20dalam%20kondisi%20*{{ $transaction->kondisi_servis }}*%20pada%20tanggal%20{{ \Carbon\Carbon::parse($transaction->tgl_ambil)->translatedFormat('d F Y') }}%20oleh%20*{{ $transaction->pengambil }}*%20dengan%20pembayaran%20*{{ $transaction->cara_pembayaran }}*.%20Item%20ini%20Tidak%20Ada%20Garansi.%20Terima%20Kasih%20atas%20kepercayaan%20Anda%20telah%20melakukan%20Servis%20di%20*{{ $toko->nama_toko }}*." target="__blank">
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
                                                    <div
                                                        class="min-w-56 bg-slate-800 p-2 rounded overflow-hidden mb-2"
                                                        x-show="open"
                                                        x-transition:enter="transition ease-out duration-200 transform"
                                                        x-transition:enter-start="opacity-0 translate-y-2"
                                                        x-transition:enter-end="opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-out duration-200"
                                                        x-transition:leave-start="opacity-100"
                                                        x-transition:leave-end="opacity-0"
                                                        x-cloak
                                                    >
                                                        <div class="text-xs text-slate-200">Kirim Nota Pengambilan dan link untuk cek Status Garansi</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End -->
                                        @else
                                            <!-- Start -->
                                            <div
                                                class="relative"
                                                x-data="{ open: false }"
                                                @mouseenter="open = true"
                                                @mouseleave="open = false"
                                            >
                                                <a href="#">
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
                                                        <div class="text-xs text-slate-200 whitespace-nowrap">Kirim pesan melalui Whatsapp</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End -->
                                            
                                            <!-- Start -->
                                            <div
                                                class="relative"
                                                x-data="{ open: false }"
                                                @mouseenter="open = true"
                                                @mouseleave="open = false"
                                            >
                                                <a href="#">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-invoice" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                    <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                    <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                    <line x1="9" y1="7" x2="10" y2="7" />
                                                    <line x1="9" y1="13" x2="15" y2="13" />
                                                    <line x1="13" y1="17" x2="15" y2="17" />
                                                    </svg>
                                                </a>
                                                <div class="z-10 absolute bottom-full left-1/2 -translate-x-1/2">
                                                    <div
                                                        class="min-w-56 bg-slate-800 p-2 rounded overflow-hidden mb-2"
                                                        x-show="open"
                                                        x-transition:enter="transition ease-out duration-200 transform"
                                                        x-transition:enter-start="opacity-0 translate-y-2"
                                                        x-transition:enter-end="opacity-100 translate-y-0"
                                                        x-transition:leave="transition ease-out duration-200"
                                                        x-transition:leave-start="opacity-100"
                                                        x-transition:leave-end="opacity-0"
                                                        x-cloak
                                                    >
                                                        <div class="text-xs text-slate-200">Kirim Nota Pengambilan dan link untuk cek Status Garansi</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End -->
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $transaction->type->name }} {{ $transaction->brand->name }} {{ $transaction->modelserie->name }}</div>
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
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">Rp. {{ number_format($transaction->modal_sparepart) }}</div>
                                </td>
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
                                    <div class="font-medium">{{ $transaction->tgl_ambil }}</div>
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
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <a href="{{ route('servis-belum-disetujui-approve.edit', $transaction->id) }}">
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
                                                                <form x-bind:action="'{{ route('transaksi-servis-belum-disetujui.destroy', '') }}/' + deleteId" method="post">
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
                            </tr>
                        @endforeach
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
                    fetch('/services/delete', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ selectedIds }),
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
                        body: JSON.stringify({ selectedIds }),
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
                        body: JSON.stringify({ selectedIds }),
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

    <!-- Pagination -->
    <div class="mt-8">
        {{ $service_transactions->links() }}
    </div>
</div>
