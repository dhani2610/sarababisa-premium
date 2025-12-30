@section('title')
    Karyawan
@endsection

{{-- <x-toko-layout> --}}
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-3">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Karyawan ✨</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                @if (auth()->user()->role == 'Kepala Toko')
                    {{-- <x-search-form placeholder="Cari nama karyawan..." /> --}}

                    <div x-data="{ modalOpen: false }">
                        <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16"><path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" /></svg>
                            <span class="hidden xs:block ml-2">Tambah Karyawan</span>
                        </button>
                        <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>
                        <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                            <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full" @click.outside="modalOpen = false">
                                <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                                    <div class="font-semibold text-slate-800">Tambah Karyawan</div>
                                    <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">&times;</button>
                                </div>
                                <form action="{{ route('karyawan.store') }}" method="post">
                                    @csrf
                                    <div class="px-5 py-4">
                                        <div class="space-y-3">
                                            <div><label class="block text-sm font-medium mb-1" for="name">Nama Karyawan <span class="text-rose-500">*</span></label><input id="name" name="name" class="form-input w-full px-2 py-1" type="text" required /></div>
                                            <div><label class="block text-sm font-medium mb-1" for="jabatan">Jabatan <span class="text-rose-500">*</span></label><input id="jabatan" name="jabatan" class="form-input w-full px-2 py-1" type="text" required /></div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="status">Status <span class="text-rose-500">*</span></label>
                                                <select id="status" name="status" class="form-select text-sm py-1 w-full" required>
                                                    <option value="Karyawan Tetap">Karyawan Tetap</option>
                                                    <option value="Karyawan Kontrak">Karyawan Kontrak</option>
                                                    <option value="Magang">Magang</option>
                                                    <option value="Freelancer">Freelancer</option>
                                                </select>
                                            </div>
                                            <div><label class="block text-sm font-medium mb-1" for="bulankerja">Bulan Kerja <span class="text-rose-500">*</span></label><input id="bulankerja" name="bulankerja" class="form-input w-full px-2 py-1" type="date" required /></div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="gaji">Gaji Pokok <span class="text-rose-500">*</span></label>
                                                <div class="relative"><input id="gaji" name="gaji" class="form-input w-full pl-10 px-2 py-1 input-currency" type="text" required /><div class="absolute inset-0 right-auto flex items-center pointer-events-none"><span class="text-sm text-slate-400 font-medium px-3">Rp.</span></div></div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="absen">Absen Harian <span class="text-rose-500">*</span></label>
                                                <div class="relative"><input id="absen" name="absen" class="form-input w-full pl-10 px-2 py-1 input-currency" type="text" required placeholder="0" /><div class="absolute inset-0 right-auto flex items-center pointer-events-none"><span class="text-sm text-slate-400 font-medium px-3">Rp.</span></div></div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="bpjs">BPJS <span class="text-rose-500">*</span></label>
                                                <div class="relative"><input id="bpjs" name="bpjs" class="form-input w-full pl-10 px-2 py-1 input-currency" type="text" required placeholder="0" /><div class="absolute inset-0 right-auto flex items-center pointer-events-none"><span class="text-sm text-slate-400 font-medium px-3">Rp.</span></div></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="px-5 py-4 border-t border-slate-200 flex justify-end space-x-2">
                                        <button type="button" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600" @click="modalOpen = false">Batal</button>
                                        <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 px-4 py-2 rounded-sm text-sm bg-emerald-500 text-white">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 px-4 py-2 rounded-sm text-sm bg-rose-500 text-white">{{ session('error') }}</div>
        @endif

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                <h2 class="font-semibold text-slate-800">Semua Karyawan</h2>
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count">0</span> item yang dipilih</div>
                            <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" onclick="deleteSelected()">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto p-4">
                <table id="karyawan-table" class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center"><label class="inline-flex"><span class="sr-only">Select all</span><input id="parent-checkbox" class="form-checkbox" type="checkbox" /></label></div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">No</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Nama</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Jabatan</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Gaji Pokok</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Absen</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">BPJS</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Kasbon</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Insiden</th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="printModalWrapper" class="relative z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">

                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-base font-semibold leading-6 text-gray-900" id="printModalTitle">Atur Pencetakan Slip Gaji</h3>
                                <div class="mt-4">
                                    <form id="printForm" action="" method="get" target="_blank">
                                        @csrf
                                        <div class="space-y-4">
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Penanggalan Slip Gaji <span class="text-rose-500">*</span></label>
                                                <input id="modal_penanggalan" name="penanggalan" class="form-input w-full py-2" type="date" required />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium mb-1">Pilih Periode Gaji <span class="text-rose-500">*</span></label>
                                                <input id="modal_periode" type="month" name="periode" class="form-input w-full py-2" required>
                                            </div>
                                        </div>

                                        <input type="hidden" id="modal_worker_id">
                                        <input type="hidden" id="modal_worker_name">
                                        <input type="hidden" id="modal_worker_hp">

                                        <div class="mt-5 sm:flex sm:flex-row-reverse gap-2">
                                            <button type="submit" class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white w-full sm:w-auto">Cetak PDF</button>

                                            <button type="button" onclick="sendWAFromModal()" class="btn-sm bg-emerald-500 hover:bg-emerald-600 text-white w-full sm:w-auto flex justify-center items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-brand-whatsapp" width="18" height="18" viewBox="0 0 24 24" stroke-width="1.5" stroke="#ffffff" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" /><path d="M9 10a0.5 .5 0 0 0 1 0v-1a0.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a0.5 .5 0 0 0 0 -1h-1a0.5 .5 0 0 0 0 1" /></svg>
                                                Kirim WA
                                            </button>

                                            <button type="button" onclick="closePrintModal()" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600 w-full sm:w-auto">Batal</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_wrapper .dataTables_length select { padding-right: 30px; width: auto; }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        // --- HELPER FORMAT RUPIAH ---
        function formatRupiah(angka) {
            if (!angka) return '';
            var number_string = angka.toString().replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return rupiah;
        }

        const fonnteToken = "{{ getStoreSettingByCabang()->fonnte ?? '' }}";

        $(document).ready(function() {

            // 1. Format Rupiah Input
            $(document).on('input', '.input-currency', function() {
                $(this).val(formatRupiah($(this).val()));
            });

            // 2. Clean Input on Submit
            $('form').on('submit', function() {
                $(this).find('.input-currency').each(function() {
                    var cleanVal = $(this).val().replace(/\./g, '');
                    $(this).val(cleanVal);
                });
            });

            // 3. DataTables Init
            var table = $('#karyawan-table').DataTable({
                processing: false,
                serverSide: false,
                ajax: "{{ route('karyawan.data') }}",
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'jabatan', name: 'jabatan' },
                    { data: 'gaji', name: 'gaji' },
                    { data: 'absen', name: 'absen' },
                    { data: 'bpjs', name: 'bpjs' },
                    { data: 'kasbon_total', name: 'kasbon_total', searchable: false },
                    { data: 'insiden_total', name: 'insiden_total', searchable: false },
                    { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                ],
                order: [[1, 'asc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: { first: "Awal", last: "Akhir", next: "Lanjut", previous: "Kembali" }
                },
                drawCallback: function() {
                    attachCheckboxHandlers();
                }
            });

            // 4. Logic Checkbox & Bulk Delete
            function attachCheckboxHandlers() {
                $('#parent-checkbox').prop('checked', false);
                toggleBulkAction();
                $('#parent-checkbox').off('click').on('click', function() {
                    var checked = $(this).is(':checked');
                    $('input.table-item').prop('checked', checked);
                    toggleBulkAction();
                });
                $('#karyawan-table').off('change', '.table-item').on('change', '.table-item', function() {
                    var all = $('input.table-item').length;
                    var checked = $('input.table-item:checked').length;
                    $('#parent-checkbox').prop('checked', all === checked && all > 0);
                    toggleBulkAction();
                });
            }

            function toggleBulkAction() {
                var checkedCount = $('input.table-item:checked').length;
                $('.table-items-count').text(checkedCount);
                if (checkedCount > 0) {
                    $('.table-items-action').removeClass('hidden');
                } else {
                    $('.table-items-action').addClass('hidden');
                }
            }

            window.deleteSelected = function() {
                var selectedIds = $('input.table-item:checked').map(function() { return $(this).val(); }).get();
                if (selectedIds.length === 0) return alert('Pilih data terlebih dahulu.');
                if (!confirm('Yakin ingin menghapus data ini?')) return;
                $.ajax({
                    url: "{{ route('karyawan.delete-batch') }}",
                    method: 'POST',
                    data: { ids: selectedIds, _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        alert(response.message);
                        table.ajax.reload();
                        $('.table-items-action').addClass('hidden');
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menghapus data.';
                        alert(msg);
                    }
                });
            };
        });

        // --- GLOBAL PRINT MODAL FUNCTIONS ---
        function openPrintModal(id, name, hp) {
            // Set value ke hidden input di modal
            $('#modal_worker_id').val(id);
            $('#modal_worker_name').val(name);
            $('#modal_worker_hp').val(hp);

            // Set judul
            $('#printModalTitle').text('Atur Pencetakan: ' + name);

            // Set Form Action untuk tombol Cetak (default form submit)
            // Route cetak-slip-gaji/{id}
            let urlCetak = "{{ url('slip-gaji') }}/" + id;
            $('#printForm').attr('action', urlCetak);

            // Tampilkan Modal
            $('#printModalWrapper').removeClass('hidden');
        }

        function closePrintModal() {
            $('#printModalWrapper').addClass('hidden');
        }

        function sendWAFromModal() {
            const id = $('#modal_worker_id').val();
            const name = $('#modal_worker_name').val();
            const originalHp = $('#modal_worker_hp').val();
            const penanggalan = $('#modal_penanggalan').val();
            const periode = $('#modal_periode').val();

            if (!penanggalan || !periode) {
                alert('Harap isi Penanggalan dan Periode Gaji terlebih dahulu!');
                return;
            }

            if (!originalHp || originalHp === 'null') {
                alert('Nomor HP karyawan tidak ditemukan!');
                return;
            }

            let phone = originalHp.toString().replace(/\D/g, '');
            if (phone.startsWith('0')) { phone = '62' + phone.substring(1); }

            const baseUrl = "{{ url('slip-gaji') }}/" + id;
            const fullLink = `${baseUrl}?penanggalan=${penanggalan}&periode=${periode}`;

            const message = `*Slip Gaji Karyawan*%0A%0A` +
                            `Halo ${name},%0A` +
                            `Berikut adalah link slip gaji Anda untuk periode *${periode}*:%0A%0A` +
                            `${fullLink}%0A%0A` +
                            `Harap disimpan. Terima kasih.`;

            if (fonnteToken) {
                kirimFontee(fonnteToken, phone, message);
            } else {
                window.open(`https://wa.me/${phone}?text=${message}`, '_blank');
            }
        }

        function kirimFontee(token, phone, message) {
            fetch('https://api.fonnte.com/send', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Authorization': token },
                body: JSON.stringify({ target: phone, message: decodeURIComponent(message) })
            })
            .then(async response => {
                const data = await response.json().catch(() => ({}));
                if (response.ok && (data.status === true || data.success)) {
                    alert('✅ Link Slip Gaji berhasil dikirim ke ' + phone);
                    closePrintModal();
                } else {
                    alert('⚠️ Gagal mengirim via WA Server.');
                }
            })
            .catch(error => { console.error(error); alert('❌ Kesalahan koneksi.'); });
        }
    </script>
{{-- </x-toko-layout> --}}
