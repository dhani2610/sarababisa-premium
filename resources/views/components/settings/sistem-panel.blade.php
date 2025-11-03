<div class="grow">
    <!-- Panel body -->
    <form action="{{ route('informasi-toko-update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="p-6 space-y-6">
            <!-- Business Profile -->
            <section>
                <h3 class="text-xl leading-snug text-slate-800 font-bold mb-1">Sistem</h3>
                <div class="text-sm">Informasi ini akan mengatur bagaimana sistem ingin diterapkan pada beberapa bagian.</div>
            </section>
            <livewire:toggle-tax></livewire:toggle-tax>
            <livewire:toggle-bonus></livewire:toggle-bonus>
            <livewire:toggle-edit-transaksi></livewire:toggle-edit-transaksi>
            <livewire:toggle-edit-produk></livewire:toggle-edit-produk>
            <livewire:toggle-telegram-bot />
            <livewire:toggle-absensi-setting />
        </div>

        <!-- Panel footer -->
        <footer>
            <div class="flex flex-col px-6 py-5 border-t border-slate-200">
                <div class="flex self-end">
                    {{-- <button class="btn border-slate-200 hover:border-slate-300 text-slate-600">Batal</button> --}}
                    <button class="btn bg-indigo-500 hover:bg-indigo-600 text-white ml-3">Simpan Perubahan</button>
                </div>
            </div>
        </footer>
    </form>
</div>
<script>
window.addEventListener('notify', event => {
    alert(event.detail.message); // Bisa diganti pakai toast atau SweetAlert
});
</script>
