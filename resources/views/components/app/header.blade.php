<header class="sticky top-0 bg-white border-b border-slate-200 z-30">
    <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 -mb-px">

            <!-- Header: Left side -->
            <div class="flex">

                <!-- Hamburger button -->
                <button class="text-slate-500 hover:text-slate-600 lg:hidden" @click.stop="sidebarOpen = !sidebarOpen"
                    aria-controls="sidebar" :aria-expanded="sidebarOpen">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <rect x="4" y="5" width="16" height="2" />
                        <rect x="4" y="11" width="16" height="2" />
                        <rect x="4" y="17" width="16" height="2" />
                    </svg>
                </button>

            </div>

            <!-- Header: Right side -->
            <div class="flex items-center space-x-3">

                <!-- Notifications button -->
                {{-- <x-dropdown-notifications align="right" /> --}}

                <!-- Info button -->
                {{-- <x-dropdown-help align="right" /> --}}
                <x-exp-date align="right" />

                <!-- Divider -->
                <hr class="w-px h-6 bg-slate-200" />

                <!-- User button -->
                <x-dropdown-profile align="right" />

            </div>

            <div id="dateTimeWidget"
                class="fixed top-4 right-4 bg-white shadow-lg rounded-xl px-4 py-2 flex items-center gap-2 text-sm font-semibold text-slate-700 border border-slate-200 z-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span id="dateTimeText">Loading...</span>
            </div>

        </div>
    </div>

    <script>
        function updateDateTime() {
            const now = new Date();

            const options = {
                weekday: 'long', // Senin, Selasa, dst
                year: 'numeric',
                month: 'long', // Januari, Februari, dst
                day: 'numeric'
            };
            const dateStr = now.toLocaleDateString('id-ID', options);

            const timeStr = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            document.getElementById('dateTimeText').textContent = `${dateStr} ${timeStr}`;
        }

        // Update setiap 1 detik
        setInterval(updateDateTime, 1000);
        updateDateTime();
    </script>


    <style>
        /* Responsif untuk mobile */
        @media (max-width: 640px) {
            #dateTimeWidget {
                top: auto;
                bottom: 1rem;
                right: 1rem;
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
            }
        }
    </style>
</header>
