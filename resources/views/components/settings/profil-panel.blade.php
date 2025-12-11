<div class="grow">

    <!-- Panel body -->
    <form action="{{ route('informasi-toko-update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="p-6 space-y-6" x-data="formData()">
            <input type="hidden" value="{{ $users->id }}" name="id_kepala_toko">
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

            <!-- Picture -->
            <section>
                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-2">Logo Toko</h3>
                <div class="flex items-center">
                    <div class="mr-4">
                        <img class="w-20 h-20 rounded-full" src="{{ Storage::url($users->profile_photo_path) }}"
                            width="80" height="80" alt="Logo Toko" />
                    </div>
                    <input type="file" name="profile_photo_path" id="profile_photo_path"
                        class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white">
                </div>
            </section>
            {{-- @if ($users->id == 1) --}}
                <section>
                    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-2">Foto Login</h3>
                    <div class="flex items-center">
                        <div class="mr-4">
                            <img class="w-20 h-20 object-cover rounded"
                                src="{{ $users->foto_login ? Storage::url($users->foto_login) : asset('img/default-login.jpg') }}"
                                width="80" height="80" alt="Foto Login" />
                        </div>
                        <div>
                            <input
                                type="file"
                                name="foto_login"
                                id="foto_login"
                                accept="image/*"
                                class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white"
                                onchange="checkFileSizeLogin(this)"
                            >
                            <p id="foto_login_alert" class="text-red-500 text-xs mt-1 hidden">
                                Ukuran foto maksimal 1 MB!
                            </p>
                            <p class="text-gray-500 text-xs mt-1">
                                Maksimal ukuran file: 1 MB
                            </p>
                        </div>
                    </div>

                    <script>
                        function checkFileSizeLogin(input) {
                            const file = input.files[0];
                            const alertEl = document.getElementById('foto_login_alert');
                            if (file && file.size > 1024 * 1024) {
                                alertEl.classList.remove('hidden');
                                input.value = ''; // reset input
                            } else {
                                alertEl.classList.add('hidden');
                            }
                        }
                    </script>
                </section>

                <section>
                    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-2">Foto Portal</h3>
                    <div class="flex items-center">
                        <div class="mr-4">
                            <img class="w-20 h-20 " src="{{ Storage::url($users->foto_portal) }}"
                                width="80" height="80" alt="Logo Toko" />
                        </div>
                        <div>
                        <input
                            type="file"
                            name="foto_portal"
                            id="foto_portal"
                            accept="image/*"
                            class="btn-sm bg-indigo-500 hover:bg-indigo-600 text-white"
                            onchange="checkFileSize(this)"
                        >
                        <p id="foto_portal_alert" class="text-red-500 text-xs mt-1 hidden">
                            Ukuran foto maksimal 1 MB!
                        </p>
                        <p class="text-gray-500 text-xs mt-1">
                            Maksimal ukuran file: 1 MB
                        </p>
                    </div>

                    <script>
                    function checkFileSize(input) {
                        const file = input.files[0];
                        const alertEl = document.getElementById('foto_portal_alert');
                        if (file && file.size > 1024 * 1024) {
                            alertEl.classList.remove('hidden');
                            input.value = ''; // reset input
                        } else {
                            alertEl.classList.add('hidden');
                        }
                    }
                    </script>

                    </div>

                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="owner">Warna Portal</label>
                        <input name="color_portal" id="color_portal" class="form-input w-full" type="color"
                            value="{{ $users->color_portal }}" />
                    </div>
                </section>
                <!-- Business Profile -->
                <section>
                    <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Social Media Portal</h3>
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mt-3" for="owner">Instagram (Link Url)</label>
                        <input name="ig" id="ig" class="form-input w-full" type="text" placeholder="https://www.instagram.com/yourusername"
                            value="{{ $users->ig }}" />
                    </div>
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mt-3" for="owner">Tiktok (Link Url)</label>
                        <input name="tiktok" id="tiktok" class="form-input w-full" type="text" placeholder="http://tiktok.com/@yourusername"
                            value="{{ $users->tiktok }}" />
                    </div>
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mt-3" for="owner">Facebook (Link Url)</label>
                        <input name="fb" id="fb" class="form-input w-full" type="text" placeholder="http://facebook.com/yourusername"
                            value="{{ $users->fb }}" />
                    </div>

                </section>
            {{-- @endif --}}

            <section>
                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Profil Toko</h3>
                <div class="text-sm">Informasi ini akan terlihat pada halaman web dan nota transaksi.</div>
                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="owner">Pemilik Toko/Usaha</label>
                        <input name="owner" id="owner" class="form-input w-full" type="text"
                            value="{{ $users->owner }}" />
                    </div>
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="nama_toko">Nama Toko/Usaha</label>
                        <input name="nama_toko" id="nama_toko" class="form-input w-full" type="text"
                            value="{{ $users->nama_toko }}" />
                    </div>
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="nomor_hp_toko">Nomor HP/WA Toko</label>
                        <input name="nomor_hp_toko" id="nomor_hp_toko" class="form-input w-full" type="number"
                            placeholder="6200000000000" value="{{ $users->nomor_hp_toko }}" />
                    </div>
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="kota">Kota/Kabupaten</label>
                        <input name="kota" id="kota" class="form-input w-full" type="text"
                            value="{{ $users->kota }}" />
                    </div>
                </div>
                <div class="sm:flex sm:items-center space-y-4 sm:space-y-0 sm:space-x-4 mt-5">
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="bank">Nama BANK</label>
                        <input name="bank" id="bank" class="form-input w-full" type="text"
                            value="{{ $users->bank }}" />
                    </div>
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="rekening">Nomor Rekening</label>
                        <input name="rekening" id="rekening" class="form-input w-full" type="number"
                            value="{{ $users->rekening }}" />
                    </div>
                    <div class="sm:w-1/2">
                        <label class="block text-sm font-medium mb-1" for="pemilik_rekening">Nama Pemilik
                            Rekening</label>
                        <input name="pemilik_rekening" id="pemilik_rekening" class="form-input w-full" type="text"
                            value="{{ $users->pemilik_rekening }}" />
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1" for="deskripsi_toko">Deskripsi Toko/Usaha</label>
                    <textarea name="deskripsi_toko" id="deskripsi_toko" rows="2" class="w-full">{{ $users->deskripsi_toko }}</textarea>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium mb-1" for="deskripsi_toko">Alamat Toko</label>
                    <textarea name="alamat_toko" id="alamat_toko" rows="2" class="w-full">{{ $users->alamat_toko }}</textarea>
                </div>
            </section>
            <!-- Multiple Nomor HP Tambahan -->
            <section class="mt-6">
                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-2">Nomor HP Tambahan</h3>
                <div id="phones-wrapper">
                    @php
                        $phones = old('phones', json_decode($users->phones ?? '[]', true));
                    @endphp
                    @foreach ($phones as $index => $phone)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-2 phone-row">
                            <input class="form-input" type="text" name="phones[{{ $index }}][title]"
                                value="{{ $phone['title'] }}" placeholder="Contoh: CS, Admin">
                            <input class="form-input" type="text" name="phones[{{ $index }}][nomor]"
                                value="{{ $phone['nomor'] }}" placeholder="Nomor HP">
                            <button type="button" class="text-red-500 remove-phone">Hapus</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn-sm border mt-2" id="add-phone">+ Tambah Nomor</button>
            </section>


            <!-- Multiple Rekening BANK -->
            <section class="mt-6">
                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-2">Rekening Bank Tambahan</h3>
                <div id="banks-wrapper">
                    @php
                        $banks = old('banks', json_decode($users->banks ?? '[]', true));
                    @endphp
                    @foreach ($banks as $index => $bank)
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-2 bank-row">
                            <input class="form-input" type="text" name="banks[{{ $index }}][bank]"
                                value="{{ $bank['bank'] }}" placeholder="Contoh: BCA">
                            <input class="form-input" type="text" name="banks[{{ $index }}][rekening]"
                                value="{{ $bank['rekening'] }}" placeholder="Nomor Rekening">
                            <input class="form-input" type="text" name="banks[{{ $index }}][pemilik]"
                                value="{{ $bank['pemilik'] }}" placeholder="Nama Pemilik">
                            <button type="button" class="text-red-500 mt-1 sm:col-span-3 remove-bank">Hapus</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn-sm border mt-2" id="add-bank">+ Tambah Rekening</button>
            </section>



        </div>

        <!-- Panel footer -->
        <footer>
            <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                <div class="flex self-end">
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white ml-3">Simpan Perubahan</button>
                </div>
            </div>
        </footer>
    </form>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let phoneIndex = {{ count($phones) }};
    let bankIndex = {{ count($banks) }};

    $('#add-phone').click(function() {
        $('#phones-wrapper').append(`
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-2 phone-row">
                <input class="form-input" type="text" name="phones[${phoneIndex}][title]" placeholder="Contoh: CS, Admin">
                <input class="form-input" type="text" name="phones[${phoneIndex}][nomor]" placeholder="Nomor HP">
                <button type="button" class="text-red-500 remove-phone">Hapus</button>
            </div>
        `);
        phoneIndex++;
    });

    $('#phones-wrapper').on('click', '.remove-phone', function() {
        $(this).closest('.phone-row').remove();
    });

    $('#add-bank').click(function() {
        $('#banks-wrapper').append(`
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-2 bank-row">
                <input class="form-input" type="text" name="banks[${bankIndex}][bank]" placeholder="Contoh: BCA">
                <input class="form-input" type="text" name="banks[${bankIndex}][rekening]" placeholder="Nomor Rekening">
                <input class="form-input" type="text" name="banks[${bankIndex}][pemilik]" placeholder="Nama Pemilik">
                <button type="button" class="text-red-500 mt-1 sm:col-span-3 remove-bank">Hapus</button>
            </div>
        `);
        bankIndex++;
    });

    $('#banks-wrapper').on('click', '.remove-bank', function() {
        $(this).closest('.bank-row').remove();
    });
</script>
