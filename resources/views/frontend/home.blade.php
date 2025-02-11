@extends('layouts.frontend.app')
@section('title', 'Home')

@section('content')
    
    <div class="hero-wrap ftco-degree-bg" style="background-image: url('{{ asset('frontend/images/bg_1.jpg') }}');"
        data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text justify-content-start align-items-center justify-content-center">
                <div class="col-lg-8 ftco-animate">
                    <div class="text w-100 text-center mb-md-5 pb-md-5">
                        <h1 class="mb-4">Cara Cepat &amp; Mudah Untuk Menyewa Kendaraan</h1>
                        <p style="font-size: 18px;">Sebuah jalan kecil bernama Nuden melintasi area ini dan menyediakan akses
                            ke berbagai layanan penyewaan kendaraan.
                            Ini adalah kawasan yang nyaman,
                            di mana pelanggan dapat dengan mudah menemukan berbagai pilihan untuk menyewa kendaraan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="ftco-section ftco-no-pt bg-light">
        <div class="container">
            <div class="row no-gutters">
                <div class="col-md-12	featured-top">
                    <div class="row no-gutters">
                        <div class="col-md-12 d-flex align-items-center">
                            <div class="services-wrap rounded-right w-100">
                                <h3 class="heading-section mb-4">Cara yang Lebih Baik untuk Menyewa Kendaraan Sempurna Anda
                                </h3>
                                <div class="row d-flex mb-4">
                                    <div class="col-md-4 d-flex align-self-stretch ftco-animate">
                                        <div class="services w-100 text-center">
                                            <div class="icon d-flex align-items-center justify-content-center"><span
                                                    class="flaticon-route"></span></div>
                                            <div class="text w-100">
                                                <h3 class="heading mb-2">Pilih Lokasi Penjemputan</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-self-stretch ftco-animate">
                                        <div class="services w-100 text-center">
                                            <div class="icon d-flex align-items-center justify-content-center"><span
                                                    class="flaticon-handshake"></span></div>
                                            <div class="text w-100">
                                                <h3 class="heading mb-2">Pilih Penawaran Terbaik</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-flex align-self-stretch ftco-animate">
                                        <div class="services w-100 text-center">
                                            <div class="icon d-flex align-items-center justify-content-center"><span
                                                    class="flaticon-rent"></span></div>
                                            <div class="text w-100">
                                                <h3 class="heading mb-2">Pesan Kendaraan Rental Anda</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p><a href="{{ route('reservations.create') }}" class="btn btn-primary py-3 px-4">Pesan
                                        Kendaraan Sempurna Anda</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>


    <section class="ftco-section ftco-no-pt bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                    <span class="subheading">Apa yang kami tawarkan</span>
                    <h2 class="mb-2">Kendaraan dengan Fitur Biaya</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="carousel-car owl-carousel">
                        @foreach ($vehicles as $item)
                            <div class="item">
                                <div class="car-wrap rounded ftco-animate">
                                    <div class="img rounded d-flex align-items-end"
                                        style="background-image: url('{{ asset('storage/'. $item->vehicle_images) }}');">
                                    </div>
                                    <div class="text">
                                        <h2 class="mb-0"><a href="#">{{ $item->name }}</a></h2>
                                        <div class="d-flex mb-3">
                                            <span class="cat">{{ $item->brand }}</span>
                                            <p class="price ml-auto">Rp.
                                                {{ number_format($item->prices->price_24_hours ?? 0, 0, ',', '.') }}
                                                <span>/Per Hari</span></p>
                                        </div>
                                        <p class="d-flex mb-0 d-block">
                                            <a href="{{ route('reservations.create', ['id' => $item->id]) }}" class="btn btn-primary py-2 mr-1">Pesan</a>
                                            <a href="{{ route('vehicles.show', ['id' => $item->id]) }}"
                                                class="btn btn-secondary py-2 ml-1">Details</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
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

                        <p>Sebuah jalan kecil bernama Nuden melintasi area ini dan menyediakan akses ke berbagai layanan
                            penyewaan kendaraan. </p>

                        <p>Di sini, pelanggan dapat menemukan berbagai pilihan untuk menyewa mobil dan motor dalam suasana
                            yang nyaman dan menyenangkan.
                            Dalam perjalanannya, pelanggan akan diperhatikan oleh staf kami yang akan memberikan informasi
                            lengkap dan memastikan pengalaman penyewaan yang memuaskan.
                            Jika pelanggan bertanya tentang prosedur atau syarat, kami akan dengan senang hati memberikan
                            penjelasan dan membantu mereka kembali dengan kendaraan yang mereka butuhkan.
                            Ini adalah kawasan yang ideal untuk menemukan kendaraan sewaan dengan layanan yang ramah dan
                            profesional.</p>
                        <p><a href="{{ route('vehicles.index') }}" class="btn btn-primary py-3 px-4">Cari Kendaraan</a></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-md-7 text-center heading-section ftco-animate">
                    <span class="subheading">Layanan</span>
                    <h2 class="mb-3">Layanan Terbaru Kami</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="services services-2 w-100 text-center">
                        <div class="icon d-flex align-items-center justify-content-center"><span
                                class="flaticon-wedding-car"></span></div>
                        <div class="text w-100">
                            <h3 class="heading mb-2">Upacara Pernikahan</h3>
                            <p>Ciptakan momen istimewa dengan layanan penyewaan Mobil kami untuk pernikahan Anda.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="services services-2 w-100 text-center">
                        <div class="icon d-flex align-items-center justify-content-center"><span
                                class="flaticon-transportation"></span></div>
                        <div class="text w-100">
                            <h3 class="heading mb-2">Antar Jemput Kota</h3>
                            <p>Rasakan kenyamanan antar jemput kota dengan layanan kami yang cepat dan efisien.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="services services-2 w-100 text-center">
                        <div class="icon d-flex align-items-center justify-content-center"><span
                                class="flaticon-car"></span></div>
                        <div class="text w-100">
                            <h3 class="heading mb-2">Antar Jemput Bandara</h3>
                            <p>Nikmati kemudahan dan kenyamanan dengan layanan antar-jemput bandara kami.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="services services-2 w-100 text-center">
                        <div class="icon d-flex align-items-center justify-content-center"><span
                                class="flaticon-transportation"></span></div>
                        <div class="text w-100">
                            <h3 class="heading mb-2">Tur Seluruh Kota</h3>
                            <p>Jelajahi kota dengan nyaman melalui layanan tur seluruh kota kami.</p>
                        </div>
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
                            <strong class="number" data-number="10">0</strong>
                            <span>Tahun <br>Pengalaman</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
                    <div class="block-18">
                        <div class="text text-border d-flex align-items-center">
                            <strong class="number" data-number="{{ $totalVehicle }}">0</strong>
                            <span>Jumlah <br>Kendaraan</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 justify-content-center counter-wrap ftco-animate">
                    <div class="block-18">
                        <div class="text text-border d-flex align-items-center">
                            <strong class="number" data-number="{{ $totalUser }}">0</strong>
                            <span>Pelanggan <br>yang Bahagia</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        let a = "{{ session('successCanceled') }}"
        if (a){
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Berhasil Membatalkan Reservasi',
                confirmButtonText: 'OK'
            });
        }
        
    </script>
@endpush