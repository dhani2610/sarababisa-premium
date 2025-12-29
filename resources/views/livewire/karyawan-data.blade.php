<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Karyawan ✨</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            @if (auth()->user()->role == 'Kepala Toko')
            <!-- Search form -->
            <x-search-form placeholder="Masukkan nama karyawan" />

            <!-- Create invoice button -->
            <div x-data="{ modalOpen: false }">
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Karyawan</span>
                </button>
                <!-- Modal backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-out duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    aria-hidden="true"
                    x-cloak
                ></div>
                <!-- Modal dialog -->
                <div
                    id="tambah-modal"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog"
                    aria-modal="true"
                    x-show="modalOpen"
                    x-transition:enter="transition ease-in-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-4"
                    x-cloak
                >
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                        <!-- Modal header -->
                        <div class="px-5 py-3 border-b border-slate-200">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-slate-800">Tambah Karyawan</div>
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- Modal content -->
                        <form action="{{ route('karyawan.store') }}" method="post">
                            @csrf
                            <div class="px-5 py-4">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="name">Nama Karyawan <span class="text-rose-500">*</span></label>
                                        <input id="name" name="name" class="form-input w-full px-2 py-1" type="text" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="jabatan">Jabatan <span class="text-rose-500">*</span></label>
                                        <input id="jabatan" name="jabatan" class="form-input w-full px-2 py-1" type="text" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="status">Status <span class="text-rose-500">*</span></label>
                                        <select id="status" name="status" class="form-select text-sm py-1 w-full" required>
                                            <option value="Karyawan Tetap">Karyawan Tetap</option>
                                            <option value="Karyawan Kontrak">Karyawan Kontrak</option>
                                            <option value="Magang">Magang</option>
                                            <option value="Freelancer">Freelancer</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="bulankerja">Bulan Kerja <span class="text-rose-500">*</span></label>
                                        <input id="bulankerja" name="bulankerja" class="form-input w-full px-2 py-1" type="date" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="gaji">Gaji Pokok <span class="text-rose-500">*</span></label>
                                        <input id="gaji" name="gaji" class="form-input w-full px-2 py-1" type="number" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="absen">Absen Harian <span class="text-rose-500">*</span></label>
                                        <input id="absen" name="absen" class="form-input w-full px-2 py-1" type="number" placeholder="Isikan 0 jika tidak ada" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="bpjs">BPJS <span class="text-rose-500">*</span></label>
                                        <input id="bpjs" name="bpjs" class="form-input w-full px-2 py-1" type="number" placeholder="Isikan 0 jika tidak ada" required />
                                    </div>
                                </div>
                            </div>
                            <!-- Modal footer -->
                            <div class="px-5 py-4 border-t border-slate-200">
                                <div class="flex flex-wrap justify-end space-x-2">
                                    <a href="{{ route('karyawan.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                        Batal
                                    </a>
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

        </div>

    </div>

    <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
        <div x-data="handleSelect">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                {{-- Left side --}}
                <h2 class="font-semibold text-slate-800">Semua Karyawan <span class="text-slate-400 font-medium">{{ $workers->count() }}</span></h2>
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span> item yang dipilih</div>
                            <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" @click="deleteSelected">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="table-auto w-full">
                    <!-- Table header -->
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            @if (auth()->user()->role == 'Kepala Toko')
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox" @click="toggleAll" />
                                    </label>
                                </div>
                            </th>
                            @endif
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">No.</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Jabatan</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Gaji Pokok</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Absen</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">BPJS</div>
                            </th>
                            {{-- <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Bonus</div>
                            </th> --}}
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Kasbon</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Insiden</div>
                            </th>
                            {{-- <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Total Gaji</div>
                            </th> --}}
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Aksi</div>
                            </th>
                        </tr>
                    </thead>
                    <!-- Table body -->
                    <tbody class="text-sm divide-y divide-slate-200">
                        <!-- Row -->
                        @php
                            $i = 1
                        @endphp
                        @foreach($workers as $item)
                            <tr>
                                @if (auth()->user()->role == 'Kepala Toko')
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="flex items-center">
                                        <label class="inline-flex">
                                            <span class="sr-only">Select</span>
                                            <input class="table-item form-checkbox" type="checkbox" value="{{ $item->id }}" @click="uncheckParent" />
                                        </label>
                                    </div>
                                </td>
                                @endif
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $i++ }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->name }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">{{ $item->jabatan }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">Rp. {{ number_format($item->gaji) }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">Rp. {{ number_format($item->absen) }}</div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">Rp. {{ number_format($item->bpjs) }}</div>
                                </td>
                                {{-- <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">
                                        Rp. {{ number_format($item->salary->sum('bonus')) }}
                                    </div>
                                </td> --}}
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">
                                        Rp. {{ number_format($item->debt->sum('total')) }}
                                    </div>
                                </td>
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">
                                        Rp. {{ number_format($item->incident->sum('biaya_teknisi')) }}
                                    </div>
                                </td>
                                {{-- <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                    <div class="font-medium">
                                        Rp. {{ number_format($item->gaji + $item->absen + $item->bpjs + $item->salary->sum('bonus') - $item->debt->sum('total')) }}
                                    </div>
                                </td> --}}
                                <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                    <div class="space-x-1 flex">
                                        <div x-data="{ modalOpen: false }">
                                            <button class="text-slate-400 hover:text-slate-500 rounded-full" @click.prevent="modalOpen = true"
                                                aria-controls="cetak-modal-{{ $item->id }}">
                                                <span class="sr-only">Cetak</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-printer w-6 h-6 mt-1"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="#00abfb" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                                                    <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                                                    <rect x="7" y="13" width="10" height="8" rx="2" />
                                                </svg>
                                            </button>

                                            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                                                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                                                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak>
                                            </div>

                                            <div id="cetak-modal-{{ $item->id }}"
                                                class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                                                role="dialog" aria-modal="true" x-show="modalOpen"
                                                x-transition:enter="transition ease-in-out duration-200"
                                                x-transition:enter-start="opacity-0 translate-y-4"
                                                x-transition:enter-end="opacity-100 translate-y-0"
                                                x-transition:leave="transition ease-in-out duration-200"
                                                x-transition:leave-start="opacity-100 translate-y-0"
                                                x-transition:leave-end="opacity-0 translate-y-4" x-cloak>

                                                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full"
                                                    @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">

                                                    <div class="px-5 py-3 border-b border-slate-200">
                                                        <div class="flex justify-between items-center">
                                                            <div class="font-semibold text-slate-800">Atur Pencetakan Slip Gaji: {{ $item->name }}</div>
                                                            <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                                                <div class="sr-only">Close</div>
                                                                <svg class="w-4 h-4 fill-current">
                                                                    <path
                                                                        d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </div>

                                                    <form action="{{ route('cetak-slip-gaji', $item->id) }}" method="get">
                                                        @csrf
                                                        <div class="px-5 py-4">
                                                            <div class="space-y-3">
                                                                <div>
                                                                    <label class="block text-sm font-medium mb-1">Penanggalan Slip Gaji <span
                                                                            class="text-rose-500">*</span></label>
                                                                    <input id="penanggalan-{{ $item->id }}" name="penanggalan" class="form-input w-full py-2" type="date"
                                                                        required />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-medium mb-1">Pilih Periode Gaji <span
                                                                            class="text-rose-500">*</span></label>
                                                                    <input type="month" name="periode" id="periode-{{ $item->id }}" class="form-input w-full py-2" required>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="px-5 py-4 border-t border-slate-200">
                                                            <div class="flex flex-wrap justify-end space-x-2">
                                                                <button type="button" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600"
                                                                    @click="modalOpen = false">
                                                                    Batal
                                                                </button>
                                                                @php
                                                                    $dataAkun = \App\Models\User::where('workers_id',$item->id)->first();
                                                                @endphp

                                                                {{-- TOMBOL KIRIM WA --}}
                                                                {{-- Kita kirim Parameter ID, Nama, HP ke Javascript --}}
                                                                <button type="button"
                                                                    onclick="handleKirimWA('{{ $item->id }}', '{{ $item->name }}', '{{ $dataAkun->nomor_hp ?? '' }}')"
                                                                    class="btn-sm bg-emerald-500 hover:bg-emerald-600 text-white">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp mr-1" width="18" height="18" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                        <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                                                        <path d="M9 10a0.5 .5 0 0 0 1 0v-1a0.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a0.5 .5 0 0 0 0 -1h-1a0.5 .5 0 0 0 0 1" />
                                                                    </svg>
                                                                    Kirim WA
                                                                </button>

                                                                <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Cetak</button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('handleSelect', () => ({
                selectall: false,
                selectAction() {
                    countEl = document.querySelector('.table-items-action');
                    if (!countEl) return;
                    checkboxes = document.querySelectorAll('input.table-item:checked');
                    document.querySelector('.table-items-count').innerHTML = checkboxes.length;
                    if (checkboxes.length > 0) {
                        countEl.classList.remove('hidden');
                    } else {
                        countEl.classList.add('hidden');
                    }
                },
                toggleAll() {
                    this.selectall = !this.selectall;
                    checkboxes = document.querySelectorAll('input.table-item');
                    [...checkboxes].map((el) => {
                        el.checked = this.selectall;
                    });
                    this.selectAction();
                },
                uncheckParent() {
                    this.selectall = false;
                    document.getElementById('parent-checkbox').checked = false;
                    this.selectAction();
                },
                deleteSelected() {
                    const checkboxes = document.querySelectorAll('input.table-item:checked');
                    const selectedIds = [...checkboxes].map((checkbox) => checkbox.value);

                    // Kirim permintaan penghapusan ke server
                    fetch('/workers/delete', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ selectedIds }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        // Refresh halaman atau lakukan tindakan lain setelah penghapusan
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Gagal menghapus data:', error);
                    });
                },
            }))
        })
    </script>
    <script>
    // Ambil Token Fonnte sekali saja dari PHP (jika ada)
    const fonnteToken = "{{ getStoreSettingByCabang()->fonnte ?? '' }}";

    function handleKirimWA(id, name, originalHp) {
        // 1. Ambil Value bedasarkan ID Unik baris tersebut
        const penanggalan = document.getElementById('penanggalan-' + id).value;
        const periode = document.getElementById('periode-' + id).value;

        // Validasi
        if (!penanggalan || !periode) {
            alert('Harap isi Penanggalan dan Periode Gaji terlebih dahulu!');
            return;
        }

        // 2. Format Nomor HP (Ganti 08/0 jadi 62)
        if (!originalHp) {
            alert('Nomor HP karyawan tidak ditemukan!');
            return;
        }

        let phone = originalHp.toString().replace(/\D/g, ''); // Hapus non-angka
        if (phone.startsWith('0')) {
            phone = '62' + phone.substring(1);
        }

        // 3. Susun Link
        // Base URL cetak slip gaji (sesuaikan route Anda jika perlu)
        const baseUrl = "{{ url('slip-gaji') }}/" + id;
        const fullLink = `${baseUrl}?penanggalan=${penanggalan}&periode=${periode}`;

        // 4. Susun Pesan
        const message = `*Slip Gaji Karyawan*%0A%0A` +
                        `Halo ${name},%0A` +
                        `Berikut adalah link slip gaji Anda untuk periode *${periode}*:%0A%0A` +
                        `${fullLink}%0A%0A` +
                        `Harap disimpan. Terima kasih.`;

        // 5. Eksekusi Kirim
        if (fonnteToken) {
            kirimFontee(fonnteToken, phone, message);
        } else {
            window.open(`https://wa.me/${phone}?text=${message}`, '_blank');
        }
    }

    // Fungsi Kirim Fonnte (Reusable)
    function kirimFontee(token, phone, message) {

        fetch('https://api.fonnte.com/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': token
            },
            body: JSON.stringify({
                target: phone,
                message: decodeURIComponent(message)
            })
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (response.ok && (data.status === true || data.success)) {
                alert('✅ Link Slip Gaji berhasil dikirim ke ' + phone);
            } else {
                console.error("Gagal Fontee:", data);
                alert('⚠️ Gagal mengirim via WA Server. Cek Console.');
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert('❌ Terjadi kesalahan koneksi.');
        });
    }
</script>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $workers->links() }}
    </div>
</div>
