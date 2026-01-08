@section('title')
    Laporan Servis
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-3">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Laporan Servis ✨</h1>
            </div>

        </div>

        <div class="grid grid-cols-12 gap-6">
            {{-- HARIAN  --}}
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
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Omzet</div>
                            <div class="text-xl font-bold text-blue-500">Rp. {{ number_format($omzethari) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Pajak</div>
                            <div class="text-xl font-bold text-emerald-500">Rp. {{ number_format($pajakhari) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BULAN --}}
            <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-4 bg-white shadow-lg rounded-sm border border-slate-200">
                <div class="px-5 p-5">
                    <header class="flex justify-between items-start mb-2">
                        <!-- Icon -->
                        <h2 class="text-lg font-semibold text-slate-800 mb-2">Bulan Ini</h2>
                        <!-- Menu button -->
                        <div class="text-sm font-semibold text-white px-1.5 bg-indigo-500 rounded-full">{{ \Carbon\Carbon::parse(strtotime(now()))->translatedFormat('F') }}</div>
                    </header>
                    <div class="flex justify-between">
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Omzet</div>
                            <div class="text-xl font-bold text-blue-500">Rp. {{ number_format($omzetbulan) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Pajak</div>
                            <div class="text-xl font-bold text-emerald-500">Rp. {{ number_format($pajakbulan) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAHUN  --}}
            <div class="flex flex-col col-span-full sm:col-span-6 xl:col-span-4 bg-white shadow-lg rounded-sm border border-slate-200">
                <div class="p-5">
                    <header class="flex justify-between items-start mb-2">
                        <!-- Icon -->
                        <h2 class="text-lg font-semibold text-slate-800 mb-2">Tahun Ini</h2>
                        <!-- Menu button -->
                        <div class="text-sm font-semibold text-white px-1.5 bg-indigo-500 rounded-full">{{ \Carbon\Carbon::parse(strtotime(now()))->format('Y') }}</div>
                    </header>
                    <div class="flex justify-between">
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Omzet</div>
                            <div class="text-xl font-bold text-blue-500">Rp. {{ number_format($omzettahun) }}</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase mb-1">Pajak</div>
                            <div class="text-xl font-bold text-emerald-500">Rp. {{ number_format($pajaktahun) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <livewire:laporan-servis-pajak-data></livewire:laporan-servis-pajak-data>

    </div>
</x-toko-layout>
