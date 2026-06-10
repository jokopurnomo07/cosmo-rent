@extends('layouts.admin.app')
@section('title', 'Tracking Armada')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Tracking Armada</h3>
                    <p class="text-subtitle text-muted">Peta posisi kendaraan aktif (demo)</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first"></div>
            </div>
        </div>

        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div id="map" style="height: 520px; width: 100%;"></div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Daftar Rental Aktif</h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>TRX</th>
                            <th>Kendaraan</th>
                            <th>Penyewa</th>
                            <th>Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($activeRentals as $r)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $r->trx_id }}</td>
                                <td>{{ $r->vehicle->name }}</td>
                                <td>{{ $r->user->name }}</td>
                                <td>
                                    @php($loc = \App\Models\RentalLocationLog::getLatestForRental($r->id))
                                    {{ $loc?->updated_at?->diffForHumans() ?? 'Belum ada' }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.tracking.show', $r->id) }}" class="btn btn-sm btn-primary">Lihat</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-sA+e2m0b9jv0x1F9sA/2w9jV6g9YkNf9v+1qkG6k9h0=" crossorigin=""/>
    <style>
        .leaflet-div-icon.car-icon { background: transparent; border: none; }
        .leaflet-div-icon.car-icon div { transform: translateY(-2px); }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-QV4q7s9tXgk9Yf0cKJr0J8eYp6y8q1zF6vG9p8d4aTA=" crossorigin=""></script>
    <script>
        const rentalLocations = @json($rentalLocations);

        const map = L.map('map').setView([-6.200000, 106.816666], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        // custom car icon
        const carIcon = L.divIcon({
            html: '<div style="font-size:24px; line-height:24px;">🚗</div>',
            className: 'leaflet-div-icon car-icon',
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        const markers = [];
        rentalLocations.forEach(loc => {
            const m = L.marker([loc.latitude, loc.longitude], { icon: carIcon }).addTo(map)
                .bindPopup(`<strong>${loc.vehicle_name}</strong><br/>${loc.user_name}<br/>${loc.address || ''}`);
            markers.push(m);
        });

        if (markers.length) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.3));
        }
    </script>
@endpush
