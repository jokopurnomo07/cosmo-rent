@extends('layouts.admin.app')
@section('title', 'Dashboard')

@section('content')
    <div class="page-heading">
        <h3>Dashboard</h3>
    </div>
    <div class="page-content">
        <section class="row">
            <div class="col-12 col-lg-12">

                {{-- Stats Cards --}}
                <div class="row">

                    {{-- Total Penyewaan Selesai --}}
                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon blue mb-2">
                                            <i class="iconly-boldProfile"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Penyewaan Selesai</h6>
                                        <h6 class="font-extrabold mb-0">{{ $totalRent }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Total Reservasi Confirmed --}}
                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="card">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon green mb-2">
                                            <i class="iconly-boldAdd-User"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Reservasi Dikonfirmasi</h6>
                                        <h6 class="font-extrabold mb-0">{{ $totalReservation }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Kendaraan Sedang Disewa (conditional) --}}
                    <div class="col-12 col-lg-3 col-md-6">
                        <div class="card {{ $activeRental ? 'border-warning' : '' }}">
                            <div class="card-body px-4 py-4-5">
                                <div class="row">
                                    <div class="col-md-4 col-lg-12 col-xl-12 col-xxl-5 d-flex justify-content-start">
                                        <div class="stats-icon {{ $activeRental ? 'orange' : 'purple' }} mb-2">
                                            <i class="iconly-boldShow"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-12 col-xl-12 col-xxl-7">
                                        <h6 class="text-muted font-semibold">Sedang Disewa</h6>
                                        <h6 class="font-extrabold mb-0">
                                            @if($activeRental)
                                                {{ $activeRental->vehicle->name ?? '-' }}
                                            @else
                                                <span class="text-muted" style="font-size: 0.85rem; font-weight: 500;">Tidak ada</span>
                                            @endif
                                        </h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Active Rental Detail (jika ada) --}}
                @if($activeRental)
                <div class="row mt-1">
                    <div class="col-12">
                        <div class="alert alert-warning d-flex align-items-center gap-2" role="alert">
                            <i class="iconly-boldSend me-2"></i>
                            <div>
                                Kamu sedang menyewa <strong>{{ $activeRental->vehicle->name ?? '-' }}</strong>.
                                Tanggal pengembalian: <strong>{{ \Carbon\Carbon::parse($activeRental->end_date)->format('d M Y, H:i') }}</strong>.
                                Status: 
                                <span class="badge
                                    @switch($activeRental->status)
                                        @case('ongoing') bg-primary @break
                                        @case('awaiting_confirmation') bg-warning text-dark @break
                                        @case('paid') bg-info @break
                                        @default bg-secondary
                                    @endswitch">
                                    {{ ucfirst(str_replace('_', ' ', $activeRental->status)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Notifikasi Belum Dibaca --}}
                @if($notifications->count() > 0)
                <div class="row mt-1">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    Notifikasi
                                    <span class="badge bg-danger ms-1">{{ $notifications->total() }}</span>
                                </h5>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    @foreach($notifications as $notif)
                                        <li class="list-group-item d-flex justify-content-between align-items-start py-3 px-4">
                                            <div>
                                                <p class="mb-0">{{ $notif->message ?? $notif->body ?? '-' }}</p>
                                                <small class="text-muted">{{ $notif->created_at->diffForHumans() }}</small>
                                            </div>
                                            <span class="badge bg-warning text-dark ms-3 mt-1">Baru</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Riwayat Penyewaan Terbaru --}}
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">Riwayat Penyewaan Terbaru</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped" id="table-rentals">
                                    <thead>
                                        <tr>
                                            <th>Kendaraan</th>
                                            <th>Tanggal Sewa</th>
                                            <th>Tanggal Kembali</th>
                                            <th>Total Harga</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentRentals as $rental)
                                            <tr>
                                                <td>{{ $rental->vehicle->name ?? '-' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($rental->start_date)->format('d M Y') }}</td>
                                                <td>{{ \Carbon\Carbon::parse($rental->end_date)->format('d M Y') }}</td>
                                                <td>Rp {{ number_format($rental->total_price, 0, ',', '.') }}</td>
                                                <td>
                                                    <span class="badge
                                                        @switch($rental->status)
                                                            @case('returned')
                                                            @case('completed')
                                                                bg-success
                                                                @break
                                                            @case('ongoing')
                                                                bg-primary
                                                                @break
                                                            @case('awaiting_confirmation')
                                                                bg-warning text-dark
                                                                @break
                                                            @case('payment_failed')
                                                                bg-danger
                                                                @break
                                                            @case('paid')
                                                                bg-info
                                                                @break
                                                            @default
                                                                bg-secondary
                                                        @endswitch">
                                                        {{ ucfirst(str_replace('_', ' ', $rental->status)) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    Belum ada riwayat penyewaan.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
@endsection