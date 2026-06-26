<div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
    <!-- Table -->

    <div class="mb-4 sm:mb-0 px-5">
        <form action="{{ route('laporan-teknisi') }}" method="GET" class="flex items-center gap-2">
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
                <a href="{{ route('laporan-teknisi') }}" class="btn-sm bg-white border-slate-200 text-slate-500 hover:text-slate-600">
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
                        <div class="font-semibold text-left">Teknisi</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Servis Ditangani</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Bonus</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Target Servis</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Progres</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Bonus Pencapaian Target</div>
                    </th>
                </tr>
            </thead>
            <!-- Table body -->
            <tbody class="text-sm divide-y divide-slate-200">
                <!-- Row -->
                @foreach($users as $item)
                    @php
                        // $bonus_cek = ($item->servicetransaction->where('tipe','Hardware')->sum('profit') / 100) * $item->persen;
                        // $bonus = $bonus_cek + $item->servicetransaction->where('tipe','Interface')->sum('bonus_interface');
                        $bonus = calculateBonus($item->id);
                    @endphp

                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $item->name }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $item->filteredServicetransaction->count() }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">
                                Rp. {{ number_format($bonus) }}
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">
                                @if ($item->filteredTargetServis->sum('item') != 0)
                                    {{ $item->filteredTargetServis->sum('item') }}
                                @else
                                    -
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">
                                @if ($item->filteredTargetServis->sum('item') != 0)
                                    {{ ($item->filteredServicetransaction->count() / $item->filteredTargetServis->sum('item')) * 100 }}%
                                @else
                                    -
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">
                                @if ($item->filteredTargetServis->sum('item') != 0)
                                    @php
                                        $reward = $bonus * (($item->filteredServicetransaction->count() / $item->filteredTargetServis->sum('item')) * 100) / 100;
                                    @endphp
                                    @if ($item->filteredServicetransaction->count() < $item->filteredTargetServis->sum('item'))
                                    Rp. {{ number_format($reward) }}
                                    @else
                                    Rp. {{ number_format($bonus) }}
                                    @endif
                                @else
                                    -
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
