<header id="header" class="header sticky-top">
    <div class="branding d-flex align-items-cente">

        <div class="container position-relative d-flex align-items-center justify-content-between">
            <a href="index.html" class="logo d-flex align-items-center">
                <!-- Uncomment the line below if you also wish to use an image logo -->
                {{-- <img src="portal/assets/img/logo.webp" alt=""> --}}
                <h1 class="sitename">{{ $toko_setting->nama_toko }}</h1>
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                    <li><a href="{{ url('/') }}" class="active">Home</a></li>
                    <li>
                        <a href="https://wa.me/{{ $toko_setting->nomor_hp_toko }}" target="_blank">
                            Contact
                        </a>
                    </li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

        </div>

    </div>

</header>
