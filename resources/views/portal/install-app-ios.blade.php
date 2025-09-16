@extends('portal.layouts.app')

@section('content')
    <!-- Hotel Hero Section -->


    <!-- Gallery Showcase Section -->
    <section id="gallery-showcase" class="gallery-showcase section">
        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <!-- Section Title -->
            <div class="section-title text-center">
                <span class="description-title">Tutorial</span>
                <h2>Cara Install</h2>
                <p class="text-muted">Ikuti langkah mudah berikut untuk menambahkan PWA ke layar utama</p>
            </div>
            <!-- End Section Title -->

            <!-- Steps -->
            <div class="row g-4 justify-content-center">

                <!-- Step 1 -->
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="150">
                    <div class="card h-100 shadow-sm border-0 text-center p-3">
                        <h5 class="fw-bold">1. Buka Safari</h5>
                        <p class="text-muted">Masuk ke halaman login melalui browser Safari di iPhone.</p>
                        <img src="{{ asset('step/1.jpeg') }}" class="img-fluid rounded mb-3" alt="Step 1">
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="250">
                    <div class="card h-100 shadow-sm border-0 text-center p-3">
                        <h5 class="fw-bold">2. Tekan Icon Panah</h5>
                        <p class="text-muted">Pilih tombol <strong>Share</strong> (ikon panah ke atas) di bagian bawah
                            Safari.</p>
                            <img src="{{ asset('step/2.jpeg') }}" class="img-fluid rounded mb-3" alt="Step 2">
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="350">
                    <div class="card h-100 shadow-sm border-0 text-center p-3">
                        <h5 class="fw-bold">3. Tambahkan ke Layar Utama</h5>
                        <p class="text-muted">Pilih <strong>Tambah ke Layar Utama</strong> agar aplikasi muncul seperti
                            aplikasi biasa.</p>
                            <img src="{{ asset('step/3.jpeg') }}" class="img-fluid rounded mb-3" alt="Step 3">
                    </div>
                </div>

            </div>
            <!-- End Steps -->

        </div>
    </section>
@endsection
