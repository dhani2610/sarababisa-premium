@extends('portal.layouts.app')

@section('content')
    <!-- Hotel Hero Section -->

    <section id="hotel-hero" class="hotel-hero section">

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="row gy-4 align-items-center">

                <div class="col-lg-6" data-aos="fade-right" data-aos-delay="200">
                    <div class="hero-content">
                        <h1>{{ $kepala_toko_setting->nama_toko }}</h1>
                        <p class="lead">{{ $kepala_toko_setting->deskripsi_toko }} </p>
                        <div class="hero-buttons">
                            <a href="https://wa.me/{{ $kepala_toko_setting->nomor_hp_toko }}"
                                class="btn btn-primary main-layout-setting">Hubungi Kami</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
                    <div class="hero-images">
                        <div class="main-image">
                            @if (empty($kepala_toko_setting->foto_portal))
                                <img src="{{ asset('images/bg-auth.jpg') }}" alt="Portal foto" class="img-fluid">
                            @else
                                <img src="{{ Storage::url($kepala_toko_setting->foto_portal) }}" alt="Portal foto"
                                    class="img-fluid">
                            @endif
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
                                {{-- <div class="guest-info">
                                    <span>Mulai dari Rp 150.000</span>
                                </div> --}}
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <div class="hero-stats" data-aos="fade-up">
                <div class="row text-center">
                    <div class="col-md-6 col-6">
                        <div class="stat-item">
                            <span class="stat-number purecounter main-color-text-layout-setting" data-purecounter-start="0"
                                data-purecounter-end="{{ $pelanggan }}"
                                data-purecounter-duration="1">{{ $pelanggan }}</span>
                            <span class="stat-label">Pelanggan</span>
                        </div>
                    </div>
                    <div class="col-md-6 col-6">
                        <div class="stat-item">
                            <span class="stat-number purecounter main-color-text-layout-setting" data-purecounter-start="0"
                                data-purecounter-end="{{ $total_products }}"
                                data-purecounter-duration="1">{{ $total_products }}</span>
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
            <div class="container section-title" data-aos="fade-up">
                <span class="description-title">Foto</span>
                <h2>Foto</h2>
            </div><!-- End Section Title -->

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
                "576": { "slidesPerView": 2, "centeredSlides": false },
                "768": { "slidesPerView": 3, "centeredSlides": false },
                "992": { "slidesPerView": 4, "centeredSlides": false },
                "1200": { "slidesPerView": 5, "centeredSlides": false }
              }
            }
            </script>

                <div class="swiper-wrapper">
                    @foreach ($galleries as $item)
                        <div class="swiper-slide">
                            <div class="gallery-item">
                                <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->title }}"
                                    class="img-fluid" loading="lazy">
                                <a href="{{ asset('storage/' . $item->foto) }}" class="gallery-overlay glightbox"
                                    data-gallery="gallery-showcase">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                            <!-- Judul 1 baris saja -->
                            <p class="text-xs text-center mt-1"
                                style="max-width: 100%; margin: 0 auto; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                title="{{ $item->title }}">
                                {{ $item->title }}
                            </p>
                        </div>
                    @endforeach
                </div>


            </div>

        </div>
    </section>


    <section id="rooms-2" class="rooms-2 section">
        <div class="container section-title" data-aos="fade-up">
            <span class="description-title">Produk</span>
            <h2>Produk</h2>
        </div><!-- End Section Title -->

        <div class="container" data-aos="fade-up" data-aos-delay="100">

            <div class="room-filters" data-aos="fade-up" data-aos-delay="200">
                <form method="GET" action="{{ route('portal.branch', empty($current_cabang) ? 1 : $current_cabang->id) }}">
                    <div class="row g-3 align-items-end">
                        {{-- Category --}}
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Kategori</label>
                            <select class="form-select" name="category" onchange="this.form.submit()">
                                <option value="">Semua Kategori</option>
                                @foreach ($productCategory as $item)
                                    <option value="{{ $item->id }}"
                                        {{ request('category') == $item->id ? 'selected' : '' }}>
                                        {{ $item->category_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Search --}}
                        <div class="col-lg-4 col-md-6">
                            <label class="form-label">Cari Produk</label>
                            <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                                placeholder="Cari nama produk...">
                        </div>

                        {{-- Show per page --}}
                        <div class="col-lg-2 col-md-6">
                            <label class="form-label">Show</label>
                            <select class="form-select" name="show" onchange="this.form.submit()">
                                @foreach ([3, 5, 10, 20] as $limit)
                                    <option value="{{ $limit }}"
                                        {{ request('show', 3) == $limit ? 'selected' : '' }}>
                                        {{ $limit }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tombol submit (kalau mau manual search) --}}
                        <div class="col-lg-2 col-md-6">
                            <button type="submit" class="btn btn-primary main-layout-setting w-100">Cari</button>
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
                                    @if (!empty($product->foto))
                                        <img src="{{ Storage::url($product->foto) }}" alt="{{ $product->product_name }}"
                                            class="img-fluid">
                                    @else
                                        <img src="{{ asset('images/logo-saraba-bisa.png') }}"
                                            alt="{{ $product->product_name }}" class="img-fluid">
                                    @endif
                                </div>
                                <div class="room-content">
                                    <div class="room-header">
                                        <h3>
                                            @if ($product->categories_id == 1)
                                                {{ $product->product_name }} {{ $product->kondisi }}
                                                {{ $product->warna }} {{ $product->ram }} / @if ($product->capacity != null)
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
                                            <span class="price-amount main-color-text-layout-setting">
                                                Rp {{ number_format($product->harga_jual, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        <a href="https://wa.me/{{ $kepala_toko_setting->nomor_hp_toko }}"  target="_blank"
                                            class="btn-room-details main-layout-setting">Pesan Sekarang</a>
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
