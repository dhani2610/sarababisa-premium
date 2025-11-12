@section('title')
    Edit Akun
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
                <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path
                            d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">Tambah Akun</span>
                </button>

            </div>

        </div>
        <div x-data="{ modalOpen: true }">
            <!-- Modal backdrop -->
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true" x-cloak>
            </div>
            <!-- Modal dialog -->
            <div id="edit-modal"
                class="fixed inset-0 z-50 overflow-hidden flex items-center my-4 justify-center px-4 sm:px-6"
                role="dialog" aria-modal="true" x-show="modalOpen"
                x-transition:enter="transition ease-in-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in-out duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
                x-cloak>
                <div class="bg-white rounded shadow-lg overflow-auto max-w-lg w-full max-h-full">
                    <!-- Modal header -->
                    <div class="px-5 py-3 border-b border-slate-200">
                        <div class="flex justify-between items-center">
                            <div class="font-semibold text-slate-800">Edit Akun</div>
                            <a href="{{ route('akun') }}" class="text-slate-400 hover:text-slate-500">
                                <div class="sr-only">Close</div>
                                <svg class="w-4 h-4 fill-current">
                                    <path
                                        d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <!-- Modal content -->
                    <form action="{{ route('akun-update', $item->id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="px-5 py-4">
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="name">Nama Lengkap</label>
                                    <input id="name" name="name" class="form-input w-full px-2 py-1"
                                        type="text" value="{{ $item->name }}" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="username">Nama Pengguna</label>
                                    <input id="username" name="username" class="form-input w-full px-2 py-1"
                                        type="text" value="{{ $item->username }}" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="password">Kata Sandi</label>
                                    <input id="password" name="password" class="form-input w-full px-2 py-1"
                                        type="password" placeholder="Kosongkan jika tidak ingin mengganti password" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="nik">NIK</label>
                                    <input id="nik" name="nik" class="form-input w-full px-2 py-1"
                                        type="number" value="{{ $item->nik }}" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="nomor_hp">HP</label>
                                    <input id="nomor_hp" name="nomor_hp" class="form-input w-full px-2 py-1"
                                        type="number" value="{{ $item->nomor_hp }}" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="alamat">Alamat</label>
                                    <textarea id="alamat" name="alamat" class="form-textarea w-full px-2 py-1" rows="4">{{ $item->alamat }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="shift_id">Shift <span
                                            class="text-rose-500">*</span></label>
                                    <select id="shift_id" name="shift_id"
                                        class="form-select text-sm py-1 w-full" required>
                                        @foreach ($shift as $its)
                                            <option value="{{ $its->id }}" {{ $item->shift_id == $its->id ? 'selected' : '' }}>{{ $its->nama_shift }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1" for="role">Role</label>
                                    <select id="role" name="role" class="form-select text-sm py-2 w-full">
                                        <option value="Kepala Toko" {{ $item->role == 'Kepala Toko' ? 'selected' : '' }}>Kepala Toko</option>
                                        <option value="Investor" {{ $item->role == 'Investor' ? 'selected' : '' }}>Investor</option>
                                        <option value="Admin Toko" {{ $item->role == 'Admin Toko' ? 'selected' : '' }}>Admin Toko</option>
                                        <option value="Teknisi" {{ $item->role == 'Teknisi' ? 'selected' : '' }}>Teknisi</option>
                                        <option value="Sales" {{ $item->role == 'Sales' ? 'selected' : '' }}>Sales</option>
                                    </select>
                                </div>


                                {{-- Hanya muncul kalau role = Investor --}}
                                <div id="upload-investor" style="display: none;">
                                    <label class="block text-sm font-medium mb-1" for="pdf_investor">Upload PDF
                                        Investor</label>
                                    <input id="pdf_investor" name="pdf_investor" class="form-input w-full px-2 py-1"
                                        type="file" accept="application/pdf" />
                                    @if ($item->pdf_investor)
                                        <p class="text-sm mt-1">📎
                                            <a href="{{ asset('storage/' . $item->pdf_investor) }}" target="_blank"
                                                class="text-indigo-500 underline">Lihat PDF Lama</a>
                                        </p>
                                    @endif
                                </div>

                                <!-- Semua field setelah role dibungkus -->
                                <div id="extra-fields">
                                    <div id="bonus-interface-input">
                                        <label class="block text-sm font-medium mb-1" for="role">Bonus
                                            Interface?</label>
                                        <select id="bagian_teknisi" name="bagian_teknisi"
                                            class="form-select text-sm py-1 w-full">
                                            <option value="">Pilih</option>
                                            <option value="Teknisi Interface"
                                                {{ $item->bagian_teknisi == 'Teknisi Interface' ? 'selected' : '' }}>Nominal Pertipe
                                            </option>
                                            <option value="Teknisi Hardware"
                                                {{ $item->bagian_teknisi == 'Teknisi Hardware' ? 'selected' : '' }}>
                                                Persentase</option>
                                        </select>
                                    </div>

                                    <div x-data="{ showDetails: false }" id="spesialis-input">
                                        <label class="block text-sm font-medium mb-1" for="types_id">Jika teknisi,
                                            apakah memiliki spesialisasi jenis barang?</label>
                                        <div class="flex flex-wrap items-center -m-3">
                                            <div class="m-3">
                                                <label class="flex items-center">
                                                    <input type="radio" name="radio-buttons" class="form-radio"
                                                        checked x-on:click="showDetails = false" />
                                                    <span class="text-sm ml-2">Tidak</span>
                                                </label>
                                            </div>
                                            <div class="m-3">
                                                <label class="flex items-center">
                                                    <input type="radio" name="radio-buttons" class="form-radio"
                                                        x-on:click="showDetails = true" />
                                                    <span class="text-sm ml-2">Ya</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div x-show="showDetails" class="mt-3" >
                                            <label class="block text-sm font-medium mb-1" for="types_id">Spesialisasi
                                                jenis barang</label>
                                            <select id="types_id" name="types_id"
                                                class="form-select text-sm py-1 w-full">
                                                @if ($item->types_id != null)
                                                    <option selected value="{{ $item->type->id }}">
                                                        {{ $item->type->name }}</option>
                                                @else
                                                    <option selected value="">Pilih spesialisasi</option>
                                                @endif
                                                @foreach ($types as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium mb-1" for="workers_id">Relasi Data
                                            Karyawan</label>
                                        <select id="workers_id" name="workers_id"
                                            class="form-select text-sm py-1 w-full">
                                            @if ($item->worker != null)
                                                <option selected value="{{ $item->worker->id }}">
                                                    {{ $item->worker->name }}</option>
                                            @else
                                                <option selected value=""></option>
                                            @endif
                                            @foreach ($workers as $worker)
                                                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- BONUS ADMIN TOKO -->
                                    <div id="bonus-admin" style="display: none;">
                                        <div>
                                            <label class="block text-sm font-medium mb-1" for="tipe_bonus_admin">
                                                Tipe Bonus <span class="text-rose-500">*</span>
                                            </label>
                                            <select id="tipe_bonus_admin" name="tipe_bonus_admin" onchange="toggleBonusType()" class="form-select text-sm py-1 w-full">
                                                <option value="">Pilih Tipe Bonus</option>
                                                <option value="Persen" {{ $item->tipe_bonus_admin == 'Persen' ? 'selected' : '' }}>Persen</option>
                                                <option value="Tetap" {{ $item->tipe_bonus_admin == 'Tetap' ? 'selected' : '' }}>Tetap</option>
                                            </select>
                                        </div>

                                        <div id="bonus-tetap" class="mt-3" style="display: none;">
                                            <label id="bonus-tetap" class="block text-sm font-medium mb-1" for="nominal_bonus_admin">
                                                Nominal Bonus per Nota <span class="text-rose-500">*</span>
                                            </label>
                                            <input id="nominal_bonus_admin" name="nominal_bonus_admin"
                                                class="form-input w-full px-2 py-1" type="number" placeholder="Contoh: 5000"
                                                value="{{ $item->nominal_bonus_admin }}" />
                                        </div>
                                    </div>

                                    <div id="bonus-persen" style="display: none;">
                                        <label class="block text-sm font-medium mb-1" for="persen">Bonus Hardware Persentase</label>
                                        <input id="persen" name="persen" class="form-input w-full px-2 py-1"
                                            type="number" value="{{ $item->persen }}" placeholder="Contoh: 10" />
                                    </div>
                                </div>

                            </div>
                        </div>
                        <!-- Modal footer -->
                        <div class="px-5 py-4 border-t border-slate-200">
                            <div class="flex flex-wrap justify-end space-x-2">
                                <a href="{{ route('akun') }}"
                                    class="btn-sm border-slate-200 hover:border-slate-300 text-slate-600">
                                    Batal
                                </a>
                                <button class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">Simpan</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
   <script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role');
    const extraFields = document.getElementById('extra-fields');
    const uploadInvestor = document.getElementById('upload-investor');
    const bonusAdmin = document.getElementById('bonus-admin');
    const spesialisInput = document.getElementById('spesialis-input');
    const bonusPersen = document.getElementById('bonus-persen');
    const bonusInterfaceInput = document.getElementById('bonus-interface-input');

    function toggleInputs() {
        const role = roleSelect.value;

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

    toggleInputs();
    roleSelect.addEventListener('change', toggleInputs);
});
</script>

<script>
function toggleBonusType() {
    const tipe = document.getElementById('tipe_bonus_admin').value;
    const roleAkun = document.getElementById('role').value;
    console.log(roleAkun);
    
    if (roleAkun == 'Admin Toko') {
        console.log('====================================');
        console.log(tipe);
        console.log('====================================');
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
}

document.addEventListener('DOMContentLoaded', toggleBonusType);
</script>


</x-toko-layout>
