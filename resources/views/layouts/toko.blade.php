<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title')</title>

        {{-- Fav Icon --}}
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        @stack('styles')

        <!-- Styles -->
        @livewireStyles

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body
        class="font-inter antialiased bg-slate-100 text-slate-600"
        :class="{ 'sidebar-expanded': sidebarExpanded }"
        x-data="{ sidebarOpen: false, sidebarExpanded: localStorage.getItem('sidebar-expanded') == 'true' }"
        x-init="$watch('sidebarExpanded', value => localStorage.setItem('sidebar-expanded', value))"
    >

        <script>
            if (localStorage.getItem('sidebar-expanded') == 'true') {
                document.querySelector('body').classList.add('sidebar-expanded');
            } else {
                document.querySelector('body').classList.remove('sidebar-expanded');
            }
        </script>

        <!-- Page wrapper -->
        <div class="flex h-screen overflow-hidden">

            <x-kepalatoko.sidebar />

            <!-- Content area -->
            <div class="relative flex flex-col flex-1 overflow-y-auto overflow-x-hidden @if($attributes['background']){{ $attributes['background'] }}@endif" x-ref="contentarea">

                <x-app.header />

                <main>
                    @include('sweetalert::alert')
                    {{ $slot }}
                </main>

            </div>

        </div>

        @livewireScripts

        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <x-livewire-alert::scripts />

        @stack('scripts')
        @if(auth()->check() && (auth()->user()->role == 'Kepala Toko' || auth()->user()->id == 1))
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                // Helper wajib untuk konversi VAPID key
                function urlBase64ToUint8Array(base64String) {
                    const padding = '='.repeat((4 - base64String.length % 4) % 4);
                    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
                    const rawData = window.atob(base64);
                    const outputArray = new Uint8Array(rawData.length);
                    for (let i = 0; i < rawData.length; ++i) {
                        outputArray[i] = rawData.charCodeAt(i);
                    }
                    return outputArray;
                }

                // Daftarkan Service Worker
                if ('serviceWorker' in navigator && 'PushManager' in window) {
                    console.log("Service Worker didukung oleh browser!"); // CEK 1
                    
                    navigator.serviceWorker.register('/sw.js').then(function (registration) {
                        console.log("Service Worker berhasil didaftarkan!"); // CEK 2
                        // Minta izin ke user
                        Notification.requestPermission().then(function (permission) {
                            if (permission === 'granted') {
                                const vapidPublicKey = "{{ env('VAPID_PUBLIC_KEY') }}";
                                const convertedVapidKey = urlBase64ToUint8Array(vapidPublicKey);

                                // Subscribe ke server Push (Google/Mozilla)
                                registration.pushManager.subscribe({
                                    userVisibleOnly: true,
                                    applicationServerKey: convertedVapidKey
                                }).then(function (subscription) {

                                    // Kirim token ke Route web.php kita
                                    fetch('/push-subscribe', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        },
                                        body: JSON.stringify(subscription)
                                    });

                                }).catch(function (error) {
                                    console.error('Push subscription error: ', error);
                                });
                            }
                        });

                    });
                }
            });
        </script>
        @endif
    </body>
</html>
