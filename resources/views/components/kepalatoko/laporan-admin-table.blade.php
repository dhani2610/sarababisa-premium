<div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
    <!-- Table -->
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
                            <div class="font-medium">{{ $item->adminservice->count() + $item->adminsale->count() }}</div>
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

                                    $totalProfitService = $item->adminservice->sum('profit');
                                    $totalProfitSale = $item->adminsale->sum('profit');
                                    $totalNotaService = $item->adminservice->count();
                                    $totalNotaSale = $item->adminsale->count();

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
