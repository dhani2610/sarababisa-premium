<div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
    <header class="px-5 py-4">
        <h2 class="font-semibold text-slate-800">Semua Riwayat Aktivitas</h2>
    </header>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table id="log-servis-table" class="table-auto w-full">
            <!-- Table header -->
            <thead
                class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">No.</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Waktu</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Akun</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Aktivitas</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Nomor Servis</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Pelanggan</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Nama Barang</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Sebelum</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">Sesudah</div>
                    </th>
                </tr>
            </thead>
            <!-- Table body -->
            <tbody class="text-sm divide-y divide-slate-200">
                <!-- Row -->
                {{-- @php
                    $i = 1;
                @endphp
                @foreach ($activities as $item)
                    <tr>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $i++ }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @if ($item->causer)
                                @if ($item->causer->exists())
                                    <div class="font-medium">{{ $item->causer->name }}</div>
                                @else
                                    <div class="font-medium text-rose-600">Data servis telah dihapus</div>
                                @endif
                            @else
                                <div class="font-medium text-rose-600">Data servis telah dihapus</div>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium">{{ $item->description }}</div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @if ($item->subject)
                                @if ($item->subject->exists())
                                    <div class="font-medium">#{{ $item->subject->nomor_servis }}</div>
                                @else
                                    <div class="font-medium text-rose-600">Data servis telah dihapus</div>
                                @endif
                            @else
                                <div class="font-medium text-rose-600">Data servis telah dihapus</div>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @if ($item->subject)
                                @if ($item->subject->exists())
                                    <div class="font-medium">{{ $item->subject->nama_pelanggan }}</div>
                                @else
                                    <div class="font-medium text-rose-600">Data servis telah dihapus</div>
                                @endif
                            @else
                                <div class="font-medium text-rose-600">Data servis telah dihapus</div>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            @if ($item->subject)
                                @if ($item->subject->exists())
                                    <div class="font-medium">{{ $item->subject->type->name }}
                                        {{ $item->subject->brand->name }} {{ $item->subject->modelserie->name ?? '-' }}</div>
                                @else
                                    <div class="font-medium text-rose-600">Data servis telah dihapus</div>
                                @endif
                            @else
                                <div class="font-medium text-rose-600">Data servis telah dihapus</div>
                            @endif
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium text-orange-600">
                                @if ($item->description === 'deleted')
                                    -
                                @elseif ($item->description === 'created')
                                    -
                                @else
                                    @if (@is_array($item->changes['old']))
                                        @foreach ($item->changes['old'] as $key => $itemChange)
                                            {{ $key }} : {{ $itemChange }} <br>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="font-medium text-blue-600">
                                @if ($item->description === 'deleted')
                                    -
                                @elseif ($item->description === 'created')
                                    -
                                @else
                                    @if (@is_array($item->changes['attributes']))
                                        @foreach ($item->changes['attributes'] as $key => $itemChange)
                                            {{ $key }} : {{ $itemChange }} <br>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach --}}
            </tbody>
        </table>

    </div>
</div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        /* Styling tambahan agar sesuai tema toko layout */
        .dataTables_wrapper .dataTables_length select { padding-right: 30px; width: auto; }
        table.dataTable tbody td { vertical-align: top; } /* Agar teks perubahan panjang tetap rapi di atas */
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            var table = $('#log-servis-table').DataTable({
                processing: false,
                serverSide: false,
                ajax: "{{ route('log-servis.data') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'causer_name', name: 'causer_name' },
                    { data: 'description', name: 'description' },
                    { data: 'nomor_servis', name: 'nomor_servis' },
                    { data: 'pelanggan', name: 'pelanggan' },
                    { data: 'nama_barang', name: 'nama_barang' },
                    { data: 'sebelum', name: 'sebelum', orderable: false, searchable: false },
                    { data: 'sesudah', name: 'sesudah', orderable: false, searchable: false },
                ],
                order: [[0, 'asc']], // Default urut waktu terbaru
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                },
            });
        });
    </script>
