@extends('portal.layouts.app')

@section('content')
    <!-- Hotel Hero Section -->
    <section id="hotel-hero" class="hotel-hero section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row gy-4 align-items-center">

                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="hero-content">
                        <h1>{{ $toko_setting->nama_toko }}</h1>
                        <p class="lead">{{ $toko_setting->deskripsi_toko }} </p>
                        <div class="hero-buttons">
                            <a href="wa.me/{{ $toko_setting->nomor_hp_toko }}" class="btn btn-primary">Hubungi Kami</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                    <div class="hero-images">
                        <div class="main-image">
                            <img src="{{ asset('images/bg-auth.jpg') }}" alt="Luxury Hotel" class="img-fluid">
                        </div>
                        <div class="floating-card" data-aos="zoom-in" data-aos-delay="400">
                            <div class="card-content">
                                <div class="rating">
                                    <span class="badge bg-success">Sparepart</span>
                                    <span class="badge bg-primary">Service</span>
                                </div>
                                <h6>Service Handphone & Laptop</h6>
                                <p>
                                    Layanan perbaikan cepat dan bergaransi, serta sparepart original untuk berbagai merek.
                                </p>
                                <div class="guest-info">
                                    <span>Mulai dari Rp 150.000</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <div class="hero-stats" data-aos="fade-up">
                <div class="row text-center">
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <span class="stat-number purecounter" data-purecounter-start="0"
                                data-purecounter-end="{{ $pelanggan }}"
                                data-purecounter-duration="1">{{ $pelanggan }}</span>
                            <span class="stat-label">Pelanggan</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <span class="stat-number purecounter" data-purecounter-start="0"
                                data-purecounter-end="{{ count($products) }}"
                                data-purecounter-duration="1">{{ count($products) }}</span>
                            <span class="stat-label">Produk</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </section><!-- /Hotel Hero Section -->


    <!-- Gallery Showcase Section -->
    <section id="gallery-showcase" class="gallery-showcase section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="gallery-carousel swiper init-swiper" data-aos="fade-up" data-aos-delay="200">
                <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 3000
              },
              "slidesPerView": 1,
              "spaceBetween": 20,
              "centeredSlides": true,
              "breakpoints": {
                "576": {
                  "slidesPerView": 2,
                  "centeredSlides": false
                },
                "768": {
                  "slidesPerView": 3,
                  "centeredSlides": false
                },
                "992": {
                  "slidesPerView": 4,
                  "centeredSlides": false
                },
                "1200": {
                  "slidesPerView": 5,
                  "centeredSlides": false
                }
              }
            }
          </script>
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="gallery-item">
                            <img src="portal/assets/img/hotel/gallery-1.webp" alt="Luxurious Suite" class="img-fluid"
                                loading="lazy">
                            <a href="assets/img/hotel/gallery-1.webp" class="gallery-overlay glightbox">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="gallery-item">
                            <img src="portal/assets/img/hotel/gallery-5.webp" alt="Modern Lobby" class="img-fluid"
                                loading="lazy">
                            <a href="assets/img/hotel/gallery-5.webp" class="gallery-overlay glightbox">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="gallery-item">
                            <img src="portal/assets/img/hotel/gallery-12.webp" alt="Elegant Dining Area" class="img-fluid"
                                loading="lazy">
                            <a href="assets/img/hotel/gallery-12.webp" class="gallery-overlay glightbox">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="gallery-item">
                            <img src="portal/assets/img/hotel/gallery-8.webp" alt="Grand Ballroom Setup" class="img-fluid"
                                loading="lazy">
                            <a href="assets/img/hotel/gallery-8.webp" class="gallery-overlay glightbox">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="gallery-item">
                            <img src="portal/assets/img/hotel/gallery-15.webp" alt="Relaxing Poolside" class="img-fluid"
                                loading="lazy">
                            <a href="assets/img/hotel/gallery-15.webp" class="gallery-overlay glightbox">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="gallery-item">
                            <img src="portal/assets/img/hotel/gallery-3.webp" alt="Cozy Guest Room" class="img-fluid"
                                loading="lazy">
                            <a href="assets/img/hotel/gallery-3.webp" class="gallery-overlay glightbox">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="gallery-item">
                            <img src="portal/assets/img/hotel/gallery-18.webp" alt="Spa and Wellness Center"
                                class="img-fluid" loading="lazy">
                            <a href="assets/img/hotel/gallery-18.webp" class="gallery-overlay glightbox">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="gallery-item">
                            <img src="portal/assets/img/hotel/gallery-7.webp" alt="Conference Facilities"
                                class="img-fluid" loading="lazy">
                            <a href="assets/img/hotel/gallery-7.webp" class="gallery-overlay glightbox">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5" data-aos="fade-up" data-aos-delay="300">
                <a href="gallery.html" class="btn btn-gallery">
                    <i class="bi bi-collection me-2"></i>Lihat Semua Gallery
                </a>
            </div>

        </div>

    </section><!-- /Gallery Showcase Section -->

    <section id="rooms-2" class="rooms-2 section">
        <div class="container section-title" data-aos="fade-up">
            <span class="description-title">Produk</span>
            <h2>Produk</h2>
            <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="room-filters" data-aos="fade-up" data-aos-delay="200">
                <form method="GET" action="{{ route('portal') }}">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-3 col-md-6">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" onchange="this.form.submit()">
                                <option value="">Semua Category</option>
                                @foreach ($productCategory as $item)
                                    <option value="{{ $item->id }}"
                                        {{ request('category') == $item->id ? 'selected' : '' }}>
                                        {{ $item->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <div class="rooms-grid" data-aos="fade-up" data-aos-delay="300">
                <div class="row g-4">
                    @forelse ($products as $product)
                        <div class="col-xl-4 col-lg-6">
                            <div class="room-card">
                                <div class="room-image">
                                    <img src="{{ asset('images/logo-saraba-bisa.png') }}"
                                        alt="{{ $product->product_name }}" class="img-fluid">
                                </div>
                                <div class="room-content">
                                    <div class="room-header">
                                        <h3>
                                            @if ($product->categories_id == 1)
                                                {{ $product->product_name }} {{ $product->kondisi }} {{ $product->warna }} {{ $product->ram }} / @if ($product->capacity != null)
                                                    {{ $product->capacity->name }}
                                                @else
                                                    -
                                                @endif (IMEI {{ $product->nomor_seri }})
                                            @else
                                                {{ $product->product_name }} {{ $product->nomor_seri }}
                                            @endif
                                        </h3>
                                        <div class="room-rating">
                                            <span class="badge bg-primary">{{ $product->category_name }}</span>
                                        </div>
                                    </div>
                                    <p class="room-description">
                                        {{ Str::limit($product->keterangan ?? '-', 80) }}
                                    </p>
                                    <div class="room-footer">
                                        <div class="room-price">
                                            <span class="price-amount">
                                                Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        <a href="wa.me/{{ $toko_setting->nomor_hp_toko }}" class="btn-room-details">Pesan Sekarang</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted">Tidak ada produk ditemukan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Pagination --}}
            <div class="load-more-section" data-aos="fade-up" data-aos-delay="400">
                <div class="text-center">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>


        </div>

    </section><!-- /Rooms 2 Section -->
@endsection
