@extends('layouts.frontend.app')
@section('title', 'Detail Kendaraan')
@section('content')


    <section class="hero-wrap hero-wrap-2 js-fullheight"
        style="background-image: url('{{ asset('frontend/images/bg_3.jpg') }}');" data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text js-fullheight align-items-end justify-content-start">
                <div class="col-md-9 ftco-animate pb-5">
                    <p class="breadcrumbs"><span class="mr-2"><a href="{{ route('home') }}">Home <i
                                    class="ion-ios-arrow-forward"></i></a></span> <span>Detail Kendaraan <i
                                class="ion-ios-arrow-forward"></i></span></p>
                    <h1 class="mb-3 bread">Detail Kendaraan</h1>
                </div>
            </div>
        </div>
    </section>


    <section class="ftco-section ftco-car-details">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    <div class="car-details">
                        <div class="img rounded"
                            style="background-image: url('{{ asset('storage/' . $vehicle->vehicle_images) }}');"></div>
                        <div class="text text-center">
                            <span class="subheading">{{ $vehicle->brand }}</span>
                            <h2>{{ $vehicle->name }}</h2>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services">
                        <div class="media-body py-md-4">
                            <div class="d-flex mb-3 align-items-center">
                                <div class="icon d-flex align-items-center justify-content-center"><span
                                        class="flaticon-pistons"></span></div>
                                <div class="text">
                                    <h3 class="heading mb-0 pl-3">
                                        Transmisi
                                        <span>{{ $vehicle->transmission }}</span>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services">
                        <div class="media-body py-md-4">
                            <div class="d-flex mb-3 align-items-center">
                                <div class="icon d-flex align-items-center justify-content-center"><span
                                        class="flaticon-car-seat"></span></div>
                                <div class="text">
                                    <h3 class="heading mb-0 pl-3">
                                        Kapasitas Penumpang
                                        <span>{{ $vehicle->capacity }} Orang</span>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md d-flex align-self-stretch ftco-animate">
                    <div class="media block-6 services">
                        <div class="media-body py-md-4">
                            <div class="d-flex mb-3 align-items-center">
                                <div class="icon d-flex align-items-center justify-content-center"><span
                                        class="flaticon-diesel"></span></div>
                                <div class="text">
                                    <h3 class="heading mb-0 pl-3">
                                        Bahan Bakar
                                        <span>{{ $vehicle->fuel }}</span>
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12 pills">
                    <div class="bd-example bd-example-tabs">
                        <div class="d-flex justify-content-center">
                            <ul class="nav nav-pills" id="pills-tab" role="tablist">

                                <li class="nav-item">
                                    <a class="nav-link active" id="pills-description-tab" data-toggle="pill"
                                        href="#pills-description" role="tab" aria-controls="pills-description"
                                        aria-expanded="true">Fitur</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-manufacturer-tab" data-toggle="pill"
                                        href="#pills-manufacturer" role="tab" aria-controls="pills-manufacturer"
                                        aria-expanded="true">Deskripsi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-review-tab" data-toggle="pill" href="#pills-review"
                                        role="tab" aria-controls="pills-review" aria-expanded="true">Review</a>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-description" role="tabpanel"
                                aria-labelledby="pills-description-tab">
                                <div class="row">
                                    @php
                                        $columns = array_chunk($features, ceil(count($features) / 3));
                                    @endphp
                                    @foreach ($columns as $column)
                                        <div class="col-md-4">
                                            <ul class="features">
                                                @foreach ($column as $feature)
                                                    @php
                                                        $name = str_replace('_', ' ', $feature['name']);
                                                        $exists = $vehicle->features->contains('name', $feature['name']);
                                                    @endphp
                                                    <li class="{{ $exists ? 'check' : 'remove' }}">
                                                        <span class="{{ $exists ? 'ion-ios-checkmark' : 'ion-ios-close' }}"></span>
                                                        {{ ucwords($name) }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>                                
                            </div>

                            <div class="tab-pane fade" id="pills-manufacturer" role="tabpanel"
                                aria-labelledby="pills-manufacturer-tab">
                                <p>{{ $vehicle->description }}</p>
                            </div>

                            <div class="tab-pane fade" id="pills-review" role="tabpanel"
                                aria-labelledby="pills-review-tab">
                                <div class="row">
                                    <div class="col-md-7">
                                        <h3 class="head">23 Reviews</h3>
                                        <div class="review d-flex">
                                            <div class="user-img" style="background-image: url(images/person_1.jpg)">
                                            </div>
                                            <div class="desc">
                                                <h4>
                                                    <span class="text-left">Jacob Webb</span>
                                                    <span class="text-right">14 March 2018</span>
                                                </h4>
                                                <p class="star">
                                                    <span>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                    </span>
                                                    <span class="text-right"><a href="#" class="reply"><i
                                                                class="icon-reply"></i></a></span>
                                                </p>
                                                <p>When she reached the first hills of the Italic Mountains, she had a last
                                                    view back on the skyline of her hometown Bookmarksgrov</p>
                                            </div>
                                        </div>
                                        <div class="review d-flex">
                                            <div class="user-img" style="background-image: url(images/person_2.jpg)">
                                            </div>
                                            <div class="desc">
                                                <h4>
                                                    <span class="text-left">Jacob Webb</span>
                                                    <span class="text-right">14 March 2018</span>
                                                </h4>
                                                <p class="star">
                                                    <span>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                    </span>
                                                    <span class="text-right"><a href="#" class="reply"><i
                                                                class="icon-reply"></i></a></span>
                                                </p>
                                                <p>When she reached the first hills of the Italic Mountains, she had a last
                                                    view back on the skyline of her hometown Bookmarksgrov</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="rating-wrap">
                                            <h3 class="head">Give a Review</h3>
                                            <div class="wrap">
                                                <p class="star">
                                                    <span>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        (98%)
                                                    </span>
                                                    <span>20 Reviews</span>
                                                </p>
                                                <p class="star">
                                                    <span>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        (85%)
                                                    </span>
                                                    <span>10 Reviews</span>
                                                </p>
                                                <p class="star">
                                                    <span>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        (70%)
                                                    </span>
                                                    <span>5 Reviews</span>
                                                </p>
                                                <p class="star">
                                                    <span>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        (10%)
                                                    </span>
                                                    <span>0 Reviews</span>
                                                </p>
                                                <p class="star">
                                                    <span>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        <i class="ion-ios-star"></i>
                                                        (0%)
                                                    </span>
                                                    <span>0 Reviews</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section ftco-cart">
        <div class="container">
            <div class="row">
                <div class="col-md-12 ftco-animate">
                    <div class="car-list">
                        <table class="table">
                            <thead class="thead-primary">
                                <tr class="text-center">
                                    <th class="bg-primary heading">Per 4 Jam</th>
                                    <th class="bg-dark heading">Per 12 Jam</th>
                                    <th class="bg-black heading">Per 1 Hari</th>
                                </tr>

                            </thead>
                            <tbody>
                                <tr class="">

                                    <td class="price">
                                        <p class="btn-custom"><a href="#">Pesan Sekarang</a></p>
                                        <div class="price-rate">
                                            <h3>
                                                <span class="num">Rp. {{ number_format($vehicle->prices->price_4_hours ?? 0, 0, ",", ".") }}</span>
                                                <span class="per">/Per 4 Jam</span>
                                            </h3>
                                        </div>
                                    </td>

                                    <td class="price">
                                        <p class="btn-custom"><a href="#">Pesan Sekarang</a></p>
                                        <div class="price-rate">
                                            <h3>
                                                <span class="num">Rp. {{ number_format($vehicle->prices->price_12_hours ?? 0, 0, ",", ".") }}</span>
                                                <span class="per">/Per 12 Jam</span>
                                            </h3>
                                        </div>
                                    </td>

                                    <td class="price">
                                        <p class="btn-custom"><a href="#">Pesan Sekarang</a></p>
                                        <div class="price-rate">
                                            <h3>
                                                <span class="num">Rp. {{ number_format($vehicle->prices->price_24_hours ?? 0, 0, ",", ".") }}</span>
                                                <span class="per">/Per 1 Hari</span>
                                            </h3>
                                        </div>
                                    </td>
                                </tr><!-- END TR-->

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section class="ftco-section ftco-no-pt">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12 heading-section text-center ftco-animate mb-5">
                    <span class="subheading">Pilih Kendaraan Lain</span>
                    <h2 class="mb-2">Kendaraan yang Sesuai</h2>
                </div>
            </div>
            <div class="row">
                @foreach ($recommendation as $item)
                    <div class="col-md-4">
                        <div class="car-wrap rounded ftco-animate">
                            <div class="img rounded d-flex align-items-end"
                                style="background-image: url('{{ asset('storage/' . $item->vehicle_images) }}');">
                            </div>
                            <div class="text">
                                <h2 class="mb-0"><a href="car-single.html">{{ $item->name }}</a></h2>
                                <div class="d-flex mb-3">
                                    <span class="cat">{{ $item->brand }}</span>
                                    <p class="price ml-auto">Rp. {{ number_format($item->prices->price_24_hours ?? 0, 0, ",", ".") }} <span>/Hari</span></p>
                                </div>
                                <p class="d-flex mb-0 d-block"><a href="#" class="btn btn-primary py-2 mr-1">Pesan</a> <a href="{{ route('vehicles.show', ['id' => $item->id]) }}"
                                        class="btn btn-secondary py-2 ml-1">Detail</a></p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

@endsection
