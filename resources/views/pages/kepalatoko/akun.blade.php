@section('title')
    Akun
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <!-- Page header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Akun ✨</h1>
            </div>

            <!-- Right: Actions -->
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                <!-- Search form -->
                <x-search-form placeholder="Cari berdasarkan nama" />

                <!-- Create invoice button -->
                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true"
                        aria-controls="tambah-modal">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path
                                d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="hidden xs:block ml-2">Tambah Akun</span>
                    </button>
                    <!-- Modal backdrop -->
                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true"
                        x-cloak></div>
                    <!-- Modal dialog -->
                    <div id="tambah-modal"
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
                            <!-- Modal header -->
                            <div class="px-5 py-3 border-b border-slate-200">
                                <div class="flex justify-between items-center">
                                    <div class="font-semibold text-slate-800">Tambah Akun</div>
                                    <button class="text-slate-400 hover:text-slate-500" @click="modalOpen = false">
                                        <div class="sr-only">Close</div>
                                        <svg class="w-4 h-4 fill-current">
                                            <path
                                                d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <!-- Modal content -->
                            <form action="{{ route('akun-store') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                <div class="px-5 py-4">
                                    <div class="space-y-3">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="name">Nama Lengkap
                                                <span class="text-rose-500">*</span></label>
                                            <input id="name" name="name" class="form-input w-full px-2 py-1"
                                                type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="username">Nama Pengguna
                                                <span class="text-rose-500">*</span></label>
                                            <input id="username" name="username" class="form-input w-full px-2 py-1"
                                                type="text" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="password">Kata Sandi
                                                <span class="text-rose-500">*</span></label>
                                            <input id="password" name="password" class="form-input w-full px-2 py-1"
                                                type="password" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="nik">NIK <span
                                                    class="text-rose-500">*</span></label>
                                            <input id="nik" name="nik" class="form-input w-full px-2 py-1"
                                                type="number" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="nomor_hp">HP <span
                                                    class="text-rose-500">*</span></label>
                                            <input id="nomor_hp" name="nomor_hp" class="form-input w-full px-2 py-1"
                                                type="number" required />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="alamat">Alamat <span
                                                    class="text-rose-500">*</span></label>
                                            <textarea id="alamat" name="alamat" class="form-textarea w-full px-2 py-1" rows="2" required></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="role">Role <span
                                                    class="text-rose-500">*</span></label>
                                            <select id="role" name="role"
                                                class="form-select text-sm py-1 w-full" required
                                                onchange="toggleInputs()">
                                                <option value="Kepala Toko">Kepala Toko</option>
                                                <option value="Investor">Investor</option>
                                                <option value="Admin Toko">Admin Toko</option>
                                                <option value="Teknisi">Teknisi</option>
                                                <option value="Sales">Sales</option>
                                            </select>
                                        </div>



                                        <div id="upload-investor" style="display: none;">
                                            <label class="block text-sm font-medium mb-1" for="pdf_investor">
                                                Upload PDF Investor <span class="text-rose-500">*</span>
                                            </label>
                                            <input id="pdf_investor" name="pdf_investor" class="form-input w-full px-2 py-1"
                                                type="file" accept="application/pdf" />
                                        </div>


                                        <!-- Inputan bawah (dibungkus agar bisa di-hide semua sekaligus) -->
                                        <div id="extra-fields">
                                            <div id="bonus-interface-input">
                                                <label class="block text-sm font-medium mb-1"
                                                    for="bagian_teknisi">Bonus Interface?</label>
                                                <select id="bagian_teknisi" name="bagian_teknisi"
                                                    class="form-select text-sm py-1 w-full">
                                                    <option value="">Pilih</option>
                                                    <option value="Teknisi Interface">Ya</option>
                                                    <option value="Teknisi Hardware">Tidak</option>
                                                </select>
                                            </div>
                                            <br>

                                            <div x-data="{ showDetails: false }" id="spesialis-input">
                                                <label class="block text-sm font-medium mb-1" for="types_id">Jika
                                                    teknisi, apakah memiliki spesialisisasi jenis barang?</label>
                                                <div class="flex flex-wrap items-center -m-3">
                                                    <div class="m-3">
                                                        <label class="flex items-center">
                                                            <input type="radio" name="radio-buttons"
                                                                class="form-radio" checked
                                                                x-on:click="showDetails = false" />
                                                            <span class="text-sm ml-2">Tidak</span>
                                                        </label>
                                                    </div>
                                                    <div class="m-3">
                                                        <label class="flex items-center">
                                                            <input type="radio" name="radio-buttons"
                                                                class="form-radio" x-on:click="showDetails = true" />
                                                            <span class="text-sm ml-2">Ya</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div x-show="showDetails" class="mt-3">
                                                    <label class="block text-sm font-medium mb-1"
                                                        for="types_id">Spesialisasi jenis barang</label>
                                                    <select id="types_id" name="types_id"
                                                        class="form-select text-sm py-1 w-full">
                                                        <option selected value="">Pilih spesialisasi</option>
                                                        @foreach ($types as $type)
                                                            <option value="{{ $type->id }}">{{ $type->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <br>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium" for="workers_id">Relasi Data
                                                    Karyawan</label>
                                                <div class="text-xs text-slate-600 mb-1">
                                                    Tidak perlu dipilih jika tidak ada relasi data karyawan
                                                </div>
                                                <select id="workers_id" name="workers_id"
                                                    class="form-select text-sm py-1 w-full">
                                                    <option selected value=""></option>
                                                    @foreach ($workers as $worker)
                                                        <option value="{{ $worker->id }}">{{ $worker->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div id="bonus-admin" style="display: none;">
                                                <div>
                                                    <label class="block text-sm font-medium mb-1" for="tipe_bonus_admin">
                                                        Tipe Bonus <span class="text-rose-500">*</span>
                                                    </label>
                                                    <select id="tipe_bonus_admin" name="tipe_bonus_admin"
                                                        class="form-select text-sm py-1 w-full"
                                                        onchange="toggleBonusType()">
                                                        <option value="">Pilih Tipe Bonus</option>
                                                        <option value="Persen">Persen</option>
                                                        <option value="Tetap">Tetap</option>
                                                    </select>
                                                </div>

                                                <!-- Input nominal (untuk bonus tetap) -->
                                                <div id="bonus-tetap" class="mt-3" style="display: none;">
                                                    <label class="block text-sm font-medium mb-1" for="nominal_bonus_admin">
                                                        Nominal Bonus per Nota <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="nominal_bonus_admin" name="nominal_bonus_admin"
                                                        class="form-input w-full px-2 py-1" type="number"
                                                        placeholder="Contoh: 5000" />
                                                </div>
                                            </div>

                                            <!-- Input persen -->
                                            <div id="bonus-persen" style="display: none;">
                                                <label class="block text-sm font-medium mb-1" for="persen">
                                                    Persen <span class="text-rose-500">*</span>
                                                </label>
                                                <input id="persen" name="persen"
                                                    class="form-input w-full px-2 py-1" type="number"
                                                    placeholder="Contoh: 10" />
                                            </div>

                                        </div>

                                    </div>
                                </div>
                                <!-- Modal footer -->
                                <div class="px-5 py-4 border-t border-slate-200">
                                    <div class="flex flex-wrap justify-end space-x-2">
                                        <button
                                            class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <div class="bg-emerald-100 rounded border border-emerald-200 text-emerald-600 p-4">
            <div class="text-left md:flex md:items-center md:space-x-2">
                <div class="text-sm">
                    Silahkan atur persen pada tiap-tiap akun untuk implementasi pembagian hasil. Persen Kepala Toko
                    tidak perlu diisi.
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

        <!-- Table -->
        <x-kepalatoko.akun-table :users="$users" :count="$users_count" />

        <!-- Pagination -->
        <div class="mt-8">
            {{ $users->links() }}
        </div>

    </div>


<script>
function toggleInputs() {
    const role = document.getElementById('role').value;
    const extraFields = document.getElementById('extra-fields');
    const uploadInvestor = document.getElementById('upload-investor');
    const bonusAdmin = document.getElementById('bonus-admin');
    const spesialisInput = document.getElementById('spesialis-input');
    const bonusPersen = document.getElementById('bonus-persen');
    const bonusInterfaceInput = document.getElementById('bonus-interface-input');

    if (role === 'Investor') {
        extraFields.style.display = 'none';
        uploadInvestor.style.display = 'block';
        bonusAdmin.style.display = 'none';
        spesialisInput.style.display = 'none';
        bonusInterfaceInput.style.display = 'none';
    } else if (role === 'Kepala Toko') {
        extraFields.style.display = 'none';
        uploadInvestor.style.display = 'none';
        bonusAdmin.style.display = 'none';
        spesialisInput.style.display = 'none';
        bonusInterfaceInput.style.display = 'none';
    } else if (role === 'Admin Toko') {
        extraFields.style.display = 'block';
        uploadInvestor.style.display = 'none';
        bonusAdmin.style.display = 'block';
        spesialisInput.style.display = 'none';
        bonusInterfaceInput.style.display = 'none';
    } else if (role === 'Teknisi') {
        extraFields.style.display = 'block';
        uploadInvestor.style.display = 'none';
        bonusAdmin.style.display = 'none';
        spesialisInput.style.display = 'block';
        bonusPersen.style.display = 'block';
        bonusInterfaceInput.style.display = 'block';
    } else if (role === 'Sales') {
        extraFields.style.display = 'block';
        uploadInvestor.style.display = 'none';
        bonusAdmin.style.display = 'none';
        spesialisInput.style.display = 'none';
        bonusPersen.style.display = 'block';
        bonusInterfaceInput.style.display = 'none';
    }
}

// Jalankan saat pertama kali halaman dimuat
document.addEventListener('DOMContentLoaded', toggleInputs);
document.getElementById('role').addEventListener('change', toggleInputs);
</script>

<script>
function toggleBonusType() {
    const tipe = document.getElementById('tipe_bonus_admin').value;
    const bonusTetap = document.getElementById('bonus-tetap');
    const bonusPersen = document.getElementById('bonus-persen');

    bonusTetap.style.display = 'none';
    bonusPersen.style.display = 'none';

    if (tipe === 'Tetap') {
        bonusTetap.style.display = 'block';
    } else if (tipe === 'Persen') {
        bonusPersen.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', toggleBonusType);
</script>


</x-toko-layout>
