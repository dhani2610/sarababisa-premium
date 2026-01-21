@section('title')
    Akun
@endsection

<x-toko-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

        <div class="sm:flex sm:justify-between sm:items-center mb-5">

            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-slate-800 font-bold">Akun ✨</h1>
            </div>

            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">

                {{-- <x-search-form placeholder="Cari berdasarkan nama" /> --}}
                {{-- Search sudah dihandle DataTables --}}

                <div x-data="{ modalOpen: false }">
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white" @click.prevent="modalOpen = true"
                        aria-controls="tambah-modal">
                        <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                            <path
                                d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                        </svg>
                        <span class="hidden xs:block ml-2">Tambah Akun</span>
                    </button>
                    <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="modalOpen"
                        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-out duration-100"
                        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" aria-hidden="true"
                        x-cloak></div>
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
                                            <label class="block text-sm font-medium mb-1" for="username">Email
                                                <span class="text-rose-500">*</span></label>
                                            <input id="email" name="email" class="form-input w-full px-2 py-1"
                                                type="email" required />
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

                                        <div id="persen_investor_input" style="display: none">
                                            <label class="block text-sm font-medium mb-1" for="persen_investor">Persentase Pembagian Servis<span
                                                    class="text-rose-500">*</span></label>
                                            <input id="persen_investor" name="persen_investor" class="form-input w-full px-2 py-1"
                                                type="number" />
                                        </div>
                                        <div id="persen_investor_input_produk" style="display: none">
                                            <label class="block text-sm font-medium mb-1" for="persen_investor_produk">Persentase Pembagian Produk<span
                                                    class="text-rose-500">*</span></label>
                                            <input id="persen_investor_produk" name="persen_investor_produk" class="form-input w-full px-2 py-1"
                                                type="number" />
                                        </div>

                                        <div id="shift_input" style="display: none">
                                            <label class="block text-sm font-medium mb-1" for="shift_id">Shift</label>
                                            <select id="shift_id" name="shift_id"
                                                class="form-select text-sm py-1 w-full">
                                                @foreach ($shift as $item)
                                                    <option value="{{ $item->id }}">{{ $item->nama_shift }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div id="upload-investor" style="display: none;">
                                            <label class="block text-sm font-medium mb-1" for="pdf_investor">
                                                Upload PDF Investor <span class="text-rose-500">*</span>
                                            </label>
                                            <input id="pdf_investor" name="pdf_investor" class="form-input w-full px-2 py-1"
                                                type="file" accept="application/pdf" />
                                        </div>

                                        <div id="extra-fields">
                                            <div id="bonus-interface-input">
                                                <label class="block text-sm font-medium mb-1"
                                                    for="bagian_teknisi">Bonus Interface?</label>
                                                <select id="bagian_teknisi" name="bagian_teknisi"
                                                    class="form-select text-sm py-1 w-full" onchange="toggleInputs()">
                                                    <option value="">Pilih</option>
                                                    <option value="Teknisi Interface">Nominal Leveling Pertipe </option>
                                                    <option value="Teknisi Hardware">Persentase</option>
                                                </select>
                                            </div>
                                            <br>
                                            <div id="bonus-leveling-container" class="mt-3 border border-slate-200 rounded p-3 bg-slate-50" style="display: none;">
                                                <small for="">
                                                    <a href="{{  asset('storage/assets/Format Leveling.xlsx')  }}" download="" style="color:blue">Download Format</a>
                                                </small>
                                                <div class="flex justify-between items-center mb-2">
                                                    <label class="block text-sm font-bold text-slate-800">Setting Bonus Leveling Interface</label>

                                                    <div class="flex space-x-2">
                                                        <input type="file" id="excel_file" accept=".xlsx, .xls" style="display: none;" onchange="processExcel(this)">
                                                        
                                                        <button type="button" class="btn-xs bg-indigo-500 hover:bg-indigo-600 text-white" onclick="document.getElementById('excel_file').click()">
                                                            Import
                                                        </button>


                                                        <button type="button" class="btn-xs bg-emerald-500 hover:bg-emerald-600 text-white" onclick="addLevelingRow()">+ Tambah</button>
                                                    </div>
                                                </div>
                                                
                                                <div id="leveling-rows-wrapper" class="space-y-2">
                                                    <div class="leveling-row grid grid-cols-1 md:grid-cols-12 gap-2 items-end border-b pb-2">
                                                        <div class="md:col-span-3">
                                                            <label class="block text-xs font-medium mb-1">Jenis Barang</label>
                                                            <select name="lvl_id_jenis_barang[]" class="form-select text-xs w-full py-1">
                                                                @foreach($categories as $cat)
                                                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label class="block text-xs font-medium mb-1">Tipe OS</label>
                                                            <select name="lvl_id_tipe_os[]" class="form-select text-xs w-full py-1">
                                                                @foreach($tipe_os as $os)
                                                                    <option value="{{ $os->id }}">{{ $os->nama }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label class="block text-xs font-medium mb-1">Dari Rate</label>
                                                            <input type="text" name="lvl_start_rate[]" class="form-input text-xs w-full py-1 input-currency" placeholder="Rp 0">
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label class="block text-xs font-medium mb-1">Sampai Rate</label>
                                                            <input type="text" name="lvl_end_rate[]" class="form-input text-xs w-full py-1 input-currency" placeholder="Rp 0">
                                                        </div>
                                                        <div class="md:col-span-2">
                                                            <label class="block text-xs font-medium mb-1">Bonus</label>
                                                            <input type="text" name="lvl_nominal_bonus[]" class="form-input text-xs w-full py-1 input-currency" placeholder="Rp Bonus">
                                                        </div>
                                                        <div class="md:col-span-1 text-center">
                                                            <button type="button" class="text-rose-500 hover:text-rose-700" onclick="removeLevelingRow(this)">
                                                                <svg class="w-4 h-4 fill-current" viewBox="0 0 16 16"><path d="M5 7h2v6H5V7zm4 0h2v6H9V7zm3-6v2h4v2h-1v10c0 .6-.4 1-1 1H2c-.6 0-1-.4-1-1V5H0V3h4V1c0-.6.4-1 1-1h6c.6 0 1 .4 1 1z"/></svg>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
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

                                                <div id="bonus-tetap" class="mt-3" style="display: none;">
                                                    <label class="block text-sm font-medium mb-1" for="nominal_bonus_admin">
                                                        Nominal Bonus per Nota <span class="text-rose-500">*</span>
                                                    </label>
                                                    <input id="nominal_bonus_admin" name="nominal_bonus_admin"
                                                        class="form-input w-full px-2 py-1 input-currency" type="text"
                                                        placeholder="Contoh: 5000" />
                                                </div>
                                            </div>

                                            <div id="bonus-persen" style="display: none;">
                                                <label class="block text-sm font-medium mb-1" for="persen">
                                                   Bonus Hardware Persentase <span class="text-rose-500">*</span>
                                                </label>
                                                <input id="persen" name="persen"
                                                    class="form-input w-full px-2 py-1" type="number"
                                                    placeholder="Contoh: 10" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-6 border-t border-slate-200 pt-4">
                                        <span class="font-semibold text-slate-800 mb-4">Detail Akun (Dokumen)</h3>
                                        <p style="color: red" class="text-xs text-slate-500 mt-1">Format: JPG, PNG, atau PDF</p>
                                        <hr>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="foto_ktp">
                                                    KTP
                                                </label>
                                                <input id="foto_ktp" name="foto_ktp" class="form-input w-full px-2 py-1 text-sm"
                                                    type="file" accept=".pdf,.jpg,.jpeg,.png" />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="foto_kk">
                                                    KK
                                                </label>
                                                <input id="foto_kk" name="foto_kk" class="form-input w-full px-2 py-1 text-sm"
                                                    type="file" accept=".pdf,.jpg,.jpeg,.png" />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="foto_ijasah">
                                                    Ijasah
                                                </label>
                                                <input id="foto_ijasah" name="foto_ijasah" class="form-input w-full px-2 py-1 text-sm"
                                                    type="file" accept=".pdf,.jpg,.jpeg,.png" />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium mb-1" for="dokumen_lain">
                                                    Dokumen Lain
                                                </label>
                                                <input id="dokumen_lain" name="dokumen_lain" class="form-input w-full px-2 py-1 text-sm"
                                                    type="file" accept=".pdf,.jpg,.jpeg,.png" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

        <div class="bg-emerald-100 rounded border border-emerald-200 text-emerald-600 p-4 mb-5">
            <div class="text-left md:flex md:items-center md:space-x-2">
                <div class="text-sm">
                    Silahkan atur persen pada tiap-tiap akun untuk implementasi pembagian hasil. Persen Kepala Toko
                    tidak perlu diisi.
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div x-show="open" x-data="{ open: true }" class="mb-5">
                <div class="px-4 py-2 rounded-sm text-sm bg-rose-500 text-white">
                    <div class="flex w-full justify-between items-start">
                        <div class="flex">
                            <svg class="w-4 h-4 shrink-0 fill-current opacity-80 mt-[3px] mr-3" viewBox="0 0 16 16">
                                <path
                                    d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm1 12H7V7h2v5zM8 6c-.6 0-1-.4-1-1s.4-1 1-1 1 .4 1 1-.4 1-1 1z" />
                            </svg>
                            <div>
                                @foreach ($errors->all() as $error)
                                    <div class="font-medium">{{ $error }}</div>
                                @endforeach
                            </div>
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

        @if (session()->has('message'))
            <div x-show="open" x-data="{ open: true }" class="mb-5">
                <div class="px-4 py-2 rounded-sm text-sm bg-emerald-500 text-white">
                    <div class="flex w-full justify-between items-start">
                        <div class="flex">
                            <div class="font-medium">{{ session('message') }}</div>
                        </div>
                        <button class="opacity-70 hover:opacity-80 ml-3 mt-[3px]" @click="open = false">
                            <svg class="w-4 h-4 fill-current"><path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" /></svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div x-data="{ detailModalOpen: false }" @open-detail-modal.window="detailModalOpen = true">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 z-50 transition-opacity" x-show="detailModalOpen" x-cloak></div>
            <div class="fixed inset-0 z-50 overflow-hidden flex items-center justify-center px-4 sm:px-6" role="dialog" aria-modal="true" x-show="detailModalOpen" x-cloak>
                <div class="bg-white rounded shadow-lg overflow-auto max-w-2xl w-full max-h-full" @click.outside="detailModalOpen = false">
                    <div class="px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                        <div class="font-semibold text-slate-800">Detail Bonus Leveling Interface</div>
                        <button class="text-slate-400 hover:text-slate-500" @click="detailModalOpen = false">
                            <svg class="w-4 h-4 fill-current"><path d="M7.95 6.536l4.242-4.243a1 1 0 111.415 1.414L9.364 7.95l4.243 4.242a1 1 0 11-1.415 1.415L7.95 9.364l-4.243 4.243a1 1 0 01-1.414-1.415L6.536 7.95 2.293 3.707a1 1 0 011.414-1.414L7.95 6.536z" /></svg>
                        </button>
                    </div>
                    <div class="px-5 py-4">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-2 py-2">Jenis Barang</th>
                                    <th class="px-2 py-2">OS</th>
                                    <th class="px-2 py-2">Rate (Start - End)</th>
                                    <th class="px-2 py-2">Bonus</th>
                                </tr>
                            </thead>
                            <tbody id="detail-bonus-body">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-lg rounded-sm border border-slate-200 mt-5 mb-8">
            <div class="sm:flex sm:justify-between sm:items-center px-5 py-4">
                {{-- Left side --}}
                <h2 class="font-semibold text-slate-800">Semua Akun <span class="text-slate-400 font-medium">{{ $count }}</span></h2>
                {{-- Right side --}}
                <div class="relative inline-flex">
                    <div class="table-items-action hidden">
                        <div class="flex items-center">
                            <div class="text-sm italic mr-2 whitespace-nowrap"><span class="table-items-count"></span> item yang dipilih</div>
                            <button class="btn bg-white border-slate-200 hover:border-slate-300 text-rose-500 hover:text-rose-600" onclick="deleteSelected()">Hapus</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto p-4">
                <table id="akun-table" class="table-auto w-full">
                    <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-b border-slate-200">
                        <tr>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap w-px">
                                <div class="flex items-center">
                                    <label class="inline-flex">
                                        <span class="sr-only">Select all</span>
                                        <input id="parent-checkbox" class="form-checkbox" type="checkbox" />
                                    </label>
                                </div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">No.</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Cabang</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Email</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Nama Pengguna</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Bagian Teknisi</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">NIK</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Alamat</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">HP</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Hak Akses</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Persen</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">PDF Investor</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Persen Pembagian Investor Servis</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Persen Pembagian Investor Produk</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Shift</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">KTP</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">KK</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Ijasah</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Dok. Lain</div>
                            </th>
                            <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                                <div class="font-semibold text-left">Aksi</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-slate-200">
                        </tbody>
                </table>
            </div>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>

    document.addEventListener('input', function (e) {
        if (!e.target.classList.contains('input-currency')) return;

        let value = e.target.value;

        // hapus semua selain angka
        value = value.replace(/\D/g, '');

        // format rupiah pakai titik
        e.target.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    });
    function toggleInputs() {
        const role = document.getElementById('role').value;
        const bagianTeknisi = document.getElementById('bagian_teknisi').value;
        const extraFields = document.getElementById('extra-fields');
        const uploadInvestor = document.getElementById('upload-investor');
        const bonusAdmin = document.getElementById('bonus-admin');
        const spesialisInput = document.getElementById('spesialis-input');
        const bonusPersen = document.getElementById('bonus-persen');
        const bonusInterfaceInput = document.getElementById('bonus-interface-input');
        const ShiftInput = document.getElementById('shift_input');
        const persenInvestorInput = document.getElementById('persen_investor_input');
        const persenInvestorProdukInput = document.getElementById('persen_investor_input_produk');
        const bonusLevelingContainer = document.getElementById('bonus-leveling-container');
        if (role === 'Investor') {
            extraFields.style.display = 'none';
            uploadInvestor.style.display = 'block';
            bonusAdmin.style.display = 'none';
            spesialisInput.style.display = 'none';
            bonusInterfaceInput.style.display = 'none';
            ShiftInput.style.display = 'none';
            persenInvestorInput.style.display = 'block';
            persenInvestorProdukInput.style.display = 'block';
        } else if (role === 'Kepala Toko') {
            extraFields.style.display = 'none';
            uploadInvestor.style.display = 'none';
            bonusAdmin.style.display = 'none';
            spesialisInput.style.display = 'none';
            ShiftInput.style.display = 'none';
            bonusInterfaceInput.style.display = 'none';
            persenInvestorInput.style.display = 'none';
            persenInvestorProdukInput.style.display = 'none';
        } else if (role === 'Admin Toko') {
            extraFields.style.display = 'block';
            uploadInvestor.style.display = 'none';
            bonusAdmin.style.display = 'block';
            spesialisInput.style.display = 'none';
            bonusInterfaceInput.style.display = 'none';
            ShiftInput.style.display = 'block';
            persenInvestorInput.style.display = 'none';
            persenInvestorProdukInput.style.display = 'none';
        } else if (role === 'Teknisi') {
            extraFields.style.display = 'block';
            uploadInvestor.style.display = 'none';
            bonusAdmin.style.display = 'none';
            spesialisInput.style.display = 'block';
            bonusPersen.style.display = 'block';
            bonusInterfaceInput.style.display = 'block';
            ShiftInput.style.display = 'block';
            persenInvestorInput.style.display = 'none';
            persenInvestorProdukInput.style.display = 'none';

            if (bagianTeknisi === 'Teknisi Interface') {
                bonusLevelingContainer.style.display = 'block'; // Munculkan Div Leveling
                bonusPersen.style.display = 'none'; // Sembunyikan persen hardware
            } else if (bagianTeknisi === 'Teknisi Hardware') {
                bonusLevelingContainer.style.display = 'none';
                bonusPersen.style.display = 'block';
            } else {
                bonusPersen.style.display = 'block'; 
            }
        } else if (role === 'Sales') {
            extraFields.style.display = 'block';
            uploadInvestor.style.display = 'none';
            bonusAdmin.style.display = 'none';
            spesialisInput.style.display = 'none';
            bonusPersen.style.display = 'block';
            bonusInterfaceInput.style.display = 'none';
            ShiftInput.style.display = 'block';
            persenInvestorInput.style.display = 'none';
            persenInvestorProdukInput.style.display = 'none';
        }
    }

    // Jalankan saat pertama kali halaman dimuat
    document.addEventListener('DOMContentLoaded', toggleInputs);
    document.getElementById('role').addEventListener('change', toggleInputs);


    function processExcel(input) {
        if(!input.files || !input.files[0]) return;

        const file = input.files[0];
        const reader = new FileReader();

        reader.onload = function(e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, {type: 'array'});
            
            // Ambil sheet pertama
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            
            // Konversi ke JSON
            // Header Excel harus: id_jenis_barang, id_tipe_os, dari_rate, sampai_rate, nominal_bonus
            const jsonData = XLSX.utils.sheet_to_json(worksheet);

            if(jsonData.length > 0) {
                // Loop data excel dan buat row
                jsonData.forEach(row => {
                    appendExcelRow(row);
                });
                
                // Reset input file agar bisa upload file yang sama jika perlu revisi
                input.value = '';
                alert('Berhasil import ' + jsonData.length + ' baris data!');
            } else {
                alert('Data Excel kosong atau format salah.');
            }
        };

        reader.readAsArrayBuffer(file);
    }

    function appendExcelRow(data) {
        const wrapper = document.getElementById('leveling-rows-wrapper');
        // Kita clone dari row pertama yang sudah ada di HTML (Template)
        const templateRow = wrapper.querySelector('.leveling-row');
        
        if(!templateRow) {
            alert('Tidak ada template baris. Pastikan minimal ada 1 baris input.');
            return;
        }

        const newRow = templateRow.cloneNode(true);
        
        // Mapping data excel ke input
        // Pastikan nama kolom di Excel sesuai (case sensitive biasanya, tapi kita handle basic)
        
        const selJenis = newRow.querySelector('select[name="lvl_id_jenis_barang[]"]');
        const selOs = newRow.querySelector('select[name="lvl_id_tipe_os[]"]');
        const inpStart = newRow.querySelector('input[name="lvl_start_rate[]"]');
        const inpEnd = newRow.querySelector('input[name="lvl_end_rate[]"]');
        const inpBonus = newRow.querySelector('input[name="lvl_nominal_bonus[]"]');

        // Set Value dari Excel
        // Menggunakan optional chaining (?.) dan fallback || ''
        if(selJenis) selJenis.value = data['id_jenis_barang'] || data['ID Jenis Barang']; 
        if(selOs) selOs.value = data['id_tipe_os'] || data['ID Tipe OS'];
        
        // Format Rupiah untuk inputan angka
        // Fungsi helper formatRupiahManual ada di bawah
        if(inpStart) inpStart.value = formatRupiahManual(data['dari_rate'] || data['Dari Rate']);
        if(inpEnd) inpEnd.value = formatRupiahManual(data['sampai_rate'] || data['Sampai Rate']);
        if(inpBonus) inpBonus.value = formatRupiahManual(data['nominal_bonus'] || data['Nominal Bonus']);

        wrapper.appendChild(newRow);
    }

    // Helper untuk memformat angka dari Excel (misal: 50000) menjadi format input (50.000)
    function formatRupiahManual(angka) {
        if(!angka) return '';
        return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function removeLevelingRow(btn) {
        const wrapper = document.getElementById('leveling-rows-wrapper');
        const rows = wrapper.querySelectorAll('.leveling-row');
        
        // Sisakan minimal 1 baris
        if (rows.length > 1) {
            btn.closest('.leveling-row').remove();
        } else {
            alert("Minimal satu baris data diperlukan.");
        }
    }

    // 3. Logic Format Rupiah (Event Delegation agar jalan di elemen dinamis)
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('input-currency')) {
            let value = e.target.value;
            value = value.replace(/\D/g, ''); // Hapus non-digit
            e.target.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); // Tambah titik
        }
    });

    // 4. Logic Tombol Detail (DataTable Click)
    $(document).on('click', '.btn-detail-bonus', function() {
        var userId = $(this).data('id');
        var url = "{{ url('akun/bonus-detail') }}/" + userId; // Pastikan rute ini dibuat

        // Bersihkan table body
        $('#detail-bonus-body').html('<tr><td colspan="4" class="text-center py-4">Loading...</td></tr>');
        
        // Buka Modal (menggunakan AlpineJS dispatch)
        window.dispatchEvent(new CustomEvent('open-detail-modal'));

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                var rows = '';
                if(response.length > 0) {
                    response.forEach(function(item) {
                        rows += `
                            <tr class="bg-white border-b">
                                <td class="px-2 py-2">${item.category_name ?? '-'}</td>
                                <td class="px-2 py-2">${item.nama ?? '-'}</td>
                                <td class="px-2 py-2">Rp ${parseInt(item.start_rate).toLocaleString('id-ID')} - Rp ${parseInt(item.end_rate).toLocaleString('id-ID')}</td>
                                <td class="px-2 py-2 font-bold text-emerald-600">Rp ${parseInt(item.nominal_bonus).toLocaleString('id-ID')}</td>
                            </tr>
                        `;
                    });
                } else {
                    rows = '<tr><td colspan="4" class="text-center py-4 text-slate-400">Tidak ada data leveling.</td></tr>';
                }
                $('#detail-bonus-body').html(rows);
            },
            error: function() {
                $('#detail-bonus-body').html('<tr><td colspan="4" class="text-center py-4 text-rose-500">Gagal memuat data.</td></tr>');
            }
        });
    });
    </script>

    <script>
    function toggleBonusType() {
        const tipe = document.getElementById('tipe_bonus_admin').value;
        const bonusTetap = document.getElementById('bonus-tetap');
        const bonusPersen = document.getElementById('bonus-persen');
        const roleAkun = document.getElementById('role').value;

        bonusTetap.style.display = 'none';
        bonusPersen.style.display = 'none';

        if (roleAkun == 'Admin Toko') {
            if (tipe === 'Tetap') {
                bonusTetap.style.display = 'block';
            } else if (tipe === 'Persen') {
                bonusPersen.style.display = 'block';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', toggleBonusType);
    </script>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
        <style>
            .dataTables_wrapper .dataTables_length select {
                padding-right: 30px;
                width: auto;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

        <script>
            $(document).ready(function() {
                var table = $('#akun-table').DataTable({
                    processing: false,
                    serverSide: false,
                    ajax: "{{ route('akun.data') }}",
                    columns: [
                        { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                        {
                            data: null,
                            sortable: false,
                            orderable: false,
                            searchable: false,
                            render: function (data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },

                        { data: 'cabang_name', name: 'cabang_name', orderable: false, searchable: false },
                        { data: 'name', name: 'name' },
                        { data: 'email', name: 'email' },
                        { data: 'username', name: 'username' },
                        { data: 'bagian_teknisi', name: 'bagian_teknisi' },
                        { data: 'nik', name: 'nik' },
                        { data: 'alamat', name: 'alamat' },
                        { data: 'nomor_hp', name: 'nomor_hp' },
                        { data: 'hak_akses', name: 'role' }, // Searchable by role
                        { data: 'persen', name: 'persen' },
                        { data: 'pdf_investor', name: 'pdf_investor', orderable: false, searchable: false },
                        { data: 'persen_investor', name: 'persen_investor' },
                        { data: 'persen_investor_produk', name: 'persen_investor_produk' },
                        { data: 'shift_name', name: 'shift.nama_shift' },
                        { data: 'foto_ktp', name: 'foto_ktp', orderable: false, searchable: false },
                        { data: 'foto_kk', name: 'foto_kk', orderable: false, searchable: false },
                        { data: 'foto_ijasah', name: 'foto_ijasah', orderable: false, searchable: false },
                        { data: 'dokumen_lain', name: 'dokumen_lain', orderable: false, searchable: false },
                        { data: 'aksi', name: 'aksi', orderable: false, searchable: false },
                    ],
                    order: [[1, 'asc']], // Default urut berdasarkan Nama (index ke-3)
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json',
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        paginate: {
                            first: "Awal",
                            last: "Akhir",
                            next: "Lanjut",
                            previous: "Kembali"
                        }
                    },
                    drawCallback: function() {
                        attachCheckboxHandlers();
                    }
                });

                // Logic Checkbox & Bulk Delete
                function attachCheckboxHandlers() {
                    $('#parent-checkbox').prop('checked', false);
                    toggleBulkAction();

                    $('#parent-checkbox').off('click').on('click', function() {
                        var checked = $(this).is(':checked');
                        $('input.table-item').prop('checked', checked);
                        toggleBulkAction();
                    });

                    $('#akun-table').off('change', '.table-item').on('change', '.table-item', function() {
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

                // Fungsi Bulk Delete
                window.deleteSelected = function() {
                    var selectedIds = $('input.table-item:checked').map(function() {
                        return $(this).val();
                    }).get();

                    if (selectedIds.length === 0) return alert('Pilih data terlebih dahulu.');
                    if (!confirm('Yakin ingin menghapus ' + selectedIds.length + ' akun ini?')) return;

                    $.ajax({
                        url: "{{ route('akun.delete-batch') }}",
                        method: 'POST',
                        data: {
                            ids: selectedIds,
                            _token: "{{ csrf_token() }}"
                        },
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
        </script>
    @endpush

</x-toko-layout>
