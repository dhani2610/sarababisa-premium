<div>
    <div class="sm:flex sm:justify-between sm:items-center mt-5">
        <select wire:model="paginate" id="" class="form-select mb-2 md:mb-0">
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
        </select>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            <!-- Search form -->
            <x-search-form placeholder="YYYY-MM-DD" />

        </div>
    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <header class="px-5 py-4">
            <h2 class="font-semibold text-slate-800">Jumlah Servis Ditangani <span class="text-slate-400 font-medium">{{ $jumlah }}</span></h2>
        </header>
        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
                <!-- Table header -->
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                    <tr>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Tanggal Disetujui</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Tanggal Diambil</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Teknisi</div>
                        </th>
                       
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Tindakan</div>
                        </th>
                         <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Biaya Servis</div>
                        </th>
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Diskon</div>
                        </th>
                        </th><th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Pembayaran</div>
                        </th>
                        @if ($toko->is_bonus === 1)
                        <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-semibold text-left">Bonus</div>
                        </th>
                        @endif
                    </tr>
                </thead>
                <!-- Table body -->
                <tbody class="text-sm divide-y divide-slate-200">
                    <!-- Row -->
                    @foreach($services as $item)                  
                        <tr>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ \Carbon\Carbon::parse($item->tgl_disetujui)->translatedFormat('d F Y') }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ \Carbon\Carbon::parse($item->tgl_ambil)->translatedFormat('d F Y') }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                @if ($item->user)
                                    <div class="font-medium">{{ $item->user->name }}</div>
                                @else
                                    <div class="font-medium text-red-600">Akun sudah dihapus</div>
                                @endif
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">
                                    {{ implode(', ', json_decode($item->tindakan_servis) ?? []) }}
                                </div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ number_format($item->biaya) }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ number_format($item->diskon) }}</div>
                            </td>
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">{{ $item->cara_pembayaran }}</div>
                            </td>
                            @if ($toko->is_bonus === 1)
                            <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-medium">
                                    @php
                                        if ($item->tipe == 'Interface') {
                                            $bonus = $item->bonus_interface;
                                        }else{
                                            $bonus = $item->profit/100;
                                            $bonus *= Auth::user()->persen;
                                        }
                                    @endphp
                                    Rp. {{ number_format($bonus) }}
                                </div>
                            </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
    <!-- Pagination -->
    <div class="mt-8">
        {{ $services->links() }}
    </div>
    
</div>
