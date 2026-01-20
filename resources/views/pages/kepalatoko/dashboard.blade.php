@section('title')
    Dashboard Kepala Toko
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Welcome banner -->
        <x-dashboard.welcome-banner :haritotalomzet="$haritotalomzet" :haritotalprofitkotor="$haritotalprofitkotor" :haripengeluaranToko="$haripengeluaranToko" :haripengeluaranServis="$haripengeluaranServis" :haripengeluaranPenjualan="$haripengeluaranPenjualan" :haripembelian="$haripembelian"/>

        <!-- Banner -->
        @if (Auth::user()->role != 'Investor')
        <div class="mb-6">
            <div class="space-y-3">
                @if ($stokhabis != null)
                    <div class="px-4 py-2 rounded-sm text-sm bg-amber-100 border border-amber-200 text-amber-600">
                        <div class="flex w-full justify-between items-start">
                            <div class="flex">
                                <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                                    <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                                </svg>
                                <div class="font-medium">Ada {{ $stokhabis }} produk yang hampir kehabisan stok nih, cek sekarang!</div>
                            </div>
                            <a class="font-medium ml-4 mt-[3px]" href="{{ route('item.index') }}">-&gt;</a>
                        </div>
                    </div>
                @endif
                <!-- Reminder Servis Start -->
                @if ($reminders > 0)
                    <div class="px-4 py-2 rounded-sm text-sm bg-red-700 text-white">
                        <div class="flex w-full justify-between items-start">
                            <div class="flex">
                                <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                                    <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                                </svg>
                                <div class="font-medium">Ada {{ $reminders }} servis yang belum dikerjakan lebih dari 1 minggu nih, cek sekarang!</div>
                            </div>
                            <a class="font-medium text-white ml-4 mt-[3px]" href="{{ route('transaksi-servis.index') }}">-&gt;</a>
                        </div>
                    </div>
                @endif
                <!-- Reminder Servis End -->
                <!-- Reminder Inventaris Start -->
                @if ($inventories > 0)
                    <div class="px-4 py-2 rounded-sm text-sm bg-red-700 text-white">
                        <div class="flex w-full justify-between items-start">
                            <div class="flex">
                                <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                                    <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                                </svg>
                                <div class="font-medium">Ada {{ $inventories }} inventaris yang perlu diganti, cek sekarang!</div>
                            </div>
                            <a class="font-medium text-white ml-4 mt-[3px]" href="{{ route('inventaris.index') }}">-&gt;</a>
                        </div>
                    </div>
                @endif
                <!-- Reminder Inventaris End -->
                <!-- Start -->
                @if ($approveservis != null)
                    <div class="px-4 py-2 rounded-sm text-sm bg-emerald-500 text-white">
                        <div class="flex w-full justify-between items-start">
                            <div class="flex">
                                <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                                    <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                                </svg>
                                <div class="font-medium">Hore! Ada {{ $approveservis }} transaksi servis selesai nih, cek sekarang!</div>
                            </div>
                            <a class="font-medium text-white ml-4 mt-[3px]" href="{{ route('transaksi-servis-belum-disetujui.index') }}">-&gt;</a>
                        </div>
                    </div>
                @endif
                <!-- End -->
                <!-- Start -->
                @if ($approvepenjualan != null)
                    <div class="px-4 py-2 rounded-sm text-sm bg-emerald-500 text-white">
                        <div class="flex w-full justify-between items-start">
                            <div class="flex">
                                <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                                    <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                                </svg>
                                <div class="font-medium">Hore! Ada {{ $approvepenjualan }} transaksi produk terjual nih, cek sekarang!</div>
                            </div>
                            <a class="font-medium text-white ml-4 mt-[3px]" href="{{ route('transaksi-produk.index') }}">-&gt;</a>
                        </div>
                    </div>
                @endif
                <!-- End -->
                <!-- End -->
                <!-- Start -->
                @if ($approvekasbon != null)
                    <div class="px-4 py-2 rounded-sm text-sm bg-amber-500 text-white">
                        <div class="flex w-full justify-between items-start">
                            <div class="flex">
                                <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                                    <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                                </svg>
                                <div class="font-medium">Ada {{ $approvekasbon }} kasbon menunggu persetujuan, cek sekarang!</div>
                            </div>
                            <a class="font-medium text-white ml-3 mt-[3px]" href="{{ route('kasbon.index') }}">-&gt;</a>
                        </div>
                    </div>
                @endif
                <!-- End -->
                <!-- Start -->
                @if ($approvepengeluaran != null)
                    <div class="px-4 py-2 rounded-sm text-sm bg-amber-500 text-white">
                        <div class="flex w-full justify-between items-start">
                            <div class="flex">
                                <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                                    <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                                </svg>
                                <div class="font-medium">Ada {{ $approvepengeluaran }} pengeluaran menunggu persetujuan, cek sekarang!</div>
                            </div>
                            <a class="font-medium text-white ml-3 mt-[3px]" href="{{ route('pengeluaran.index') }}">-&gt;</a>
                        </div>
                    </div>
                @endif
                <!-- End -->
            </div>
        </div>
        @endif

        <div>
            <div class="px-4 py-2 rounded-sm text-sm  text-white" style="background-color: silver">
                <div class="mb-4 sm:mb-0 px-5">
                    <form action="{{ url('/dashboard') }}" method="GET" class="flex items-center gap-2">
                        {{-- <label for="filter_month" class="text-sm font-medium text-slate-600">Periode:</label> --}}
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
                            <a href="{{ url('/dashboard') }}" class="btn-sm bg-white border-slate-200 text-slate-500 hover:text-slate-600">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">

            <!-- Card Keuangan -->
            {{-- @dd('total_bonus_karyawan',$total_bonus_karyawan) --}}
            <x-kepalatoko.keuangan-card :bulantotalprofitbersih="$bulantotalprofitbersih" :bulantotalprofitkotor="$bulantotalprofitkotor" :totalpengeluaran="$totalpengeluaran" :totalinsiden="$totalinsiden" :totalpembelian="$totalpembelian" :total_bonus_karyawan="$total_bonus_karyawan"/>

            {{-- Progres --}}
            <div class="flex flex-col col-span-full xl:col-span-3 bg-white shadow-lg rounded-sm border border-slate-200">
                <header class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-800">Progres Target Bulanan</h2>
                </header>
                <div class="h-full flex flex-col px-5 py-3">
                    <!-- Circle -->
                @php
                    $circumference = 30 * 2 * pi();
                    $percent = $totalbudgets != 0 ? round(($bulantotalprofitbersih / $totalbudgets) * 100) : 0;
                @endphp

                    <div class="inline-flex items-center justify-center overflow-hidden rounded-full">
                        <svg class="w-20 h-20">
                            <circle
                            class="text-gray-300"
                            stroke-width="5"
                            stroke="currentColor"
                            fill="transparent"
                            r="30"
                            cx="40"
                            cy="40"
                            />
                            <circle
                            class="text-blue-600"
                            stroke-width="5"
                            stroke-dasharray="{{ $circumference }}"
                            stroke-dashoffset="{{ $circumference - $percent / 100 * $circumference }}"
                            stroke-linecap="round"
                            stroke="currentColor"
                            fill="transparent"
                            r="30"
                            cx="40"
                            cy="40"
                            />
                        </svg>
                        <span class="absolute text-xl text-blue-700">{{ $percent }}%</span>
                    </div>
                    <div class="text-xl font-bold text-slate-800 text-center">Rp. {{ number_format($bulantotalprofitbersih) }} / Rp. {{ number_format($totalbudgets) }}</div>
                    @if ($bulantotalprofitbersih < $totalbudgets)
                        <p class="text-center mt-2 text-sm font-semibold text-blue-700">Tingkatkan profit hingga <span class="text-red-600">Rp. {{ number_format($totalbudgets - $bulantotalprofitbersih) }}</span> lagi!</p>
                    @else
                        <p class="text-center mt-2 text-sm font-semibold text-blue-700">Selamat kamu sudah <span class="text-green-600">BERHASIL</span> mencapai target! Profit toko sekarang lebih Rp. {{ number_format($bulantotalprofitbersih - $totalbudgets) }} dari target 👏🏻</p>
                    @endif
                </div>
            </div>
             {{-- Profit Servis --}}
            <div class="flex flex-col col-span-full xl:col-span-3 bg-white shadow-lg rounded-sm border border-slate-200">
                <header class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-800">Profit Servis</h2>
                </header>
                <div class="flex flex-col h-full">
                    <div class="px-5 py-3">
                        <div class="flex items-center">
                            <div class="relative flex items-center justify-center w-4 h-4 rounded-full bg-green-100 mr-3"
                                aria-hidden="true">
                                <div class="absolute w-1.5 h-1.5 rounded-full bg-green-500"></div>
                            </div>
                            <div>
                                <div class="text-xl font-bold text-slate-800 mr-2">Rp. {{ number_format($bulanprofitbersihservis) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="grow px-5 pt-0 pb-1">
                        <div class="overflow-x-auto">
                            <table class="table-auto w-full">
                                <thead class="text-xs uppercase text-slate-400">
                                <tr>
                                    <th class="py-2">
                                        <div class="font-semibold text-left">Item</div>
                                    </th>
                                    <th class="py-2">
                                        <div class="font-semibold text-right">Profit</div>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-slate-100">
                                @foreach ($types as $item)
                                    <tr>
                                        <td class="py-2">
                                            <div class="text-left">{{ $item->name }}</div>
                                        </td>
                                        <td class="py-2">
                                            <div class="font-medium text-right text-slate-800">Rp. {{ number_format($item->service->sum('profit')) }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Profit Penjualan --}}
            <div class="flex flex-col col-span-full xl:col-span-3 bg-white shadow-lg rounded-sm border border-slate-200">
                <header class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-800">Profit Penjualan</h2>
                </header>
                <div class="flex flex-col h-full">
                    <div class="px-5 py-3">
                        <div class="flex items-center">
                            <div class="relative flex items-center justify-center w-4 h-4 rounded-full bg-blue-100 mr-3"
                                aria-hidden="true">
                                <div class="absolute w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                            </div>
                            <div>
                                <div class="text-xl font-bold text-slate-800 mr-2">Rp. {{ number_format($bulanprofitbersihpenjualan) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="grow px-5 pt-0 pb-1">
                        <div class="overflow-x-auto">
                            <table class="table-auto w-full">
                                <thead class="text-xs uppercase text-slate-400">
                                <tr>
                                    <th class="py-2">
                                        <div class="font-semibold text-left">Item</div>
                                    </th>
                                    <th class="py-2">
                                        <div class="font-semibold text-right">Profit</div>
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="text-sm divide-y divide-slate-100">
                                    @foreach ($categorySales as $categorySale)
                                        <tr>
                                            <td class="py-2">
                                                <div class="text-left uppercase">{{ $categorySale['category'] }}</div>
                                            </td>
                                            <td class="py-2">
                                                <div class="font-medium text-right text-slate-800">
                                                    Rp. {{ number_format($categorySale['total_sales']) }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 mt-4">
            {{-- Grafik Servis --}}
            <x-dashboard.grafik-servis />
            {{-- Grafik Penjualan --}}
            <x-dashboard.grafik-penjualan />
            {{-- Grafik Pengeluaran --}}
            <x-dashboard.grafik-pengeluaran />
            @if ($hasData)
                {{-- Grafik Target --}}
                <x-dashboard.grafik-target />
                <x-dashboard.grafik-target-persen />
            @endif
        </div>
    </div>
</x-toko-layout>
