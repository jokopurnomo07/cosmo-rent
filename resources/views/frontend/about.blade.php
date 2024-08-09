@extends('layouts.frontend.app')
@section('title', 'Tentang Kami')

@section('content')

    <section class="hero-wrap hero-wrap-2 js-fullheight" style="background-image: url('{{ asset('frontend/images/bg_3.jpg') }}');"
        data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text js-fullheight align-items-end justify-content-start">
                <div class="col-md-9 ftco-animate pb-5">
                    <p class="breadcrumbs"><span class="mr-2"><a href="{{ route('home') }}">Home <i
                                    class="ion-ios-arrow-forward"></i></a></span> <span>Tentang Kami <i
                                class="ion-ios-arrow-forward"></i></span></p>
                    <h1 class="mb-3 bread">Tentang Kami</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section ftco-about">
        <div class="container">
            <div class="row no-gutters">
                <div class="col-md-6 p-md-5 img img-2 d-flex justify-content-center align-items-center"
                    style="background-image: url('{{ asset('frontend/images/about.jpg') }}');">
                </div>
                <div class="col-md-6 wrap-about ftco-animate">
                    <div class="heading-section heading-section-white pl-md-5">
                        <span class="subheading">Tentang Kami</span>
                        <h2 class="mb-4">Selamat Datang di Cosmo Rent</h2>

                        <p>Sebuah jalan kecil bernama Nuden melintasi area ini dan menyediakan akses ke berbagai layanan penyewaan kendaraan. </p>
                            
                        <p>Di sini, pelanggan dapat menemukan berbagai pilihan untuk menyewa mobil dan motor dalam suasana yang nyaman dan menyenangkan. 
                            Dalam perjalanannya, pelanggan akan diperhatikan oleh staf kami yang akan memberikan informasi lengkap dan memastikan pengalaman penyewaan yang memuaskan. 
                            Jika pelanggan bertanya tentang prosedur atau syarat, kami akan dengan senang hati memberikan penjelasan dan membantu mereka kembali dengan kendaraan yang mereka butuhkan. 
                            Ini adalah kawasan yang ideal untuk menemukan kendaraan sewaan dengan layanan yang ramah dan profesional.</p>It
                            is a paradisematic country, in which roasted parts of sentences fly into your mouth.</p>
                        <p><a href="{{ route('vehicles.index') }}" class="btn btn-primary py-3 px-4">Cari Kendaraan</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-counter ftco-section img bg-light" id="section-counter">
        <div class="overlay"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
                    <div class="block-18">
                        <div class="text text-border d-flex align-items-center">
                            <strong class="number" data-number="5">0</strong>
                            <span>Tahun <br>Pengalaman</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
                    <div class="block-18">
                        <div class="text text-border d-flex align-items-center">
                            <strong class="number" data-number="10">0</strong>
                            <span>Jumlah <br>Kendaraan</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
                    <div class="block-18">
                        <div class="text text-border d-flex align-items-center">
                            <strong class="number" data-number="2">0</strong>
                            <span>Pelanggan <br>yang Bahagia</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection
