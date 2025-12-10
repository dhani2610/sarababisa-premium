<div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-4 bg-white shadow-lg rounded-sm border border-slate-200">
    <div class="px-5 p-5">
        <header class="flex justify-between items-start mb-2">
            <!-- Icon -->
            <h2 class="text-lg font-semibold text-slate-800 mb-2">Hari Ini</h2>
            <!-- Menu button -->
            <div class="text-sm font-semibold text-white px-1.5 bg-indigo-500 rounded-full">{{ \Carbon\Carbon::parse(strtotime(now()))->translatedFormat('d F Y') }}</div>
        </header>
        <div class="flex justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Jumlah Produk</div>
                <div class="text-2xl font-bold text-blue-500">{{ $penjualanhari }}</div>
            </div>
            @if ($toko->is_bonus_produk === 1)
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Bonus</div>
                <div class="text-2xl font-bold text-emerald-500">
                    @php
                        $bonus = $bonushari/100;
                        $bonus *= Auth::user()->persen;
                    @endphp
                    Rp. {{ number_format($bonus) }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
