@extends('layouts.frontend.app')
@section('title', 'Detail Kendaraan')

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
                        <span>
                            <a href="{{ route('vehicles.index') }}">Kendaraan <i class="ion-ios-arrow-forward"></i></a>
                        </span>
                        <span>Detail Kendaraan <i class="ion-ios-arrow-forward"></i></span>
                    </p>
                    <h1 class="mb-3 bread">Detail Kendaraan</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section ftco-car-details">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-12">
                    @php
                        $hasImage = $vehicle->vehicle_images
                            && Storage::disk('public')->exists($vehicle->vehicle_images);
                        $imageUrl = $hasImage
                            ? asset('storage/' . $vehicle->vehicle_images)
                            : asset('frontend/images/placeholder-vehicle.jpg');

                        $badgeConfig = match($vehicle->status) {
                            'available'   => ['label' => 'Tersedia',           'color' => '#28a745'],
                            'rented'      => ['label' => 'Sedang Disewa',      'color' => '#dc3545'],
                            'maintenance' => ['label' => 'Sedang Maintenance', 'color' => '#fd7e14'],
                            default       => ['label' => 'Tidak Tersedia',     'color' => '#6c757d'],
                        };
                    @endphp
                    <div class="car-details">
                        <div style="position: relative;">
                            <div class="img rounded"
                                 style="background-image: url('{{ $imageUrl }}');"></div>
                            {{-- Badge availability di atas gambar detail --}}
                            <span style="
                                position: absolute;
                                top: 16px;
                                left: 16px;
                                background-color: {{ $badgeConfig['color'] }};
                                color: #fff;
                                font-size: 13px;
                                font-weight: 600;
                                padding: 5px 16px;
                                border-radius: 20px;
                                letter-spacing: 0.5px;
                                box-shadow: 0 2px 6px rgba(0,0,0,0.2);
                            ">
                                {{ $badgeConfig['label'] }}
                            </span>
                        </div>
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
                                <div class="icon d-flex align-items-center justify-content-center">
                                    <span class="flaticon-pistons"></span>
                                </div>
                                <div class="text">
                                    <h3 class="heading mb-0 pl-3">
                                        Transmisi
                                        <span>
                                            @if ($vehicle->transmission == 'manual')
                                                Manual
                                            @elseif ($vehicle->transmission == 'automatic' || $vehicle->transmission == 'otomatic')
                                                Otomatis / Matic
                                            @else
                                                {{ ucfirst($vehicle->transmission) }}
                                            @endif
                                        </span>
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
                                <div class="icon d-flex align-items-center justify-content-center">
                                    <span class="flaticon-car-seat"></span>
                                </div>
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
                                <div class="icon d-flex align-items-center justify-content-center">
                                    <span class="flaticon-diesel"></span>
                                </div>
                                <div class="text">
                                    <h3 class="heading mb-0 pl-3">
                                        Bahan Bakar
                                        <span>{{ ucwords($vehicle->fuel) }}</span>
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
                                    <a class="nav-link active" id="pills-description-tab"
                                       data-toggle="pill" href="#pills-description" role="tab">Fitur</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="pills-manufacturer-tab"
                                       data-toggle="pill" href="#pills-manufacturer" role="tab">Deskripsi</a>
                                </li>
                            </ul>
                        </div>

                        <div class="tab-content" id="pills-tabContent">
                            {{-- Tab Fitur --}}
                            <div class="tab-pane fade show active" id="pills-description" role="tabpanel">
                                @if (!empty($features))
                                    @php
                                        $columns = array_chunk($features, ceil(count($features) / 3));
                                    @endphp
                                    <div class="row">
                                        @foreach ($columns as $column)
                                            <div class="col-md-4">
                                                <ul class="features">
                                                    @foreach ($column as $feature)
                                                        @php
                                                            $name   = str_replace('_', ' ', $feature['name']);
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
                                @else
                                    <p class="text-center text-muted mt-3">Belum ada fitur yang tersedia.</p>
                                @endif
                            </div>

                            {{-- Tab Deskripsi --}}
                            <div class="tab-pane fade" id="pills-manufacturer" role="tabpanel">
                                <p>{{ $vehicle->description ?? 'Deskripsi belum tersedia.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Tabel Harga --}}
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
                                <tr>
                                    <td class="price">
                                        {{-- Tombol pesan hanya aktif jika available --}}
                                        <p class="btn-custom">
                                            @if ($vehicle->status === 'available')
                                                <a href="{{ route('reservations.create', ['id' => $vehicle->id]) }}">
                                                    Pesan Sekarang
                                                </a>
                                            @else
                                                <span style="color: #6c757d; cursor: not-allowed;">
                                                    {{ $badgeConfig['label'] }}
                                                </span>
                                            @endif
                                        </p>
                                        <div class="price-rate">
                                            <h3>
                                                <span class="num">Rp. {{ number_format($vehicle->prices->price_4_hours ?? 0, 0, ',', '.') }}</span>
                                                <span class="per">/Per 4 Jam</span>
                                            </h3>
                                        </div>
                                    </td>
                                    <td class="price">
                                        <p class="btn-custom">
                                            @if ($vehicle->status === 'available')
                                                <a href="{{ route('reservations.create', ['id' => $vehicle->id]) }}">
                                                    Pesan Sekarang
                                                </a>
                                            @else
                                                <span style="color: #6c757d; cursor: not-allowed;">
                                                    {{ $badgeConfig['label'] }}
                                                </span>
                                            @endif
                                        </p>
                                        <div class="price-rate">
                                            <h3>
                                                <span class="num">Rp. {{ number_format($vehicle->prices->price_12_hours ?? 0, 0, ',', '.') }}</span>
                                                <span class="per">/Per 12 Jam</span>
                                            </h3>
                                        </div>
                                    </td>
                                    <td class="price">
                                        <p class="btn-custom">
                                            @if ($vehicle->status === 'available')
                                                <a href="{{ route('reservations.create', ['id' => $vehicle->id]) }}">
                                                    Pesan Sekarang
                                                </a>
                                            @else
                                                <span style="color: #6c757d; cursor: not-allowed;">
                                                    {{ $badgeConfig['label'] }}
                                                </span>
                                            @endif
                                        </p>
                                        <div class="price-rate">
                                            <h3>
                                                <span class="num">Rp. {{ number_format($vehicle->prices->price_24_hours ?? 0, 0, ',', '.') }}</span>
                                                <span class="per">/Per 1 Hari</span>
                                            </h3>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Rekomendasi Kendaraan --}}
    @if ($recommendation->isNotEmpty())
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
                        @php
                            $recHasImage = $item->vehicle_images
                                && Storage::disk('public')->exists($item->vehicle_images);
                            $recImageUrl = $recHasImage
                                ? asset('storage/' . $item->vehicle_images)
                                : asset('frontend/images/placeholder-vehicle.jpg');

                            $recBadge = match($item->status) {
                                'available'   => ['label' => 'Tersedia',           'color' => '#28a745'],
                                'rented'      => ['label' => 'Sedang Disewa',      'color' => '#dc3545'],
                                'maintenance' => ['label' => 'Sedang Maintenance', 'color' => '#fd7e14'],
                                default       => ['label' => 'Tidak Tersedia',     'color' => '#6c757d'],
                            };
                        @endphp
                        <div class="col-md-4">
                            <div class="car-wrap rounded ftco-animate">
                                <div class="img rounded d-flex align-items-end"
                                     style="background-image: url('{{ $recImageUrl }}'); position: relative;">
                                    <span style="
                                        position: absolute;
                                        top: 12px;
                                        left: 12px;
                                        background-color: {{ $recBadge['color'] }};
                                        color: #fff;
                                        font-size: 11px;
                                        font-weight: 600;
                                        padding: 3px 10px;
                                        border-radius: 20px;
                                        letter-spacing: 0.5px;
                                        box-shadow: 0 1px 4px rgba(0,0,0,0.2);
                                    ">
                                        {{ $recBadge['label'] }}
                                    </span>
                                </div>
                                <div class="text">
                                    <h2 class="mb-0">
                                        <a href="{{ route('vehicles.show', ['id' => $item->id]) }}">
                                            {{ $item->name }}
                                        </a>
                                    </h2>
                                    <div class="d-flex mb-3">
                                        <span class="cat">{{ $item->brand }}</span>
                                        <p class="price ml-auto">
                                            Rp. {{ number_format($item->prices->price_24_hours ?? 0, 0, ',', '.') }}
                                            <span>/Hari</span>
                                        </p>
                                    </div>
                                    <p class="d-flex mb-0 d-block">
                                        @if ($item->status === 'available')
                                            <a href="{{ route('reservations.create', ['id' => $item->id]) }}"
                                               class="btn btn-primary py-2 mr-1">Pesan</a>
                                        @else
                                            <button class="btn btn-secondary py-2 mr-1" disabled>Pesan</button>
                                        @endif
                                        <a href="{{ route('vehicles.show', ['id' => $item->id]) }}"
                                           class="btn btn-secondary py-2 ml-1">Detail</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@endsection