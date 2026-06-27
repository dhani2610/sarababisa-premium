@section('title')
    Edit Transaksi Produk
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
        <div class="sm:flex sm:justify-between sm:items-center mb-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Edit Transaksi Produk ✨</h1>
            </div>
            <x-search-form placeholder="Masukkan nama pelanggan" />
        </div>

        <div x-data="{
            modalOpen: true,
            tab: '1',
            paymentMethod: '{{ $item->payment_method }}',
            pay: '{{ number_format($item->pay, 0, '', '.') }}',
            tunai: '{{ number_format($item->tunai ?? 0, 0, '', '.') }}',
            transfer: '{{ number_format($item->transfer ?? 0, 0, '', '.') }}',

            formatRupiah(val) {
                let str = val.toString().replace(/\D/g, '');
                return str.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            },
            parseRupiah(val) {
                return parseInt(val.toString().replace(/\D/g, '')) || 0;
            },

            // Fungsi Split Tunai ke Transfer
            updateSplitFromTunai() {
                let p = this.parseRupiah(this.pay);
                let t = this.parseRupiah(this.tunai);
                if (t > p) { t = p;
                    this.tunai = this.formatRupiah(t); }
                this.transfer = this.formatRupiah(p - t);
            },

            // Fungsi Split Transfer ke Tunai
            updateSplitFromTransfer() {
                let p = this.parseRupiah(this.pay);
                let tf = this.parseRupiah(this.transfer);
                if (tf > p) { tf = p;
                    this.transfer = this.formatRupiah(tf); }
                this.tunai = this.formatRupiah(p - tf);
            },

            // KHUSUS jika mengubah Total (Pay), split otomatis ke Tunai dulu (atau kosongkan transfer)
            // Sesuai permintaan: Jika pindah ke Tunai & Transfer, tidak usah auto kalkulasi jika tidak perlu
            handlePayChange() {
                this.pay = this.formatRupiah(this.pay);
                if (this.paymentMethod === 'Tunai & Transfer') {
                    // Jika ingin auto-isi Tunai = Total, aktifkan baris bawah:
                    // this.tunai = this.pay; this.transfer = '0';
                    // Jika ingin tetap old value, jangan lakukan apa-apa di sini
                }
            }
        }">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen" x-cloak>
            </div>

            <div class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                x-show="modalOpen" x-cloak>
                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">

                    <div class="px-5 py-3 border-b border-slate-200">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Edit Transaksi Produk</div>
                            <a href="{{ route('transaksi-produk.index') }}" class="text-slate-400 hover:text-slate-500">
                                <svg class="w-4 h-4 fill-current">
                                    <path
                                        d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="px-5 py-4">
                        <div class="flex flex-wrap items-center -m-3 mb-0">
                            <div class="m-3"><label class="flex items-center"><input type="radio" name="tabs"
                                        class="form-radio" checked @click="tab = '1'" /> <span class="text-sm ml-2">Data
                                        Utama</span></label></div>
                            <div class="m-3"><label class="flex items-center"><input type="radio" name="tabs"
                                        class="form-radio" @click="tab = '2'" /> <span class="text-sm ml-2">Data
                                        Detail</span></label></div>
                        </div>

                        <form action="{{ route('transaksi-produk.update', $item->id) }}" method="post">
                            @method('PUT') @csrf

                            <div x-show="tab === '1'" class="mt-3">
                                <div class="space-y-3 mb-4">
                                    <div><label class="block text-sm font-medium mb-1">Tgl. Transaksi</label>
                                        <input name="created_at" class="form-input w-full px-2 py-1" type="date"
                                            value="{{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}" />
                                    </div>
                                    <div><label class="block text-sm font-medium mb-1">Nama Pelanggan</label>
                                        <select name="customers_id" class="form-select text-sm py-1 w-full">
                                            <option selected value="{{ $item->customer->id }}">
                                                {{ $item->customer->nama }}</option>
                                            @foreach ($customers as $c)
                                                <option value="{{ $c->id }}">{{ $c->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Metode Pembayaran</label>
                                        <select name="payment_method" x-model="paymentMethod"
                                            class="form-select text-sm py-1 w-full">
                                            <option value="Tunai">Tunai</option>
                                            <option value="Transfer">Transfer</option>
                                            <option value="Tunai & Transfer">Tunai & Transfer</option>
                                            @foreach (getMetodePembayaran() as $mp)
                                                <option value="{{ $mp->nama }}">{{ $mp->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1">Pembayaran (Total)</label>
                                        <input name="pay" x-model="pay" @input="handlePayChange()"
                                            class="form-input w-full px-2 py-1" type="text" />
                                    </div>

                                    <div x-show="paymentMethod === 'Tunai & Transfer'"
                                        class="flex space-x-4 p-3 bg-slate-50 border rounded" x-cloak>
                                        <div class="w-1/2">
                                            <label class="block text-sm font-medium mb-1">Tunai</label>
                                            <input name="tunai" x-model="tunai"
                                                @input="tunai = formatRupiah(tunai); updateSplitFromTunai();"
                                                class="form-input w-full px-2 py-1" type="text" />
                                        </div>
                                        <div class="w-1/2">
                                            <label class="block text-sm font-medium mb-1">Transfer</label>
                                            <input name="transfer" x-model="transfer"
                                                @input="transfer = formatRupiah(transfer); updateSplitFromTransfer();"
                                                class="form-input w-full px-2 py-1" type="text" />
                                        </div>
                                    </div>

                                    <div><label class="block text-sm font-medium mb-1">Status Pembayaran</label>
                                        <select name="tipe_status_pembayaran" class="form-select text-sm py-1 w-full">
                                            <option value="0"
                                                {{ $item->tipe_status_pembayaran == 0 ? 'selected' : '' }}>Belum Lunas
                                            </option>
                                            <option value="1"
                                                {{ $item->tipe_status_pembayaran == 1 ? 'selected' : '' }}>Lunas
                                            </option>
                                        </select>
                                    </div>
                                    <div><label class="block text-sm font-medium mb-1">Sales</label>
                                        <select name="users_id" class="form-select text-sm py-1 w-full">
                                            @foreach ($users as $u)
                                                <option value="{{ $u->id }}"
                                                    {{ $item->users_id == $u->id ? 'selected' : '' }}>
                                                    {{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div><label class="block text-sm font-medium mb-1">Hutang</label>
                                        <input name="due" class="form-input w-full px-2 py-1" type="text"
                                            value="{{ number_format($item->due, 0, '', '.') }}" />
                                    </div>
                                </div>
                            </div>

                            <div x-show="tab === '2'" class="mt-3">
                                @foreach ($item->detailOrders as $d)
                                    <input type="hidden" name="order_details[{{ $d->id }}][persen_sales]"
                                        value="{{ $d->persen_sales }}">
                                    <input type="hidden" name="order_details[{{ $d->id }}][persen_admin]"
                                        value="{{ $d->persen_admin }}">
                                    <div class="mb-4">
                                        <label
                                            class="block text-sm font-semibold text-indigo-600 mb-1">{{ $d->product_name }}</label>
                                        <input name="order_details[{{ $d->id }}][modal]"
                                            class="form-input w-full px-2 py-1 mb-2" type="text"
                                            value="{{ number_format($d->modal, 0, '', '.') }}" placeholder="Modal" />
                                        <input name="order_details[{{ $d->id }}][total]"
                                            class="form-input w-full px-2 py-1" type="text"
                                            value="{{ number_format($d->total, 0, '', '.') }}"
                                            placeholder="Harga Jual" />
                                    </div>
                                @endforeach
                            </div>

                            <div class="py-4 border-t">
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('transaksi-produk.index') }}"
                                        class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">Batal</a>
                                    <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-toko-layout>
