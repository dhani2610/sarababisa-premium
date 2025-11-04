<div>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .map-small {
            height: 140px;
            width: 100%;
        }

        .map-modal {
            height: 400px;
            width: 100%;
        }

        .select2-container {
            z-index: 9999 !important;
        }
        .swal2-confirm{
            background: #7066e0!important
        }
        .swal2-cancel{
            background: red!important
        }
        [x-cloak] {
            display: none !important;
        }

        .map-small {
            height: 140px;
            width: 100%;
        }

        .map-modal {
            height: 70vh;
            /* ✅ ganti dari 400px ke 70% tinggi viewport */
            width: 100%;
            min-height: 400px;
            /* biar nggak terlalu kecil di layar kecil */
            border-radius: 10px;
        }

        .select2-container {
            z-index: 9999 !important;
        }
        /* Modal styling */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .modal-content {
            background: #fff;
            border-radius: 1rem;
            padding: 1rem;
            width: 90%;
            max-width: 700px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .map-container {
            height: 400px;
            width: 100%;
            border-radius: .75rem;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-…" crossorigin="anonymous" referrerpolicy="no-referrer" />


    <!-- load leaflet + sweetalert2 -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div x-data="{
        modalOpen: false,
        editModal: false,
        editData: {},
        showPhoto: null,
        showMap: null,
        // camera / geolocation states
        videoStream: null,
        permissionDenied: false,
        startCamera() {
            // start camera - ask
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
                        if (res.isConfirmed) {
                            // try again
                            this.startCamera();
                        }
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
            // get geolocation
            let coords = { lat: null, lng: null };
            try {
                await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(pos => {
                        coords.lat = pos.coords.latitude;
                        coords.lng = pos.coords.longitude;
                        resolve();
                    }, err => {
                        // show sweetalert to request location permission
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

            // capture from video if available
            let dataUrl = null;
            if ($refs.video && $refs.video.srcObject) {
                const canvas = document.createElement('canvas');
                canvas.width = $refs.video.videoWidth || 640;
                canvas.height = $refs.video.videoHeight || 480;
                canvas.getContext('2d').drawImage($refs.video, 0, 0, canvas.width, canvas.height);
                dataUrl = canvas.toDataURL('image/jpeg', 0.9);
            } else if ($refs.fileInput && $refs.fileInput.files.length) {
                // use chosen file
                const file = $refs.fileInput.files[0];
                dataUrl = null;
                // we'll submit file via form as photo_file
            }

            // build form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.enctype = 'multipart/form-data';
            form.action = '{{ route('master-absensi.store') }}';

            const token = document.createElement('input');
            token.type = 'hidden';
            token.name = '_token';
            token.value = '{{ csrf_token() }}';
            form.appendChild(token);

            const t = document.createElement('input');
            t.type = 'hidden';
            t.name = 'type';
            t.value = type;
            form.appendChild(t);

            const tanggal = document.createElement('input');
            tanggal.type = 'hidden';
            tanggal.name = 'tanggal';
            tanggal.value = new Date().toISOString().split('T')[0];
            form.appendChild(tanggal);

            const waktu = document.createElement('input');
            waktu.type = 'hidden';
            waktu.name = 'waktu';
            waktu.value = new Date().toISOString();
            form.appendChild(waktu);

            const lat = document.createElement('input');
            lat.type = 'hidden';
            lat.name = 'lat';
            lat.value = coords.lat ?? '';
            form.appendChild(lat);
            const lng = document.createElement('input');
            lng.type = 'hidden';
            lng.name = 'lng';
            lng.value = coords.lng ?? '';
            form.appendChild(lng);

            if (dataUrl) {
                const photo = document.createElement('input');
                photo.type = 'hidden';
                photo.name = 'photo';
                photo.value = dataUrl;
                form.appendChild(photo);
            } else if ($refs.fileInput && $refs.fileInput.files.length) {
                // append file input directly
                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.name = 'photo_file';
                // move the selected file into this new input (can't programmatically set file), fallback: submit original form element
                // Instead append original form with known file input (we will submit via fetch using FormData)
                const fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                fd.append('type', type);
                fd.append('tanggal', tanggal.value);
                fd.append('waktu', waktu.value);
                fd.append('lat', lat.value);
                fd.append('lng', lng.value);
                fd.append('note', $refs.note ? $refs.note.value : '');

                if ($refs.fileInput.files.length) fd.append('photo_file', $refs.fileInput.files[0]);

                // submit via fetch
                const res = await fetch(form.action, { method: 'POST', body: fd });
                if (res.redirected) window.location = res.url;
                else window.location.reload();
                return;
            } else {
                // no image, continue (photo optional)
            }

            const note = document.createElement('input');
            note.type = 'hidden';
            note.name = 'note';
            note.value = $refs.note ? $refs.note.value : '';
            form.appendChild(note);

            document.body.appendChild(form);
            form.submit();
        },
        openPhoto(url) {
            this.showPhoto = url;
            $nextTick(() => { this.$refs.photoModalOpen = true; });
        },
        openLocation(lat, lng) {
            this.showMap = { lat: lat, lng: lng };
            $nextTick(() => {
                this.$refs.mapModalOpen = true;
                initModalMap();
            });
        }
    }" x-init="$watch('modalOpen', value => { if (value) { $nextTick(() => startCamera()) } else { stopCamera() } })">

            <!-- Tombol buka modal -->
    <div class="flex justify-end mb-4">
        <button
            x-data
            @click="$dispatch('open-filter-modal')"
            class="btn bg-blue-500 text-white flex items-center px-3 py-2 rounded-lg shadow hover:bg-blue-600 transition">
            <i class="fas fa-filter mr-1"></i> Filter Tanggal
        </button>
    </div>

    <!-- Modal Popup -->
    <div
        x-data="{ open: false }"
        x-on:open-filter-modal.window="open = true"
        x-on:close-filter-modal.window="open = false"
        x-show="open"
        x-cloak
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50"
    >
        <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md relative">

            <h2 class="text-lg font-semibold text-gray-700 mb-4">
                Filter Berdasarkan Range Tanggal
            </h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Dari Tanggal</label>
                    <input type="date" wire:model="start_date" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Sampai Tanggal</label>
                    <input type="date" wire:model="end_date" class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button
                    @click="open = false"
                    class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-700"
                >
                    Batal
                </button>

                <button
                    wire:click="$refresh"
                    @click="open = false"
                    class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700"
                >
                    Terapkan Filter
                </button>

                <button
                    wire:click="$set('start_date', null); $set('end_date', null)"
                    @click="open = false"
                    class="px-4 py-2 rounded bg-gray-500 text-white hover:bg-gray-600"
                >
                    Reset
                </button>
            </div>
        </div>
    </div>


        <!-- Header -->
        @if (Auth::user()->role == 'Kepala Toko')
        <!-- Statistik Absen -->
        <div class="flex flex-wrap gap-4 mb-5">
            <!-- Hari Ini -->
            <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                <div class="p-3 bg-purple-100 text-purple-600 rounded-full">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-500">Hari Ini</h3>
                    <div class="text-lg font-bold text-slate-800">
                        {{ \Carbon\Carbon::now()->format('d M Y') }}
                    </div>
                </div>
            </div>

            <!-- Masuk -->
            <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                <div class="p-3 bg-green-100 text-green-600 rounded-full">
                    <i class="fas fa-sign-in-alt text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-500">Masuk Hari Ini</h3>
                    <div class="text-lg font-bold text-slate-800">
                        {{ $hariIniMasuk ?? 0 }} Orang
                    </div>
                </div>
            </div>

            <!-- Pulang -->
            <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                <div class="p-3 bg-yellow-100 text-yellow-600 rounded-full">
                    <i class="fas fa-sign-out-alt text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-500">Pulang Hari Ini</h3>
                    <div class="text-lg font-bold text-slate-800">
                        {{ $hariIniPulang ?? 0 }} Orang
                    </div>
                </div>
            </div>

            <!-- Total -->
            <div class="flex-1 min-w-[200px] bg-white border rounded-lg shadow p-4 flex items-center space-x-3">
                <div class="p-3 bg-blue-100 text-blue-600 rounded-full">
                    <i class="fas fa-user-check text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-slate-500">Total Absen Hari Ini</h3>
                    <div class="text-lg font-bold text-slate-800">
                        {{ $hariIniTotal ?? 0 }} Orang
                    </div>
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
                    <select wire:model="filter_role_user_id" class="form-select">
                        <option value="">Semua User</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                @endif

                <button class="btn bg-green-500 text-white" @click="modalOpen = true">Absen</button>
            </div>
        </div>

        <!-- Modal capture (Masuk/Pulang) -->
        <div x-show="modalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            @click.self="modalOpen = false" @keydown.escape.window="modalOpen = false" x-transition>
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 grid grid-cols-1 gap-4">
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="font-semibold text-lg">Kamera</h2>
                        <button
                            class="text-gray-500 hover:text-gray-700"
                            @click="modalOpen = false"
                            title="Tutup"
                        >
                            ✕
                        </button>
                    </div>
                    <video x-ref="video" class="w-full h-64 bg-black rounded" autoplay muted playsinline></video>
                    <div class="mt-2 ">
                        <button class="btn bg-indigo-500 text-white" @click="captureAndSubmit('masuk')">Absen
                            Masuk</button>
                        <button class="btn bg-yellow-500 text-white" @click="captureAndSubmit('pulang')">Absen
                            Pulang</button>
                        {{-- <input type="file" x-ref="fileInput" accept="image/*" class="ml-2" /> --}}
                    </div>
                    <label class="block mt-3 text-sm">Catatan (opsional)</label>
                    <textarea x-ref="note" class="form-input w-full"></textarea>
                </div>
            </div>
        </div>

        <!-- Table & bulk actions -->
        <div class="bg-white shadow rounded border mt-5 p-4">
            <div class="flex justify-between items-center mb-3">
                <div class="text-sm text-slate-600">Riwayat Absensi</div>
                <div class="table-items-action hidden flex items-center gap-2">
                    <div class="text-sm"><span class="table-items-count">0</span> terpilih</div>
                    <button class="btn bg-rose-500 text-white btn-sm"
                        @click="() => {
                        // confirm
                        Swal.fire({
                            title: 'Hapus terpilih?',
                            text: 'Data yang dihapus tidak bisa dikembalikan.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Hapus'
                        }).then(res => {
                            if(res.isConfirmed) {
                                const selected = [...document.querySelectorAll('input.table-item:checked')].map(cb=>cb.value);
                                fetch('{{ route('master-absensi.deleteSelected') }}', {
                                    method: 'DELETE',
                                    headers: {'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
                                    body: JSON.stringify({selectedIds:selected})
                                }).then(r=>r.json()).then(d=>{ Swal.fire('Sukses', d.message, 'success').then(()=> location.reload())});
                            }
                        });
                    }">Hapus
                        Terpilih</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                <input id="parent-checkbox" class="form-checkbox" type="checkbox">
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">No
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-left">User
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                Tipe</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                Waktu</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                Lokasi</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                Foto</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                Catatan</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y">
                        @php $no = ($attendances->currentPage()-1) * $attendances->perPage() + 1; @endphp
                        @foreach ($attendances as $att)
                            <tr>
                                <td
                                    class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                    <input class="form-checkbox table-item" type="checkbox"
                                        value="{{ $att->id }}">
                                </td>
                                <td
                                    class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                    {{ $no++ }}</td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">
                                    {{ $att->user->name }}</td>
                                <td
                                    class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                    {{ ucfirst($att->type) }}</td>
                                @php
                                    $setting = \App\Models\StoreSetting::first();
                                    $jamMasuk = $setting?->jam_masuk ? \Carbon\Carbon::parse($setting->jam_masuk) : null;
                                    $jamAbsen = \Carbon\Carbon::parse($att->created_at);

                                    $status = 'Tidak Aktif';
                                    $warna = 'text-slate-400 italic';

                                    if ($setting && $setting->active_setting_absensi) {
                                        if ($jamAbsen->gt($jamMasuk)) {
                                            $status = 'Terlambat';
                                            $warna = 'text-red-500 font-semibold';
                                        } else {
                                            $status = 'Tepat Waktu';
                                            $warna = 'text-green-500 font-semibold';
                                        }
                                    }
                                @endphp

                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">
                                    {{ \Carbon\Carbon::parse($att->created_at)->translatedFormat('l, d F Y H:i:s') }}
                                    @if (ucfirst($att->type) == 'Masuk')
                                        @if ($setting && $setting->active_setting_absensi)
                                            <span class="ml-1 {{ $warna }}">| {{ $status }}</span>
                                        @else
                                            <span class="ml-1 {{ $warna }}">| {{ $status }}</span>
                                        @endif
                                    @endif
                                </td>

                                <td
                                    class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                    @if ($att->lat && $att->lng)
                                        <button onclick="showMapModal({{ $att->lat }}, {{ $att->lng }})"
                                            class="text-indigo-600 hover:underline">
                                            Lihat Lokasi
                                        </button>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td
                                    class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                    @if ($att->photo)
                                        <button class="text-indigo-600"
                                            @click="openPhoto('{{ $att->photo ? asset('storage/' . $att->photo) : '' }}')">Lihat
                                            Foto</button>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                </td>
                                <td
                                    class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                    @if ($att->note)
                                        <span class="text-slate-400">
                                        {{ $att->note }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td
                                    class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                    <form method="POST" action="{{ route('master-absensi.destroy', $att->id) }}"
                                        onsubmit="return confirm('Yakin hapus?')">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-500">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $attendances->links() }}</div>
        </div>

        <!-- Photo modal -->
        <div x-show="showPhoto" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60"
            @click.self="showPhoto=null">
            <div class="bg-white rounded-lg overflow-hidden max-w-2xl w-full p-4">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold">Foto Absensi</h3>
                    <button @click="showPhoto=null">Tutup</button>
                </div>
                <div class="mt-3">
                    <img :src="showPhoto" class="w-full h-auto object-contain" />
                </div>
            </div>
        </div>

        <!-- Map modal -->
        <div id="mapModal" class="modal-overlay">
            <div class="modal-content">
                <h2 class="text-lg font-semibold mb-3">Lokasi Absensi</h2>
                <div class="text-right mt-4">
                    <button onclick="closeMapModal()" class="bg-gray-700 px-4 py-2 rounded hover:bg-gray-800"
                        style="color:black">
                        Tutup
                    </button>
                </div>
                <div id="map" class="map-container"></div>
            </div>
        </div>

    </div>

    <script>
        let mapInstance = null;

        function showMapModal(lat, lng) {
            const modal = document.getElementById('mapModal');
            const mapContainer = document.getElementById('map');
            modal.style.display = 'flex';

            // bersihkan map sebelumnya
            mapContainer.innerHTML = '';

            // tunggu sebentar agar modal benar-benar visible
            setTimeout(() => {
                mapInstance = L.map('map').setView([lat, lng], 16);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(mapInstance);

                L.marker([lat, lng]).addTo(mapInstance);

                // pastikan map benar-benar render penuh
                setTimeout(() => {
                    mapInstance.invalidateSize();
                }, 300);
            }, 200);
        }

        function closeMapModal() {
            document.getElementById('mapModal').style.display = 'none';
            if (mapInstance) {
                mapInstance.remove(); // hapus map instance
                mapInstance = null;
            }
        }

        function showPhotoModal(photoUrl) {
            const modal = document.getElementById('photoModal');
            const img = document.getElementById('photoPreview');
            img.src = photoUrl;
            modal.style.display = 'flex';
        }

        function closePhotoModal() {
            document.getElementById('photoModal').style.display = 'none';
        }
    </script>

    <script>
        // initialize mini map for modalOpen preview
        document.addEventListener('alpine:init', () => {
            Alpine.magic('map', () => {
                return {
                    miniMap: null,
                    modalMap: null,
                }
            });

            Alpine.data('handleSelect', () => ({
                selectall: false,
                toggleAll() {},
            }));
        });

        // mount small map and track position
        document.addEventListener('DOMContentLoaded', () => {
            // small map init
            const mini = document.getElementById('mini-map');
            if (mini) {
                const map = L.map(mini).setView([0, 0], 2);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                const marker = L.marker([0, 0]).addTo(map);
                // try get location
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        const lat = pos.coords.latitude,
                            lng = pos.coords.longitude;
                        map.setView([lat, lng], 15);
                        marker.setLatLng([lat, lng]);
                    }, () => {
                        /* ignore */
                    }, {
                        enableHighAccuracy: true
                    });
                }
            }
        });

        // functions used inside Alpine component (global)
        function initModalMap() {
            const target = document.getElementById('modal-map');
            if (!target) return;
            // read Alpine showMap variable
            const alpineRoot = document.querySelector('[x-data]');
            const showMap = alpineRoot.__x.$data.showMap;
            if (!showMap) return;
            // init map
            target.innerHTML = '';
            const map = L.map(target).setView([showMap.lat, showMap.lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([showMap.lat, showMap.lng]).addTo(map);
            setTimeout(() => {
                map.invalidateSize();
            }, 200);
        }

        // checkbox bulk actions (plain JS)
        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'parent-checkbox') {
                const checked = e.target.checked;
                document.querySelectorAll('input.table-item').forEach(cb => cb.checked = checked);
                updateBulkUI();
            }
            if (e.target && e.target.classList.contains('table-item')) {
                updateBulkUI();
            }
        });

        function updateBulkUI() {
            const selected = document.querySelectorAll('input.table-item:checked');
            const el = document.querySelector('.table-items-action');
            document.querySelector('.table-items-count').innerText = selected.length;
            if (selected.length > 0) el.classList.remove('hidden');
            else el.classList.add('hidden');
        }

        // show photo modal from Alpine: handled by Alpine openPhoto -> showPhoto
        // show location modal: handled by Alpine openLocation -> showMap then initModalMap when shown
    </script>
</div>
