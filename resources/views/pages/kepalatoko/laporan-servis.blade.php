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
            <x-kepalatoko.card-laporan-hari :omzethari="$omzethari" :totalhari="$total_hari" :profithari="$profithari"/>
            <x-kepalatoko.card-laporan-bulan :omzetbulan="$omzetbulan" :totalbulan="$total_bulan" :profitbulan="$profitbulan"/>
            <x-kepalatoko.card-laporan-tahun :omzettahun="$omzettahun" :totaltahun="$total_tahun" :profittahun="$profittahun"/>
        </div>

        <!-- Table -->
        <livewire:laporan-servis-data></livewire:laporan-servis-data>

    </div>
</x-toko-layout>
