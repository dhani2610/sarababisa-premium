<!DOCTYPE html>
<html lang="en">

{{-- head  --}}
@include('portal.layouts.partials.head')

<body class="index-page">
    <style>
        :root {
            --main-bg: {{ $kepala_toko_setting->color_portal ?? '#6366f1' }};
        }

        .main-layout-setting {
            background: var(--main-bg) !important;
        }

        .main-color-text-layout-setting {
            color: var(--main-bg) !important;
        }

        .active>.page-link,
        .page-link.active {
            z-index: 3;
            color: white !important;
            background-color: var(--main-bg) !important;
            border-color: var(--main-bg) !important;
        }

        .page-link {
            color: var(--main-bg) !important;
        }

        .scroll-top {
            background: var(--main-bg) !important;
        }

        .footer .social-links a {
            font-size: 18px;
            display: inline-block;
            background:
                white;
            color: var(--main-bg);
            line-height: 1;
            padding: 8px 0;
            margin-right: 4px;
            border-radius: 4px;
            text-align: center;
            width: 36px;
            height: 36px;
            transition: 0.3s;
        }
        .footer .social-links a:hover {
            background:
                black!important;
        }
    </style>



    @include('portal.layouts.partials.navbar')


    <main class="main">

        @yield('content')
    </main>

    @include('portal.layouts.partials.footer')

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    @include('portal.layouts.partials.foot')

</body>

</html>
