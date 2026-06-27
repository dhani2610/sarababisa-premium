<div>
    <div class="w-full px-2" dir="ltr">
        <x-validation-errors class="mb-4" :errors="$errors" />
        <div class="flex flex-col gap-2 mt-4">
            <div class="flex flex-col gap-2 mt-4">

                <div class="flex items-center mb-2">
                    <input wire:model="is_manual_customer" id="manual_checkbox" type="checkbox"
                        class="form-checkbox h-4 w-4 text-indigo-600 transition duration-150 ease-in-out">
                    <label for="manual_checkbox" class="ml-2 block text-sm leading-5 text-gray-900 font-bold">
                        Input Pelanggan Manual (Pelanggan Baru)
                    </label>
                </div>

                @if ($is_manual_customer)
                    <div class="p-4 border border-indigo-200 rounded-md bg-indigo-50" wire:key="manual-input-section">
                        <div class="grid grid-cols-1 gap-3">
                            <div>
                                <label class="block text-sm font-medium mb-1" for="manual_nama">Nama Pelanggan <span
                                        class="text-rose-500">*</span></label>
                                <input wire:model.defer="manual_nama" id="manual_nama"
                                    class="form-input w-full px-2 py-1 text-sm border-gray-300 rounded-md"
                                    type="text" placeholder="Nama Lengkap" />
                                @error('manual_nama')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="manual_kategori">Kategori <span
                                        class="text-rose-500">*</span></label>

                                <select wire:model="manual_kategori" id="manual_kategori"
                                    class="form-select w-full px-2 py-1 text-sm border-gray-300 rounded-md"
                                    x-on:change="Livewire.emit('updateCustomerType', $event.target.value)">
                                    <option value="">Pilih Kategori</option>
                                    <option value="User">User</option>
                                    <option value="Toko">Toko</option>
                                </select>

                                <div class="text-xs text-slate-500">Pilih kategori untuk menampilkan harga produk yg
                                    sesuai.</div>
                                @error('manual_kategori')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="manual_nomor_hp">No. HP <span
                                        class="text-rose-500">*</span></label>
                                <input wire:model.defer="manual_nomor_hp" id="manual_nomor_hp"
                                    class="form-input w-full px-2 py-1 text-sm border-gray-300 rounded-md"
                                    type="number" placeholder="08..." />
                                @error('manual_nomor_hp')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium mb-1" for="manual_alamat">Alamat <span
                                        class="text-rose-500">*</span></label>
                                <textarea wire:model.defer="manual_alamat" id="manual_alamat" rows="2"
                                    class="form-textarea w-full px-2 py-1 text-sm border-gray-300 rounded-md" placeholder="Alamat Lengkap"></textarea>
                                @error('manual_alamat')
                                    <span class="text-xs text-rose-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                @else
                    <div class="w-full relative inline-flex" wire:key="select-customer-section">
                        <div class="w-full" wire:ignore> <select id="customer_id" name="customer_id"
                                class="form-select text-sm block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md">
                                <option value="">Pilih Pelanggan</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->nama }}
                                        ({{ $customer->nomor_hp }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @error('customer_id')
                        <span class="text-xs text-rose-500 mt-1">{{ $message }}</span>
                    @enderror
                @endif
            </div>
            <livewire:product-cart :cartInstance="'sale'" />

            @if (allowTransaksiCabang() == 1)
                <button
                    class="w-full inline-flex items-center px-4 py-2 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest disabled:opacity-25 transition ease-in-out duration-150 bg-indigo-500 hover:bg-indigo-600"
                    type="submit" wire:click="proceed">
                    Proses Pembayaran
                </button>
            @endif
        </div>

        <div x-data="{ modalOpen: @entangle('checkoutModal') }">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak>
            </div>

            <div id="checkout-modal"
                class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                role="dialog" aria-modal="true" x-show="modalOpen"
                x-transition:enter="transition ease-in-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in-out duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
                x-cloak>
                <div class="bg-white rounded shadow-lg overflow-auto max-w-xl w-full max-h-full"
                    @click.outside="modalOpen = false" @keydown.escape.window="modalOpen = false">
                    <div class="px-5 py-4">
                        <div class="text-center text-xl mb-5">
                            Pembayaran
                        </div>
                        <form id="checkout-form" wire:submit.prevent="store">
                            <input type="hidden" wire:model="order_date" name="order_date"
                                value="{{ \Carbon\Carbon::today()->locale('id')->translatedFormat('d F Y') }}">
                            <div class="space-y-3">

                                <div class="w-full px-2">
                                    <label class="block text-sm font-medium" for="users_id">Sales <span
                                            class="text-rose-500">*</span></label>
                                    <select wire:model="users_id" name="users_id" id="users_id" required
                                        class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1">
                                        <option value="">Pilih Sales</option>
                                        @foreach ($users as $sales)
                                            <option value="{{ $sales->id }}">{{ $sales->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="w-full px-2">
                                    <label class="block text-sm font-medium" for="payment_method">Metode Pembayaran
                                        <span class="text-rose-500">*</span></label>
                                    <small class="mb-1">Jika jumlah pembayaran kurang dari jumlah total maka pilih
                                        Kredit</small>
                                    <select wire:model="payment_method" id="payment_method" name="payment_method"
                                        required
                                        class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1">
                                        <option value="">Pilih Metode Pembayaran</option>
                                        <option value="Tunai">Tunai</option>
                                        <option value="Transfer">Transfer</option>
                                        <option value="Kredit">Kredit</option>
                                        <option value="Tunai & Transfer">Tunai & Transfer</option>
                                        @foreach (getMetodePembayaran() as $mp)
                                            <option value="{{ $mp->nama }}">{{ $mp->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="w-full px-2">
                                    <label class="block text-sm font-medium mb-1" for="tipe_status_pembayaran">Status
                                        Pembayaran</label>
                                    <select wire:model.defer="tipe_status_pembayaran" id="tipe_status_pembayaran"
                                        name="tipe_status_pembayaran" class="form-select text-sm py-1 w-full">
                                        <option value="0">Belum Lunas</option>
                                        <option value="1">Lunas</option>
                                    </select>
                                </div>


                                <div class="w-full px-2">
                                    <label class="block text-sm font-medium mb-1" for="total_amount">Jumlah Total
                                        <span class="text-rose-500">*</span></label>
                                    <input id="total_amount" type="text"
                                        value="{{ number_format($total_amount, 0, ',', '.') }}"
                                        class="block w-full shadow-sm bg-gray-100 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"
                                        readonly required>
                                </div>

                                <div class="w-full px-2">
                                    <label class="block text-sm font-medium mb-1" for="paid_amount">Jumlah Pembayaran
                                        <span class="text-rose-500">*</span></label>
                                    <input id="paid_amount" type="text"
                                        class="rupiah-input block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"
                                        data-model="paid_amount"
                                        value="{{ number_format($paid_amount, 0, ',', '.') }}" required
                                        placeholder="0">
                                </div>

                                @if ($payment_method === 'Kredit')
                                    <div class="w-full px-2">
                                        <div class="flex flex-wrap items-center -m-3">
                                            <div class="m-3">
                                                <label class="flex items-center">
                                                    <input wire:model="tunai" name="tunai" type="checkbox"
                                                        class="form-checkbox" required />
                                                    <span class="text-sm ml-2">Tunai</span>
                                                </label>
                                            </div>
                                            <div class="m-3">
                                                <label class="flex items-center">
                                                    <input wire:model="transfer" name="transfer" type="checkbox"
                                                        class="form-checkbox" />
                                                    <span class="text-sm ml-2">Transfer</span>
                                                </label>
                                            </div>
                                        </div>
                                        <label class="block text-sm font-medium mt-3" for="tempo">Waktu Tempo <span
                                                class="text-rose-500">*</span></label>
                                        <select wire:model="tempo" id="tempo" name="tempo" required
                                            class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1">
                                            <option value="">Pilih Waktu Tempo</option>
                                            <option value="1">Tempo 1 Hari</option>
                                            <option value="2">Tempo 2 Hari</option>
                                            <option value="3">Tempo 3 Hari</option>
                                            <option value="7">Tempo 1 Minggu</option>
                                            <option value="14">Tempo 2 Minggu</option>
                                            <option value="30">Tempo 1 Bulan</option>
                                        </select>
                                    </div>
                                @endif

                                <div x-data="{
                                    // Hubungkan dengan Livewire
                                    tunai: @entangle('tunai').defer,
                                    transfer: @entangle('transfer').defer,
                                    total: {{ $total_amount }},

                                    // Format angka
                                    format(val) {
                                        let str = val.toString().replace(/\D/g, '');
                                        return str.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                                    },

                                    // Kalkulasi Tunai ke Transfer
                                    calcFromTunai() {
                                        let t = parseInt(this.tunai.toString().replace(/\D/g, '')) || 0;
                                        if (t > this.total) { t = this.total;
                                            this.tunai = this.format(t); }
                                        this.transfer = this.format(this.total - t);
                                    },

                                    // Kalkulasi Transfer ke Tunai
                                    calcFromTransfer() {
                                        let tf = parseInt(this.transfer.toString().replace(/\D/g, '')) || 0;
                                        if (tf > this.total) { tf = this.total;
                                            this.transfer = this.format(tf); }
                                        this.tunai = this.format(this.total - tf);
                                    }
                                }">

                                    @if ($payment_method === 'Tunai & Transfer')
                                        <label class="block text-sm font-medium px-2 text-indigo-500">Silahkan isi
                                            salah satu, sistem akan menghitung sisanya.</label>
                                        <div class="flex flex-row">
                                            <div class="w-1/2 mb-3 md:mb-0 px-2">
                                                <label class="block text-sm font-medium mb-1">Tunai</label>
                                                <input type="text" x-model="tunai"
                                                    @input="tunai = format(tunai); calcFromTunai();"
                                                    class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"
                                                    placeholder="0">
                                            </div>

                                            <div class="w-1/2 mb-3 md:mb-0 px-2">
                                                <label class="block text-sm font-medium mb-1">Transfer</label>
                                                <input type="text" x-model="transfer"
                                                    @input="transfer = format(transfer); calcFromTransfer();"
                                                    class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"
                                                    placeholder="0">
                                            </div>
                                        </div>
                                    @endif

                                </div>

                                <div class="mb-4 w-full px-2">
                                    <label class="block text-sm font-medium mb-1" for="note">Catatan</label>
                                    <textarea name="note" id="note" rows="3" wire:model="note"
                                        class="block w-full shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm border-gray-300 rounded-md mt-1"></textarea>
                                </div>

                                <div class="w-full px-2">
                                    <x-table-responsive>
                                        <x-table.tr>
                                            <x-table.th>Total Produk</x-table.th>
                                            <x-table.td>
                                                <span
                                                    class="badge badge-success">{{ Cart::instance($cart_instance)->count() }}
                                                    item</span>
                                            </x-table.td>
                                        </x-table.tr>
                                        <x-table.tr>
                                            <x-table.th>Total</x-table.th>
                                            <x-table.td>(=) Rp. {{ number_format($total_amount) }}</x-table.td>
                                        </x-table.tr>
                                    </x-table-responsive>

                                    <div class="float-right py-4">
                                        <button type="button" @click="modalOpen = false"
                                            class="btn border-slate-200 hover:border-slate-300 text-slate-600 mr-2">Batal</button>
                                        <button type="submit" wire:loading.attr="disabled"
                                            class="btn bg-indigo-500 hover:bg-indigo-600 text-white">Selesaikan
                                            Transaksi</button>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // --- Fungsi Rupiah Helper ---
            function formatRupiah(angka) {
                return new Intl.NumberFormat('id-ID').format(angka);
            }

            function cleanRupiah(value) {
                return parseInt(value.toString().replace(/\D/g, '')) || 0;
            }

            function initRupiahInputs() {
                const inputs = document.querySelectorAll('.rupiah-input');
                const totalElement = document.getElementById('total_amount');

                inputs.forEach(input => {
                    if (input.dataset.hasRupiahListener) return;
                    input.dataset.hasRupiahListener = true;

                    // GANTI: Gunakan 'input' agar berjalan real-time (on keyup)
                    input.addEventListener('input', function(e) {
                        let rawValue = cleanRupiah(this.value);
                        this.value = rawValue ? formatRupiah(rawValue) : '';

                        let modelName = this.getAttribute('data-model');

                        // Update Livewire state
                        @this.set(modelName, rawValue);

                        // --- Kalkulasi Split Payment Real-time ---
                        if ((modelName === 'tunai' || modelName === 'transfer') && totalElement) {
                            let totalBayar = cleanRupiah(totalElement.value);

                            // Validasi: Jangan biarkan input melebihi total
                            if (rawValue > totalBayar) {
                                rawValue = totalBayar;
                                this.value = formatRupiah(rawValue);
                                @this.set(modelName, rawValue);
                            }

                            let sisa = totalBayar - rawValue;
                            if (sisa < 0) sisa = 0;

                            let targetModel = (modelName === 'tunai') ? 'transfer' : 'tunai';
                            let targetInput = document.querySelector(
                                `.rupiah-input[data-model="${targetModel}"]`);

                            if (targetInput) {
                                // Update tampilan input target
                                targetInput.value = formatRupiah(sisa);
                                // Update Livewire state target
                                @this.set(targetModel, sisa);
                            }
                        }
                    });
                });
            }

            // --- Fungsi Select2 Helper (Sama seperti sebelumnya) ---
            function initSelect2() {
                var selectEl = $('#customer_id');
                if (selectEl.length) {
                    if (selectEl.hasClass("select2-hidden-accessible")) {
                        selectEl.select2('destroy');
                    }
                    selectEl.select2({
                        placeholder: "Pilih Pelanggan",
                        allowClear: true,
                        width: '100%'
                    });
                    selectEl.on('change', function(e) {
                        var data = $(this).val();
                        @this.set('customer_id', data);
                    });
                }
            }

            initRupiahInputs();
            initSelect2();

            Livewire.hook('message.processed', (message, component) => {
                initRupiahInputs();
                initSelect2();
            });
        });
    </script>
