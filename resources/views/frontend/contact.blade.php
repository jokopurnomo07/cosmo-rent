@extends('layouts.frontend.app')
@section('title', 'Kontak Kami')

@section('content')

    <section class="hero-wrap hero-wrap-2 js-fullheight"
             style="background-image: url('{{ asset('frontend/images/bg_3.jpg') }}');"
             data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text js-fullheight align-items-end justify-content-start">
                <div class="col-md-9 ftco-animate pb-5">
                    <p class="breadcrumbs">
                        <span class="mr-2">
                            <a href="{{ route('home') }}">Home <i class="ion-ios-arrow-forward"></i></a>
                        </span>
                        <span>Kontak <i class="ion-ios-arrow-forward"></i></span>
                    </p>
                    <h1 class="mb-3 bread">Kontak Kami</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section contact-section">
        <div class="container">

            {{-- Info Kontak: full width karena form dihapus --}}
            <div class="row d-flex mb-5 contact-info justify-content-center">
                <div class="col-md-4">
                    <div class="border w-100 p-4 rounded mb-3 d-flex">
                        <div class="icon mr-3">
                            <span class="icon-map-o"></span>
                        </div>
                        <p class="mb-0">
                            <span>Alamat:</span><br>
                            Jl. Nusa Indah No.3, RT.3/RW.1,
                            Melawai, Kec. Kebayoran Baru,
                            Jakarta Selatan 12160
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border w-100 p-4 rounded mb-3 d-flex">
                        <div class="icon mr-3">
                            <span class="icon-mobile-phone"></span>
                        </div>
                        <p class="mb-0">
                            <span>Nomor HP:</span><br>
                            <a href="tel:+6281294734527">+62 812-9473-4527</a>
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border w-100 p-4 rounded mb-3 d-flex">
                        <div class="icon mr-3">
                            <span class="icon-envelope-o"></span>
                        </div>
                        <p class="mb-0">
                            <span>Email:</span><br>
                            <a href="mailto:cosmorentcar.co.id@gmail.com">
                                cosmorentcar.co.id@gmail.com
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-12">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.0!2d106.7985!3d-6.2441!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNsKwMTQnMzguOCJTIDEwNsKwNDcnNTQuNiJF!5e0!3m2!1sid!2sid!4v1"
                        width="100%"
                        height="450"
                        style="border: 0; border-radius: 8px;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Lokasi Cosmo Rent">
                    </iframe>
                </div>
            </div>

        </div>
    </section>

@endsection