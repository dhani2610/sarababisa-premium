<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Sedang Tutup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center h-screen px-4">

    <div class="max-w-lg w-full bg-white rounded-xl shadow-lg overflow-hidden border border-slate-200 text-center p-8">

        <div class="flex justify-center mb-6">
            <div class="bg-red-100 p-4 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-slate-800 mb-2">Maaf, Toko Sedang Tutup</h1>

        <p class="text-slate-600 mb-6">
            Anda tidak dapat melakukan perubahan data atau transaksi saat ini karena sistem sedang terkunci di luar jam operasional.
        </p>

        <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 mb-6">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-2">Jam Operasional Toko</h2>
            <p class="text-3xl font-bold text-indigo-600">
                {{ $jam_buka }} - {{ $jam_tutup }}
            </p>
        </div>

        <div class="text-sm text-slate-500">
            <p>{{ $pesan_tambahan }}</p>
        </div>
            
    <div class="flex flex-wrap gap-3 items- mt-3">
        <a href="/" class="inline-flex items-center bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-6 rounded-lg transition duration-200">
            &larr; <span class="ml-2">Kembali ke Halaman Utama</span>
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="inline-flex items-center bg-rose-500 hover:bg-rose-600 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                {{ __('Keluar') }}
            </button>
        </form>
    </div>

    </div>

</body>
</html>
