@section('title', 'Absensi')

{{-- <x-toko-layout> --}}
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <style>
            [x-cloak] { display: none !important; }
            .map-container { height: 400px; width: 100%; border-radius: .75rem; }

            /* Modal styling untuk Map & Photo agar konsisten */
            .modal-overlay-custom {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.6);
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            .modal-content-custom {
                background: #fff;
                border-radius: 1rem;
                padding: 1rem;
                width: 90%;
                max-width: 700px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
                position: relative;
            }
            .select2-container { z-index: 9999 !important; }

            /* Datatable Style Adjustment */
            .dataTables_wrapper .dataTables_length select { padding-right: 30px; }
        </style>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <div x-data="{
            modalOpen: false,
            // camera / geolocation states
            videoStream: null,
            permissionDenied: false,
            startCamera() {
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(stream => {
                        this.videoStream = stream;
                        $refs.video.srcObject = stream;
                        $refs.video.play();
                    })
                    .catch(err => {
                        this.permissionDenied = true;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Izin kamera ditolak',
                            text: 'Silakan izinkan akses kamera di browser Anda.',
                            showCancelButton: true,
                            confirmButtonText: 'Minta lagi',
                        }).then(res => {
                            if (res.isConfirmed) { this.startCamera(); }
                        });
                    });
            },
            stopCamera() {
                if (this.videoStream) {
                    this.videoStream.getTracks().forEach(t => t.stop());
                    this.videoStream = null;
                }
            },
            async captureAndSubmit(type) {
                // --- LOGIC BARU: AMBIL 1 VALUE DARI DROPDOWN PERIODE ---
                const el = document.getElementById('shift_periode_select');
                const selectedShiftTime = el ? el.value : '';

                if (!selectedShiftTime) {
                    Swal.fire('Error', 'Silakan pilih Periode Shift terlebih dahulu!', 'error');
                    return;
                }

                let coords = { lat: null, lng: null };
                try {
                    await new Promise((resolve, reject) => {
                        navigator.geolocation.getCurrentPosition(pos => {
                            coords.lat = pos.coords.latitude;
                            coords.lng = pos.coords.longitude;
                            resolve();
                        }, err => {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Izin lokasi ditolak',
                                text: 'Silakan izinkan akses lokasi di browser Anda.',
                                confirmButtonText: 'OKE'
                            });
                            resolve();
                        }, { enableHighAccuracy: true, timeout: 10000 });
                    });
                } catch (e) {}

                let dataUrl = null;
                if ($refs.video && $refs.video.srcObject) {
                    const canvas = document.createElement('canvas');
                    canvas.width = $refs.video.videoWidth || 640;
                    canvas.height = $refs.video.videoHeight || 480;
                    canvas.getContext('2d').drawImage($refs.video, 0, 0, canvas.width, canvas.height);
                    dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.enctype = 'multipart/form-data';
                form.action = '{{ route('master-absensi.store') }}';

                const inputs = [
                    {name: '_token', value: '{{ csrf_token() }}'},
                    {name: 'type', value: type},
                    {name: 'tanggal', value: new Date().toISOString().split('T')[0]},
                    {name: 'waktu', value: new Date().toISOString()},
                    {name: 'lat', value: coords.lat ?? ''},
                    {name: 'lng', value: coords.lng ?? ''},
                    {name: 'note', value: $refs.note ? $refs.note.value : ''},
                    {name: 'shift_jam_selected', value: selectedShiftTime} // Mengirim Jam Masuk dari Periode
                ];

                inputs.forEach(i => {
                    const el = document.createElement('input');
                    el.type = 'hidden';
                    el.name = i.name;
                    el.value = i.value;
                    form.appendChild(el);
                });

                if (dataUrl) {
                    const photo = document.createElement('input');
                    photo.type = 'hidden';
                    photo.name = 'photo';
                    photo.value = dataUrl;
                    form.appendChild(photo);
                }

                document.body.appendChild(form);
                form.submit();
            }
        }" x-init="$watch('modalOpen', value => { if (value) { $nextTick(() => startCamera()) } else { stopCamera() } })">

            <div class="flex justify-end mb-4">
                <button onclick="openFilterModal()" class="btn bg-blue-500 text-white flex items-center px-3 py-2 rounded-lg shadow hover:bg-blue-600 transition">
                    <i class="fas fa-filter mr-1"></i> Filter Tanggal
                </button>
            </div>

            <div id="filterModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 hidden">
                <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md relative">
                    <h2 class="text-lg font-semibold text-gray-700 mb-4">Filter Berdasarkan Range Tanggal</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Dari Tanggal</label>
                            <input type="date" id="filter_start_date" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Sampai Tanggal</label>
                            <input type="date" id="filter_end_date" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-400">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button onclick="closeFilterModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-700">Batal</button>
                        <button onclick="applyFilter()" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Terapkan Filter</button>
                        <button onclick="resetFilter()" class="px-4 py-2 rounded bg-gray-500 text-white hover:bg-gray-600">Reset</button>
                    </div>
                </div>
            </div>

            @if (Auth::user()->role == 'Kepala Toko')
            <div class="flex flex-wrap gap-4 mb-5">
                <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-purple-100 text-purple-600 rounded-full"><i class="fas fa-calendar-day text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ \Carbon\Carbon::now()->format('d M Y') }}</div>
                    </div>
                </div>
                <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-green-100 text-green-600 rounded-full"><i class="fas fa-sign-in-alt text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Masuk Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $hariIniMasuk ?? 0 }} Orang</div>
                    </div>
                </div>
                <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-yellow-100 text-yellow-600 rounded-full"><i class="fas fa-sign-out-alt text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Pulang Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $hariIniPulang ?? 0 }} Orang</div>
                    </div>
                </div>
                <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                    <div class="p-3 bg-blue-100 text-blue-600 rounded-full"><i class="fas fa-user-check text-xl"></i></div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-500">Total Absen Hari Ini</h3>
                        <div class="text-lg font-bold text-slate-800">{{ $hariIniTotal ?? 0 }} Orang</div>
                    </div>
                </div>
            </div>
            @endif

            <div class="flex items-center gap-2 mb-4">
                <form method="GET" action="{{ route('master-absensi.export') }}">
                    <div class="flex items-center gap-2">
                        <input type="month" name="bulan" class="form-input border rounded px-2 py-1" required>
                        <button type="submit" class="btn bg-emerald-500 text-white">
                            <i class="fas fa-file-excel mr-1"></i> Export Excel
                        </button>
                    </div>
                </form>
            </div>

            <div class="sm:flex sm:justify-between sm:items-center mb-3 gap-3">
                <div>
                    <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Absensi ✨</h1>
                    <div class="text-sm text-slate-500">Masuk / Pulang dengan jam, foto & lokasi</div>
                </div>

                <div class="flex items-center gap-2">
                    @if (Auth::user()->role === 'Kepala Toko')
                        <select id="filter_role_user_id" class="form-select w-full max-w-xs">
                            <option value="">Semua User</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button class="btn bg-green-500 text-white" @click="modalOpen = true">Absen</button>
                </div>
            </div>

            <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" @click.self="modalOpen = false" @keydown.escape.window="modalOpen = false" x-transition>
                <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 grid grid-cols-1 gap-4">
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <h2 class="font-semibold text-lg">Kamera Absensi</h2>
                            <button class="text-gray-500 hover:text-gray-700" @click="modalOpen = false" title="Tutup">✕</button>
                        </div>

                        <video x-ref="video" class="w-full h-64 bg-black rounded" autoplay muted playsinline></video>

                        <div class="mt-3">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Shift</label>
                            <select id="shift_periode_select" class="form-select w-full border rounded px-3 py-2 text-sm bg-gray-50 border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                @if(!empty($shiftList))
                                    @foreach($shiftList as $index => $s)
                                        {{-- Value adalah Jam Masuk untuk pengecekan telat --}}
                                        <option value="{{ $s['value'] }}">
                                            Periode {{ $index + 1 }} ({{ $s['label'] }} WIB)
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">Anda tidak memiliki shift</option>
                                @endif
                            </select>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <button class="flex-1 btn bg-indigo-500 hover:bg-indigo-600 text-white py-2 rounded" @click="captureAndSubmit('masuk')">
                                <i class="fas fa-camera mr-1"></i> Absen Masuk
                            </button>
                            <button class="flex-1 btn bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded" @click="captureAndSubmit('pulang')">
                                <i class="fas fa-camera mr-1"></i> Absen Pulang
                            </button>
                        </div>

                        <label class="block mt-3 text-sm">Catatan (opsional)</label>
                        <textarea x-ref="note" class="form-input w-full border rounded p-2" rows="2" placeholder="Keterangan pekerjaan..."></textarea>
                    </div>
                </div>
            </div>

            @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="bg-white shadow rounded border mt-5 p-4">
                <div class="flex justify-between items-center mb-3">
                    <div class="text-sm text-slate-600">Riwayat Absensi</div>
                    <div class="table-items-action hidden flex items-center gap-2">
                        <div class="text-sm"><span class="table-items-count">0</span> terpilih</div>
                        <button class="btn bg-rose-500 text-white btn-sm" onclick="bulkDelete()">Hapus Terpilih</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table id="absensi-table" class="table-auto w-full">
                        <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50">
                            <tr>
                                @if (Auth::user()->role == 'Kepala Toko')
                                <th class="px-2 py-3 w-px text-center">
                                    <input id="parent-checkbox" class="form-checkbox" type="checkbox">
                                </th>
                                @else
                                <th class="hidden"></th>
                                @endif
                                <th class="px-2 py-3 w-px text-center">No</th>
                                <th class="px-2 py-3 text-left">Karyawan</th>
                                <th class="px-2 py-3 text-center">Tipe</th>
                                <th class="px-2 py-3 text-center">Waktu</th>
                                @if (auth()->user()->role == 'Kepala Toko')
                                <th class="px-2 py-3 text-center">Potongan Terlambat</th>
                                @endif
                                <th class="px-2 py-3 text-center">Lokasi</th>
                                <th class="px-2 py-3 text-center">Foto</th>
                                <th class="px-2 py-3 text-center">Catatan</th>
                                @if (Auth::user()->role == 'Kepala Toko')
                                <th class="px-2 py-3 text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="photoModal" class="modal-overlay-custom">
            <div class="bg-white rounded-lg overflow-hidden max-w-2xl w-full p-4 relative">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold">Foto Absensi</h3>
                    <button onclick="closePhotoModal()" class="text-gray-500">✕</button>
                </div>
                <div class="mt-3">
                    <img id="photoPreview" src="" class="w-full h-auto object-contain" />
                </div>
            </div>
        </div>

        <div id="mapModal" class="modal-overlay-custom">
            <div class="modal-content-custom">
                <h2 class="text-lg font-semibold mb-3">Lokasi Absensi</h2>
                <div id="map" class="map-container"></div>
                <div class="text-right mt-4">
                    <button onclick="closeMapModal()" style="background-color: red" class="bg-gray-700 px-4 py-2 rounded text-white hover:bg-gray-800">Tutup</button>
                </div>
            </div>
        </div>

    </div>
{{-- </x-toko-layout> --}}

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        // --- DATATABLE SETUP ---
        $(document).ready(function() {
            var table = $('#absensi-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('master-absensi.data') }}",
                    data: function(d) {
                        d.start_date = $('#filter_start_date').val();
                        d.end_date = $('#filter_end_date').val();
                        d.filter_role_user_id = $('#filter_role_user_id').val();
                    }
                },
                columns: [
                    @if (Auth::user()->role == 'Kepala Toko')
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false, className: 'text-center' },
                    @else
                    { data: null, visible: false },
                    @endif
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'user_name', name: 'user.name' },
                    { data: 'type', name: 'type', className: 'text-center' },
                    { data: 'waktu', name: 'created_at', className: 'text-center' },
                    @if (Auth::user()->role == 'Kepala Toko')
                    { data: 'nominal_potongan', name: 'nominal_potongan', className: 'text-center' },
                    @endif
                    { data: 'lokasi', name: 'lokasi', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'foto', name: 'foto', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'note', name: 'note', className: 'text-center' },
                    @if (Auth::user()->role == 'Kepala Toko')
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' },
                    @endif
                ],
                order: [[3, 'desc']],
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
                drawCallback: function() {
                    $('#parent-checkbox').prop('checked', false);
                    updateBulkUI();
                    $('.table-item').off('change').on('change', function() {
                        updateBulkUI();
                        var allChecked = $('.table-item:checked').length === $('.table-item').length;
                        $('#parent-checkbox').prop('checked', allChecked);
                    });
                }
            });

            $('#filter_role_user_id').change(function() {
                table.draw();
            });

            window.reloadTable = function() {
                table.draw();
            }
        });

        // --- FILTER MODAL LOGIC ---
        function openFilterModal() { document.getElementById('filterModal').classList.remove('hidden'); }
        function closeFilterModal() { document.getElementById('filterModal').classList.add('hidden'); }
        function applyFilter() {
            window.reloadTable();
            closeFilterModal();
        }
        function resetFilter() {
            document.getElementById('filter_start_date').value = '';
            document.getElementById('filter_end_date').value = '';
            window.reloadTable();
            closeFilterModal();
        }

        // --- BULK DELETE LOGIC ---
        $('#parent-checkbox').change(function() {
            var checked = this.checked;
            $('.table-item').prop('checked', checked);
            updateBulkUI();
        });

        function updateBulkUI() {
            var count = $('.table-item:checked').length;
            $('.table-items-count').text(count);
            if(count > 0) {
                $('.table-items-action').removeClass('hidden');
            } else {
                $('.table-items-action').addClass('hidden');
            }
        }

        function bulkDelete() {
            var selectedIds = [];
            $('.table-item:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) return;

            Swal.fire({
                title: 'Hapus terpilih?',
                text: 'Data yang dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Hapus',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('master-absensi.deleteSelected') }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ selectedIds: selectedIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire('Sukses', data.message, 'success');
                        $('#parent-checkbox').prop('checked', false);
                        window.reloadTable();
                        updateBulkUI();
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Gagal menghapus data', 'error');
                    });
                }
            });
        }

        // --- MAP MODAL LOGIC ---
        let mapInstance = null;
        function showMapModal(lat, lng) {
            const modal = document.getElementById('mapModal');
            modal.style.display = 'flex';
            if (mapInstance) { mapInstance.remove(); }
            setTimeout(() => {
                mapInstance = L.map('map').setView([lat, lng], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(mapInstance);
                L.marker([lat, lng]).addTo(mapInstance);
                mapInstance.invalidateSize();
            }, 200);
        }
        function closeMapModal() {
            document.getElementById('mapModal').style.display = 'none';
        }

        // --- PHOTO MODAL LOGIC ---
        function showPhotoModal(url) {
            document.getElementById('photoPreview').src = url;
            document.getElementById('photoModal').style.display = 'flex';
        }
        function closePhotoModal() {
            document.getElementById('photoModal').style.display = 'none';
        }
    </script>
@endpush
