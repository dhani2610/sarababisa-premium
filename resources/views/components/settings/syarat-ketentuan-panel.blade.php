<div class="grow">
    <!-- Nota Terima Servis -->
    <form action="{{ route('ketentuan-terima-update') }}" method="POST">
        @csrf
        <div class="px-6 pt-6 pb-3">
            <!-- Business Profile -->
            <section>
                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Syarat & Ketentuan</h3>
                <div class="text-sm">Informasi ini akan terlihat pada nota transaksi.</div>
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-1" for="description">Pada Nota Terima Servis</label>
                    <textarea name="description" id="description" rows="3" class="w-full">
                        {!! $termterima->description ?? '' !!}
                    </textarea>
                </div>
            </section>
        </div>

        <!-- Panel footer -->
        <footer>
            <div class="flex flex-col px-6 pt-0 pb-5 border-slate-200">
                <div class="flex self-end">
                    {{-- <button class="btn border-slate-200 hover:border-slate-300 text-slate-600">Batal</button> --}}
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white ml-3">Simpan Perubahan</button>
                </div>
            </div>
        </footer>
    </form>
    <!-- Nota Pengambilan Servis -->
    <form action="{{ route('ketentuan-pengambilan-update') }}" method="POST">
        @csrf
        <div class="px-6 py-3 border-t border-slate-200">
            <!-- Business Profile -->
            <section>
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-1" for="description">Pada Nota Pengambilan Servis</label>
                    <textarea name="description" id="description" rows="3" class="w-full">
                        {!! $termpengambilan->description ?? '' !!}
                    </textarea>
                </div>
            </section>
        </div>

        <!-- Panel footer -->
        <footer>
            <div class="flex flex-col px-6 pt-0 pb-5 border-slate-200">
                <div class="flex self-end">
                    {{-- <button class="btn border-slate-200 hover:border-slate-300 text-slate-600">Batal</button> --}}
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white ml-3">Simpan Perubahan</button>
                </div>
            </div>
        </footer>
    </form>
    <!-- Nota Penjualan -->
    <form action="{{ route('ketentuan-penjualan-update') }}" method="POST">
        @csrf
        <div class="px-6 py-3 border-t border-slate-200">
            <!-- Business Profile -->
            <section>
                <div class="mt-4">
                    <label class="block text-sm font-semibold mb-1" for="description">Pada Nota Penjualan</label>
                    <textarea name="description" id="description" rows="3" class="w-full">
                        {!! $termpenjualan->description ?? '' !!}
                    </textarea>
                </div>
            </section>
        </div>

        <!-- Panel footer -->
        <footer>
            <div class="flex flex-col px-6 pt-0 pb-5 border-slate-200">
                <div class="flex self-end">
                    {{-- <button class="btn border-slate-200 hover:border-slate-300 text-slate-600">Batal</button> --}}
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white ml-3">Simpan Perubahan</button>
                </div>
            </div>
        </footer>
    </form>
</div>