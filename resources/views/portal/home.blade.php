@extends('portal.layouts.app')

@section('content')
<style>
    /* Custom CSS untuk Halaman Pilih Cabang */
    .branch-section {
        min-height: 100vh;
        display: flex;
        align-items: center;
        background: #f3f5fa;
        padding: 80px 0;
    }
    
    .section-header h2 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 10px;
    }
    
    .section-header p {
        color: #7f8c8d;
        font-size: 1.1rem;
    }

    /* Card Styling */
    .branch-card {
        background: #fff;
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        transition: all 0.4s ease;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        height: 100%;
        position: relative;
        overflow: hidden;
        cursor: pointer;
        display: block;
        text-decoration: none;
    }

    .branch-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border-color: var(--bs-primary);
    }

    .branch-icon {
        width: 80px;
        height: 80px;
        background: rgba(var(--bs-primary-rgb), 0.1);
        color: var(--bs-primary);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2rem;
        transition: all 0.4s ease;
        overflow: hidden; /* PENTING: Agar gambar tidak keluar dari lingkaran */
    }

    /* TAMBAHAN: Agar gambar pas sebesar lingkaran */
    .branch-icon img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Agar gambar tidak gepeng/terdistorsi */
        display: block;
    }

    .branch-card:hover .branch-icon {
        /* background: var(--bs-primary); */
        color: #fff;
        transform: scale(1.1) rotate(5deg);
    }

    .branch-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #34495e;
        margin-bottom: 10px;
    }

    .branch-address {
        font-size: 0.9rem;
        color: #95a5a6;
        line-height: 1.5;
    }

    .btn-choose {
        margin-top: 20px;
        padding: 8px 25px;
        border-radius: 50px;
        font-weight: 600;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.4s ease;
    }

    .branch-card:hover .btn-choose {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<section id="branch-selection" class="branch-section">
    <div class="container">
        
        <div class="text-center mb-5 section-header" data-aos="fade-down">
            <h2>Pilih Lokasi Cabang</h2>
            <p>Silakan pilih cabang terdekat untuk layanan terbaik kami</p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach ($cabang as $item)
                @php

                    if ($item->id == 1) {
                        $kepalaToko = \App\Models\User::where('cabang_id',$item->id)->where('role','Kepala Toko')->orderBy('id','asc')->first();
                    }else{
                        $kepalaToko = \App\Models\User::where('cabang_id',$item->id)->where('id','!=',1)->where('role','Kepala Toko')->orderBy('id','asc')->first();
                        // dd($data['kepala_toko_setting']);
                    }
                @endphp

                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                    <a href="{{ route('portal.branch', $item->id) }}" class="branch-card">
                        
                        <div class="branch-icon">
                            @if ($kepalaToko && $kepalaToko->profile_photo_path)
                                <img src="{{ asset('storage/' . $kepalaToko->profile_photo_path) }}" 
                                     alt="{{ $item->nama_cabang }}">
                            @else
                                <i class="bi bi-geo-alt-fill"></i>
                            @endif
                        </div>
                        
                        <h3 class="branch-title">{{ $item->nama_cabang }}</h3>
                        
                        <span class="btn btn-primary btn-choose">
                            Kunjungi Toko <i class="bi bi-arrow-right ms-1"></i>
                        </span>
                    </a>
                </div>
            @endforeach
        </div>

    </div>
</section>
@endsection