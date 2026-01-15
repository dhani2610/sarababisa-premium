<div class="flex flex-col col-span-full xl:col-span-3 bg-gradient-to-b from-slate-700 to-slate-800 shadow-lg rounded-sm border border-slate-800">
    <header class="px-5 py-4 border-b border-slate-600 flex items-center">
        <h2 class="font-semibold text-slate-200">Keuangan Bulan Ini</h2>
    </header>
    <div class="h-full flex flex-col px-5 py-6">
        <!-- CC container -->
        <div class="relative w-full max-w-sm mx-auto bg-slate-800 p-3 rounded-2xl">
            <!-- Credit Card -->
            <div class="relative aspect-[6/2] bg-gradient-to-tr from-indigo-500 to-indigo-400 p-5 rounded-xl overflow-hidden">
                <!-- Gradients -->
                <div class="absolute left-0 -bottom-1/3 w-[398px] aspect-square" aria-hidden="true">
                    <svg class="w-full h-full" width="398" height="392" viewBox="0 0 398 392" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <filter x="-88.2%" y="-88.2%" width="276.5%" height="276.5%" filterUnits="objectBoundingBox" id="glow-a">
                                <feGaussianBlur stdDeviation="50" in="SourceGraphic" />
                            </filter>
                        </defs>
                        <circle class="fill-indigo-100 opacity-60" filter="url(#glow-a)" cx="85" cy="85" r="85" transform="translate(0 216)" />
                    </svg>
                </div>
                <div class="absolute right-0 -top-1/3 w-[398px] aspect-square" aria-hidden="true">
                    <svg class="w-full h-full" width="398" height="392" viewBox="0 0 398 392" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <filter x="-88.2%" y="-88.2%" width="276.5%" height="276.5%" filterUnits="objectBoundingBox" id="glow-b">
                                <feGaussianBlur stdDeviation="50" in="SourceGraphic" />
                            </filter>
                        </defs>
                        <circle class="fill-sky-400 opacity-60" filter="url(#glow-b)" cx="85" cy="85" r="85" transform="translate(228 0)" />
                    </svg>
                </div>
                <div class="relative h-full flex flex-col justify-center items-center">
                    <!-- Card number -->
                    <div class="text-xl font-bold text-slate-200 drop-shadow-sm">
                        Rp. {{ number_format($bulantotalprofitkotor  - ($bulantotalprofitkotor  - $bulantotalprofitbersih) - $totalpengeluaran - $totalinsiden - $totalpembelian) }}
                     </div>
                </div>
            </div>
        </div>
        <!-- Details -->
        <div class="grow flex flex-col justify-center mt-3">
            <div class="text-xs text-slate-500 font-semibold uppercase mb-3">Rincian</div>
            <div class="space-y-2">
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <div class="text-slate-300">Profit Kotor</div>
                        <div class="text-slate-400 italic">Rp. {{ number_format($bulantotalprofitkotor ) }}</div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <div class="text-slate-300">Bonus Karyawan</div>
                        <div class="text-slate-400 italic">Rp. {{ number_format($bulantotalprofitbersih - $bulantotalprofitkotor ) }}</div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <div class="text-slate-300">Pengeluaran</div>
                        <div class="text-slate-400 italic">Rp. {{ number_format($totalpengeluaran) }}</div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <div class="text-slate-300">Pembelian</div>
                        <div class="text-slate-400 italic">Rp. {{ number_format($totalpembelian) }}</div>
                    </div>
                </div>
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <div class="text-slate-300">Insiden</div>
                        <div class="text-slate-400 italic">Rp. {{ number_format($totalinsiden) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
