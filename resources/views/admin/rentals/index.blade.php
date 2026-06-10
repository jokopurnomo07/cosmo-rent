@extends('layouts.admin.app')
@section('title', 'Data Penyewaan')

@section('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last"></div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Data Penyewaan</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">
                        Data Penyewaan
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th style="width: 5%;">No</th>
                                <th style="width: 25%;" class="text-truncate">Trx ID</th>
                                <th style="width: 25%;" class="text-truncate">Nama Pemesan</th>
                                <th class="d-none d-md-table-cell" style="width: 15%;">Tanggal Sewa</th>
                                <th class="d-none d-md-table-cell" style="width: 15%;">Tanggal Selesai</th>
                                <th class="d-none d-lg-table-cell" style="width: 20%;" class="text-truncate">Email Pemesan</th>
                                <th class="d-none d-lg-table-cell" style="width: 15%;">No HP Pemesan</th>
                                <th style="width: 10%;">Status</th>
                                <th style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rentals as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $item->trx_id }}</td>
                                    <td class="text-truncate" style="max-width: 100px;">
                                        {{ $item->user_id != null ? $item->user->name : $item->nama_guest }}
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        {{ date('d-m-Y', strtotime($item->start_date)) }}
                                        @if ($item->vehicle && $item->vehicle->type === 'motorcycle' && $item->time_pickup)
                                            <br><small class="text-muted">{{ $item->time_pickup }}</small>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        {{ date('d-m-Y', strtotime($item->end_date)) }}
                                        @if ($item->vehicle && $item->vehicle->type === 'motorcycle' && $item->end_time)
                                            <br><small class="text-muted">{{ $item->end_time }}</small>
                                        @endif
                                    </td>
                                    <td class="d-none d-lg-table-cell text-truncate" style="max-width: 100px;">
                                        {{ $item->user_id != null ? $item->user->email : $item->email_guest }}
                                    </td>
                                    <td class="d-none d-lg-table-cell">
                                        {{ $item->user_id != null ? $item->user->phone : $item->no_hp_guest }}
                                    </td>
                                    <td>
                                        @if (request('status') == "paid")
                                            <select class="form-select status-select2" data-reservation-id="{{ $item->id }}">
                                                <option value="paid"     {{ $item->status == 'paid'     ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                                                <option value="ongoing"  {{ $item->status == 'ongoing'  ? 'selected' : '' }}>Berlangsung</option>
                                                <option value="returned" {{ $item->status == 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                                            </select>
                                        @elseif (request('status') == "ongoing")
                                            <select class="form-select status-select2" data-reservation-id="{{ $item->id }}">
                                                <option value="ongoing"  {{ $item->status == 'ongoing'  ? 'selected' : '' }}>Berlangsung</option>
                                                <option value="returned" {{ $item->status == 'returned' ? 'selected' : '' }}>Dikembalikan</option>
                                            </select>
                                        @else
                                            <span class="badge bg-success">Selesai</span>
                                        @endif
                                    </td>
                                     <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-outline-primary" onclick="detail({{ $item->id }})">
                                                <i class="bi bi-info-circle-fill"></i>
                                            </button>
                                            @php
                                                $canShowTrackingButton = in_array($item->status, ['ongoing'])
                                                    && ($item->vehicle && $item->vehicle->status === 'rented')
                                                    || (
                                                        $item->start_date && $item->end_date
                                                        && now()->between($item->start_date, $item->end_date)
                                                    );
                                            @endphp
                                            @if($canShowTrackingButton)
                                                <button type="button" class="btn btn-outline-success" onclick="getTracking({{ $item->id }})" title="Lacak">
                                                    <i class="bi bi-geo-fill"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-end mt-3">
                        {{ $rentals->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- Detail Modal --}}
    <div class="modal modal-lg fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalTitle"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="contentModal"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x d-block d-sm-none"></i>
                        <span class="d-none d-sm-block">Close</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            $(".status-select2").select2({
                theme: "bootstrap4",
            });

            $('.status-select2').on('change', function () {
                var status        = $(this).val();
                var reservationId = $(this).data('reservation-id');
                updateStatus(reservationId, status);
            });
        });

        function detail(id) {
            $.ajax({
                type: "GET",
                url: "/admin/rentals/" + id,
                success: function (response) {
                    $('#detailModalTitle').text('Detail Penyewaan');
                    $('#contentModal').html(response);
                    $('#detailModal').modal('show');
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching detail:', error);
                    alert('Gagal memuat detail. Silakan coba lagi.');
                }
            });
        }

        function updateStatus(reservationId, status) {
            Swal.fire({
                title: "Mohon tunggu",
                text: "Sedang memperbarui status...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('admin.rentals.update-status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: reservationId,
                    status: status,
                },
                success: function (response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil",
                            text: "Status berhasil diperbarui!",
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text: "Gagal memperbarui status!",
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function (xhr, status, error) {
                    Swal.close();
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Terjadi kesalahan. Silakan coba lagi."
                    });
                }
            });
        }
    </script>
    <style>
        /* small map in modal */
        #trackingMap { height: 320px; width: 100%; }
        .leaflet-div-icon.car-icon { background: transparent; border: none; }
        .leaflet-div-icon.car-icon div { transform: translateY(-2px); font-size:28px; }
    </style>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    <script>
        async function getTracking(rentalId) {
            try {
                const res = await fetch(`/admin/tracking/${rentalId}/current-location`);
                const json = await res.json();

                if (!json.success) {
                    Swal.fire({ icon: 'info', title: 'Info', text: json.message || 'Lokasi belum tersedia.'});
                    return;
                }

                const data = json.data;

                // populate modal
                $('#detailModalTitle').text('Tracking - ' + (data.rental_id || rentalId));
                const html = `
                    <div>
                        <p><strong>Latitude:</strong> ${data.latitude} &nbsp; <strong>Longitude:</strong> ${data.longitude}</p>
                        <p><strong>Alamat:</strong> ${data.address || '-'} &nbsp; <strong>Speed:</strong> ${data.speed || 0} km/h</p>
                        <div id="trackingMap"></div>
                    </div>`;

                $('#contentModal').html(html);
                $('#detailModal').modal('show');

                // init leaflet map with car icon
                setTimeout(() => {
                    try {
                        const lat = parseFloat(data.latitude);
                        const lng = parseFloat(data.longitude);
                        const map = L.map('trackingMap').setView([lat, lng], 14);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

                        const carIcon = L.divIcon({
                            html: '<div style="font-size:28px; line-height:28px;">🚗</div>',
                            className: 'leaflet-div-icon car-icon',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14]
                        });

                        L.marker([lat, lng], { icon: carIcon }).addTo(map).bindPopup('Posisi terakhir').openPopup();
                    } catch (e) {
                        console.error('Map init error', e);
                    }
                }, 300);

            } catch (e) {
                console.error(e);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal mengambil lokasi.'});
            }
        }
    </script>
@endpush