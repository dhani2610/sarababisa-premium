@section('title')
    Dashboard Admin Toko
@endsection

<x-admin-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page Intro -->                     
        <x-admintoko.header-admin :totalbonus="$totalbonus" :toko="$toko"/>

        <div class="space-y-3 my-6">
            @if ($stokhabis != null)
                <div class="px-4 py-2 rounded-sm text-sm bg-amber-100 border border-amber-200 text-amber-600">
                    <div class="flex w-full justify-between items-start">
                        <div class="flex">
                            <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                                <path d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                            </svg>
                            <div class="font-medium">Ada {{ $stokhabis }} produk yang kehabisan stok nih, cek sekarang!</div>
                        </div>
                        <a class="font-medium ml-4 mt-[3px]" href="{{ route('admin-item-habis.index') }}">-&gt;</a>
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
                        <a class="font-medium text-white ml-4 mt-[3px]" href="{{ route('admin-transaksi-servis.index') }}">-&gt;</a>
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
                        <a class="font-medium text-white ml-4 mt-[3px]" href="{{ route('admin-inventaris.index') }}">-&gt;</a>
                    </div>
                </div>
            @endif
            <!-- Reminder Inventaris End -->
        </div>
        
        <!-- Cards -->
        <div class="grid grid-cols-12 gap-6">

            <div class="flex flex-col col-span-full xl:col-span-4 bg-white shadow-lg rounded-sm border border-slate-200">
                <header class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-800">Progres Target Bulanan</h2>
                </header>
                <div class="h-full flex flex-col px-5 py-3">
                    <!-- Circle -->
                    @php
                        $circumference = 30 * 2 * pi();
                        if ($totalbudgets > 0) {
                            $percent = round(($totalprofit / $totalbudgets) * 100);
                        } else {
                            $percent = 0;
                        }
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
                </div>
            </div>

        </div>

    </div>
</x-admin-layout>
