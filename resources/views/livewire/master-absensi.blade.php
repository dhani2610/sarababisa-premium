<div>
    <style>
        [x-cloak]{ display: none !important; }
        .map-small { height: 140px; width: 100%; }
        .map-modal { height: 400px; width: 100%; }
        .select2-container { z-index: 9999 !important; }
    </style>
    <style>
    [x-cloak] { display: none !important; }

    .map-small {
        height: 140px;
        width: 100%;
    }

    .map-modal {
        height: 70vh; /* ✅ ganti dari 400px ke 70% tinggi viewport */
        width: 100%;
        min-height: 400px; /* biar nggak terlalu kecil di layar kecil */
        border-radius: 10px;
    }

    .select2-container {
        z-index: 9999 !important;
    }
</style>
php

    <!-- load leaflet + sweetalert2 -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div x-data="{
            modalOpen:false,
            editModal:false,
            editData:{},
            showPhoto:null,
            showMap:null,
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
                if(this.videoStream){
                    this.videoStream.getTracks().forEach(t => t.stop());
                    this.videoStream = null;
                }
            },
            async captureAndSubmit(type) {
                // get geolocation
                let coords = {lat: null, lng: null};
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
                } catch(e) {}

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
                t.type = 'hidden'; t.name = 'type'; t.value = type; form.appendChild(t);

                const tanggal = document.createElement('input');
                tanggal.type = 'hidden'; tanggal.name = 'tanggal'; tanggal.value = new Date().toISOString().split('T')[0]; form.appendChild(tanggal);

                const waktu = document.createElement('input');
                waktu.type='hidden'; waktu.name='waktu'; waktu.value = new Date().toISOString(); form.appendChild(waktu);

                const lat = document.createElement('input'); lat.type='hidden'; lat.name='lat'; lat.value = coords.lat ?? ''; form.appendChild(lat);
                const lng = document.createElement('input'); lng.type='hidden'; lng.name='lng'; lng.value = coords.lng ?? ''; form.appendChild(lng);

                if (dataUrl) {
                    const photo = document.createElement('input'); photo.type='hidden'; photo.name='photo'; photo.value = dataUrl; form.appendChild(photo);
                } else if ($refs.fileInput && $refs.fileInput.files.length) {
                    // append file input directly
                    const fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.name = 'photo_file';
                    // move the selected file into this new input (can't programmatically set file), fallback: submit original form element
                    // Instead append original form with known file input (we will submit via fetch using FormData)
                    const fd = new FormData();
                    fd.append('_token','{{ csrf_token() }}');
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

                const note = document.createElement('input'); note.type='hidden'; note.name='note'; note.value = $refs.note ? $refs.note.value : ''; form.appendChild(note);

                document.body.appendChild(form);
                form.submit();
            },
            openPhoto(url) {
                this.showPhoto = url;
                $nextTick(() => { this.$refs.photoModalOpen = true; });
            },
            openLocation(lat,lng) {
                this.showMap = {lat: lat, lng: lng};
                $nextTick(() => { this.$refs.mapModalOpen = true; initModalMap(); });
            }
        }"
        x-init="$watch('modalOpen', value => { if(value){ $nextTick(()=> startCamera() ) } else { stopCamera() } })"
    >

        <!-- Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-3 gap-3">
            <div>
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Absensi ✨</h1>
                <div class="text-sm text-slate-500">Masuk / Pulang dengan jam, foto & lokasi</div>
            </div>

            <div class="flex items-center gap-2">
                @if(Auth::user()->role === 'Kepala Toko')
                    <select wire:model="filter_role_user_id" class="form-select">
                        <option value="">Semua User</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                @endif

                <button class="btn bg-green-500 text-white" @click="modalOpen = true">Absensi Masuk / Pulang</button>
            </div>
        </div>

        <!-- Modal capture (Masuk/Pulang) -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
             @click.self="modalOpen = false" @keydown.escape.window="modalOpen = false" x-transition>
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h2 class="font-semibold">Kamera</h2>
                    <video x-ref="video" class="w-full h-64 bg-black rounded" autoplay muted playsinline></video>
                    <div class="mt-2 flex gap-2">
                        <button class="btn bg-indigo-500 text-white" @click="captureAndSubmit('masuk')">Absen Masuk</button>
                        <button class="btn bg-yellow-500 text-white" @click="captureAndSubmit('pulang')">Absen Pulang</button>
                        <input type="file" x-ref="fileInput" accept="image/*" class="ml-2" />
                    </div>
                    <label class="block mt-3 text-sm">Catatan (opsional)</label>
                    <textarea x-ref="note" class="form-input w-full"></textarea>
                </div>

                <div>
                    <h2 class="font-semibold">Lokasi (preview)</h2>
                    <div id="mini-map" class="map-small rounded border"></div>
                    <div class="text-sm text-slate-500 mt-2">Map ini menunjukkan lokasi yang akan dikirim (jika diizinkan).</div>
                </div>
            </div>
        </div>

        <!-- Table & bulk actions -->
        <div class="bg-white shadow rounded border mt-5 p-4">
            <div class="flex justify-between items-center mb-3">
                <div class="text-sm text-slate-600">Riwayat Absensi</div>
                <div class="table-items-action hidden flex items-center gap-2">
                    <div class="text-sm"><span class="table-items-count">0</span> terpilih</div>
                    <button class="btn bg-rose-500 text-white btn-sm" @click="() => {
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
                    }">Hapus Terpilih</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center"><input id="parent-checkbox" class="form-checkbox" type="checkbox"></th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">No</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-left">User</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">Tipe</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">Waktu</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">Lokasi</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">Foto</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y">
                        @php $no = ($attendances->currentPage()-1) * $attendances->perPage() + 1; @endphp
                        @foreach($attendances as $att)
                            <tr>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center"><input class="form-checkbox table-item" type="checkbox" value="{{ $att->id }}"></td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">{{ $no++ }}</td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center">{{ $att->user->name }}</td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">{{ ucfirst($att->type) }}</td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">{{ \Carbon\Carbon::parse($att->waktu)->format('d/m/Y H:i:s') }}</td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                    @if($att->lat && $att->lng)
                                        <button class="text-indigo-600" @click="openLocation({{ $att->lat }}, {{ $att->lng }})">Lihat Lokasi</button>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                    @if($att->photo)
                                        <button class="text-indigo-600" @click="openPhoto('{{ $att->photo ? asset('storage/'.$att->photo) : '' }}')">Lihat Foto</button>
                                    @else
                                        <span class="text-sm text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px text-center text-center">
                                    <form method="POST" action="{{ route('master-absensi.destroy',$att->id) }}" onsubmit="return confirm('Yakin hapus?')">
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
        <div x-show="showPhoto" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60" @click.self="showPhoto=null">
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
        <div x-show="showMap" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60" @click.self="showMap=null">
            <div class="bg-white rounded-lg overflow-hidden max-w-3xl w-full p-4">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold">Lokasi Absensi</h3>
                    <button @click="showMap=null">Tutup</button>
                </div>
                <div id="modal-map" class="map-modal mt-3 rounded"></div>
            </div>
        </div>

    </div>

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
            if(mini) {
                const map = L.map(mini).setView([0,0], 2);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                const marker = L.marker([0,0]).addTo(map);
                // try get location
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        const lat = pos.coords.latitude, lng = pos.coords.longitude;
                        map.setView([lat,lng], 15);
                        marker.setLatLng([lat,lng]);
                    }, ()=>{ /* ignore */ }, { enableHighAccuracy:true });
                }
            }
        });

        // functions used inside Alpine component (global)
        function initModalMap(){
            const target = document.getElementById('modal-map');
            if(!target) return;
            // read Alpine showMap variable
            const alpineRoot = document.querySelector('[x-data]');
            const showMap = alpineRoot.__x.$data.showMap;
            if(!showMap) return;
            // init map
            target.innerHTML = '';
            const map = L.map(target).setView([showMap.lat, showMap.lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
            L.marker([showMap.lat, showMap.lng]).addTo(map);
            setTimeout(()=> { map.invalidateSize(); }, 200);
        }

        // checkbox bulk actions (plain JS)
        document.addEventListener('change', function(e){
            if(e.target && e.target.id === 'parent-checkbox'){
                const checked = e.target.checked;
                document.querySelectorAll('input.table-item').forEach(cb => cb.checked = checked);
                updateBulkUI();
            }
            if(e.target && e.target.classList.contains('table-item')){
                updateBulkUI();
            }
        });

        function updateBulkUI(){
            const selected = document.querySelectorAll('input.table-item:checked');
            const el = document.querySelector('.table-items-action');
            document.querySelector('.table-items-count').innerText = selected.length;
            if(selected.length > 0) el.classList.remove('hidden'); else el.classList.add('hidden');
        }

        // show photo modal from Alpine: handled by Alpine openPhoto -> showPhoto
        // show location modal: handled by Alpine openLocation -> showMap then initModalMap when shown

    </script>
</div>
