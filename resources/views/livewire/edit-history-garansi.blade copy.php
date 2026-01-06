<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-3">

        <!-- Left: Title -->
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Edit Riwayat Garansi Service ✨</h1>
        </div>

        <!-- Right: Actions -->
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

            <!-- Create invoice button -->
            <div x-data="{ modalOpen: @entangle('modalOpen') }">

                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true">
                    Edit Riwayat Garansi
                </button>

                <!-- Modal backdrop -->
                <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                    x-transition aria-hidden="true" x-cloak></div>

                <div id="tambah-modal-data"
                    class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                    role="dialog" aria-modal="true" x-show="modalOpen" x-transition x-cloak>
                    <div class="bg-white rounded shadow-lg overflow-auto max-w-2xl w-full max-h-full"
                        @click.outside="if(!$event.target.closest('.select2-container')) modalOpen = false"
                        @keydown.escape.window="modalOpen = false">

                        <form action="{{ route('history-garansi.update',$historyGaransi->id) }}" method="POST" id="formGaransi">
                            @csrf
                            @method('PUT')
                            <div class="px-5 py-4 space-y-4">

                                <div x-data="{ showDetails: true }">
                                    <label class="block text-sm font-medium mb-1" for="kondisi_servis">Kondisi Servis <span class="text-rose-500">*</span></label>
                                    <div class="flex flex-wrap items-center">
                                        <div class="m-3">
                                            <!-- Start -->
                                            <label class="flex items-center">
                                                <input type="radio" name="status" value="2" class="form-radio" checked x-on:click="showDetails = true"/>
                                                <span class="text-sm ml-2">Sudah Selesai</span>
                                            </label>
                                            <!-- End -->
                                        </div>
                                        <div class="m-3">
                                            <!-- Start -->
                                            <label class="flex items-center">
                                                <input type="radio" name="status" value="1" class="form-radio" x-on:click="showDetails = false"/>
                                                <span class="text-sm ml-2">Menunggu konfirmasi</span>
                                            </label>
                                            <!-- End -->
                                        </div>
                                        <div class="m-3">
                                            <!-- Start -->
                                            <label class="flex items-center">
                                                <input type="radio" name="status" value="3" class="form-radio" x-on:click="showDetails = false"/>
                                                <span class="text-sm ml-2">Dibatalkan / Pengembalian Dana</span>
                                            </label>
                                            <!-- End -->
                                        </div>
                                    </div>


                                    <div x-show="showDetails" class="mt-3 space-y-3">

                                        <!-- Teknisi -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Teknisi<span
                                                    class="text-rose-500">*</span></label>
                                            <select name="teknisi_id" class="form-select w-full ">
                                                <option value="">-- Pilih Teknisi --</option>
                                                @foreach ($users as $u)
                                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!-- Tindakan -->
                                        <div class="mb-3">
                                            <label class="block text-sm font-medium mb-1">Tindakan</label>
                                            <button type="button" id="addTindakanRow"
                                                class="btn-sm bg-indigo-500 text-white mb-2">
                                                + Tambah Tindakan
                                            </button>
                                            <div id="tindakanContainer"></div>
                                        </div>

                                        <!-- Total Biaya Servis -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Total Modal Tindakan</label>
                                            <input type="number" name="total_biaya_tindakan" id="total_biaya_tindakan"
                                                value="0" class="form-input w-full" onkeyup="updateTotalModal()">
                                        </div>

                                        <hr>

                                        <!-- Checkbox sebelum sparepart -->
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="useSparepartCheckbox">
                                            <label class="form-check-label" for="useSparepartCheckbox">
                                                Apakah menggunakan stok sparepart toko?
                                            </label>
                                        </div>
                                        <!-- Sparepart Dynamic -->
                                        <div id="sparepart_wrapper" style="display:none;">
                                            <label class="block text-sm font-medium mb-1">Sparepart</label>
                                            <button type="button" id="addSparepartRow" class="btn-sm bg-indigo-500 text-white">
                                                + Tambah Sparepart
                                            </button>
                                            <div class="mt-2" id="rowContainer"></div>
                                        </div>

                                        <!-- Total Biaya -->
                                        <div style="display:none;" id="modal_sparepart_wrapper">
                                            <label class="block text-sm font-medium mb-1">Modal Sparepart<span
                                                    class="text-rose-500">*</span></label>
                                            <input type="number" name="modal_sparepart" id="modal_sparepart" value="0"
                                                class="form-input w-full">
                                        </div>
                                        <!-- Total Biaya -->
                                        <div>
                                            <label class="block text-sm font-medium mb-1">Total Modal<span
                                                    class="text-rose-500">*</span></label>
                                            <input type="number" name="total_biaya" id="total_biaya" value="0"
                                                class="form-input w-full">
                                        </div>
                                    </div>
                                    <!-- Catatan -->

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Pelanggan <span
                                                class="text-rose-500">*</span></label>
                                        <select name="id_customer" class="form-select select2 w-full " required>
                                            <option value="">-- Pilih Pelanggan --</option>
                                            @foreach ($customer as $cs)
                                                <option value="{{ $cs->id }}" {{  $historyGaransi->id_customer == $cs->id ? 'selected' : ''  }}>{{ $cs->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- <div>
                                        <label class="block text-sm font-medium mb-1 mt-1" for="fungsi_keluar">Pengecekan
                                            Fungsi Keluar<span class="text-rose-500">*</span></label>
                                        <input id="fungsi_keluar" name="fungsi_keluar" class="form-input w-full px-2 py-1"
                                            type="text" required
                                            placeholder="Contoh: Tombol, Kamera, Speaker, dll" />
                                    </div> --}}
                                    <div>
                                        <label class="block text-sm font-medium mb-1">List Pengecekan Fungsi (Masuk & Keluar) <span class="text-rose-500">*</span></label>
                                        <small class="text-rose-500">*Jika ingin cepat silahkan isi kolom other.</small>
                                        <div class="overflow-x-auto border rounded-sm">
                                            <table class="w-full text-xs text-left border-collapse" id="table-qc-tab2">
                                                <thead class="bg-slate-100 uppercase text-slate-500 font-semibold">
                                                    <tr>
                                                        <th class="border border-slate-300 p-2 w-8 text-center">No</th>
                                                        <th class="border border-slate-300 p-2 w-1/3">ITEM</th>
                                                        <th class="border border-slate-300 p-2 bg-blue-50 text-center">REMARK IN</th>
                                                        <th class="border border-slate-300 p-2 bg-blue-50 text-center">REMARK OUT</th>
                                                        <th class="border border-slate-300 p-2 w-8 text-center"></th> </tr>
                                                </thead>
                                                <tbody id="checklist-tbody-tab2" class="text-slate-700">
                                                    </tbody>
                                            </table>
                                        </div>
                                        <button type="button" onclick="addCustomRowTab2()" class="mt-2 text-xs flex items-center text-indigo-600 font-bold hover:text-indigo-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" /></svg>
                                            Tambah Baris Custom
                                        </button>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1 mt-1">Catatan<span
                                                class="text-rose-500">*</span></label>
                                        <textarea name="catatan" class="form-input w-full" required>{{  $historyGaransi->catatan  }}</textarea>
                                    </div>
                                </div>

                            </div>
                            <div class="px-5 py-4 border-t flex justify-end space-x-2">
                                <a type="button" class="btn-sm border-slate-200"
                                    href="{{  route('history-garansi.index')  }}">Batal</a>
                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


        </div>

    </div>


    @if ($errors->any())
        <div x-show="open" x-data="{ open: true }">
            <div class="px-4 py-2 rounded-sm text-sm bg-rose-500 text-white">
                <div class="flex w-full justify-between items-start">
                    <div class="flex">
                        <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                            <path
                                d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                        </svg>
                        @foreach ($errors->all() as $error)
                            <div class="font-medium">{{ $error }}</div>
                        @endforeach
                    </div>
                    <button class="opacity-70 hover:opacity-80 ml-3 mt-[3px]" @click="open = false">
                        <div class="sr-only">Close</div>
                        <svg class="w-4 h-4 fill-current">
                            <path
                                d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


     <script>
        // Struktur: [ArrayStandar, ObjectMasuk, ObjectKeluar]
        const fullData = @json([$qcItems, $qcMasuk, $qcKeluar]);

        // Pecah data ke variabel biar mudah dibaca
        const standardItems = fullData[0];       // List Nama Item
        const dataMasuk     = fullData[1] || {}; // Data Value Masuk
        const dataKeluar    = fullData[2] || {}; // Data Value Keluar

        document.addEventListener('DOMContentLoaded', function() {
            renderAllChecklists();
        });

        // --- FUNGSI RENDER UTAMA ---
        function renderAllChecklists() {
            const tbodyTab2 = document.getElementById('checklist-tbody-tab2');
            tbodyTab2.innerHTML = '';

            standardItems.forEach((item, index) => {
                let valMasuk = dataMasuk[item] || '';
                let valKeluar = dataKeluar[item] || '';

                if(valMasuk === null) valMasuk = '';
                if(valKeluar === null) valKeluar = '';

                const tr = document.createElement('tr');
                tr.className = "border-b border-slate-200 hover:bg-slate-50";

                tr.innerHTML = `
                    <td class="border border-slate-300 p-1 text-center font-bold row-num">${index + 1}</td>
                    <td class="border border-slate-300 p-1 font-medium bg-slate-50">${item}</td>
                    <td class="border border-slate-300 p-0">
                        <input type="text" name="qc_masuk[${item}]" value="${valMasuk}" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
                    </td>
                    <td class="border border-slate-300 p-0">
                        <input type="text" name="qc_keluar[${item}]" value="${valKeluar}" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
                    </td>
                    <td class="border border-slate-300 p-1 text-center">
                        <button type="button" class="text-rose-500 hover:text-rose-700" onclick="deleteRow(this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                        </button>
                    </td>
                `;
                tbodyTab2.appendChild(tr);
            });

            const allKeys = new Set([...Object.keys(dataMasuk), ...Object.keys(dataKeluar)]);

            allKeys.forEach(key => {
                if (!standardItems.includes(key)) {
                    let valMasuk = dataMasuk[key] || '';
                    let valKeluar = dataKeluar[key] || '';

                    addCustomRowTab2(key, valMasuk, valKeluar);
                }
            });
        }

        // --- MODIFIKASI FUNGSI CUSTOM ROW ---
        // Tambahkan parameter agar bisa diisi value-nya saat load data
        function addCustomRowTab2(name = '', valMasuk = '', valKeluar = '') {
            const tbody = document.getElementById('checklist-tbody-tab2');
            const rowCount = tbody.rows.length + 1;
            const tr = document.createElement('tr');
            tr.className = "border-b border-slate-200 hover:bg-yellow-50";

            tr.innerHTML = `
                <td class="border border-slate-300 p-1 text-center font-bold row-num">${rowCount}</td>
                <td class="border border-slate-300 p-0">
                    <input type="text" name="custom_item_name[]" value="${name}" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent font-medium text-indigo-600" placeholder="Ketik Nama Item..." required>
                </td>
                <td class="border border-slate-300 p-0">
                    <input type="text" name="custom_qc_masuk[]" value="${valMasuk}" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
                </td>
                <td class="border border-slate-300 p-0">
                    <input type="text" name="custom_qc_keluar[]" value="${valKeluar}" class="w-full h-full p-1 border-0 focus:ring-0 bg-transparent text-center" placeholder="-">
                </td>
                <td class="border border-slate-300 p-1 text-center">
                    <button type="button" class="text-rose-500 hover:text-rose-700" onclick="deleteRow(this)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="4" y1="7" x2="20" y2="7" /><line x1="10" y1="11" x2="10" y2="17" /><line x1="14" y1="11" x2="14" y2="17" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        }

        function deleteRow(btn) {
            const row = btn.closest('tr');
            const tbody = row.parentNode;
            row.remove();
            Array.from(tbody.rows).forEach((r, index) => {
                const numCell = r.querySelector('.row-num');
                if(numCell) numCell.innerText = index + 1;
            });
        }
    </script>
    <script>
        $(document).ready(function() {
            // aktifkan select2
            $('.select2').select2({
                width: '100%', // biar full width
                dropdownParent: $('#tambah-modal-data') // penting supaya muncul di dalam modal
            });

            // update teknisi + garansi saat service_id berubah
            $('#service_id').on('change', function() {
                let selected = $(this).find(':selected');
                $('#prev_teknisi').text(selected.data('teknisi'));
                $('#exp_garansi').text(selected.data('expired'));
                let notaUrl = selected.data('nota');
                if (notaUrl) {
                    $('#link_nota').html(
                        `<a href="${notaUrl}" target="_blank" class="text-blue-600 underline">Lihat Nota</a>`
                    );
                } else {
                    $('#link_nota').html('');
                }
            });

        });
    </script>

  <script>
document.addEventListener('alpine:init', () => {
    Alpine.data('handleSelect', () => ({
        selected: [],
        deleteUrl: '{{ route("history-garansi.bulkDelete") }}', // kita buat route ini
        toggleAll(e) {
            const checked = e.target.checked;
            this.selected = [];
            document.querySelectorAll('.table-item').forEach(el => {
                el.checked = checked;
                if (checked) this.selected.push(el.value);
            });
            this.toggleAction();
        },
        uncheckParent() {
            const all = document.querySelectorAll('.table-item');
            const selected = Array.from(all).filter(x => x.checked).map(x => x.value);
            this.selected = selected;
            document.getElementById('parent-checkbox').checked = selected.length === all.length;
            this.toggleAction();
        },
        toggleAction() {
            const action = document.querySelector('.table-items-action');
            const countEl = document.querySelector('.table-items-count');
            if (this.selected.length > 0) {
                action.classList.remove('hidden');
                countEl.textContent = this.selected.length;
            } else {
                action.classList.add('hidden');
            }
        },
        deleteSelected() {
            if (this.selected.length === 0) return;

            if (!confirm('Yakin ingin menghapus data terpilih?')) return;

            fetch(this.deleteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ ids: this.selected })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                }
            })
            .catch(err => console.error(err));
        }
    }))
});
</script>


    <script>
        $(document).on('click', '.toggle-status', function() {
            let id = $(this).data('id');
            let btn = $(this);

            $.ajax({
                url: `/history-garansi/${id}/toggle-status`,
                method: "PATCH",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    if (res.success) {
                        btn.text(res.label);

                        btn.removeClass(
                            'bg-yellow-100 text-yellow-700 bg-green-100 text-green-700 bg-red-100 text-red-700'
                        );

                        if (res.status == 1) {
                            btn.addClass('bg-yellow-100 text-yellow-700');
                        } else if (res.status == 2) {
                            btn.addClass('bg-green-100 text-green-700');
                        } else {
                            btn.addClass('bg-red-100 text-red-700');
                        }
                    }
                },
                error: function() {
                    alert('Gagal update status!');
                }
            });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let products = @json($products);
            let rowId = 0;

            document.getElementById("addSparepartRow").addEventListener("click", function() {
                rowId++;
                let container = document.getElementById("rowContainer");

                let div = document.createElement("div");
                div.classList.add(
                    "grid",
                    "grid-cols-1", // default 1 kolom (mobile)
                    "md:grid-cols-4", // di desktop jadi 4 kolom
                    "gap-2",
                    "items-center",
                    "mb-2"
                );

                div.innerHTML = `
                        <select name="sparepart[${rowId}][id]"
                                class="form-select sparepartSelect select2 w-full" >
                            <option value="">-- Pilih Sparepart --</option>
                            ${products.map(p => `<option value="${p.id}" data-harga="${p.harga_modal}">${p.product_name}</option>`).join("")}
                        </select>

                        <input type="number" name="sparepart[${rowId}][harga]"
                            class="form-input harga w-full" placeholder="Harga" >

                        <input type="number" name="sparepart[${rowId}][qty]"
                            class="form-input qty w-full" placeholder="Qty" value="1" min="1" >

                        <button type="button"
                                class="btn-sm bg-rose-500 text-white w-full md:w-auto removeRow">
                            ✕
                        </button>
                    `;

                container.appendChild(div);

                // update harga otomatis
                div.querySelector(".sparepartSelect").addEventListener("change", function() {
                    let harga = this.options[this.selectedIndex].dataset.harga || 0;
                    div.querySelector(".harga").value = harga;
                    calculateTotal();
                });

                div.querySelector(".harga").addEventListener("input", calculateTotal);
                div.querySelector(".qty").addEventListener("input", calculateTotal);
                div.querySelector(".tindakanHarga").addEventListener("input", calculateTotal);

                div.querySelector(".removeRow").addEventListener("click", function() {
                    div.remove();
                    calculateTotal();
                });
            });

            function calculateTotal() {
                let total = 0;
                document.querySelectorAll("#rowContainer > div").forEach(row => {
                    let harga = parseFloat(row.querySelector(".harga").value || 0);
                    let qty = parseInt(row.querySelector(".qty").value || 0);

                    total += harga * qty;
                    console.log(total);

                });
                // ubah ke angka biar gak digabung string
                let total_biaya_tindakan = parseFloat($('#total_biaya_tindakan').val() || 0);

                // hitung total keseluruhan
                let totalKeseluruhan = total + total_biaya_tindakan;

                $('#total_biaya').val(totalKeseluruhan);
                document.getElementById("modal_sparepart").value = total;
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            const sparepartCheckbox = document.getElementById("useSparepartCheckbox");
            const sparepartWrapper = document.getElementById("sparepart_wrapper");
            const modalSparepartWrapper = document.getElementById("modal_sparepart_wrapper");
            const rowContainer = document.getElementById("rowContainer");

            sparepartCheckbox.addEventListener("change", function() {
                if (this.checked) {
                    sparepartWrapper.style.display = "block";
                    modalSparepartWrapper.style.display = "block";
                    // semua input sparepart wajib diisi (required)
                    rowContainer.querySelectorAll("select, input").forEach(el => el.required = true);
                } else {
                    sparepartWrapper.style.display = "none";
                    modalSparepartWrapper.style.display = "none";
                    // reset value dan hilangkan semua row sparepart
                    rowContainer.innerHTML = "";
                    // hilangkan required
                    rowContainer.querySelectorAll("select, input").forEach(el => el.required = false);
                    // reset total biaya sparepart (biar ga ikut ngitung)
                    calculateTotal();

                }
            });

            // fungsi hitung total (sama kayak sebelumnya)
            function calculateTotal() {
                let total = 0;
                document.querySelectorAll("#rowContainer > div").forEach(row => {
                    let harga = parseFloat(row.querySelector(".harga")?.value || 0);
                    let qty = parseInt(row.querySelector(".qty")?.value || 0);
                    total += harga * qty;
                });
                document.getElementById("modal_sparepart").value = total;
            }
        });
    </script>
    <script>
        function updateTotalModal() {
            const tindakan = parseFloat(document.getElementById('total_biaya_tindakan').value) || 0;
            document.getElementById('total_biaya').value = tindakan;
        }
        document.addEventListener("DOMContentLoaded", function() {
            let tindakanList = @json($serviceActions); // pastikan kamu kirim $serviceActions dari controller
            let tindakanContainer = document.getElementById("tindakanContainer");
            let totalBiayaInput = document.getElementById("total_biaya_tindakan");
            let totalBiayaFinal = document.getElementById("total_biaya");
            let addTindakanBtn = document.getElementById("addTindakanRow");
            let tindakanRowId = 0;

            addTindakanBtn.addEventListener("click", function() {
                tindakanRowId++;
                let div = document.createElement("div");
                div.classList.add("grid", "grid-cols-1", "md:grid-cols-3", "gap-2", "items-center", "mb-2");
                div.innerHTML = `
                <select name="tindakan[${tindakanRowId}][id]"
                        class="form-select tindakanSelect w-full" >
                    <option value="">-- Pilih Tindakan --</option>
                    ${tindakanList.map(t => `<option value="${t.id}" data-harga="${t.harga_pelanggan}">${t.nama_tindakan}</option>`).join("")}
                </select>
                <input type="text" name="tindakan[${tindakanRowId}][id_manual]" class="form-input tindakanManual w-full hidden" placeholder="Input manual tindakan">

                 <div class="flex items-center space-x-2">
                    <input type="checkbox" class="form-checkbox toggleManual" id="manual-${tindakanRowId}">
                    <label for="manual-${tindakanRowId}" class="text-sm text-slate-600">Input manual</label>
                </div>

                <input type="number" name="tindakan[${tindakanRowId}][harga]"
                       class="form-input tindakanHarga w-full" placeholder="Harga" value="0" >
                <button type="button" class="btn-sm bg-rose-500 text-white removeTindakan w-full md:w-auto">✕</button>
            `;

                tindakanContainer.appendChild(div);

                // toggle manual input
                div.querySelector(".toggleManual").addEventListener("change", function() {
                    let manual = div.querySelector(".tindakanManual");
                    let select = div.querySelector(".tindakanSelect");

                    if (this.checked) {
                        select.classList.add("hidden");
                        select.removeAttribute("required");
                        manual.classList.remove("hidden");
                        manual.setAttribute("required", true);
                        $(select).val('').trigger('change');
                    } else {
                        manual.classList.add("hidden");
                        manual.removeAttribute("required");
                        select.classList.remove("hidden");
                        select.setAttribute("required", true);
                        manual.value = '';
                    }
                });

                let select = div.querySelector(".tindakanSelect");
                let hargaInput = div.querySelector(".tindakanHarga");

                div.querySelector(".tindakanHarga").addEventListener("input", calculateTindakanTotal);

                // ketika pilih tindakan
                select.addEventListener("change", function() {
                    let harga = parseFloat(select.options[select.selectedIndex].dataset.harga || 0);
                    hargaInput.value = harga;
                    calculateTindakanTotal();
                });

                // hapus row tindakan
                div.querySelector(".removeTindakan").addEventListener("click", function() {
                    div.remove();
                    calculateTindakanTotal();
                });
            });

            function calculateTindakanTotal() {
                let total = 0;
                document.querySelectorAll("#tindakanContainer .tindakanHarga").forEach(input => {
                    total += parseFloat(input.value || 0);
                });
                totalBiayaInput.value = total;
                calculateFinalTotal();
            }

            function calculateFinalTotal() {
                let totalTindakan = parseFloat(totalBiayaInput.value || 0);
                let modalSparepart = parseFloat(document.getElementById("modal_sparepart").value || 0);
                totalBiayaFinal.value = totalTindakan + modalSparepart;
            }

            // jika modal_sparepart berubah, update total akhir
            document.getElementById("modal_sparepart").addEventListener("input", calculateFinalTotal);
        });
    </script>

</div>
