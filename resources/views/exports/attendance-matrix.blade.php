<table border="1" cellspacing="0" cellpadding="5">
    <thead>
        <tr>
            <th colspan="{{ 2 * count($days) + 2 }}" style="text-align:center; font-weight:bold;">
                {{ $bulan->translatedFormat('F Y') }}
            </th>
        </tr>
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Nama Karyawan</th>
            @foreach ($days as $day)
                <th colspan="2" style="text-align:center;">
                    {{ $day->translatedFormat('d F Y') }}
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach ($days as $day)
                <th>Masuk</th>
                <th>Pulang</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $index => $user)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $user->name }}</td>
                @foreach ($days as $day)
                    @php
                        $record = $attendances[$user->id][$day->toDateString()] ?? collect();
                        $masuk = $record->firstWhere('type', 'masuk');
                        $pulang = $record->firstWhere('type', 'pulang');
                    @endphp
                    <td>{{ $masuk ? \Carbon\Carbon::parse($masuk->created_at)->format('H:i') : '-' }}</td>
                    <td>{{ $pulang ? \Carbon\Carbon::parse($pulang->created_at)->format('H:i') : '-' }}</td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
