@extends('layouts.frontend.app')
@section('title', 'Armada Kami')

@section('content')


    <section class="hero-wrap hero-wrap-2 js-fullheight" style="background-image: url('{{ asset('frontend/images/bg_3.jpg') }}');"
        data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text js-fullheight align-items-end justify-content-start">
                <div class="col-md-9 ftco-animate pb-5">
                    <p class="breadcrumbs"><span class="mr-2"><a href="index.html">Home <i
                                    class="ion-ios-arrow-forward"></i></a></span> <span>Kendaraan <i
                                class="ion-ios-arrow-forward"></i></span></p>
                    <h1 class="mb-3 bread">Pilih Kendaraanmu</h1>
                </div>
            </div>
        </div>
    </section>


    <section class="ftco-section bg-light">
        <div class="container">
            <div class="row">
                @foreach ($vehicles as $item)
                <div class="col-md-4">
                    <div class="car-wrap rounded ftco-animate">
                        <div class="img rounded d-flex align-items-end" style="background-image: url('{{ asset('storage/' . $item->vehicle_images) }}');">
                        </div>
                        <div class="text">
                            <h2 class="mb-0"><a href="{{ route('vehicles.show', ['id' => $item->id]) }}">{{ $item->name }}</a></h2>
                            <div class="d-flex mb-3">
                                <span class="cat">{{ $item->brand }}</span>
                                <p class="price ml-auto">Rp. {{ number_format($item->prices->price_24_hours ?? 0, 0, ",", ".") }}<span>/ Hari</span></p>
                            </div>
                            <p class="d-flex mb-0 d-block"><a href="{{ route('reservations.create', ['id' => $item->id]) }}" class="btn btn-primary py-2 mr-1">Pesan</a>
                                <a href="{{ route('vehicles.show', ['id' => $item->id]) }}" class="btn btn-secondary py-2 ml-1">Detail</a></p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="row mt-5">
                <div class="col text-center">
                    <div class="block-27">
                        {{ $vehicles->links('vendor.pagination.frontend') }}
                    </div>
                </div>
            </div>            
        </div>
    </section>

@endsection
