@section('title')
    Persetujuan Hapus Transaksi
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-6">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Persetujuan Hapus Transaksi ✨</h1>
                <p class="text-sm text-slate-500 mt-1">Daftar permintaan hapus transaksi Servis & POS dari Teknisi, Sales, dan Admin yang memerlukan persetujuan Kepala Toko.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $pending_count > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ $pending_count }} Permintaan Menunggu
                </span>
            </div>
        </div>

        <!-- Filter Tab -->
        <div class="mb-5 flex space-x-2">
            <a href="{{ route('approval-hapus-transaksi.index', ['status' => 'all']) }}" class="btn-sm {{ $status == 'all' ? 'bg-indigo-500 text-white' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300' }}">
                Semua
            </a>
            <a href="{{ route('approval-hapus-transaksi.index', ['status' => 'pending']) }}" class="btn-sm {{ $status == 'pending' ? 'bg-amber-500 text-white' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300' }}">
                Menunggu ({{ $pending_count }})
            </a>
            <a href="{{ route('approval-hapus-transaksi.index', ['status' => 'approved']) }}" class="btn-sm {{ $status == 'approved' ? 'bg-emerald-500 text-white' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300' }}">
                Disetujui
            </a>
            <a href="{{ route('approval-hapus-transaksi.index', ['status' => 'rejected']) }}" class="btn-sm {{ $status == 'rejected' ? 'bg-rose-500 text-white' : 'bg-white border-slate-200 text-slate-600 hover:border-slate-300' }}">
                Ditolak
            </a>
        </div>

        <!-- Table Container -->
        <div class="bg-white shadow-lg rounded-sm border border-slate-200">
            <div class="overflow-x-auto">
                <table class="table-auto w-full divide-y divide-slate-200">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap text-left">No</th>
                            <th class="px-4 py-3 whitespace-nowrap text-left">Tipe</th>
                            <th class="px-4 py-3 whitespace-nowrap text-left">No. Transaksi</th>
                            <th class="px-4 py-3 whitespace-nowrap text-left">Keterangan / Detail</th>
                            <th class="px-4 py-3 whitespace-nowrap text-left">Diminta Oleh</th>
                            <th class="px-4 py-3 whitespace-nowrap text-center">Tanggal Pengajuan</th>
                            <th class="px-4 py-3 whitespace-nowrap text-center">Status</th>
                            <th class="px-4 py-3 whitespace-nowrap text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        @forelse ($requests as $index => $req)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $requests->firstItem() + $index }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if ($req->transaksi_type === 'servis')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            Servis
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                            POS / Produk
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-800">
                                    {{ $req->transaksi_nomor ?? ('ID #' . $req->transaksi_id) }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 max-w-xs truncate" title="{{ $req->keterangan }}">
                                    <div>{{ $req->keterangan ?? '-' }}</div>
                                    @if ($req->alasan)
                                        <div class="text-xs text-slate-400 italic mt-0.5">{{ $req->alasan }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">{{ $req->user->name ?? 'User Terhapus' }}</div>
                                    <div class="text-xs text-slate-400">{{ $req->user->role ?? '-' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center text-slate-500 text-xs">
                                    {{ \Carbon\Carbon::parse($req->created_at)->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if ($req->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                            Disetujui
                                        </span>
                                    @elseif ($req->status === 'rejected')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                            Ditolak
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 animate-pulse">
                                            Menunggu
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    @if ($req->status === 'pending')
                                        <div class="flex items-center justify-center space-x-2">
                                            <!-- Approve Form -->
                                            <form action="{{ route('approval-hapus-transaksi.approve', $req->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI penghapusan transaksi ini? Data transaksi akan dihapus permanen.');">
                                                @csrf
                                                <button type="submit" class="btn-xs bg-emerald-500 hover:bg-emerald-600 text-white rounded px-2.5 py-1">
                                                    Setujui
                                                </button>
                                            </form>
                                            <!-- Reject Form -->
                                            <form action="{{ route('approval-hapus-transaksi.reject', $req->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK permintaan hapus ini?');">
                                                @csrf
                                                <button type="submit" class="btn-xs bg-rose-500 hover:bg-rose-600 text-white rounded px-2.5 py-1">
                                                    Tolak
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">
                                            Diproses oleh {{ $req->approver->name ?? 'Kepala Toko' }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-slate-500">
                                    Tidak ada permintaan hapus transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($requests->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>

    </div>
</x-toko-layout>
