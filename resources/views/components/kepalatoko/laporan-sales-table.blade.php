<div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
    <div class="mb-4 sm:mb-0 px-5">
        <form action="{{ route('laporan-sales') }}" method="GET" class="flex items-center gap-2">
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
                <a href="{{ route('laporan-sales') }}" class="btn-sm bg-white border-slate-200 text-slate-500 hover:text-slate-600">
                    Reset
                </a>
            @endif
        </form>
    </div>
    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="table-auto w-full">
            <!-- Table header -->
            <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Sales</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Produk Terjual</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Bonus</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Target Penjualan</div>
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
                        $bonus = $item->filteredSale->sum('profit')/100;
                        $bonus *= $item->persen;
                    @endphp
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $item->name }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $item->sale->sum('quantity') }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">
                                Rp. {{ number_format($bonus) }}
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">
                                @if ($item->filteredTargetSale->sum('item') != 0)
                                    {{ $item->filteredTargetSale->sum('item') }}
                                @else
                                    -
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">
                                @if ($item->filteredTargetSale->sum('item') != 0)
                                    {{ ($item->filteredSale->sum('quantity') / $item->filteredTargetSale->sum('item')) * 100 }}%
                                @else
                                    -
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">
                                @if ($item->filteredTargetSale->sum('item') != 0)
                                    @php
                                        $reward = $bonus * (($item->filteredSale->sum('quantity') / $item->filteredTargetSale->sum('item')) * 100) / 100;
                                    @endphp
                                    @if ($item->filteredSale->sum('quantity') < $item->filteredTargetSale->sum('item'))
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
