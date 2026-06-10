@php use Illuminate\Support\Facades\Storage; @endphp
@extends('layouts.frontend.app')
@section('title', 'Armada Kami')

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
                        <span>Kendaraan <i class="ion-ios-arrow-forward"></i></span>
                    </p>
                    <h1 class="mb-3 bread">Pilih Kendaraanmu</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section bg-light">
        <div class="container">
            <div class="row">
                @foreach ($vehicles as $item)
                    @php
                        $hasImage = $item->vehicle_images
                            && Storage::disk('public')->exists($item->vehicle_images);
                        $imageUrl = $hasImage
                            ? asset('storage/' . $item->vehicle_images)
                            : asset('frontend/images/placeholder-vehicle.jpg');

                        // Tentukan badge berdasarkan status
                        $badgeConfig = match($item->status) {
                            'available'   => ['label' => 'Tersedia',          'color' => '#28a745'],
                            'rented'      => ['label' => 'Sedang Disewa',     'color' => '#dc3545'],
                            'maintenance' => ['label' => 'Sedang Maintenance','color' => '#fd7e14'],
                            default       => ['label' => 'Tidak Tersedia',    'color' => '#6c757d'],
                        };
                    @endphp
                    <div class="col-md-4">
                        <div class="car-wrap rounded ftco-animate">
                            {{-- Badge availability di atas gambar --}}
                            <div class="img rounded d-flex align-items-end"
                                 style="background-image: url('{{ $imageUrl }}'); position: relative;">
                                <span style="
                                    position: absolute;
                                    top: 12px;
                                    left: 12px;
                                    background-color: {{ $badgeConfig['color'] }};
                                    color: #fff;
                                    font-size: 11px;
                                    font-weight: 600;
                                    padding: 3px 10px;
                                    border-radius: 20px;
                                    letter-spacing: 0.5px;
                                    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
                                ">
                                    {{ $badgeConfig['label'] }}
                                </span>
                            </div>
                            <div class="text">
                                <h2 class="mb-0">
                                    <a href="{{ route('vehicles.show', ['id' => $item->id]) }}">{{ $item->name }}</a>
                                </h2>
                                <div class="d-flex mb-3">
                                    <span class="cat">{{ $item->brand }}</span>
                                    <p class="price ml-auto">
                                        Rp. {{ number_format($item->prices->price_24_hours ?? 0, 0, ',', '.') }}
                                        <span>/ Hari</span>
                                    </p>
                                </div>
                                <p class="d-flex mb-0 d-block">
                                    {{-- Tombol pesan hanya aktif jika status available --}}
                                    @if ($item->status === 'available')
                                        <a href="{{ route('reservations.create', ['id' => $item->id]) }}"
                                           class="btn btn-primary py-2 mr-1">Pesan</a>
                                    @else
                                        <button class="btn btn-secondary py-2 mr-1" disabled
                                                title="{{ $badgeConfig['label'] }}">
                                            Pesan
                                        </button>
                                    @endif
                                    <a href="{{ route('vehicles.show', ['id' => $item->id]) }}"
                                       class="btn btn-secondary py-2 ml-1">Detail</a>
                                </p>
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