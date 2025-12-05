@section('title')
    Pelacakan Status Servis
@endsection

<x-customer.header :users="$users"/>

<x-customer-layout>
    <!-- Content -->
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Riwayat Servis ✨</h1>
            </div>

        </div>

        <div class="space-y-2">
            @foreach ($services as $item)
                @php
                    if ($item->status_servis === 'Sudah Diambil') :
                        $status_color = 'bg-emerald-100 text-emerald-600';
                    elseif ($item->status_servis === 'Bisa Diambil') :
                        $status_color = 'bg-blue-100 text-blue-600';
                    else :
                        $status_color = 'bg-amber-100 text-amber-600';
                    endif;
                @endphp
                <div class="shadow-lg rounded-sm border px-5 py-4 bg-white border-slate-200">
                    <div class="md:flex justify-between items-center space-y-4 md:space-y-0">
                        <!-- Left side -->
                        <div class="flex items-start space-x-3 md:space-x-4">
                            <div>
                                <div class="text-xs font-semibold text-blue-600">#{{ $item->nomor_servis }}</div>
                                <div class="inline-flex font-semibold text-slate-800">{{ $item->type->name }} {{ $item->brand->name }} {{ $item->modelserie->name ?? '-' }}</div>
                                <div class="text-sm">{{ $item->kerusakan }}</div>
                            </div>
                        </div>
                        <!-- Right side -->
                        <div class="flex flex-col md:flex-row items-start space-y-1 md:space-y-0 md:space-x-4">
                            <div class="text-xs inline-flex font-medium rounded-full text-center px-2.5 py-1 {{ $status_color }}">{{ $item->status_servis }}</div>
                            <div class="text-sm text-slate-500 italic whitespace-nowrap">
                                @if ($item->status_servis === 'Sudah Diambil')
                                    Tgl. Ambil {{ \Carbon\Carbon::parse($item->tgl_ambil)->translatedFormat('d M Y') }}
                                @elseif ($item->status_servis === 'Bisa Diambil')
                                    Tgl. Selesai {{ \Carbon\Carbon::parse($item->tgl_selesai)->translatedFormat('d M Y') }}
                                @else
                                    Tgl. Masuk {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y') }}
                                @endif
                            </div>
                            @if ($item->status_servis === 'Sudah Diambil')
                                @if ($item->exp_garansi === null)
                                    <div class="text-sm text-red-600 italic">
                                        Garansi Tidak Ada
                                    </div>
                                @elseif ($item->exp_garansi < \Carbon\Carbon::now())
                                    <div class="text-sm text-red-600 italic">
                                        Garansi Sudah Kedaluwarsa {{ \Carbon\Carbon::parse($item->exp_garansi)->translatedFormat('d M Y') }}
                                    </div>
                                @else
                                    <div class="text-sm text-blue-600">Garansi Aktif s/d {{ \Carbon\Carbon::parse($item->exp_garansi)->translatedFormat('d M Y') }}</div>
                                @endif
                            @else

                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-customer-layout>
