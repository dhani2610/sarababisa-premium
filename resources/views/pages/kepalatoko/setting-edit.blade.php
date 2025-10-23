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
                <div class="bg-white rounded shadow p-6 mb-8">
                    <h2 class="text-lg font-semibold text-slate-800 mb-4">Update Expired Date Semua User</h2>
                    <form action="{{ route('akun.update-exp-date') }}" method="POST">
                        @csrf
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <input type="date" name="exp_date"
                                class="form-input px-3 py-2 border rounded-md w-full sm:w-auto"
                                value="{{ $latestExp ? date('Y-m-d', strtotime($latestExp)) : '' }}" required>
                            <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                                Update
                            </button>
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

            function toggleFields() {
                if (roleSelect.value === 'Investor') {
                    extraFields.style.display = 'none';
                } else {
                    extraFields.style.display = 'block';
                }
            }

            // Jalankan langsung saat halaman dibuka (cek default value)
            toggleFields();

            // Jalankan setiap kali user ubah role
            roleSelect.addEventListener('change', toggleFields);
        });
    </script>

    {{-- SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const extraFields = document.getElementById('extra-fields');
            const uploadInvestor = document.getElementById('upload-investor');

            function toggleInputs() {
                if (roleSelect.value === 'Investor') {
                    uploadInvestor.style.display = 'block';
                    extraFields.style.display = 'none';
                } else {
                    uploadInvestor.style.display = 'none';
                    extraFields.style.display = 'block';
                }
            }

            // Jalankan saat halaman dimuat
            toggleInputs();

            // Jalankan setiap kali role diubah
            roleSelect.addEventListener('change', toggleInputs);
        });
    </script>

</x-toko-layout>
