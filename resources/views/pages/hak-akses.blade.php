<x-empty-layout>
    <style>
        .swal2-confirm {
            background: #7066e0 !important
        }

        .swal2-cancel {
            background: red !important
        }
    </style>
    <main class="bg-white">

        <div class="relative flex">

            <!-- Content -->
            <div class="w-full md:w-1/2">

                <div class="min-h-screen h-full flex flex-col after:flex-1">

                    <div class="flex-1">

                        <!-- Header -->
                        <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                            <!-- Logo -->
                            <a class="block" href="#">
                                <img src="{{ $setting && $setting->profile_photo_path
                                    ? Storage::url($setting->profile_photo_path)
                                    : asset('images/logo-saraba-bisa.png') }}"
                                    alt="Logo Toko" class="object-contain"
                                    style="height: 5rem!important;margin-top: 61%;!important" />
                            </a>
                        </div>
                    </div>

                    <div class="px-4 py-8">
                        <div class="max-w-md mx-auto">
                            <h1 class="text-3xl text-slate-800 font-bold mb-6">Pilih Hak Akses Anda ✨</h1>
                            <div class="space-y-3 mb-8">

                                <a href="{{ route('kepalatoko-dashboard') }}" data-role="Kepala Toko">
                                    <div
                                        class="flex items-center bg-white text-sm font-medium text-slate-800 p-4 rounded border border-slate-200 hover:border-slate-300 shadow-sm duration-150 ease-in-out mb-3">
                                        <svg class="w-6 h-6 shrink-0 fill-current mr-4" viewBox="0 0 24 24">
                                            <path class="text-indigo-500"
                                                d="m12 10.856 9-5-8.514-4.73a1 1 0 0 0-.972 0L3 5.856l9 5Z" />
                                            <path class="text-indigo-300"
                                                d="m11 12.588-9-5V18a1 1 0 0 0 .514.874L11 23.588v-11Z" />
                                            <path class="text-indigo-200"
                                                d="M13 12.588v11l8.486-4.714A1 1 0 0 0 22 18V7.589l-9 4.999Z" />
                                        </svg>
                                        <span>Kepala Toko</span>
                                    </div>
                                </a>

                                <a href="{{ route('admintoko-dashboard') }}" data-role="Admin Toko">
                                    <div
                                        class="flex items-center bg-white text-sm font-medium text-slate-800 p-4 rounded border border-slate-200 hover:border-slate-300 shadow-sm duration-150 ease-in-out mb-3">
                                        <svg class="w-6 h-6 shrink-0 fill-current mr-4" viewBox="0 0 24 24">
                                            <path class="text-indigo-500"
                                                d="m12 10.856 9-5-8.514-4.73a1 1 0 0 0-.972 0L3 5.856l9 5Z" />
                                            <path class="text-indigo-300"
                                                d="m11 12.588-9-5V18a1 1 0 0 0 .514.874L11 23.588v-11Z" />
                                            <path class="text-indigo-200"
                                                d="M13 12.588v11l8.486-4.714A1 1 0 0 0 22 18V7.589l-9 4.999Z" />
                                        </svg>
                                        <span>Admin Toko</span>
                                    </div>
                                </a>

                                <a href="{{ route('teknisi-dashboard') }}" data-role="Teknisi">
                                    <div
                                        class="flex items-center bg-white text-sm font-medium text-slate-800 p-4 rounded border border-slate-200 hover:border-slate-300 shadow-sm duration-150 ease-in-out mb-3">
                                        <svg class="w-6 h-6 shrink-0 fill-current mr-4" viewBox="0 0 24 24">
                                            <path class="text-indigo-500"
                                                d="m12 10.856 9-5-8.514-4.73a1 1 0 0 0-.972 0L3 5.856l9 5Z" />
                                            <path class="text-indigo-300"
                                                d="m11 12.588-9-5V18a1 1 0 0 0 .514.874L11 23.588v-11Z" />
                                            <path class="text-indigo-200"
                                                d="M13 12.588v11l8.486-4.714A1 1 0 0 0 22 18V7.589l-9 4.999Z" />
                                        </svg>
                                        <span>Teknisi</span>
                                    </div>
                                </a>

                                <a href="{{ route('sales-dashboard') }}" data-role="Sales">
                                    <div
                                        class="flex items-center bg-white text-sm font-medium text-slate-800 p-4 rounded border border-slate-200 hover:border-slate-300 shadow-sm duration-150 ease-in-out mb-3">
                                        <svg class="w-6 h-6 shrink-0 fill-current mr-4" viewBox="0 0 24 24">
                                            <path class="text-indigo-500"
                                                d="m12 10.856 9-5-8.514-4.73a1 1 0 0 0-.972 0L3 5.856l9 5Z" />
                                            <path class="text-indigo-300"
                                                d="m11 12.588-9-5V18a1 1 0 0 0 .514.874L11 23.588v-11Z" />
                                            <path class="text-indigo-200"
                                                d="M13 12.588v11l8.486-4.714A1 1 0 0 0 22 18V7.589l-9 4.999Z" />
                                        </svg>
                                        <span>Sales</span>
                                    </div>
                                </a>

                                <a href="{{ route('kepalatoko-dashboard') }}" data-role="Investor">
                                    <div
                                        class="flex items-center bg-white text-sm font-medium text-slate-800 p-4 rounded border border-slate-200 hover:border-slate-300 shadow-sm duration-150 ease-in-out mb-3">
                                        <svg class="w-6 h-6 shrink-0 fill-current mr-4" viewBox="0 0 24 24">
                                            <path class="text-indigo-500"
                                                d="m12 10.856 9-5-8.514-4.73a1 1 0 0 0-.972 0L3 5.856l9 5Z" />
                                            <path class="text-indigo-300"
                                                d="m11 12.588-9-5V18a1 1 0 0 0 .514.874L11 23.588v-11Z" />
                                            <path class="text-indigo-200"
                                                d="M13 12.588v11l8.486-4.714A1 1 0 0 0 22 18V7.589l-9 4.999Z" />
                                        </svg>
                                        <span>Investor</span>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Image -->
            <div class="hidden md:block absolute top-0 bottom-0 right-0 md:w-1/2" aria-hidden="true">
                <img class="object-cover object-center w-full h-full"
                    src="{{ $setting && $setting->foto_login ? Storage::url($setting->foto_login) : asset('images/bg-auth.jpg') }}"
                    width="760" height="1024" alt="Onboarding" />
            </div>

        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userRole = "{{ auth()->user()->role ?? '' }}"; // role user login

            // ambil semua <a> dengan atribut data-role
            const links = document.querySelectorAll('a[data-role]');

            links.forEach(link => {
                const targetRole = link.getAttribute('data-role')?.trim();

                if (!targetRole) return;

                if (targetRole.toLowerCase() !== userRole.toLowerCase()) {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Akses Ditolak!',
                            text: `Anda login sebagai "${userRole}", tidak bisa mengakses halaman "${targetRole}".`,
                            confirmButtonText: 'Mengerti',
                            confirmButtonColor: '#7066e0',
                            allowOutsideClick: false,
                            allowEscapeKey: false
                        });
                    });
                }
            });
        });
    </script>

</x-empty-layout>
