@section('title')
    Pelacakan Status Servis
@endsection

<script src="//unpkg.com/alpinejs" defer></script>

<x-customer.header :users="$users" />

<x-customer-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">

        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl text-slate-800 font-bold flex items-center gap-2">
                    Riwayat Servis <span class="text-2xl">✨</span>
                </h1>
                @if($customers)
                <p class="text-slate-500 mt-1">
                    Halo, <span class="font-semibold text-slate-700">{{ $customers->nama }}</span> ({{ $customers->nomor_hp }})
                </p>
                @endif
            </div>
            <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-slate-200">
                <span class="text-xs text-slate-500 uppercase font-bold tracking-wider">Total Transaksi</span>
                <div class="text-lg font-bold text-blue-600">Rp {{ number_format($totalbiaya) }}</div>
            </div>
        </div>

        <div class="space-y-6">
            @forelse ($services as $item)
                @php
                    // Logic Warna Status
                    $status_bg = 'bg-slate-100 text-slate-600';
                    $status_icon = '🕒';

                    if ($item->status_servis === 'Sudah Diambil') {
                        $status_bg = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                        $status_icon = '✅';
                    } elseif ($item->status_servis === 'Bisa Diambil') {
                        $status_bg = 'bg-blue-100 text-blue-700 border-blue-200';
                        $status_icon = '📦';
                    } elseif ($item->status_servis === 'Dibatalkan') {
                        $status_bg = 'bg-red-100 text-red-700 border-red-200';
                        $status_icon = '❌';
                    } elseif ($item->status_servis === 'Sedang Dikerjakan') {
                        $status_bg = 'bg-amber-100 text-amber-700 border-amber-200';
                        $status_icon = '🔧';
                    }

                    // Perhitungan Biaya (Sesuai Logic PDF)
                    $subtotal = $item->biaya - $item->diskon;
                    if (!empty($item->ppn)) {
                        $ppnValue = ($subtotal * $item->ppn) / 100;
                        $totalAkhir = $subtotal + $ppnValue;
                    } else {
                        $ppnValue = 0;
                        $totalAkhir = $subtotal;
                    }

                    // Sisa Bayar
                    $sisaBayar = 0;
                    if ($item->kondisi_servis != 'Dibatalkan') {
                        $sisaBayar = max(0, $totalAkhir - $item->uang_muka);
                    }
                @endphp

                <div x-data="{ open: false }" class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 border border-slate-200 overflow-hidden">

                    <div class="p-5">
                        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">

                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $status_bg }}">
                                        {{ $status_icon }} {{ $item->status_servis }}
                                    </span>
                                    <span class="text-xs text-slate-400 font-mono">#{{ $item->nomor_servis }}</span>
                                </div>
                                <h3 class="text-lg font-bold text-slate-800 leading-tight">
                                    {{ $item->type->name ?? 'Device' }} {{ $item->brand->name ?? '' }} {{ $item->modelserie->name ?? '' }}
                                </h3>
                                <p class="text-sm text-slate-500 mt-1">
                                    {{ $item->warna ?? '-' }} • {{ $item->capacity->name ?? '-' }}
                                </p>

                                <div class="mt-3 bg-red-50 text-red-700 px-3 py-2 rounded-md text-sm inline-block border border-red-100">
                                    <span class="font-semibold">Keluhan:</span> {{ $item->kerusakan }}
                                </div>
                            </div>

                            <div class="lg:text-right flex flex-col lg:items-end gap-1 text-sm text-slate-600">
                                <div><span class="text-slate-400">Masuk:</span> {{ \Carbon\Carbon::parse($item->created_at)->translatedFormat('d M Y') }}</div>
                                @if($item->tgl_selesai)
                                    <div><span class="text-slate-400">Selesai:</span> {{ \Carbon\Carbon::parse($item->tgl_selesai)->translatedFormat('d M Y') }}</div>
                                @endif
                                @if($item->tgl_ambil)
                                    <div><span class="text-slate-400">Diambil:</span> <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($item->tgl_ambil)->translatedFormat('d M Y') }}</span></div>
                                @endif

                                <a href="{{ route('kepalatoko-pengambilan-cetak-inkjet', $item->id) }}" target="_blank" class="mt-2 inline-flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium w-full lg:w-auto">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                    Cetak Nota
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 px-5 py-2 flex justify-between items-center cursor-pointer hover:bg-slate-100 transition" @click="open = !open">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Detail Pengerjaan & Biaya</span>
                        <button class="text-slate-400 hover:text-indigo-600">
                            <svg class="w-5 h-5 transform transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    </div>

                    <div x-show="open" x-collapse style="display: none;">
                        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-200">

                            <div>
                                <h4 class="font-semibold text-slate-800 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                    Detail Perangkat
                                </h4>
                                <ul class="text-sm space-y-2 text-slate-600">
                                    <li class="flex justify-between border-b border-slate-100 pb-1"><span>IMEI/SN:</span> <span class="font-mono text-slate-800">{{ $item->imei ?? '-' }}</span></li>
                                    <li class="flex justify-between border-b border-slate-100 pb-1"><span>Kelengkapan:</span> <span class="text-slate-800 text-right">{{ $item->kelengkapan ?? 'Unit Only' }}</span></li>
                                    <li class="flex justify-between border-b border-slate-100 pb-1"><span>Kondisi Awal (QC):</span> <span class="text-slate-800">{{ $item->qc_masuk ?? '-' }}</span></li>
                                    <li class="flex justify-between border-b border-slate-100 pb-1"><span>Kondisi Akhir (QC):</span> <span class="text-slate-800">{{ $item->qc_keluar ?? '-' }}</span></li>
                                    <li class="flex justify-between pb-1"><span>Teknisi:</span> <span class="text-slate-800">{{ $item->user->name ?? '-' }}</span></li>
                                </ul>

                                <h4 class="font-semibold text-slate-800 mt-5 mb-3">Tindakan Servis & Garansi</h4>

                                @php
                                    $listTindakan = json_decode($item->tindakan_servis, true);
                                    $listBiaya = json_decode($item->biaya_j, true);
                                    $listGaransi = json_decode($item->exp_garansi_j, true);
                                @endphp

                                @if ($listTindakan)
                                    <div class="space-y-2">
                                        @foreach ($listTindakan as $key => $tindakan)
                                            <div class="bg-indigo-50 p-2 rounded border border-indigo-100 text-sm">

                                                <div class="flex justify-between items-start">
                                                    <div class="font-medium text-slate-700">
                                                        {{ $tindakan }}
                                                    </div>
                                                    <div class="font-semibold text-slate-900 ml-2 whitespace-nowrap">
                                                        Rp. {{ number_format($listBiaya[$key] ?? 0) }}
                                                    </div>
                                                </div>

                                                @php
                                                    $garansiDate = $listGaransi[$key] ?? null;
                                                @endphp
                                                <div class="text-xs mt-1 {{ $garansiDate ? 'text-blue-600' : 'text-slate-400' }}">
                                                    {{ $garansiDate ? 'Garansi s/d ' . \Carbon\Carbon::make($garansiDate)->translatedFormat('d M Y') : 'Tidak ada garansi' }}
                                                </div>

                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500">- {{ $item->tindakan_servis }}</p>
                                @endif
                            </div>

                            <div class="bg-slate-50 p-4 rounded-lg h-fit border border-slate-200">
                                <h4 class="font-semibold text-slate-800 mb-3 border-b border-slate-200 pb-2">Rincian Pembayaran</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-slate-600">Biaya Jasa & Sparepart</span>
                                        <span class="font-medium">Rp {{ number_format($item->biaya) }}</span>
                                    </div>
                                    @if($item->diskon > 0)
                                    <div class="flex justify-between text-green-600">
                                        <span>Diskon</span>
                                        <span>- Rp {{ number_format($item->diskon) }}</span>
                                    </div>
                                    @endif
                                    @if($ppnValue > 0)
                                    <div class="flex justify-between text-slate-600">
                                        <span>PPN ({{ $item->ppn }}%)</span>
                                        <span>+ Rp {{ number_format($ppnValue) }}</span>
                                    </div>
                                    @endif


                                    <div class="border-t border-slate-200 my-2 pt-2 flex justify-between font-bold text-slate-800 text-base">
                                        <span>Total Biaya</span>
                                        <span>Rp {{ number_format($totalAkhir) }}</span>
                                    </div>

                                    <div class="mt-5 pt-4 border-t border-slate-200">
                                        <div class="text-xs uppercase tracking-wider mb-2">Metode Pembayaran</div>

                                        @if ($item->kondisi_servis === 'Dibatalkan')
                                            <span class="text-slate-500 italic text-sm">-</span>
                                        @else
                                            @if ($item->cara_pembayaran === 'Tunai & Transfer')
                                                {{-- Tampilan Khusus Tunai & Transfer --}}
                                                <div class="bg-slate-100 rounded-lg p-3">
                                                    <div class="font-bold text-slate-700 text-sm mb-2">Tunai & Transfer</div>
                                                    <div class="space-y-1 text-xs text-slate-600">
                                                        <div class="flex justify-between">
                                                            <span>💵 Tunai:</span>
                                                            <span class="font-mono font-medium">Rp {{ number_format($item->tunai) }}</span>
                                                        </div>
                                                        <div class="flex justify-between border-t border-slate-200 pt-1 mt-1">
                                                            <span>💳 Transfer:</span>
                                                            <span class="font-mono font-medium">Rp {{ number_format($item->transfer) }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                            @elseif ($item->cara_pembayaran === 'Tunai')
                                                <div class="text-sm font-semibold text-slate-700 bg-slate-100 inline-block px-3 py-1 rounded-lg">
                                                    Tunai (Rp {{ number_format($item->tunai) }})
                                                </div>

                                            @elseif ($item->cara_pembayaran === 'Transfer')
                                                <div class="text-sm font-semibold text-slate-700 bg-slate-100 inline-block px-3 py-1 rounded-lg">
                                                    Transfer (Rp {{ number_format($item->transfer) }})
                                                </div>

                                            @elseif ($item->cara_pembayaran === 'Kredit')
                                                <div class="text-sm font-semibold text-slate-700 bg-slate-100 inline-block px-3 py-1 rounded-lg">
                                                    Tempo (Rp {{ number_format($item->due) }})
                                                </div>
                                                <div class="text-xs text-red-500 mt-1">
                                                    Jatuh Tempo: {{ \Carbon\Carbon::parse($item->tempo)->translatedFormat('d F Y') }}
                                                </div>
                                            @else
                                                <div class="text-sm font-semibold text-slate-700 bg-slate-100 inline-block px-3 py-1 rounded-lg">
                                                    {{ $item->cara_pembayaran ?? 'Belum Lunas' }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    @if($item->uang_muka > 0)
                                    <div class="flex justify-between text-slate-600">
                                        <span>Uang Muka (DP)</span>
                                        <span>- Rp {{ number_format($item->uang_muka) }}</span>
                                    </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-white rounded-lg border border-dashed border-slate-300">
                    <div class="text-slate-300 text-5xl mb-3">📭</div>
                    <p class="text-slate-500">Tidak ada riwayat servis ditemukan untuk nomor ini.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} {{ $users->nama_toko }}. All rights reserved.
        </div>
    </div>
</x-customer-layout>
