<div class="bg-white shadow-lg rounded-sm border border-slate-200 mb-8">
    <header class="px-5 py-4">
        <h2 class="font-semibold text-slate-800">Proses <span class="text-slate-400 font-medium">{{ $count }}</span></h2>
    </header>
    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="table-auto w-full">
            <!-- Table header -->
            <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                <tr>
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
                        <div class="font-semibold text-left">Pelanggan</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Hubungi</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Nama Barang</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Kelengkapan</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Kerusakan</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">DP</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Est. Biaya</div>
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
                @foreach($processes as $process)
                    @php                    
                        if ($process->status_servis === 'Sedang Dikerjakan') :
                            $status_color = 'bg-emerald-100 text-emerald-600';
                            $total_color = 'text-emerald-500';
                        elseif ($process->status_servis === 'Menunggu Sparepart') :
                            $status_color = 'bg-amber-100 text-amber-600';
                            $total_color = 'text-amber-500';
                        elseif ($process->status_servis === 'Menunggu Konfirmasi') :
                            $status_color = 'bg-rose-100 text-rose-500';
                            $total_color = 'text-rose-500';
                        else :
                            $status_color = 'bg-slate-100 text-slate-500';
                            $total_color = 'text-slate-500';
                        endif;
                    @endphp                  
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $i++ }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $process->nomor_servis }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div>{{ \Carbon\Carbon::parse($process->created_at)->format('d/m/Y') }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $process->customer->nama }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="flex space-x-1">
                                <a href="https://api.whatsapp.com/send?phone={{ $process->customer->nomor_hp }}&text=
                                    Isi teks disini">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                        <path d="M9 10a0.5 .5 0 0 0 1 0v-1a0.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a0.5 .5 0 0 0 0 -1h-1a0.5 .5 0 0 0 0 1" />
                                    </svg>
                                </a>
                                <a href="https://api.whatsapp.com/send?phone={{ $process->customer->nomor_hp }}&text=
                                    Isi teks disini">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-report-search" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M8 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h5.697" />
                                    <path d="M18 12v-5a2 2 0 0 0 -2 -2h-2" />
                                    <rect x="8" y="3" width="6" height="4" rx="2" />
                                    <path d="M8 11h4" />
                                    <path d="M8 15h3" />
                                    <circle cx="16.5" cy="17.5" r="2.5" />
                                    <path d="M18.5 19.5l2.5 2.5" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $process->type->name }} {{ $process->brand->name }} {{ $process->modelserie->name }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">
                                @if ($process->kelengkapan != null)
                                    {{ $process->kelengkapan }}
                                @else
                                    Unit Only
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium capitalize">{{ $process->kerusakan }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">Rp. {{ number_format($process->uang_muka) }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">Rp. {{ number_format($process->estimasi_biaya) }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <a href="{{ route('teknisi-transaksi-servis.edit', $process->id) }}">
                                <div class="inline-flex font-medium rounded-full text-center px-2.5 py-0.5 {{$status_color}}">{{ $process->status_servis }}</div>
                            </a>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                            <div class="space-x-1 flex">
                                <a href="{{ route('teknisi-transaksi-servis.show', $process->id) }}">
                                    <button class="text-slate-400 hover:text-slate-500 rounded-full">
                                        <span class="sr-only">Konfirmasi</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-clipboard-check" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#00b341" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                            <path d="M9 5h-2a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-12a2 2 0 0 0 -2 -2h-2" />
                                            <rect x="9" y="3" width="6" height="4" rx="2" />
                                            <path d="M9 14l2 2l4 -4" />
                                        </svg>
                                    </button>
                                </a>
                                <!-- Start -->
                                <div x-data="{ modalOpen: false }">
                                    <button
                                        @click.prevent="modalOpen = true"
                                        aria-controls="basic-modal"
                                    >
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
                                            id="basic-modal"
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
                                            <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                                                <!-- Modal header -->
                                                <div class="px-5 py-3 border-b border-slate-200">
                                                    <div class="flex justify-between items-center">
                                                        <div class="font-semibold text-slate-800">Pilih Jenis Printer</div>
                                                        <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
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
                                                            <p>Silahkan pilih printer untuk cetak Nota Tanda Terima Servis.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Modal footer -->
                                                <div class="px-5 py-4">
                                                    <div class="flex flex-wrap justify-end space-x-2">
                                                        <button class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                                        <a href="{{ route('teknisi-cetak-termal', $process->id) }}"  hidetargetblank>
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
                                                        <a href="{{ route('teknisi-cetak-tanda-terima', $process->id) }}"  hidetargetblank>
                                                            <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">
                                                                <span class="mr-1">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer" width="20" height="20" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                    <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                                    <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                                    <rect x="7" y="13" width="10" height="8" rx="2" />
                                                                    </svg>
                                                                </span>
                                                                Printer Inkjet/Laser
                                                            </button>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                            
                                </div>
                                <!-- End -->
                                
                                <!-- Start -->
                                <div x-data="{ modalOpen: false }">
                                    <button class="text-rose-500 hover:text-rose-600 rounded-full" @click.prevent="modalOpen = true" aria-controls="danger-modal">
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
                                                        <form action="{{ route('teknisi-transaksi-servis.destroy', $process->id) }}" method="post">
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
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    </div>
</div>