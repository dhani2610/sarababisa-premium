<div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
    <!-- Table -->

    <div class="mb-4 sm:mb-0 px-5">
        <form action="{{ route('laporan-admin') }}" method="GET" class="flex items-center gap-2">
            <label for="filter_month" class="text-sm font-medium text-slate-600">Periode:</label>
            <input
                type="month"
                name="filter_month"
                id="filter_month"
                value="{{ request('filter_month', date('Y-m')) }}"
                class="form-input text-sm border-slate-200 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
            <button type="submit" class="btn-sm bg-blue-500 hover:bg-blue-600 text-white">
                Filter
            </button>

            @if(request('filter_month'))
                <a href="{{ route('laporan-admin') }}" class="btn-sm bg-white border-slate-200 text-slate-500 hover:text-slate-600">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="table-auto w-full">
            <!-- Table header -->
            <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Admin</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Transaksi Diinput</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Bonus</div>
                    </th>
                </tr>
            </thead>
            <!-- Table body -->
            <tbody class="text-sm divide-y divide-slate-200">
                <!-- Row -->
                @foreach($users as $item)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $item->name }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $item->filteredAdminservice->count() + $item->filteredAdminsale->count() }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{-- <div class="font-medium">
                                @php
                                $bonusservice = $item->adminservice->sum('profit')/100;
                                $bonusservice *= $item->persen;
                                $bonussale = $item->adminsale->sum('profit')/100;
                                $bonussale *= $item->persen;
                                @endphp
                                Rp. {{ number_format($bonusservice + $bonussale) }}
                            </div> --}}
                            <div class="font-medium">
                                @php
                                    $tipeBonusNota = $item->tipe_bonus_admin ?? 'Persen'; // default biar aman
                                    $persen = $item->persen ?? 0;
                                    $nominalBonus = $item->nominal_bonus_admin ?? 0;

                                    $totalProfitService = $item->filteredAdminservice->sum('profit');
                                    $totalProfitSale = $item->filteredAdminsale->sum('profit');
                                    $totalNotaService = $item->filteredAdminservice->count();
                                    $totalNotaSale = $item->filteredAdminsale->count();

                                    if ($tipeBonusNota === 'Persen') {
                                        $bonus = (($totalProfitService + $totalProfitSale) / 100) * $persen;
                                    } elseif ($tipeBonusNota === 'Tetap') {
                                        $totalNota = $totalNotaService + $totalNotaSale;
                                        $bonus = $totalNota * $nominalBonus;
                                    } else {
                                        $bonus = 0;
                                    }
                                @endphp

                                Rp. {{ number_format($bonus) }}
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
