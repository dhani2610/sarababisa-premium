@section('title')
    Teknisi Servis & Biaya Servis
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Transaksi Servis ✨</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <x-search-form placeholder="Cari berdasarkan nomor servis" />
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true" aria-controls="tambah-modal">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Transaksi Baru</span>
                </button>
            </div>
        </div>

        <div x-data="{ modalOpen: true }">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak></div>

            <div id="tambah-modal" class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="modalOpen" x-transition:enter="transition ease-in-out duration-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in-out duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4" x-cloak>
                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">

                    <div class="px-5 py-3 border-b border-slate-200">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-sm text-slate-800">Teknisi Servis & Biaya Servis #{{ $item->nomor_servis }}</div>
                            <a href="{{ route('transaksi-servis.index') }}">
                                <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                    <div class="sr-only">Close</div>
                                    <svg class="w-4 h-4 fill-current">
                                        <path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                    </svg>
                                </button>
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('multi-teknisi-proses', $item->id) }}" method="post">
                        @csrf
                        <input type="hidden" name="status_servis" value="Bisa Diambil"/>
                        <input type="hidden" name="tgl_selesai" value="<?php echo date('Y/m/d') ?>"/>

                        <div class="px-5 py-4">
                            <div class="space-y-3">

                                <div id="main-container">
                                    {{-- Group Teknisi akan ditambahkan di sini via JS --}}
                                </div>

                                <div class="mb-4">
                                    <button type="button" class="btn-sm bg-indigo-600 hover:bg-indigo-700 text-white w-full flex justify-center items-center" id="tambah-teknisi-baru">
                                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                                            <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                                        </svg>
                                        Tambah Teknisi Baru
                                    </button>
                                </div>

                                <div class="bg-slate-100 p-3 rounded">
                                    <div class="mb-2">
                                        <label class="block text-sm font-medium mb-1" for="total_modal_sparepart">Total Modal Sparepart <span class="text-rose-500">*</span></label>
                                        <input class="form-input w-full px-2 py-1 bg-white" type="number" name="total_modal_sparepart" id="total_modal_sparepart" readonly required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="biaya">Total Biaya Servis (Ke Pelanggan) <span class="text-rose-500">*</span></label>
                                        <input class="form-input w-full px-2 py-1 bg-white font-bold text-lg" type="number" name="biaya" id="biaya" readonly required />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium mb-1" for="catatan">Catatan <small>(Kosongkan jika tidak perlu)</small></label>
                                    <textarea id="catatan" name="catatan" class="form-textarea w-full px-2 py-1" rows="2" placeholder="Tulis catatan untuk pelanggan..."></textarea>
                                </div>

                            </div>
                        </div>

                        <div class="px-5 py-4 border-t border-slate-200">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <a href="{{ route('transaksi-servis.index') }}" class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">Batal</a>
                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <style>
            .select2-container .select2-selection--single {
                height: 38px;
                border-color: #e2e8f0;
                display: flex;
                align-items: center;
            }
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
            }
            .technician-group {
                border: 2px solid #e2e8f0;
                border-radius: 0.5rem;
                padding: 1rem;
                margin-bottom: 1.5rem;
                background-color: #f8fafc;
            }
            .action-item {
                background-color: #ffffff;
                border: 1px solid #cbd5e1;
                border-radius: 0.375rem;
                padding: 0.75rem;
                margin-bottom: 0.75rem;
                position: relative;
            }
            .position-button-x{
                position: absolute;
                right: -3%;
                top: -16px;
                font-size: 23px!important;
                background: red!important;
                color: white!important;
                padding: 0px 8px 0px 8px!important;
                border-radius: 50%!important;
            }
        </style>
    @endpush
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {

            // --- 1. TERIMA DATA DARI CONTROLLER ---
            const existingData = @json($teknisiServis ?? []);
            let groupCounter = 0;

            // --- TEMPLATES ---
            const teknisiOptions = `
                <option selected value="">Pilih Teknisi</option>
                @foreach ($users as $user) <option value="{{ $user->id }}">{{ $user->name }}</option> @endforeach
            `;
            const actionOptions = `
                <option selected value="">Pilih Tindakan</option>
                @foreach ($service_actions as $action) <option value="{{ $action->id }}">{{ $action->nama_tindakan }}</option> @endforeach
            `;
            const sparepartOptions = `
                <option selected value="">Pilih Sparepart</option>
                @foreach (App\Models\Product::where('cabang_id',getCabangId())->get() as $item)
                    <option value="{{ $item->id }}" data-harga_modal="{{ $item->harga_modal }}">{{ addslashes($item->product_name) }}</option>
                @endforeach
            `;
            const salesOptions = `
                <option selected value="1">Tidak ada Sales</option>
                @foreach ($sales as $user) <option value="{{ $user->id }}">{{ $user->name }}</option> @endforeach
            `;

            // --- HTML GENERATOR ---
            function generateActionHtml(groupIndex, actionIndex) {
                const uniqueRadioId = 'radio_' + actionIndex;
                return `
                <div class="action-item" x-data="{ useSparepart: false, showInputManual: false }">
                    <button type="button" class="position-button-x remove-action absolute top-2 right-2 text-rose-500 hover:text-rose-700 font-bold" title="Hapus">&times;</button>
                    <div class="mb-2 pr-6">
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-medium">Tindakan Servis <span class="text-rose-500">*</span></label>
                            <label class="flex items-center"><input type="checkbox" class="form-checkbox checkbox-manual" x-on:click="showInputManual = !showInputManual"/><span class="text-sm ml-2">Isi Manual</span></label>
                        </div>
                        <div x-show="!showInputManual" class="wrapper-select-action">
                            <select name="teknisi[${groupIndex}][tindakan][${actionIndex}][service_actions_id]" class="form-select text-sm py-1 w-full selectAction">${actionOptions}</select>
                        </div>
                        <div x-show="showInputManual" class="mt-2 wrapper-input-manual" style="display:none;">
                            <input class="form-input w-full px-2 py-1 input-manual-text" type="text" name="teknisi[${groupIndex}][tindakan][${actionIndex}][tindakan_servis]" placeholder="Ketik manual..."/>
                        </div>
                    </div>
                    <div class="konfirmasi-stok border-t border-slate-200 pt-2 mt-2">
                        <label class="block text-sm font-medium mb-1">Pakai Sparepart Toko?</label>
                        <div class="flex flex-wrap items-center -m-3 mb-2">
                            <div class="m-3"><label class="flex items-center"><input type="radio" name="${uniqueRadioId}" value="tidak" class="form-radio radio-sparepart-no" checked x-on:click="useSparepart = false"/><span class="text-sm ml-2">Tidak</span></label></div>
                            <div class="m-3"><label class="flex items-center"><input type="radio" name="${uniqueRadioId}" value="ya" class="form-radio radio-sparepart-yes" x-on:click="useSparepart = true"/><span class="text-sm ml-2">Ya</span></label></div>
                        </div>
                        <div x-show="useSparepart" style="display: none;" class="wrapper-sparepart-area">
                            <div class="mb-2"><label class="block text-sm font-medium mb-1">Sparepart</label><select name="teknisi[${groupIndex}][tindakan][${actionIndex}][products_id]" class="form-select text-sm py-1 w-full selectSparepart" style="width: 100%;">${sparepartOptions}</select></div>
                            <input type="hidden" value="null" name="teknisi[${groupIndex}][tindakan][${actionIndex}][sales_id]">
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-2">
                            <div><label class="block text-sm font-medium mb-1">Modal Part <span class="text-rose-500">*</span></label><input class="form-input w-full px-2 py-1 modal_sparepart" type="number" name="teknisi[${groupIndex}][tindakan][${actionIndex}][modal_sparepart]" value="0" required /></div>
                            <div><label class="block text-sm font-medium mb-1">Biaya Servis <span class="text-rose-500">*</span></label><input class="form-input w-full px-2 py-1 biaya_servis" type="number" name="teknisi[${groupIndex}][tindakan][${actionIndex}][biaya_servis]" value="0" required /></div>
                        </div>
                    </div>
                </div>`;
            }

            function addTechnicianGroup(techData = null) {
                const currentGroupIndex = groupCounter++;
                const groupHtml = `
                <div class="technician-group relative" data-group-index="${currentGroupIndex}">
                    <button style="position: absolute;right: 2%;" type="button" class="remove-group absolute top-2 right-2 text-white bg-rose-500 hover:bg-rose-600 rounded px-2 py-1 text-xs z-10">Hapus Teknisi</button>
                    <div class="bg-indigo-50 -m-4 mb-4 p-4 border-b border-indigo-100 rounded-t">
                        <h3 class="font-bold text-indigo-800 mb-2">Data Teknisi</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div><label class="block text-sm font-medium mb-1">Nama Teknisi <span class="text-rose-500">*</span></label><select name="teknisi[${currentGroupIndex}][user_id]" class="form-select text-sm py-1 w-full selectUser" required>${teknisiOptions}</select></div>
                            <div><label class="block text-sm font-medium mb-1">Tipe Bagi Hasil <span class="text-rose-500">*</span></label><select name="teknisi[${currentGroupIndex}][tipe]" class="form-select text-sm py-1 w-full selectType" required><option selected value="">Pilih Tipe</option><option value="Interface">Interface (bonus pertipe)</option><option value="Hardware">Hardware & interface (bonus persen)</option></select></div>
                        </div>
                    </div>
                    <div class="actions-list-container space-y-3"></div>
                    <div class="mt-3 text-center border-t border-dashed border-slate-300 pt-3"><button type="button" class="add-action-btn btn-sm bg-emerald-500 hover:bg-emerald-600 text-white">+ Tambah Tindakan Lain (Untuk Teknisi Ini)</button></div>
                </div>`;
                const $newGroup = $(groupHtml);
                $('#main-container').append($newGroup);
                if (techData) {
                    $newGroup.find('.selectUser').val(techData.users_id);
                    $newGroup.find('.selectType').val(techData.tipe);
                } else {
                    addActionToGroup($newGroup, currentGroupIndex);
                }
                return { $element: $newGroup, index: currentGroupIndex };
            }

            function addActionToGroup($groupElement, groupIndex, actionData = null) {
                if (groupIndex === undefined) groupIndex = $groupElement.attr('data-group-index');
                const actionIndex = Date.now() + Math.floor(Math.random() * 10000);
                const html = generateActionHtml(groupIndex, actionIndex);
                const $newItem = $(html);
                $groupElement.find('.actions-list-container').append($newItem);

                const $selAction = $newItem.find('.selectAction').select2();
                const $selSparepart = $newItem.find('.selectSparepart').select2();
                const $selSales = $newItem.find('.selectSales').select2();

                if (actionData) {
                    // Set Nilai Duit Awal (PENTING: Pastikan ini jalan)
                    // Gunakan .val() dan pastikan angka (0 jika null/kosong)
                    $newItem.find('.biaya_servis').val(actionData.biaya || 0);

                    // Untuk modal, jika 0, nanti akan ditimpa oleh harga modal sparepart jika ada
                    $newItem.find('.modal_sparepart').val(actionData.modal || 0);

                    if (actionData.act_id && actionData.act_id !== "null") $selAction.val(actionData.act_id).trigger('change.select2');

                    // Logic Sparepart
                    if (actionData.prod_id && actionData.prod_id != "null" && actionData.prod_id != "") {
                        setTimeout(() => { $newItem.find('.radio-sparepart-yes')[0].click(); }, 50);

                        setTimeout(() => {
                            // Set value sparepart
                            $selSparepart.val(actionData.prod_id).trigger('change.select2');

                            // PENTING: Ambil harga modal dari data-attribute option yang terpilih
                            // Ini logic fallback jika actionData.modal ternyata 0 atau tidak tersimpan benar
                            const hargaModalOtomatis = $selSparepart.find(':selected').data('harga_modal') || 0;

                            // Jika data modal dari DB ada (>0), pakai itu. Jika 0, ambil dari master barang.
                            if(actionData.modal > 0) {
                                $newItem.find('.modal_sparepart').val(actionData.modal);
                            } else {
                                $newItem.find('.modal_sparepart').val(hargaModalOtomatis);
                            }

                            // Trigger hitung ulang total
                            recalculateAll();
                        }, 100);
                    } else {
                        // Jika tidak ada sparepart, hitung ulang untuk biaya servis saja
                        recalculateAll();
                    }
                }
            }

            // LOAD DATA
            if (existingData && existingData.length > 0) {
                existingData.forEach(function(tech) {
                    const groupObj = addTechnicianGroup(tech);
                    let actionsArr = [], productsArr = [], biayaArr = [], modalArr = [];
                    try {
                        actionsArr = JSON.parse(tech.service_actions) || [];
                        productsArr = JSON.parse(tech.products) || [];
                        biayaArr = JSON.parse(tech.biaya_j) || [];
                        modalArr = JSON.parse(tech.modal_j) || [];
                    } catch (e) { console.error(e); }

                    if (actionsArr.length > 0) {
                        actionsArr.forEach(function(actId, i) {
                            const detailData = {
                                act_id: actId,
                                prod_id: productsArr[i] ?? null,
                                biaya: biayaArr[i] ?? 0,
                                modal: modalArr[i] ?? 0
                            };
                            addActionToGroup(groupObj.$element, groupObj.index, detailData);
                        });
                    } else {
                        addActionToGroup(groupObj.$element, groupObj.index);
                    }
                });
                // Delay sedikit untuk rekap total akhir setelah semua timeout selesai
                setTimeout(recalculateAll, 1000);
            } else {
                addTechnicianGroup();
            }

            // EVENT HANDLERS
            $('#tambah-teknisi-baru').on('click', function() { addTechnicianGroup(); });
            $(document).on('click', '.add-action-btn', function() { addActionToGroup($(this).closest('.technician-group')); });
            $(document).on('click', '.remove-action', function() {
                if ($(this).closest('.actions-list-container').children().length > 1) {
                    if(confirm('Hapus?')) { $(this).closest('.action-item').remove(); recalculateAll(); }
                } else alert('Minimal 1 tindakan.');
            });
            $(document).on('click', '.remove-group', function() {
                if(confirm('Hapus Teknisi?')) { $(this).closest('.technician-group').remove(); recalculateAll(); }
            });

            $(document).on('select2:select', '.selectAction', function(e) {
                const $select = $(this);
                const $container = $select.closest('.action-item');
                const actionId = $select.val();
                if(actionId) {
                    $.ajax({
                        url: '/get-action/' + actionId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $container.find('.biaya_servis').val(data.biaya);
                            recalculateAll();
                        }
                    });
                }
            });

            $(document).on('change', '.selectSparepart', function() {
                const $select = $(this);
                const $container = $select.closest('.action-item');
                const hargaModal = $select.find(':selected').data('harga_modal') || 0;
                $container.find('.modal_sparepart').val(hargaModal);
                recalculateAll();
            });

            $(document).on('input', '.biaya_servis, .modal_sparepart', function() { recalculateAll(); });

            function recalculateAll() {
                let totalBiaya = 0;
                let totalModal = 0;
                $('.biaya_servis').each(function() { totalBiaya += parseFloat($(this).val()) || 0; });
                $('.modal_sparepart').each(function() { totalModal += parseFloat($(this).val()) || 0; });
                $('#biaya').val(totalBiaya);
                $('#total_modal_sparepart').val(totalModal);
            }
        });
    </script>
@endpush
</x-toko-layout>
