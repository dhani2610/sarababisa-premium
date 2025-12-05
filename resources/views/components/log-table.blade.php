<div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
    <header class="px-5 py-4">
        <h2 class="font-semibold text-slate-800">Semua Riwayat Aktivitas</h2>
    </header>
    <!-- Filter -->
    <div class="mb-4 ml-4">
        <form method="GET" action="{{ route('log-servis') }}" class="flex items-center space-x-2">
            <input type="text" name="nomor_servis" value="{{ request('nomor_servis') }}"
                placeholder="Cari Nomor Servis..."
                class="form-input rounded border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" />

            <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                Filter
            </button>

            @if (request('nomor_servis'))
                <a href="{{ route('log-servis') }}" class="btn bg-slate-200 hover:bg-slate-300 text-slate-700">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="table-auto w-full">
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
                @php
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
                @endforeach
            </tbody>
        </table>

    </div>
</div>
