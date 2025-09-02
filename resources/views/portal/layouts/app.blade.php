<!DOCTYPE html>
<html lang="en">

{{-- head  --}}
@include('portal.layouts.partials.head')

<body class="index-page">

@include('portal.layouts.partials.navbar')


  <main class="main">

    @yield('content')
  </main>

  @include('portal.layouts.partials.footer')

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  @include('portal.layouts.partials.foot')

</body>

</html>