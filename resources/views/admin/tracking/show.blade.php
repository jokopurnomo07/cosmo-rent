@extends('layouts.admin.app')
@section('title', 'Detail Tracking')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-8 order-md-1 order-last">
                    <h3>Detail Tracking - {{ $rental->vehicle->name }}</h3>
                    <p class="text-subtitle text-muted">Penyewa: {{ $rental->user->name }}</p>
                </div>
            </div>
        </div>

        <section class="section">
            @if(isset($showTracking) && $showTracking)
                <div class="card">
                    <div class="card-body">
                        <div id="map" style="height:520px;width:100%;"></div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <button id="btn-refresh" class="btn btn-sm btn-outline-secondary">Refresh</button>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info mb-0">Tracking hanya tersedia untuk kendaraan yang sedang disewa (status paid/ongoing) atau saat kendaraan berstatus <strong>rented</strong>.</div>
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        .leaflet-div-icon.car-icon { background: transparent; border: none; }
        .leaflet-div-icon.car-icon div { transform: translateY(-2px); font-size:28px; }
    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        const rentalId = {{ $rental->id }};
        const showTracking = {{ (isset($showTracking) && $showTracking) ? 'true' : 'false' }};

        if (showTracking) {
            // custom car icon using emoji for clarity
            const carIcon = L.divIcon({
                html: '<div style="font-size:28px; line-height:28px;">🚗</div>',
                className: 'leaflet-div-icon car-icon',
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            const map = L.map('map').setView([{{ $currentLocation?->latitude ?? ($rental->latitude ?? -6.2088) }}, {{ $currentLocation?->longitude ?? ($rental->longitude ?? 106.8456) }}], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

            let polyline = null;
            let marker = null;

            async function loadHistory() {
                const res = await fetch(`{{ route('admin.tracking.history', ['rental_id' => $rental->id]) }}`);
                const data = await res.json();

                if (!data.success) return;

                const points = data.data.map(p => [parseFloat(p.latitude), parseFloat(p.longitude)]);

                if (polyline) map.removeLayer(polyline);
                if (points.length) {
                    polyline = L.polyline(points, { color: 'blue' }).addTo(map);

                    const last = points[points.length - 1];
                    if (marker) map.removeLayer(marker);
                    if (last) {
                        marker = L.marker(last, { icon: carIcon }).addTo(map).bindPopup('Posisi terakhir').openPopup();
                        map.fitBounds(polyline.getBounds().pad(0.3));
                    }
                } else {
                    // no points yet
                    if (marker) map.removeLayer(marker);
                }
            }

            document.getElementById('btn-refresh').addEventListener('click', loadHistory);

            // Polling every 10 seconds for near-real-time feel
            loadHistory();
            setInterval(loadHistory, 10000);
        }
    </script>
@endpush
