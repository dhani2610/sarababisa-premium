<div class="flex flex-col col-span-full bg-indigo-200 shadow-lg rounded-sm mb-8">
    <div class="px-5 py-6">

        <div class="xl:flex xl:justify-between md:items-center">
            <!-- Left side -->
            <div class="flex items-center mb-4 md:mb-0">
                <!-- User info -->
                <div>
                    {{-- <div class="mb-2">Halo, <strong class="font-semibold text-slate-800">{{ Auth::user()->name }}</strong> 👋,</div> --}}
                    <div class="mb-2">Halo, <strong class="font-semibold text-slate-800">{{ getCabangNameUser() }}</strong> 👋,</div>
                    <p>Berikut adalah keuangan toko Anda hari ini:</p>
                </div>
            </div>
            <!-- Right side -->
            <div class="flex flex-wrap">
            <div class="flex items-center py-2">
                <div class="mr-5">
                    <div class="flex items-center">
                        <div class="text-3xl font-bold text-blue-600 mr-2">Rp. {{ number_format($haritotalomzet) }}</div>
                    </div>
                    <div class="text-sm font-semibold text-slate-800">Omzet</div>
                </div>
                <div class="hidden md:block w-px h-8 bg-slate-200 mr-5" aria-hidden="true"></div>
            </div>
            <div class="flex items-center py-2">
                <div class="mr-5">
                    <div class="flex items-center">
                        <div class="text-3xl font-bold text-green-800 mr-2">Rp. {{ number_format($haritotalprofitkotor) }}</div>
                    </div>
                    <div class="text-sm font-semibold text-slate-800">Profit</div>
                </div>
                <div class="hidden md:block w-px h-8 bg-slate-200 mr-5" aria-hidden="true"></div>
            </div>
            <div class="flex items-center py-2">
                <div class="mr-5">
                    <div class="flex items-center">
                        <div class="text-3xl font-bold text-red-800 mr-2">Rp. {{ number_format($haripengeluaran) }}</div>
                    </div>
                    <div class="text-sm font-semibold text-slate-800">Pengeluaran</div>
                </div>
                <div class="hidden md:block w-px h-8 bg-slate-200 mr-5" aria-hidden="true"></div>
            </div>
            <div class="flex items-center py-2">
                <div class="mr-5">
                    <div class="flex items-center">
                        <div class="text-3xl font-bold text-indigo-800 mr-2">Rp. {{ number_format($haripembelian) }}</div>
                    </div>
                    <div class="text-sm font-semibold text-slate-800">Pembelian</div>
                </div>
            </div>
        </div>
        </div>

    </div>
</div>
